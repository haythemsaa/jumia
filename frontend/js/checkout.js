/**
 * ICHRI Tunisia - Checkout Page JavaScript
 * Multi-step checkout process
 */

let currentStep = 1;
let cartData = null;
let shippingMethods = [];
let selectedShippingMethod = null;
let checkoutData = {
    shipping: {},
    shippingMethodId: null,
    paymentMethod: 'cash'
};

document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    if (!API.getToken()) {
        HELPERS.showToast('Veuillez vous connecter pour continuer', 'warning');
        setTimeout(() => {
            window.location.href = 'login.html?redirect=' + encodeURIComponent('checkout.html');
        }, 1500);
        return;
    }

    // Load governorates
    loadGovernorates();

    // Load cart
    loadCart();

    // Load user data
    loadUserData();

    // Setup form submission
    setupCheckoutForm();
});

/**
 * Load Governorates
 */
function loadGovernorates() {
    const select = document.getElementById('governorate');

    CONFIG.GOVERNORATES.forEach(gov => {
        const option = document.createElement('option');
        option.value = gov;
        option.textContent = gov;
        select.appendChild(option);
    });
}

/**
 * Load Cart
 */
async function loadCart() {
    try {
        cartData = await API.getCart();

        if (!cartData || !cartData.items || cartData.items.length === 0) {
            HELPERS.showToast('Votre panier est vide', 'warning');
            setTimeout(() => window.location.href = 'cart.html', 1500);
            return;
        }

        renderOrderSummary();
    } catch (error) {
        console.error('Error loading cart:', error);
        HELPERS.showToast('Erreur lors du chargement du panier', 'danger');
    }
}

/**
 * Load User Data
 */
async function loadUserData() {
    try {
        const user = await API.getUserProfile();

        if (user) {
            document.getElementById('firstName').value = user.first_name || '';
            document.getElementById('lastName').value = user.last_name || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('address').value = user.address || '';
            document.getElementById('governorate').value = user.governorate || '';
            document.getElementById('postalCode').value = user.postal_code || '';
        }
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

/**
 * Render Order Summary
 */
function renderOrderSummary() {
    if (!cartData) return;

    // Render items in sidebar
    const summaryContainer = document.getElementById('orderSummaryItems');
    summaryContainer.innerHTML = cartData.items.map(item => {
        const product = item.product;
        return `
            <div class="d-flex align-items-center mb-3">
                <img src="${HELPERS.getImageUrl(product.image)}" alt="${product.name}"
                     class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex-grow-1">
                    <small>${HELPERS.truncate(product.name, 30)}</small>
                    <div class="text-muted small">x${item.quantity}</div>
                </div>
                <span class="small">${HELPERS.formatPrice((item.price || product.price) * item.quantity)}</span>
            </div>
        `;
    }).join('');

    // Calculate and display totals
    updateOrderTotals();
}

/**
 * Update Order Totals
 */
function updateOrderTotals() {
    if (!cartData) return;

    // Calculate subtotal
    let subtotal = 0;
    cartData.items.forEach(item => {
        const price = item.price || item.product.price;
        subtotal += price * item.quantity;
    });

    // Shipping cost
    const shippingCost = selectedShippingMethod ? selectedShippingMethod.rate : 0;

    // Tax (19% TVA)
    const taxRate = 0.19;
    const tax = (subtotal + shippingCost) * taxRate;

    // Total
    const total = subtotal + shippingCost + tax;

    // Update UI
    document.getElementById('summarySubtotal').textContent = HELPERS.formatPrice(subtotal);
    document.getElementById('summaryShipping').textContent = shippingCost > 0
        ? HELPERS.formatPrice(shippingCost)
        : (selectedShippingMethod ? 'Gratuit' : 'À calculer');
    document.getElementById('summaryTax').textContent = HELPERS.formatPrice(tax);
    document.getElementById('summaryTotal').textContent = HELPERS.formatPrice(total);
}

/**
 * Load Shipping Methods
 */
async function loadShippingMethods(governorate) {
    const container = document.getElementById('shippingMethods');

    try {
        HELPERS.showLoading(container);

        shippingMethods = await API.getShippingMethods(governorate);

        if (shippingMethods && shippingMethods.length > 0) {
            container.innerHTML = shippingMethods.map((method, index) => `
                <div class="form-check shipping-option mb-3">
                    <input class="form-check-input" type="radio" name="shippingMethod"
                           id="shipping${method.id}" value="${method.id}"
                           ${index === 0 ? 'checked' : ''}
                           onchange="selectShippingMethod(${method.id})">
                    <label class="form-check-label" for="shipping${method.id}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${method.name}</strong>
                                <p class="text-muted mb-0">${method.description || ''}</p>
                                ${method.estimated_days_min ? `
                                    <small class="text-muted">
                                        Livraison en ${method.estimated_days_min}-${method.estimated_days_max} jours
                                    </small>
                                ` : ''}
                            </div>
                            <div class="text-end">
                                <strong>${HELPERS.formatPrice(method.rate)}</strong>
                            </div>
                        </div>
                    </label>
                </div>
            `).join('');

            // Select first method by default
            selectShippingMethod(shippingMethods[0].id);
        } else {
            container.innerHTML = '<p class="text-muted">Aucune méthode de livraison disponible pour cette région.</p>';
        }
    } catch (error) {
        console.error('Error loading shipping methods:', error);
        HELPERS.showError(container, 'Erreur lors du chargement des méthodes de livraison');
    }
}

/**
 * Select Shipping Method
 */
function selectShippingMethod(methodId) {
    selectedShippingMethod = shippingMethods.find(m => m.id === methodId);
    checkoutData.shippingMethodId = methodId;
    updateOrderTotals();
}

/**
 * Next Step
 */
function nextStep() {
    // Validate current step
    if (!validateStep(currentStep)) {
        return;
    }

    // Move to next step
    currentStep++;
    updateStepDisplay();

    // Special actions for specific steps
    if (currentStep === 2) {
        // Load shipping methods based on selected governorate
        const governorate = document.getElementById('governorate').value;
        loadShippingMethods(governorate);
    } else if (currentStep === 4) {
        // Display order review
        displayOrderReview();
    }

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Previous Step
 */
function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepDisplay();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

/**
 * Update Step Display
 */
function updateStepDisplay() {
    // Update step indicators
    document.querySelectorAll('.step-item').forEach((item, index) => {
        if (index + 1 < currentStep) {
            item.classList.add('completed');
            item.classList.remove('active');
        } else if (index + 1 === currentStep) {
            item.classList.add('active');
            item.classList.remove('completed');
        } else {
            item.classList.remove('active', 'completed');
        }
    });

    // Update step content
    document.querySelectorAll('.checkout-step').forEach(step => {
        const stepNum = parseInt(step.getAttribute('data-step'));
        step.style.display = stepNum === currentStep ? 'block' : 'none';
    });
}

/**
 * Validate Step
 */
function validateStep(step) {
    if (step === 1) {
        // Validate shipping information
        const form = document.getElementById('checkoutForm');
        const requiredFields = form.querySelectorAll('[data-step="1"] [required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (isValid) {
            // Save shipping data
            checkoutData.shipping = {
                first_name: document.getElementById('firstName').value,
                last_name: document.getElementById('lastName').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                governorate: document.getElementById('governorate').value,
                postal_code: document.getElementById('postalCode').value,
                notes: document.getElementById('notes').value
            };
        } else {
            HELPERS.showToast('Veuillez remplir tous les champs obligatoires', 'danger');
        }

        return isValid;
    } else if (step === 2) {
        // Validate shipping method
        if (!selectedShippingMethod) {
            HELPERS.showToast('Veuillez sélectionner une méthode de livraison', 'danger');
            return false;
        }
        return true;
    } else if (step === 3) {
        // Validate payment method
        checkoutData.paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        return true;
    }

    return true;
}

/**
 * Display Order Review
 */
function displayOrderReview() {
    // Display order items
    const itemsContainer = document.getElementById('orderReviewItems');
    itemsContainer.innerHTML = cartData.items.map(item => {
        const product = item.product;
        return `
            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                <img src="${HELPERS.getImageUrl(product.image)}" alt="${product.name}"
                     class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${product.name}</h6>
                    <small class="text-muted">Quantité: ${item.quantity}</small>
                </div>
                <div class="text-end">
                    <strong>${HELPERS.formatPrice((item.price || product.price) * item.quantity)}</strong>
                </div>
            </div>
        `;
    }).join('');

    // Display shipping address
    document.getElementById('reviewShippingAddress').innerHTML = `
        ${checkoutData.shipping.first_name} ${checkoutData.shipping.last_name}<br>
        ${checkoutData.shipping.address}<br>
        ${checkoutData.shipping.governorate}, ${checkoutData.shipping.postal_code}<br>
        ${checkoutData.shipping.phone}
    `;

    // Display shipping method
    document.getElementById('reviewShippingMethod').textContent =
        selectedShippingMethod ? selectedShippingMethod.name : 'N/A';

    // Display payment method
    const paymentMethodNames = {
        cash: 'Paiement à la livraison',
        edinar: 'E-Dinar',
        konnect: 'Konnect',
        d17: 'D17'
    };
    document.getElementById('reviewPaymentMethod').textContent =
        paymentMethodNames[checkoutData.paymentMethod] || checkoutData.paymentMethod;
}

/**
 * Setup Checkout Form
 */
function setupCheckoutForm() {
    const form = document.getElementById('checkoutForm');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validate terms acceptance
        if (!document.getElementById('acceptTerms').checked) {
            HELPERS.showToast('Veuillez accepter les conditions générales', 'danger');
            return;
        }

        // Submit order
        await submitOrder();
    });
}

/**
 * Submit Order
 */
async function submitOrder() {
    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Traitement...';

        // Prepare order data
        const orderData = {
            ...checkoutData.shipping,
            shipping_method_id: checkoutData.shippingMethodId,
            payment_method: checkoutData.paymentMethod
        };

        // Create order
        const order = await API.createOrder(orderData);

        if (order) {
            // Handle payment redirect if needed
            if (order.payment_url) {
                HELPERS.showToast('Redirection vers le paiement...', 'info');
                setTimeout(() => {
                    window.location.href = order.payment_url;
                }, 1500);
            } else {
                // Redirect to success page
                HELPERS.showToast('Commande créée avec succès !', 'success');
                setTimeout(() => {
                    window.location.href = `order-success.html?id=${order.id}`;
                }, 1500);
            }
        }
    } catch (error) {
        console.error('Error submitting order:', error);
        HELPERS.showToast(error.message || 'Erreur lors de la création de la commande', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Export functions for global use
window.nextStep = nextStep;
window.previousStep = previousStep;
window.selectShippingMethod = selectShippingMethod;
