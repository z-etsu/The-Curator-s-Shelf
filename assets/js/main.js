// Main JavaScript functionality

// Toggle user dropdown menu
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

// Show logout confirmation modal
function showLogoutConfirm() {
    const modal = document.createElement('div');
    modal.className = 'login-modal-overlay';
    modal.innerHTML = `
        <div class="login-modal">
            <div class="login-modal-content">
                <h3>Confirm Logout</h3>
                <p>Are you sure you want to log out?</p>
                <div class="login-modal-buttons">
                    <button class="btn" onclick="window.location.href='/CURATOR/auth/logout.php'; return false;">Yes, Logout</button>
                    <button class="btn btn-secondary" onclick="this.closest('.login-modal-overlay').remove(); return false;">Cancel</button>
                </div>
                <button class="login-modal-close" onclick="this.closest('.login-modal-overlay').remove()">&times;</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
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
    fetch('/CURATOR/cart/get-count.php')
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
                    <button class="btn" onclick="window.location.href='/CURATOR/auth/login.php'; return false;">Login</button>
                    <button class="btn btn-secondary" onclick="window.location.href='/CURATOR/auth/register.php'; return false;">Register</button>
                </div>
                <button class="login-modal-close" onclick="this.closest('.login-modal-overlay').remove()">&times;</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Show generic confirmation modal
function showConfirmModal(title, message, confirmText, confirmCallback, cancelText = 'Cancel') {
    const modal = document.createElement('div');
    modal.className = 'login-modal-overlay';
    
    // Create a unique ID for this callback
    const callbackId = 'modalCallback_' + Math.random().toString(36).substr(2, 9);
    window[callbackId] = confirmCallback;
    
    modal.innerHTML = `
        <div class="login-modal">
            <div class="login-modal-content">
                <h3>${title}</h3>
                <p>${message}</p>
                <div class="login-modal-buttons">
                    <button class="btn" onclick="window['${callbackId}'](); this.closest('.login-modal-overlay').remove(); return false;">${confirmText}</button>
                    <button class="btn btn-secondary" onclick="this.closest('.login-modal-overlay').remove(); return false;">${cancelText}</button>
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

    // Show confirmation modal
    showConfirmModal(
        'Add to Cart',
        'Do you want to add this item to your cart?',
        'Yes, Add',
        function() {
            performAddToCart(productId, quantity);
        }
    );
}

// Perform the actual add to cart after confirmation
function performAddToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/CURATOR/cart/add.php', {
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

// Buy now - redirects directly to checkout with product details
function buyNow(productId, quantity = 1) {
    if (!isUserLoggedIn()) {
        showLoginModal();
        return false;
    }

    // Show confirmation modal
    showConfirmModal(
        'Buy Now',
        'Proceed to checkout with this item?',
        'Yes, Proceed',
        function() {
            performBuyNow(productId, quantity);
        }
    );
}

// Perform the actual buy now after confirmation
function performBuyNow(productId, quantity = 1) {
    // Redirect to checkout with product details as URL parameters
    window.location.href = '/CURATOR/checkout/index.php?product_id=' + productId + '&quantity=' + quantity;
}

// Remove from cart
function removeFromCart(cartItemId) {
    const formData = new FormData();
    formData.append('cart_item_id', cartItemId);

    fetch('/CURATOR/cart/remove.php', {
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

    fetch('/CURATOR/cart/update.php', {
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

// Category filtering
function filterByCategory(categoryId, tabElement) {
    // Update active tab
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    tabElement.classList.add('active');

    // Fetch filtered products
    fetch(`/CURATOR/products/get-products.php?category_id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const grid = document.getElementById('productsGrid');
                
                if (data.products.length === 0) {
                    grid.innerHTML = '<div class="empty-cart"><h2>No Products in This Category</h2></div>';
                    return;
                }

                // Build HTML for products
                let html = '';
                data.products.forEach(product => {
                    html += `
                        <div class="product-card" data-product-id="${product.id}" data-full-description="${product.full_description}" data-stock="${product.stock}" data-series="${product.series}">
                            <div class="product-image">
                                <img src="${product.image_url}" alt="${product.name}">
                            </div>
                            <div class="product-info">
                                <h4 class="product-name">${product.name}</h4>
                                <p class="product-description">${product.description}</p>
                                <p class="product-price">₱ ${product.price}</p>
                                <div class="product-actions">
                                    <button class="add-to-cart-btn" onclick="addToCart(${product.id})">Add to Cart</button>
                                    <a href="/CURATOR/products/detail.php?id=${product.id}" class="view-details-btn">Details</a>
                                </div>
                            </div>
                        </div>
                    `;
                });

                grid.innerHTML = html;

                // Reinitialize hover modals for new products
                initProductHoverModals();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Product Hover Modal Functions
let productHoverModal = null;
let modalHideTimeout = null;
let modalShowTimeout = null;

function showProductHoverModal(card, productData) {
    // Clear any pending timeouts
    if (modalHideTimeout) {
        clearTimeout(modalHideTimeout);
    }
    
    // If already showing, don't set another timeout
    if (productHoverModal && productHoverModal.classList.contains('active')) {
        return;
    }

    // Set delay before showing modal
    modalShowTimeout = setTimeout(function() {
        // Create modal if it doesn't exist
        if (!productHoverModal) {
            productHoverModal = document.createElement('div');
            productHoverModal.className = 'product-hover-modal';
            document.body.appendChild(productHoverModal);
        }

        // Populate modal with product data
        productHoverModal.innerHTML = `
            <div class="product-modal-image">
                <img src="${productData.image}" alt="${productData.name}">
            </div>
            <div class="product-modal-content">
                <h3 class="product-modal-name">${productData.name}</h3>
                <p class="product-modal-description">${productData.description}</p>
                <p class="product-modal-price">₱ ${productData.price}</p>
                <div class="product-modal-details">
                    <p><strong>Series:</strong> ${productData.series}</p>
                    <p><strong>Stock:</strong> ${productData.stock} available</p>
                </div>
            </div>
        `;

        // Position modal in front of the hovered product card
        const rect = card.getBoundingClientRect();
        const modal = productHoverModal;
        const modalWidth = 350;
        const modalHeight = 450;
        
        // Position to the right of the product card
        let left = rect.right + 15;
        let top = rect.top + rect.height / 2 - modalHeight / 2;
        
        // If too close to right edge, position to the left instead
        if (left + modalWidth > window.innerWidth - 10) {
            left = rect.left - modalWidth - 15;
        }
        
        // Adjust if goes off top/bottom
        if (top < 10) top = 10;
        if (top + modalHeight > window.innerHeight - 10) top = window.innerHeight - modalHeight - 10;

        modal.style.top = top + 'px';
        modal.style.left = left + 'px';
        modal.classList.add('active');

        // Keep modal visible when hovering over it
        modal.addEventListener('mouseenter', function() {
            if (modalHideTimeout) {
                clearTimeout(modalHideTimeout);
            }
        });

        modal.addEventListener('mouseleave', function() {
            hideProductHoverModal();
        });
    }, 1000); // 1 second delay
}

function hideProductHoverModal() {
    // Clear show timeout if modal hasn't appeared yet
    if (modalShowTimeout) {
        clearTimeout(modalShowTimeout);
        modalShowTimeout = null;
    }

    if (modalHideTimeout) {
        clearTimeout(modalHideTimeout);
    }

    modalHideTimeout = setTimeout(function() {
        if (productHoverModal) {
            productHoverModal.classList.remove('active');
        }
    }, 200);
}

function initProductHoverModals() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        // Get product data from card
        const name = card.querySelector('.product-name').textContent;
        const description = card.querySelector('.product-description').textContent;
        const price = card.querySelector('.product-price').textContent.replace('₱ ', '');
        const image = card.querySelector('.product-image img').src;
        const productId = card.dataset.productId;
        const stock = card.dataset.stock || 'N/A';
        const series = card.dataset.series || 'N/A';

        // Get full product data from data attributes
        const productData = {
            id: productId,
            name: name,
            description: card.dataset.fullDescription || description,
            price: price,
            image: image,
            stock: stock,
            series: series
        };

        card.addEventListener('mouseenter', function() {
            showProductHoverModal(card, productData);
        });

        card.addEventListener('mouseleave', function() {
            hideProductHoverModal();
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
    initCategoriesCarousel();
    initProductHoverModals();
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
