/**
 * ICHRI Tunisia - Dashboard JavaScript
 */

document.addEventListener('DOMContentLoaded', async function() {
    if (!API.getToken()) {
        window.location.href = 'login.html?redirect=dashboard.html';
        return;
    }

    loadUserInfo();
    loadDashboardStats();
    loadRecentOrders();
});

async function loadUserInfo() {
    try {
        const user = await API.getUserProfile();
        if (user) {
            document.getElementById('userName').textContent = `${user.first_name || ''} ${user.last_name || ''}`;
            document.getElementById('userEmail').textContent = user.email || '';
        }
    } catch (error) {
        console.error('Error loading user:', error);
    }
}

async function loadDashboardStats() {
    try {
        const stats = await API.getDashboardStats();
        if (stats) {
            document.getElementById('totalOrders').textContent = stats.total_orders || 0;
            document.getElementById('loyaltyPoints').textContent = stats.loyalty_points || 0;
            document.getElementById('wishlistCount').textContent = stats.wishlist_count || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadRecentOrders() {
    const container = document.getElementById('recentOrders');
    try {
        const orders = await API.getOrders({ per_page: 5 });
        if (orders && orders.data && orders.data.length > 0) {
            container.innerHTML = orders.data.map(order => `
                <div class="order-item border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>#${order.order_number}</strong>
                            <p class="mb-0 text-muted small">${HELPERS.formatDate(order.created_at)}</p>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">${HELPERS.formatPrice(order.total)}</div>
                            <span class="badge bg-${getStatusColor(order.status)}">${order.status}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="text-muted text-center py-4">Aucune commande</p>';
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        container.innerHTML = '<p class="text-danger">Erreur de chargement</p>';
    }
}

function getStatusColor(status) {
    const colors = {
        pending: 'warning',
        confirmed: 'info',
        shipped: 'primary',
        delivered: 'success',
        cancelled: 'danger'
    };
    return colors[status] || 'secondary';
}

function logout() {
    API.logout();
    window.location.href = '../index.html';
}

window.logout = logout;
