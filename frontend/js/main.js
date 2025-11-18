/**
 * ICHRI Tunisia - Main JavaScript
 * Core functionality and navigation
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize app
    initApp();

    // Initialize header
    initHeader();

    // Initialize cart and wishlist counts
    updateCartCount();
    updateWishlistCount();

    // Initialize user menu
    initUserMenu();

    // Initialize back to top button
    initBackToTop();

    // Initialize language switcher
    initLanguageSwitcher();

    // Initialize tooltips
    initTooltips();

    // Load categories
    loadCategories();
});

/**
 * Initialize Application
 */
function initApp() {
    console.log(`%c${CONFIG.APP_NAME} v${CONFIG.APP_VERSION}`, 'color: #667eea; font-size: 20px; font-weight: bold;');

    // Set language
    const savedLang = API.getLang();
    if (savedLang) {
        API.setLang(savedLang);
    }

    // Check authentication
    const token = API.getToken();
    if (token) {
        checkAuth();
    }
}

/**
 * Initialize Header Scroll Effect
 */
function initHeader() {
    const header = document.getElementById('header');
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

        lastScroll = currentScroll;
    });

    // Search form
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', handleSearch);
    }
}

/**
 * Handle search
 */
function handleSearch(e) {
    e.preventDefault();
    const query = e.target.querySelector('input').value.trim();

    if (query) {
        window.location.href = `pages/products.html?search=${encodeURIComponent(query)}`;
    }
}

/**
 * Initialize Back to Top Button
 */
function initBackToTop() {
    const backToTop = document.getElementById('backToTop');

    if (!backToTop) return;

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTop.classList.remove('d-none');
        } else {
            backToTop.classList.add('d-none');
        }
    });

    backToTop.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Initialize Language Switcher
 */
function initLanguageSwitcher() {
    const langLinks = document.querySelectorAll('[data-lang]');

    langLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const lang = link.getAttribute('data-lang');
            API.setLang(lang);
            location.reload();
        });
    });
}

/**
 * Initialize Tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Check Authentication
 */
async function checkAuth() {
    try {
        const user = await API.getUser();
        updateUserMenu(user);
    } catch (error) {
        // Token invalid, remove it
        API.removeToken();
        localStorage.removeItem(CONFIG.STORAGE_KEYS.USER);
    }
}

/**
 * Initialize User Menu
 */
function initUserMenu() {
    const logoutBtn = document.getElementById('logoutBtn');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            await handleLogout();
        });
    }
}

/**
 * Update User Menu
 */
function updateUserMenu(user) {
    const userMenu = document.getElementById('userMenu');
    const userName = document.getElementById('userName');

    if (userMenu && user) {
        userMenu.classList.remove('d-none');
        if (userName) {
            userName.textContent = user.name;
        }
    }
}

/**
 * Handle Logout
 */
async function handleLogout() {
    try {
        await API.logout();
        HELPERS.showToast('Déconnexion réussie', 'success');
        setTimeout(() => {
            window.location.href = 'index.html';
        }, 1000);
    } catch (error) {
        HELPERS.showToast(error.message, 'danger');
    }
}

/**
 * Update Cart Count
 */
async function updateCartCount() {
    const cartCount = document.getElementById('cartCount');
    if (!cartCount) return;

    try {
        // Get cart from localStorage or API
        const cart = getLocalCart();
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = count;

        if (count > 0) {
            cartCount.classList.remove('d-none');
        } else {
            cartCount.classList.add('d-none');
        }
    } catch (error) {
        console.error('Error updating cart count:', error);
    }
}

/**
 * Update Wishlist Count
 */
async function updateWishlistCount() {
    const wishlistCount = document.getElementById('wishlistCount');
    if (!wishlistCount) return;

    try {
        // Get wishlist from localStorage or API
        const wishlist = getLocalWishlist();
        const count = wishlist.length;
        wishlistCount.textContent = count;

        if (count > 0) {
            wishlistCount.classList.remove('d-none');
        } else {
            wishlistCount.classList.add('d-none');
        }
    } catch (error) {
        console.error('Error updating wishlist count:', error);
    }
}

/**
 * Load Categories
 */
async function loadCategories() {
    const categoriesMenu = document.getElementById('categoriesMenu');
    if (!categoriesMenu) return;

    try {
        const categories = await API.getCategories();

        if (categories && categories.length > 0) {
            renderCategoriesMenu(categories, categoriesMenu);
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

/**
 * Render Categories Menu
 */
function renderCategoriesMenu(categories, container) {
    container.innerHTML = categories.map(category => `
        <li>
            <a class="dropdown-item" href="pages/products.html?category=${category.id}">
                ${category.name}
                ${category.children && category.children.length > 0 ?
                    '<i class="bi bi-chevron-right float-end"></i>' : ''
                }
            </a>
            ${category.children && category.children.length > 0 ?
                `<ul class="dropdown-menu dropdown-submenu">
                    ${category.children.map(child => `
                        <li>
                            <a class="dropdown-item" href="pages/products.html?category=${child.id}">
                                ${child.name}
                            </a>
                        </li>
                    `).join('')}
                </ul>` : ''
            }
        </li>
    `).join('');
}

/**
 * Add to Cart
 */
async function addToCart(productId, quantity = 1, variantId = null) {
    try {
        if (API.getToken()) {
            // User logged in, use API
            const response = await API.addToCart(productId, quantity, variantId);
            HELPERS.showToast('Produit ajouté au panier', 'success');
        } else {
            // Guest user, use localStorage
            const cart = getLocalCart();
            const existingItem = cart.find(item =>
                item.product_id === productId && item.variant_id === variantId
            );

            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({ product_id: productId, quantity, variant_id: variantId });
            }

            localStorage.setItem(CONFIG.STORAGE_KEYS.CART, JSON.stringify(cart));
            HELPERS.showToast('Produit ajouté au panier', 'success');
        }

        updateCartCount();
    } catch (error) {
        HELPERS.showToast(error.message, 'danger');
    }
}

/**
 * Add to Wishlist
 */
async function addToWishlist(productId) {
    try {
        if (API.getToken()) {
            // User logged in, use API
            await API.addToWishlist(productId);
            HELPERS.showToast('Produit ajouté aux favoris', 'success');
        } else {
            // Guest user, use localStorage
            const wishlist = getLocalWishlist();

            if (!wishlist.includes(productId)) {
                wishlist.push(productId);
                localStorage.setItem(CONFIG.STORAGE_KEYS.WISHLIST, JSON.stringify(wishlist));
                HELPERS.showToast('Produit ajouté aux favoris', 'success');
            } else {
                HELPERS.showToast('Produit déjà dans les favoris', 'info');
            }
        }

        updateWishlistCount();
    } catch (error) {
        HELPERS.showToast(error.message, 'danger');
    }
}

/**
 * Remove from Wishlist
 */
async function removeFromWishlist(productId) {
    try {
        if (API.getToken()) {
            await API.removeFromWishlist(productId);
        } else {
            const wishlist = getLocalWishlist();
            const index = wishlist.indexOf(productId);
            if (index > -1) {
                wishlist.splice(index, 1);
                localStorage.setItem(CONFIG.STORAGE_KEYS.WISHLIST, JSON.stringify(wishlist));
            }
        }

        HELPERS.showToast('Produit retiré des favoris', 'success');
        updateWishlistCount();
    } catch (error) {
        HELPERS.showToast(error.message, 'danger');
    }
}

/**
 * Get Local Cart
 */
function getLocalCart() {
    const cart = localStorage.getItem(CONFIG.STORAGE_KEYS.CART);
    return cart ? JSON.parse(cart) : [];
}

/**
 * Get Local Wishlist
 */
function getLocalWishlist() {
    const wishlist = localStorage.getItem(CONFIG.STORAGE_KEYS.WISHLIST);
    return wishlist ? JSON.parse(wishlist) : [];
}

/**
 * Create Product Card HTML
 */
function createProductCard(product) {
    const discount = product.compare_price ?
        HELPERS.calculateDiscount(product.compare_price, product.price) : 0;

    return `
        <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up">
            <div class="product-card">
                ${discount > 0 ? `<span class="badge bg-danger">-${discount}%</span>` : ''}
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
                        <h5 class="product-title">${product.name}</h5>
                    </a>
                    <div class="rating mb-2">
                        ${HELPERS.generateStars(product.average_rating || 0)}
                        <span class="text-muted small ms-1">
                            (${product.reviews_count || 0})
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="product-price">${HELPERS.formatPrice(product.price)}</span>
                        ${product.compare_price ?
                            `<span class="product-price-old">${HELPERS.formatPrice(product.compare_price)}</span>`
                            : ''
                        }
                    </div>
                    <button class="btn btn-primary add-to-cart-btn mt-3"
                            onclick="addToCart(${product.id})">
                        <i class="bi bi-cart-plus"></i> Ajouter
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Make functions globally available
window.addToCart = addToCart;
window.addToWishlist = addToWishlist;
window.removeFromWishlist = removeFromWishlist;
window.createProductCard = createProductCard;
