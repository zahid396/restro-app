const API_BASE = '/api';

class ApiService {
    constructor() {
        this.cache = {};
    }

    async fetch(endpoint, options = {}) {
        const url = `${API_BASE}${endpoint}`;
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`API Error: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error(`API request failed: ${endpoint}`, error);
            throw error;
        }
    }

    async getRestaurantConfig() {
        if (this.cache.config) return this.cache.config;
        const data = await this.fetch('/restaurant/config');
        this.cache.config = data;
        return data;
    }

    async getCategories(lang = 'en') {
        const data = await this.fetch(`/menu/categories?lang=${lang}`);
        return data.categories;
    }

    async getMenuItems(lang = 'en') {
        const data = await this.fetch(`/menu/items?lang=${lang}`);
        return data.items;
    }

    async getMenuItem(id, lang = 'en') {
        const data = await this.fetch(`/menu/item/${id}?lang=${lang}`);
        return data;
    }

    async createOrder(tableNumber, items, paymentMethod) {
        const data = await this.fetch('/order/create', {
            method: 'POST',
            body: JSON.stringify({
                table: tableNumber,
                items: items.map(ci => ({ id: ci.item.id, qty: ci.quantity })),
                payment_method: paymentMethod
            })
        });
        return data;
    }

    async getOrder(orderId) {
        const data = await this.fetch(`/order/${orderId}`);
        return data;
    }

    async updateOrderStatus(orderId, status) {
        const data = await this.fetch('/order/status', {
            method: 'POST',
            body: JSON.stringify({ order_id: orderId, status })
        });
        return data;
    }

    async splitBill(orderId, members) {
        const data = await this.fetch('/order/split', {
            method: 'POST',
            body: JSON.stringify({ order_id: orderId, members })
        });
        return data;
    }

    async getReward() {
        const data = await this.fetch('/game/reward');
        return data;
    }

    async submitReview(orderId, itemId, rating, comment) {
        const data = await this.fetch('/review/submit', {
            method: 'POST',
            body: JSON.stringify({
                order_id: orderId,
                item_id: itemId,
                rating,
                comment
            })
        });
        return data;
    }
}

const api = new ApiService();
export default api;
