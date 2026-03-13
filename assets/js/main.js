// Main JavaScript functionality

// Toggle user dropdown menu
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-menu');
    const dropdown = document.getElementById('userDropdown');
    
    if (userMenu && dropdown && !userMenu.contains(event.target)) {
        dropdown.classList.remove('active');
    }
});

// Update cart count in navbar
function updateCartCount() {
    fetch('/cart/get-count.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.getElementById('cartCount');
            if (cartCount) {
                cartCount.textContent = data.count || 0;
            }
        })
        .catch(error => console.log('Cart count fetch error:', error));
}

// Check if user is logged in
function isUserLoggedIn() {
    const userIdMeta = document.querySelector('meta[data-user-id]');
    return userIdMeta && userIdMeta.getAttribute('data-user-id') !== '';
}

// Show login required modal
function showLoginModal() {
    const modal = document.createElement('div');
    modal.className = 'login-modal-overlay';
    modal.innerHTML = `
        <div class="login-modal">
            <div class="login-modal-content">
                <h3>Login Required</h3>
                <p>Please log in or create an account to continue shopping.</p>
                <div class="login-modal-buttons">
                    <button class="btn" onclick="window.location.href='auth/login.php'; return false;">Login</button>
                    <button class="btn btn-secondary" onclick="window.location.href='auth/register.php'; return false;">Register</button>
                </div>
                <button class="login-modal-close" onclick="this.closest('.login-modal-overlay').remove()">&times;</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Add to cart
function addToCart(productId, quantity = 1) {
    if (!isUserLoggedIn()) {
        showLoginModal();
        return false;
    }

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/cart/add.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to cart!');
                updateCartCount();
            } else {
                alert('Error adding to cart: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding to cart');
        });
}

// Buy now - adds to cart and redirects to checkout
function buyNow(productId, quantity = 1) {
    if (!isUserLoggedIn()) {
        showLoginModal();
        return false;
    }

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/cart/add.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/checkout/index.php';
            } else {
                alert('Error adding to cart: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding to cart');
        });
}

// Remove from cart
function removeFromCart(cartItemId) {
    const formData = new FormData();
    formData.append('cart_item_id', cartItemId);

    fetch('/cart/remove.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error removing from cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing from cart');
        });
}

// Update quantity in cart
function updateCartQuantity(cartItemId, quantity) {
    if (quantity < 1) return;

    const formData = new FormData();
    formData.append('cart_item_id', cartItemId);
    formData.append('quantity', quantity);

    fetch('/cart/update.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating quantity');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating quantity');
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
    initCategoriesCarousel();
});

// Categories Carousel
function initCategoriesCarousel() {
    const items = document.querySelectorAll('.carousel-item');
    if (items.length === 0) return;

    let currentIndex = 0;

    function showItem(index) {
        items.forEach((item, i) => {
            item.classList.remove('active');
        });
        items[index].classList.add('active');
    }

    function nextItem() {
        currentIndex = (currentIndex + 1) % items.length;
        showItem(currentIndex);
    }

    // Show first item
    showItem(currentIndex);

    // Change item every 5 seconds
    setInterval(nextItem, 5000);
}
