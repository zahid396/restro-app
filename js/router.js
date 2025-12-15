class Router {
    constructor() {
        this.history = [];
        this.currentScreen = 'menu';
        this.listeners = [];
    }

    subscribe(listener) {
        this.listeners.push(listener);
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    }

    notify() {
        this.listeners.forEach(listener => listener(this.currentScreen, this.history));
    }

    navigate(screen, data = null) {
        if (this.currentScreen !== screen) {
            this.history.push({ screen: this.currentScreen, data });
        }
        this.currentScreen = screen;
        this.notify();
        return screen;
    }

    back() {
        if (this.history.length > 0) {
            const prev = this.history.pop();
            this.currentScreen = prev.screen;
            this.notify();
            return prev;
        }
        return null;
    }

    canGoBack() {
        return this.history.length > 0;
    }

    getCurrent() {
        return this.currentScreen;
    }

    reset() {
        this.history = [];
        this.currentScreen = 'menu';
        this.notify();
    }
}

const router = new Router();
export default router;
