/**
 * ICHRI Tunisia - Cart Page JavaScript
 * Shopping cart management and checkout
 */

let cartData = null;
let appliedCoupon = null;

document.addEventListener('DOMContentLoaded', function() {
    // Load cart
    loadCart();
});

/**
 * Load Cart
 */
async function loadCart() {
    const container = document.getElementById('cartItems');

    try {
        HELPERS.showLoading(container);

        if (API.getToken()) {
            // Load cart from API for logged-in users
            cartData = await API.getCart();
        } else {
            // Load cart from localStorage for guests
            cartData = getLocalCartData();
        }

        if (cartData && cartData.items && cartData.items.length > 0) {
            renderCartItems(cartData.items, container);
            updateCartSummary();
        } else {
            renderEmptyCart(container);
        }
    } catch (error) {
        console.error('Error loading cart:', error);
        HELPERS.showError(container, 'Erreur lors du chargement du panier');
    }
}

/**
 * Get Local Cart Data
 */
function getLocalCartData() {
    const localCart = JSON.parse(localStorage.getItem(CONFIG.STORAGE_KEYS.CART) || '[]');

    // Convert local cart format to API format
    // In a real app, you'd fetch product details for each item
    return {
        items: localCart.map(item => ({
            id: item.id || Date.now(),
            product_id: item.product_id,
            quantity: item.quantity,
            variant_id: item.variant_id,
            product: {
                // This would be fetched from API in production
                id: item.product_id,
                name: 'Produit',
                price: 0,
                image: null
            }
        })),
        subtotal: 0,
        total: 0
    };
}

/**
 * Render Cart Items
 */
function renderCartItems(items, container) {
    container.innerHTML = items.map(item => {
        const product = item.product;
        const itemTotal = (item.price || product.price) * item.quantity;

        return `
            <div class="cart-item card mb-3" data-item-id="${item.id}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Product Image -->
                        <div class="col-md-2 col-3">
                            <a href="product-detail.html?id=${product.id}">
                                <img src="${HELPERS.getImageUrl(product.image)}"
                                     alt="${product.name}"
                                     class="img-fluid rounded">
                            </a>
                        </div>

                        <!-- Product Info -->
                        <div class="col-md-4 col-9">
                            <a href="product-detail.html?id=${product.id}" class="text-decoration-none">
                                <h6 class="mb-2">${product.name}</h6>
                            </a>
                            ${item.variant ? `
                                <small class="text-muted d-block">
                                    ${Object.entries(item.variant.attributes || {}).map(([key, value]) =>
                                        `${key}: ${value}`
                                    ).join(', ')}
                                </small>
                            ` : ''}
                            <small class="text-muted">SKU: ${product.sku || 'N/A'}</small>
                        </div>

                        <!-- Price -->
                        <div class="col-md-2 col-4">
                            <div class="fw-bold">${HELPERS.formatPrice(item.price || product.price)}</div>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-2 col-4">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="updateItemQuantity(${item.id}, ${item.quantity - 1})">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center"
                                       value="${item.quantity}" min="1" max="${product.stock || 99}"
                                       onchange="updateItemQuantity(${item.id}, this.value)">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="updateItemQuantity(${item.id}, ${item.quantity + 1})">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="col-md-1 col-3">
                            <div class="fw-bold text-primary">${HELPERS.formatPrice(itemTotal)}</div>
                        </div>

                        <!-- Remove -->
                        <div class="col-md-1 col-1">
                            <button class="btn btn-sm btn-outline-danger" onclick="removeCartItem(${item.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Render Empty Cart
 */
function renderEmptyCart(container) {
    container.innerHTML = `
        <div class="empty-cart text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted mb-3"></i>
            <h3 class="mb-3">Votre panier est vide</h3>
            <p class="text-muted mb-4">Commencez vos achats dès maintenant et découvrez nos produits !</p>
            <a href="products.html" class="btn btn-primary btn-lg">
                <i class="bi bi-shop me-2"></i> Parcourir les produits
            </a>
        </div>
    `;

    // Hide checkout button
    document.getElementById('checkoutBtn').disabled = true;
}

/**
 * Update Item Quantity
 */
async function updateItemQuantity(itemId, newQuantity) {
    newQuantity = parseInt(newQuantity);

    if (newQuantity < 1) {
        removeCartItem(itemId);
        return;
    }

    try {
        if (API.getToken()) {
            await API.updateCartItem(itemId, newQuantity);
        } else {
            // Update local storage
            const localCart = JSON.parse(localStorage.getItem(CONFIG.STORAGE_KEYS.CART) || '[]');
            const itemIndex = localCart.findIndex(item => item.id === itemId);
            if (itemIndex !== -1) {
                localCart[itemIndex].quantity = newQuantity;
                localStorage.setItem(CONFIG.STORAGE_KEYS.CART, JSON.stringify(localCart));
            }
        }

        // Reload cart
        loadCart();
        updateCartCount();
        HELPERS.showToast('Panier mis à jour', 'success');
    } catch (error) {
        console.error('Error updating cart item:', error);
        HELPERS.showToast('Erreur lors de la mise à jour', 'danger');
    }
}

/**
 * Remove Cart Item
 */
async function removeCartItem(itemId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
        return;
    }

    try {
        if (API.getToken()) {
            await API.removeFromCart(itemId);
        } else {
            // Remove from local storage
            let localCart = JSON.parse(localStorage.getItem(CONFIG.STORAGE_KEYS.CART) || '[]');
            localCart = localCart.filter(item => item.id !== itemId);
            localStorage.setItem(CONFIG.STORAGE_KEYS.CART, JSON.stringify(localCart));
        }

        // Reload cart
        loadCart();
        updateCartCount();
        HELPERS.showToast('Article supprimé du panier', 'success');
    } catch (error) {
        console.error('Error removing cart item:', error);
        HELPERS.showToast('Erreur lors de la suppression', 'danger');
    }
}

/**
 * Update Cart Summary
 */
function updateCartSummary() {
    if (!cartData || !cartData.items || cartData.items.length === 0) {
        return;
    }

    // Calculate subtotal
    let subtotal = 0;
    cartData.items.forEach(item => {
        const price = item.price || item.product.price;
        subtotal += price * item.quantity;
    });

    // Calculate discount
    let discount = 0;
    if (appliedCoupon) {
        if (appliedCoupon.type === 'percentage') {
            discount = (subtotal * appliedCoupon.value) / 100;
        } else if (appliedCoupon.type === 'fixed') {
            discount = appliedCoupon.value;
        }
        document.getElementById('discountRow').style.display = 'flex';
        document.getElementById('discount').textContent = '-' + HELPERS.formatPrice(discount);
    } else {
        document.getElementById('discountRow').style.display = 'none';
    }

    // Calculate tax (19% TVA in Tunisia)
    const taxRate = 0.19;
    const tax = (subtotal - discount) * taxRate;

    // Shipping (will be calculated at checkout based on location)
    const shipping = 0; // TBD

    // Calculate total
    const total = subtotal - discount + tax + shipping;

    // Update UI
    document.getElementById('subtotal').textContent = HELPERS.formatPrice(subtotal);
    document.getElementById('tax').textContent = HELPERS.formatPrice(tax);
    document.getElementById('total').textContent = HELPERS.formatPrice(total);

    // Show free shipping progress
    const freeShippingThreshold = 150;
    if (subtotal < freeShippingThreshold) {
        const remaining = freeShippingThreshold - subtotal;
        document.getElementById('shipping').textContent = HELPERS.formatPrice(7); // Base shipping cost
    } else {
        document.getElementById('shipping').textContent = 'Gratuit';
    }
}

/**
 * Apply Coupon
 */
async function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim();
    const messageDiv = document.getElementById('couponMessage');

    if (!couponCode) {
        messageDiv.innerHTML = '<small class="text-danger">Veuillez entrer un code promo</small>';
        return;
    }

    try {
        if (API.getToken()) {
            const result = await API.applyCoupon(couponCode);
            appliedCoupon = result.coupon;

            messageDiv.innerHTML = `
                <small class="text-success">
                    <i class="bi bi-check-circle me-1"></i>
                    Code promo appliqué : ${result.coupon.discount}% de réduction
                </small>
            `;

            updateCartSummary();
            HELPERS.showToast('Code promo appliqué avec succès', 'success');
        } else {
            // For guests, simulate coupon validation
            // In production, you'd validate via API without authentication
            messageDiv.innerHTML = '<small class="text-warning">Connectez-vous pour utiliser un code promo</small>';
        }
    } catch (error) {
        messageDiv.innerHTML = `<small class="text-danger">${error.message || 'Code promo invalide'}</small>`;
    }
}

/**
 * Proceed to Checkout
 */
function proceedToCheckout() {
    if (!cartData || !cartData.items || cartData.items.length === 0) {
        HELPERS.showToast('Votre panier est vide', 'warning');
        return;
    }

    if (!API.getToken()) {
        // Redirect to login with return URL
        HELPERS.showToast('Veuillez vous connecter pour passer commande', 'warning');
        setTimeout(() => {
            window.location.href = 'login.html?redirect=' + encodeURIComponent('checkout.html');
        }, 1500);
        return;
    }

    // Proceed to checkout
    window.location.href = 'checkout.html';
}

// Export functions for global use
window.updateItemQuantity = updateItemQuantity;
window.removeCartItem = removeCartItem;
window.applyCoupon = applyCoupon;
window.proceedToCheckout = proceedToCheckout;
