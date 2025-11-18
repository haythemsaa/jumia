/**
 * ICHRI Tunisia - Products Page JavaScript
 * Product listing, filtering, sorting, and pagination
 */

// State management
let currentPage = 1;
let currentFilters = {
    category: '',
    search: '',
    minPrice: '',
    maxPrice: '',
    rating: '',
    inStock: false,
    onSale: false,
    sortBy: 'newest'
};
let totalPages = 1;
let totalProducts = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize page
    initProductsPage();

    // Load categories for filter
    loadCategoriesFilter();

    // Load products
    loadProducts();

    // Setup event listeners
    setupEventListeners();
});

/**
 * Initialize Products Page
 */
function initProductsPage() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);

    // Set initial filters from URL
    currentFilters.category = urlParams.get('category') || '';
    currentFilters.search = urlParams.get('search') || '';
    currentPage = parseInt(urlParams.get('page')) || 1;

    // Set search input value if search param exists
    if (currentFilters.search) {
        document.getElementById('searchInput').value = currentFilters.search;
    }

    // Update breadcrumb if category specified
    if (currentFilters.category) {
        updateBreadcrumb();
    }
}

/**
 * Setup Event Listeners
 */
function setupEventListeners() {
    // Search form
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        currentFilters.search = document.getElementById('searchInput').value;
        currentPage = 1;
        loadProducts();
        updateURL();
    });

    // Sort dropdown
    document.getElementById('sortBy').addEventListener('change', function() {
        currentFilters.sortBy = this.value;
        currentPage = 1;
        loadProducts();
    });

    // Category filter
    document.addEventListener('change', function(e) {
        if (e.target.name === 'category') {
            currentFilters.category = e.target.value;
            currentPage = 1;
            loadProducts();
            updateURL();
            updateBreadcrumb();
        }
    });

    // Rating filter
    document.addEventListener('change', function(e) {
        if (e.target.name === 'rating') {
            currentFilters.rating = e.target.value;
            currentPage = 1;
            loadProducts();
        }
    });

    // In stock filter
    document.getElementById('inStockOnly').addEventListener('change', function() {
        currentFilters.inStock = this.checked;
        currentPage = 1;
        loadProducts();
    });

    // On sale filter
    document.getElementById('onSaleOnly').addEventListener('change', function() {
        currentFilters.onSale = this.checked;
        currentPage = 1;
        loadProducts();
    });
}

/**
 * Load Categories for Filter
 */
async function loadCategoriesFilter() {
    try {
        const categories = await API.getCategories();

        if (categories && categories.length > 0) {
            const container = document.getElementById('categoriesFilter');

            categories.forEach(category => {
                const isChecked = category.id == currentFilters.category ? 'checked' : '';

                container.innerHTML += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="category"
                               id="cat${category.id}" value="${category.id}" ${isChecked}>
                        <label class="form-check-label" for="cat${category.id}">
                            ${category.name} <span class="text-muted">(${category.products_count || 0})</span>
                        </label>
                    </div>
                `;
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

/**
 * Load Products
 */
async function loadProducts() {
    const container = document.getElementById('productsGrid');

    try {
        HELPERS.showLoading(container);

        // Build query parameters
        const params = {
            page: currentPage,
            per_page: CONFIG.PRODUCTS_PER_PAGE,
            ...currentFilters
        };

        // Remove empty filters
        Object.keys(params).forEach(key => {
            if (params[key] === '' || params[key] === false) {
                delete params[key];
            }
        });

        const response = await API.getProducts(params);

        if (response && response.data && response.data.length > 0) {
            renderProducts(response.data, container);

            // Update pagination
            totalPages = response.last_page || 1;
            totalProducts = response.total || 0;
            renderPagination();

            // Update results count
            updateResultsCount();
        } else {
            HELPERS.showEmpty(container, 'Aucun produit trouvé', 'inbox');
            document.getElementById('resultsCount').textContent = '0 produits';
            document.getElementById('pagination').innerHTML = '';
        }
    } catch (error) {
        console.error('Error loading products:', error);
        HELPERS.showError(container, 'Erreur lors du chargement des produits');
    }
}

/**
 * Render Products
 */
function renderProducts(products, container) {
    container.innerHTML = products.map(product => {
        const discount = product.compare_price
            ? HELPERS.calculateDiscount(product.compare_price, product.price)
            : 0;

        const hasDiscount = discount > 0;
        const inStock = product.stock > 0;

        return `
            <div class="col-6 col-md-4 col-xl-3" data-aos="fade-up">
                <div class="product-card">
                    ${hasDiscount ? `<span class="badge bg-danger">-${discount}%</span>` : ''}
                    ${!inStock ? `<span class="badge bg-secondary">Rupture</span>` : ''}
                    <button class="wishlist-btn" onclick="addToWishlist(${product.id})">
                        <i class="bi bi-heart"></i>
                    </button>
                    <div class="product-image">
                        <a href="product-detail.html?id=${product.id}">
                            <img src="${HELPERS.getImageUrl(product.image)}"
                                 alt="${product.name}"
                                 loading="lazy">
                        </a>
                    </div>
                    <div class="product-info">
                        <a href="product-detail.html?id=${product.id}" class="text-decoration-none">
                            <h5 class="product-title">${HELPERS.truncate(product.name, 50)}</h5>
                        </a>
                        ${product.average_rating ? `
                            <div class="product-rating mb-2">
                                ${HELPERS.generateStars(product.average_rating)}
                                <span class="text-muted small ms-1">(${product.reviews_count || 0})</span>
                            </div>
                        ` : ''}
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="product-price">${HELPERS.formatPrice(product.price)}</span>
                            ${hasDiscount ? `<span class="product-price-old">${HELPERS.formatPrice(product.compare_price)}</span>` : ''}
                        </div>
                        ${inStock ? `
                            <button class="btn btn-primary add-to-cart-btn" onclick="addToCart(${product.id})">
                                <i class="bi bi-cart-plus"></i> Ajouter
                            </button>
                        ` : `
                            <button class="btn btn-secondary add-to-cart-btn" disabled>
                                <i class="bi bi-x-circle"></i> Indisponible
                            </button>
                        `}
                    </div>
                </div>
            </div>
        `;
    }).join('');

    // Reinitialize AOS for new elements
    AOS.refresh();
}

/**
 * Render Pagination
 */
function renderPagination() {
    const container = document.getElementById('pagination');

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let paginationHTML = '';

    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

    // Page numbers
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    if (endPage - startPage < maxPagesToShow - 1) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    // First page
    if (startPage > 1) {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(1); return false;">1</a>
            </li>
        `;
        if (startPage > 2) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
            </li>
        `;
    }

    // Last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${totalPages}); return false;">${totalPages}</a>
            </li>
        `;
    }

    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;

    container.innerHTML = paginationHTML;
}

/**
 * Change Page
 */
function changePage(page) {
    if (page < 1 || page > totalPages || page === currentPage) return;

    currentPage = page;
    loadProducts();
    updateURL();

    // Scroll to top of products grid
    document.querySelector('.products-section').scrollIntoView({ behavior: 'smooth' });
}

/**
 * Apply Price Filter
 */
function applyPriceFilter() {
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;

    if (minPrice && maxPrice && parseFloat(minPrice) > parseFloat(maxPrice)) {
        HELPERS.showToast('Le prix minimum doit être inférieur au prix maximum', 'danger');
        return;
    }

    currentFilters.minPrice = minPrice;
    currentFilters.maxPrice = maxPrice;
    currentPage = 1;
    loadProducts();
}

/**
 * Clear Filters
 */
function clearFilters() {
    // Reset all filters
    currentFilters = {
        category: '',
        search: '',
        minPrice: '',
        maxPrice: '',
        rating: '',
        inStock: false,
        onSale: false,
        sortBy: 'newest'
    };
    currentPage = 1;

    // Reset form inputs
    document.getElementById('searchInput').value = '';
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('sortBy').value = 'newest';
    document.getElementById('inStockOnly').checked = false;
    document.getElementById('onSaleOnly').checked = false;

    // Reset category radio buttons
    const categoryRadios = document.querySelectorAll('input[name="category"]');
    categoryRadios.forEach(radio => {
        radio.checked = radio.value === '';
    });

    // Reset rating radio buttons
    const ratingRadios = document.querySelectorAll('input[name="rating"]');
    ratingRadios.forEach(radio => {
        radio.checked = false;
    });

    // Reload products
    loadProducts();
    updateURL();
    updateBreadcrumb();
}

/**
 * Update Results Count
 */
function updateResultsCount() {
    const start = (currentPage - 1) * CONFIG.PRODUCTS_PER_PAGE + 1;
    const end = Math.min(currentPage * CONFIG.PRODUCTS_PER_PAGE, totalProducts);

    document.getElementById('resultsCount').textContent =
        `${start}-${end} sur ${totalProducts} produits`;
}

/**
 * Update URL with current filters
 */
function updateURL() {
    const params = new URLSearchParams();

    if (currentFilters.category) params.set('category', currentFilters.category);
    if (currentFilters.search) params.set('search', currentFilters.search);
    if (currentPage > 1) params.set('page', currentPage);

    const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.history.pushState({}, '', newURL);
}

/**
 * Update Breadcrumb
 */
async function updateBreadcrumb() {
    const breadcrumbTitle = document.getElementById('breadcrumbTitle');

    if (currentFilters.category) {
        try {
            const category = await API.getCategory(currentFilters.category);
            if (category) {
                breadcrumbTitle.textContent = category.name;
            }
        } catch (error) {
            console.error('Error loading category:', error);
        }
    } else if (currentFilters.search) {
        breadcrumbTitle.textContent = `Recherche: "${currentFilters.search}"`;
    } else {
        breadcrumbTitle.textContent = 'Tous les produits';
    }
}

// Export functions for global use
window.changePage = changePage;
window.applyPriceFilter = applyPriceFilter;
window.clearFilters = clearFilters;
