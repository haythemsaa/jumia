/**
 * ICHRI Tunisia - Homepage JavaScript
 * Homepage specific functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize hero slider
    initHeroSlider();

    // Initialize flash sales slider
    initFlashSalesSlider();

    // Load flash sales
    loadFlashSales();

    // Load trending products
    loadTrendingProducts();

    // Load popular categories
    loadPopularCategories();

    // Initialize newsletter form
    initNewsletterForm();

    // Start flash sale countdown
    startFlashSaleCountdown();
});

/**
 * Initialize Hero Slider
 */
function initHeroSlider() {
    new Swiper('.heroSwiper', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        }
    });
}

/**
 * Initialize Flash Sales Slider
 */
function initFlashSalesSlider() {
    new Swiper('.flashSalesSwiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            576: {
                slidesPerView: 2,
            },
            768: {
                slidesPerView: 3,
            },
            992: {
                slidesPerView: 4,
            },
            1200: {
                slidesPerView: 5,
            }
        }
    });
}

/**
 * Load Flash Sales
 */
async function loadFlashSales() {
    const container = document.getElementById('flashSalesProducts');
    if (!container) return;

    try {
        HELPERS.showLoading(container);

        const flashSales = await API.getFlashSales();

        if (flashSales && flashSales.length > 0) {
            // Get products from first active flash sale
            const activeFlashSale = flashSales[0];
            if (activeFlashSale.products && activeFlashSale.products.length > 0) {
                renderFlashSaleProducts(activeFlashSale.products, container);
            } else {
                HELPERS.showEmpty(container, 'Aucune vente flash active', 'lightning-charge');
            }
        } else {
            HELPERS.showEmpty(container, 'Aucune vente flash active', 'lightning-charge');
        }
    } catch (error) {
        console.error('Error loading flash sales:', error);
        HELPERS.showError(container, 'Erreur lors du chargement des ventes flash');
    }
}

/**
 * Render Flash Sale Products
 */
function renderFlashSaleProducts(products, container) {
    container.innerHTML = products.map(item => {
        const product = item.product || item;
        const flashPrice = item.flash_price || product.price;
        const discount = HELPERS.calculateDiscount(product.price, flashPrice);

        return `
            <div class="swiper-slide">
                <div class="product-card">
                    <span class="badge bg-danger">-${discount}%</span>
                    <button class="wishlist-btn" onclick="addToWishlist(${product.id})">
                        <i class="bi bi-heart"></i>
                    </button>
                    <div class="product-image">
                        <a href="pages/product-detail.html?id=${product.id}">
                            <img src="${HELPERS.getImageUrl(product.image)}"
                                 alt="${product.name}"
                                 loading="lazy">
                        </a>
                    </div>
                    <div class="product-info">
                        <a href="pages/product-detail.html?id=${product.id}"
                           class="text-decoration-none">
                            <h5 class="product-title">${HELPERS.truncate(product.name, 50)}</h5>
                        </a>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="product-price">${HELPERS.formatPrice(flashPrice)}</span>
                            <span class="product-price-old">${HELPERS.formatPrice(product.price)}</span>
                        </div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-danger" role="progressbar"
                                 style="width: ${(item.sold / item.stock_limit * 100)}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-fire"></i>
                            ${item.stock_limit - item.sold} restants
                        </small>
                        <button class="btn btn-primary add-to-cart-btn mt-2"
                                onclick="addToCart(${product.id})">
                            <i class="bi bi-cart-plus"></i> Ajouter
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Load Trending Products
 */
async function loadTrendingProducts() {
    const container = document.getElementById('trendingProducts');
    if (!container) return;

    try {
        HELPERS.showLoading(container);

        const products = await API.getTrendingProducts();

        if (products && products.length > 0) {
            container.innerHTML = products.slice(0, 8).map(product =>
                createProductCard(product)
            ).join('');
        } else {
            HELPERS.showEmpty(container, 'Aucun produit tendance');
        }
    } catch (error) {
        console.error('Error loading trending products:', error);
        HELPERS.showError(container, 'Erreur lors du chargement des produits');
    }
}

/**
 * Load Popular Categories
 */
async function loadPopularCategories() {
    const container = document.getElementById('popularCategories');
    if (!container) return;

    try {
        const categories = await API.getCategories();

        if (categories && categories.length > 0) {
            renderPopularCategories(categories.slice(0, 6), container);
        } else {
            HELPERS.showEmpty(container, 'Aucune catégorie disponible');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
        HELPERS.showError(container, 'Erreur lors du chargement des catégories');
    }
}

/**
 * Render Popular Categories
 */
function renderPopularCategories(categories, container) {
    // Category icons mapping
    const categoryIcons = {
        'électronique': 'laptop',
        'mode': 'bag',
        'maison': 'house',
        'sports': 'bicycle',
        'beauté': 'heart',
        'livres': 'book',
        'default': 'grid-3x3-gap'
    };

    // Category gradients
    const gradients = [
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
        'linear-gradient(135deg, #30cfd0 0%, #330867 100%)'
    ];

    container.innerHTML = categories.map((category, index) => {
        const iconKey = category.name.toLowerCase();
        const icon = categoryIcons[iconKey] || categoryIcons.default;
        const gradient = gradients[index % gradients.length];

        return `
            <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="${index * 100}">
                <a href="pages/products.html?category=${category.id}" class="text-decoration-none">
                    <div class="category-card" style="background: ${gradient};">
                        <i class="bi bi-${icon}"></i>
                        <h4>${category.name}</h4>
                        <p class="mb-0 small">${category.products_count || 0} produits</p>
                    </div>
                </a>
            </div>
        `;
    }).join('');
}

/**
 * Initialize Newsletter Form
 */
function initNewsletterForm() {
    const form = document.getElementById('newsletterForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const emailInput = form.querySelector('input[type="email"]');
        const email = emailInput.value.trim();

        if (!HELPERS.validateEmail(email)) {
            HELPERS.showToast('Email invalide', 'danger');
            return;
        }

        try {
            const btn = form.querySelector('button');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            await API.subscribeNewsletter(email);

            HELPERS.showToast('Inscription réussie !', 'success');
            emailInput.value = '';

            btn.disabled = false;
            btn.textContent = originalText;
        } catch (error) {
            HELPERS.showToast(error.message, 'danger');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
}

/**
 * Start Flash Sale Countdown
 */
function startFlashSaleCountdown() {
    // Set end date (example: 24 hours from now)
    const endDate = new Date();
    endDate.setHours(endDate.getHours() + 24);

    const countdown = setInterval(() => {
        const now = new Date().getTime();
        const distance = endDate - now;

        if (distance < 0) {
            clearInterval(countdown);
            document.getElementById('hours').textContent = '00';
            document.getElementById('minutes').textContent = '00';
            document.getElementById('seconds').textContent = '00';
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    }, 1000);
}
