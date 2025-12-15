class AppState {
    constructor() {
        this.state = {
            tableId: 12,
            language: 'en',
            menu: [],
            cart: [],
            order: null,
            activeFilter: null,
            activeMood: null,
            ui: {
                currentScreen: 'menu',
                selectedItemId: null,
                showComboSuggestion: false,
                comboItems: []
            },
            feedback: {
                rating: 0,
                tags: [],
                comment: ''
            }
        };
        this.listeners = [];
        this.init();
    }

    init() {
        const urlParams = new URLSearchParams(window.location.search);
        const tableParam = urlParams.get('table');
        if (tableParam) {
            this.state.tableId = parseInt(tableParam) || 12;
        }

        const pathMatch = window.location.pathname.match(/\/t\/(\d+)/);
        if (pathMatch) {
            this.state.tableId = parseInt(pathMatch[1]) || 12;
        }

        const savedLang = localStorage.getItem('preferredLanguage');
        if (savedLang) {
            this.state.language = savedLang;
        }

        const savedCart = sessionStorage.getItem('cart');
        if (savedCart) {
            try {
                this.state.cart = JSON.parse(savedCart);
            } catch (e) {
                this.state.cart = [];
            }
        }

        const savedOrder = sessionStorage.getItem('currentOrder');
        if (savedOrder) {
            try {
                this.state.order = JSON.parse(savedOrder);
            } catch (e) {
                this.state.order = null;
            }
        }
    }

    subscribe(listener) {
        this.listeners.push(listener);
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }

    notify() {
        this.listeners.forEach(listener => listener(this.state));
    }

    get(key) {
        return key ? this.state[key] : this.state;
    }

    set(key, value) {
        this.state[key] = value;
        this.persist();
        this.notify();
    }

    update(updates) {
        Object.assign(this.state, updates);
        this.persist();
        this.notify();
    }

    persist() {
        sessionStorage.setItem('cart', JSON.stringify(this.state.cart));
        if (this.state.order) {
            sessionStorage.setItem('currentOrder', JSON.stringify(this.state.order));
        }
        localStorage.setItem('preferredLanguage', this.state.language);
    }

    setLanguage(lang) {
        this.state.language = lang;
        localStorage.setItem('preferredLanguage', lang);
        this.notify();
    }

    addToCart(item, quantity = 1, customizations = '') {
        const existingIndex = this.state.cart.findIndex(
            ci => ci.item.id === item.id && ci.customizations === customizations
        );

        if (existingIndex >= 0) {
            this.state.cart[existingIndex].quantity += quantity;
        } else {
            this.state.cart.push({ item, quantity, customizations });
        }
        this.persist();
        this.notify();
        return this.state.cart;
    }

    removeFromCart(index) {
        this.state.cart.splice(index, 1);
        this.persist();
        this.notify();
    }

    updateCartQuantity(index, quantity) {
        if (quantity <= 0) {
            this.removeFromCart(index);
        } else {
            this.state.cart[index].quantity = quantity;
            this.persist();
            this.notify();
        }
    }

    clearCart() {
        this.state.cart = [];
        this.persist();
        this.notify();
    }

    getCartTotal() {
        return this.state.cart.reduce((total, ci) => total + (ci.item.price * ci.quantity), 0);
    }

    getCartItemCount() {
        return this.state.cart.reduce((count, ci) => count + ci.quantity, 0);
    }

    createOrder(paymentMethod) {
        const order = {
            id: 'ORD-' + Date.now(),
            tableId: this.state.tableId,
            items: [...this.state.cart],
            paymentMethod,
            status: 'received',
            createdAt: new Date().toISOString(),
            total: this.getCartTotal()
        };
        this.state.order = order;
        localStorage.setItem('lastOrder', JSON.stringify(order));
        this.persist();
        this.notify();
        return order;
    }

    updateOrderStatus(status) {
        if (this.state.order) {
            this.state.order.status = status;
            this.persist();
            this.notify();
        }
    }

    getLastOrder() {
        const saved = localStorage.getItem('lastOrder');
        return saved ? JSON.parse(saved) : null;
    }

    setFilter(category) {
        this.state.activeFilter = category;
        this.notify();
    }

    setMoodFilter(mood) {
        this.state.activeMood = this.state.activeMood === mood ? null : mood;
        this.notify();
    }

    setScreen(screen) {
        this.state.ui.currentScreen = screen;
        this.notify();
    }

    setSelectedItem(itemId) {
        this.state.ui.selectedItemId = itemId;
        this.notify();
    }
}

const appState = new AppState();
export default appState;
