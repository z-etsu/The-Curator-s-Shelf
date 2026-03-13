// Main JavaScript functionality

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

// Add to cart
function addToCart(productId, quantity = 1) {
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
