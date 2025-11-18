/**
 * ICHRI Tunisia - Product Detail Page JavaScript
 * Product details, variants, reviews, and related products
 */

let productId = null;
let currentProduct = null;
let selectedVariant = null;
let selectedQuantity = 1;
let selectedRating = 5;

document.addEventListener('DOMContentLoaded', function() {
    // Get product ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    productId = urlParams.get('id');

    if (!productId) {
        HELPERS.showToast('Produit non trouvé', 'danger');
        setTimeout(() => window.location.href = 'products.html', 2000);
        return;
    }

    // Load product details
    loadProductDetail();

    // Setup star rating input
    setupStarRatingInput();

    // Setup review form
    setupReviewForm();
});

/**
 * Load Product Detail
 */
async function loadProductDetail() {
    const container = document.getElementById('productDetailContainer');

    try {
        HELPERS.showLoading(container);

        currentProduct = await API.getProduct(productId);

        if (currentProduct) {
            renderProductDetail(currentProduct, container);
            updateBreadcrumb(currentProduct);
            loadProductReviews();
            loadRelatedProducts(currentProduct.category_id);
            trackProductView(productId);
        } else {
            HELPERS.showError(container, 'Produit non trouvé');
        }
    } catch (error) {
        console.error('Error loading product:', error);
        HELPERS.showError(container, 'Erreur lors du chargement du produit');
    }
}

/**
 * Render Product Detail
 */
function renderProductDetail(product, container) {
    const discount = product.compare_price
        ? HELPERS.calculateDiscount(product.compare_price, product.price)
        : 0;
    const hasDiscount = discount > 0;
    const inStock = product.stock > 0;

    // Prepare images for gallery
    const images = [product.image, ...(product.images || [])];

    container.innerHTML = `
        <!-- Product Images -->
        <div class="col-lg-6">
            <div class="product-images-section">
                <!-- Main Image Slider -->
                <div class="swiper productMainSwiper mb-3">
                    <div class="swiper-wrapper">
                        ${images.map(img => `
                            <div class="swiper-slide">
                                <div class="product-main-image">
                                    <img src="${HELPERS.getImageUrl(img)}" alt="${product.name}" class="img-fluid rounded">
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>

                <!-- Thumbnails Slider -->
                ${images.length > 1 ? `
                    <div class="swiper productThumbsSwiper">
                        <div class="swiper-wrapper">
                            ${images.map(img => `
                                <div class="swiper-slide">
                                    <div class="product-thumb-image">
                                        <img src="${HELPERS.getImageUrl(img)}" alt="${product.name}" class="img-fluid rounded">
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                ` : ''}
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info-section">
                <!-- Product Title -->
                <h1 class="product-title mb-3">${product.name}</h1>

                <!-- Product Rating -->
                ${product.average_rating ? `
                    <div class="product-rating mb-3">
                        ${HELPERS.generateStars(product.average_rating)}
                        <span class="ms-2">${product.average_rating.toFixed(1)}</span>
                        <span class="text-muted ms-2">(${product.reviews_count || 0} avis)</span>
                    </div>
                ` : ''}

                <!-- Product Price -->
                <div class="product-price-section mb-4">
                    ${hasDiscount ? `
                        <span class="badge bg-danger me-2">-${discount}%</span>
                        <h3 class="d-inline-block mb-0">${HELPERS.formatPrice(product.price)}</h3>
                        <span class="text-muted text-decoration-line-through ms-2">${HELPERS.formatPrice(product.compare_price)}</span>
                    ` : `
                        <h3 class="mb-0">${HELPERS.formatPrice(product.price)}</h3>
                    `}
                </div>

                <!-- Product Description (Short) -->
                <div class="product-short-description mb-4">
                    <p class="text-muted">${HELPERS.truncate(product.description || '', 200)}</p>
                </div>

                <!-- Product Variants -->
                ${product.variants && product.variants.length > 0 ? renderVariants(product.variants) : ''}

                <!-- Quantity Selector -->
                <div class="quantity-section mb-4">
                    <label class="form-label fw-bold">Quantité:</label>
                    <div class="input-group" style="max-width: 150px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="quantityInput" value="1" min="1" max="${product.stock || 1}">
                        <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                    ${inStock ? `
                        <small class="text-success d-block mt-2">
                            <i class="bi bi-check-circle"></i> ${product.stock} en stock
                        </small>
                    ` : `
                        <small class="text-danger d-block mt-2">
                            <i class="bi bi-x-circle"></i> Rupture de stock
                        </small>
                    `}
                </div>

                <!-- Action Buttons -->
                <div class="product-actions d-flex gap-3 mb-4">
                    ${inStock ? `
                        <button class="btn btn-primary btn-lg flex-grow-1" onclick="addProductToCart()">
                            <i class="bi bi-cart-plus me-2"></i> Ajouter au panier
                        </button>
                    ` : `
                        <button class="btn btn-secondary btn-lg flex-grow-1" disabled>
                            <i class="bi bi-x-circle me-2"></i> Indisponible
                        </button>
                    `}
                    <button class="btn btn-outline-danger btn-lg" onclick="addToWishlist(${product.id})">
                        <i class="bi bi-heart"></i>
                    </button>
                </div>

                <!-- Product Meta -->
                <div class="product-meta">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>SKU:</strong> <span class="text-muted">${product.sku || 'N/A'}</span>
                        </li>
                        <li class="mb-2">
                            <strong>Catégorie:</strong>
                            <a href="products.html?category=${product.category_id}" class="text-primary">
                                ${product.category?.name || 'N/A'}
                            </a>
                        </li>
                        ${product.vendor ? `
                            <li class="mb-2">
                                <strong>Vendeur:</strong> <span class="text-muted">${product.vendor.name}</span>
                            </li>
                        ` : ''}
                    </ul>
                </div>

                <!-- Share Buttons -->
                <div class="product-share mt-4">
                    <strong class="d-block mb-2">Partager:</strong>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-primary" onclick="shareProduct('facebook'); return false;">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-info" onclick="shareProduct('twitter'); return false;">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-success" onclick="shareProduct('whatsapp'); return false;">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyProductLink()">
                            <i class="bi bi-link-45deg"></i> Copier le lien
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Initialize image sliders
    initProductImageSliders();

    // Set product description in tab
    document.getElementById('productDescription').innerHTML = `
        <div class="product-full-description">
            ${product.description || '<p class="text-muted">Aucune description disponible.</p>'}
        </div>
    `;
}

/**
 * Render Product Variants
 */
function renderVariants(variants) {
    // Group variants by attributes
    const attributes = {};

    variants.forEach(variant => {
        if (variant.attributes) {
            Object.entries(variant.attributes).forEach(([key, value]) => {
                if (!attributes[key]) attributes[key] = new Set();
                attributes[key].add(value);
            });
        }
    });

    let html = '<div class="product-variants mb-4">';

    Object.entries(attributes).forEach(([attrName, values]) => {
        html += `
            <div class="variant-group mb-3">
                <label class="form-label fw-bold">${attrName}:</label>
                <div class="variant-options d-flex gap-2 flex-wrap">
        `;

        Array.from(values).forEach(value => {
            html += `
                <button class="btn btn-outline-primary variant-option"
                        onclick="selectVariantOption('${attrName}', '${value}')">
                    ${value}
                </button>
            `;
        });

        html += `
                </div>
            </div>
        `;
    });

    html += '</div>';
    return html;
}

/**
 * Initialize Product Image Sliders
 */
function initProductImageSliders() {
    // Thumbnails slider
    const thumbsSwiper = new Swiper('.productThumbsSwiper', {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesProgress: true,
    });

    // Main slider
    new Swiper('.productMainSwiper', {
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        thumbs: {
            swiper: thumbsSwiper,
        },
    });
}

/**
 * Load Product Reviews
 */
async function loadProductReviews() {
    try {
        const reviews = await API.getProductReviews(productId);

        if (reviews && reviews.data) {
            renderReviewsSummary(reviews);
            renderReviewsList(reviews.data);
            document.getElementById('reviewsCountBadge').textContent = reviews.total || 0;
        }
    } catch (error) {
        console.error('Error loading reviews:', error);
    }
}

/**
 * Render Reviews Summary
 */
function renderReviewsSummary(reviews) {
    const container = document.getElementById('reviewsSummary');

    if (!reviews.total) {
        container.innerHTML = '<p class="text-muted">Aucun avis pour ce produit.</p>';
        return;
    }

    const avgRating = reviews.average_rating || 0;

    container.innerHTML = `
        <div class="row align-items-center">
            <div class="col-md-4 text-center">
                <h2 class="display-4 mb-0">${avgRating.toFixed(1)}</h2>
                <div class="rating-stars my-2">
                    ${HELPERS.generateStars(avgRating)}
                </div>
                <p class="text-muted">${reviews.total} avis</p>
            </div>
            <div class="col-md-8">
                ${[5, 4, 3, 2, 1].map(rating => {
                    const count = reviews.rating_distribution?.[rating] || 0;
                    const percentage = reviews.total ? (count / reviews.total * 100) : 0;

                    return `
                        <div class="d-flex align-items-center mb-2">
                            <span class="me-2">${rating} <i class="bi bi-star-fill text-warning"></i></span>
                            <div class="progress flex-grow-1" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: ${percentage}%"></div>
                            </div>
                            <span class="ms-2 text-muted">${count}</span>
                        </div>
                    `;
                }).join('')}
            </div>
        </div>
    `;
}

/**
 * Render Reviews List
 */
function renderReviewsList(reviews) {
    const container = document.getElementById('reviewsList');

    if (!reviews || reviews.length === 0) {
        return;
    }

    container.innerHTML = `
        <h5 class="mb-3">Avis des clients</h5>
        ${reviews.map(review => `
            <div class="review-item border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong>${review.user?.name || 'Anonyme'}</strong>
                        <div class="rating-stars">
                            ${HELPERS.generateStars(review.rating)}
                        </div>
                    </div>
                    <small class="text-muted">${HELPERS.formatDate(review.created_at)}</small>
                </div>
                ${review.title ? `<h6 class="mb-2">${review.title}</h6>` : ''}
                <p class="mb-0">${review.comment}</p>
            </div>
        `).join('')}
    `;
}

/**
 * Setup Star Rating Input
 */
function setupStarRatingInput() {
    const stars = document.querySelectorAll('#starRatingInput i');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.getAttribute('data-rating'));
            document.getElementById('ratingValue').value = selectedRating;

            // Update stars display
            stars.forEach(s => {
                const rating = parseInt(s.getAttribute('data-rating'));
                if (rating <= selectedRating) {
                    s.classList.remove('bi-star');
                    s.classList.add('bi-star-fill', 'text-warning');
                } else {
                    s.classList.remove('bi-star-fill', 'text-warning');
                    s.classList.add('bi-star');
                }
            });
        });
    });

    // Initialize with 5 stars
    stars.forEach(s => s.classList.add('bi-star-fill', 'text-warning'));
}

/**
 * Setup Review Form
 */
function setupReviewForm() {
    const form = document.getElementById('reviewForm');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!API.getToken()) {
            HELPERS.showToast('Veuillez vous connecter pour laisser un avis', 'warning');
            return;
        }

        const title = document.getElementById('reviewTitle').value;
        const comment = document.getElementById('reviewComment').value;
        const rating = parseInt(document.getElementById('ratingValue').value);

        try {
            await API.createReview(productId, rating, title, comment);
            HELPERS.showToast('Votre avis a été soumis avec succès', 'success');

            // Reset form
            form.reset();
            selectedRating = 5;

            // Reload reviews
            loadProductReviews();
        } catch (error) {
            HELPERS.showToast(error.message || 'Erreur lors de la soumission', 'danger');
        }
    });
}

/**
 * Load Related Products
 */
async function loadRelatedProducts(categoryId) {
    const container = document.getElementById('relatedProducts');

    try {
        const products = await API.getProducts({
            category: categoryId,
            per_page: 4,
            exclude: productId
        });

        if (products && products.data && products.data.length > 0) {
            container.innerHTML = products.data.map(product =>
                createProductCard(product)
            ).join('');
        } else {
            container.innerHTML = '<p class="text-muted">Aucun produit similaire.</p>';
        }
    } catch (error) {
        console.error('Error loading related products:', error);
    }
}

/**
 * Track Product View
 */
async function trackProductView(productId) {
    try {
        await API.trackProductView(productId);
    } catch (error) {
        console.error('Error tracking product view:', error);
    }
}

/**
 * Update Breadcrumb
 */
function updateBreadcrumb(product) {
    const breadcrumb = document.getElementById('breadcrumb');

    breadcrumb.innerHTML = `
        <li class="breadcrumb-item"><a href="../index.html">Accueil</a></li>
        <li class="breadcrumb-item"><a href="products.html">Produits</a></li>
        ${product.category ? `
            <li class="breadcrumb-item">
                <a href="products.html?category=${product.category.id}">${product.category.name}</a>
            </li>
        ` : ''}
        <li class="breadcrumb-item active" aria-current="page">${HELPERS.truncate(product.name, 50)}</li>
    `;
}

/**
 * Increase Quantity
 */
function increaseQuantity() {
    const input = document.getElementById('quantityInput');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);

    if (current < max) {
        input.value = current + 1;
        selectedQuantity = current + 1;
    }
}

/**
 * Decrease Quantity
 */
function decreaseQuantity() {
    const input = document.getElementById('quantityInput');
    const current = parseInt(input.value);

    if (current > 1) {
        input.value = current - 1;
        selectedQuantity = current - 1;
    }
}

/**
 * Select Variant Option
 */
function selectVariantOption(attribute, value) {
    // Update UI
    document.querySelectorAll('.variant-option').forEach(btn => {
        if (btn.textContent.trim() === value) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Find matching variant
    // This is simplified - in production, you'd need to match all selected attributes
    selectedVariant = currentProduct.variants.find(v =>
        v.attributes && Object.values(v.attributes).includes(value)
    );
}

/**
 * Add Product to Cart
 */
async function addProductToCart() {
    const quantity = parseInt(document.getElementById('quantityInput').value);
    const variantId = selectedVariant?.id || null;

    await addToCart(productId, quantity, variantId);
}

/**
 * Share Product
 */
function shareProduct(platform) {
    const url = window.location.href;
    const title = currentProduct?.name || 'ICHRI Tunisia';

    const shareUrls = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
        twitter: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`,
        whatsapp: `https://wa.me/?text=${encodeURIComponent(title + ' - ' + url)}`
    };

    if (shareUrls[platform]) {
        window.open(shareUrls[platform], '_blank', 'width=600,height=400');
    }
}

/**
 * Copy Product Link
 */
function copyProductLink() {
    HELPERS.copyToClipboard(window.location.href);
}

// Export functions for global use
window.increaseQuantity = increaseQuantity;
window.decreaseQuantity = decreaseQuantity;
window.selectVariantOption = selectVariantOption;
window.addProductToCart = addProductToCart;
window.shareProduct = shareProduct;
window.copyProductLink = copyProductLink;
