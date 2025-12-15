import appState from './state.js';
import router from './router.js';
import { MenuData, RewardsData, OrderStatusConfig } from './data.js';

class RestaurantApp {
    constructor() {
        this.screens = {};
        this.orderStatusTimer = null;
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.cacheScreens();
            this.bindGlobalEvents();
            this.setupRouter();
            this.renderMenu();
            this.updateCartBadge();
            this.updateTableNumber();
            this.updateLanguageToggle();
        });

        appState.subscribe(() => {
            this.updateCartBadge();
            this.updateTableNumber();
        });
    }

    cacheScreens() {
        this.screens = {
            menu: document.getElementById('screen-menu'),
            detail: document.getElementById('screen-detail'),
            cart: document.getElementById('screen-cart'),
            status: document.getElementById('screen-status'),
            feedback: document.getElementById('screen-feedback'),
            game: document.getElementById('screen-game')
        };
    }

    setupRouter() {
        router.subscribe((screen) => {
            this.showScreen(screen);
        });
    }

    showScreen(screenName) {
        Object.values(this.screens).forEach(screen => {
            if (screen) screen.classList.add('hidden');
        });
        if (this.screens[screenName]) {
            this.screens[screenName].classList.remove('hidden');
            window.scrollTo(0, 0);
        }
    }

    bindGlobalEvents() {
        document.querySelectorAll('[data-lang-toggle]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const lang = e.currentTarget.dataset.lang;
                appState.setLanguage(lang);
                this.updateLanguageToggle();
                this.renderMenu();
                this.renderActiveScreen();
            });
        });

        document.querySelectorAll('[data-nav-back]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (router.canGoBack()) {
                    router.back();
                } else {
                    router.navigate('menu');
                }
            });
        });

        document.querySelectorAll('[data-nav-cart]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.renderCart();
                router.navigate('cart');
            });
        });

        document.querySelectorAll('[data-mood-filter]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const mood = e.currentTarget.dataset.moodFilter;
                appState.setMoodFilter(mood);
                this.updateMoodFilters();
                this.renderMenuItems();
            });
        });

        document.querySelectorAll('[data-category-filter]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const category = e.currentTarget.dataset.categoryFilter;
                appState.setFilter(category);
                this.updateCategoryNav();
                this.scrollToCategory(category);
            });
        });

        const viewOrderBtn = document.getElementById('view-order-btn');
        if (viewOrderBtn) {
            viewOrderBtn.addEventListener('click', () => {
                this.renderCart();
                router.navigate('cart');
            });
        }

        const payNowBtn = document.getElementById('pay-now-btn');
        if (payNowBtn) {
            payNowBtn.addEventListener('click', () => {
                this.handlePayment();
            });
        }

        const addToOrderDetailBtn = document.getElementById('add-to-order-detail');
        if (addToOrderDetailBtn) {
            addToOrderDetailBtn.addEventListener('click', () => {
                this.handleAddFromDetail();
            });
        }

        document.querySelectorAll('[data-bill-split]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const mode = e.currentTarget.dataset.billSplit;
                this.setBillSplitMode(mode);
            });
        });

        document.querySelectorAll('[data-payment-method]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const method = e.currentTarget.dataset.paymentMethod;
                this.setPaymentMethod(method);
            });
        });

        const gameClaimBtn = document.getElementById('game-claim-btn');
        if (gameClaimBtn) {
            gameClaimBtn.addEventListener('click', () => {
                router.navigate('menu');
            });
        }

        const feedbackSubmitBtn = document.getElementById('feedback-submit-btn');
        if (feedbackSubmitBtn) {
            feedbackSubmitBtn.addEventListener('click', () => {
                this.submitFeedback();
            });
        }

        document.querySelectorAll('[data-game-card]').forEach(card => {
            card.addEventListener('click', (e) => {
                this.revealReward(e.currentTarget);
            });
        });

        document.querySelectorAll('[data-star-rating]').forEach(star => {
            star.addEventListener('click', (e) => {
                const rating = parseInt(e.currentTarget.dataset.starRating);
                this.setRating(rating);
            });
        });

        document.querySelectorAll('[data-feedback-tag]').forEach(tag => {
            tag.addEventListener('click', (e) => {
                this.toggleFeedbackTag(e.currentTarget);
            });
        });

        const reorderBtn = document.getElementById('reorder-btn');
        if (reorderBtn) {
            reorderBtn.addEventListener('click', () => {
                this.handleReorder();
            });
        }

        const addMoreBtn = document.getElementById('add-more-btn');
        if (addMoreBtn) {
            addMoreBtn.addEventListener('click', () => {
                router.navigate('menu');
            });
        }
    }

    renderActiveScreen() {
        const current = router.getCurrent();
        switch(current) {
            case 'menu': this.renderMenu(); break;
            case 'cart': this.renderCart(); break;
            case 'detail': this.renderDetail(appState.get('ui').selectedItemId); break;
            case 'status': this.renderOrderStatus(); break;
            case 'feedback': this.renderFeedback(); break;
        }
    }

    updateLanguageToggle() {
        const lang = appState.get('language');
        document.querySelectorAll('[data-lang-toggle]').forEach(btn => {
            const btnLang = btn.dataset.lang;
            if (btnLang === lang) {
                btn.classList.add('bg-white', 'dark:bg-card-dark', 'shadow-sm');
                btn.classList.remove('text-text-muted');
            } else {
                btn.classList.remove('bg-white', 'dark:bg-card-dark', 'shadow-sm');
                btn.classList.add('text-text-muted');
            }
        });
    }

    updateTableNumber() {
        const tableId = appState.get('tableId');
        document.querySelectorAll('[data-table-number]').forEach(el => {
            el.textContent = `Table ${tableId}`;
        });
        document.querySelectorAll('[data-table-badge]').forEach(el => {
            el.textContent = `Table #${tableId}`;
        });
    }

    updateCartBadge() {
        const count = appState.getCartItemCount();
        const total = appState.getCartTotal();
        const lang = appState.get('language');

        document.querySelectorAll('[data-cart-count]').forEach(el => {
            el.textContent = count;
        });
        document.querySelectorAll('[data-cart-total]').forEach(el => {
            el.textContent = `BDT ${total.toLocaleString()}`;
        });
        document.querySelectorAll('[data-cart-items-text]').forEach(el => {
            el.textContent = lang === 'bn' ? `${count}টি আইটেম যোগ করা হয়েছে` : `${count} Items added`;
        });

        const cartBar = document.getElementById('view-order-btn');
        if (cartBar) {
            cartBar.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    renderMenu() {
        const lang = appState.get('language');
        const items = MenuData.items;
        const trendingItems = items.filter(i => i.trending);

        const trendingContainer = document.getElementById('trending-items');
        if (trendingContainer) {
            trendingContainer.innerHTML = trendingItems.map(item => this.renderTrendingCard(item, lang)).join('');
            this.bindQuickAddButtons(trendingContainer);
            this.bindCardClicks(trendingContainer);
        }

        this.renderMenuItems();
    }

    renderMenuItems() {
        const lang = appState.get('language');
        const activeMood = appState.get('activeMood');
        let items = MenuData.items;

        if (activeMood) {
            items = items.filter(i => i.mood && i.mood.includes(activeMood));
        }

        const categories = MenuData.categories;
        
        categories.forEach(cat => {
            const container = document.getElementById(`items-${cat.id}`);
            if (container) {
                const catItems = items.filter(i => i.category === cat.id);
                container.innerHTML = catItems.map(item => this.renderMenuCard(item, lang)).join('');
                this.bindQuickAddButtons(container);
                this.bindCardClicks(container);
            }
        });
    }

    renderTrendingCard(item, lang) {
        const name = item.name[lang] || item.name.en;
        return `
        <div class="flex flex-col w-[260px] bg-white dark:bg-card-dark rounded-2xl shadow-md overflow-hidden relative group cursor-pointer" data-item-id="${item.id}">
            <div class="w-full aspect-square bg-gray-200 dark:bg-gray-800 bg-center bg-cover" style='background-image: url("${item.image}");'></div>
            <button class="absolute top-[230px] right-4 bg-white dark:bg-card-dark rounded-full p-2 shadow-lg z-10 text-gray-300 dark:text-gray-600 hover:text-red-500 transition-colors" data-favorite="${item.id}">
                <span class="material-symbols-outlined text-[20px]">favorite</span>
            </button>
            <div class="p-4 pt-6 flex flex-col gap-1">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">${name}</h3>
                <div class="flex items-center gap-6 mt-2">
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-orange-500 text-[18px]">thumb_up</span>
                        <span class="text-sm font-semibold text-text-muted">${this.formatNumber(item.likes)}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-blue-400 text-[18px]">chat_bubble</span>
                        <span class="text-sm font-semibold text-text-muted">${item.comments}</span>
                    </div>
                </div>
            </div>
        </div>`;
    }

    renderMenuCard(item, lang) {
        const name = item.name[lang] || item.name.en;
        const desc = item.description[lang] || item.description.en;
        const tags = item.tags || [];
        
        return `
        <div class="@container">
            <div class="flex flex-col items-stretch justify-start rounded-2xl bg-white dark:bg-card-dark shadow-md dark:shadow-none overflow-hidden transition-transform active:scale-[0.98] cursor-pointer" data-item-id="${item.id}">
                <div class="relative w-full aspect-[21/9] bg-gray-200 dark:bg-gray-800 bg-center bg-cover" style='background-image: url("${item.image}");'>
                    <div class="absolute top-3 left-3 flex gap-2">
                        ${tags.map(tag => `<span class="px-2 py-1 bg-black/60 backdrop-blur-md rounded-lg text-white text-[10px] font-bold uppercase tracking-wide border border-white/10">${tag}</span>`).join('')}
                    </div>
                </div>
                <div class="flex w-full flex-col p-4 gap-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-lg font-bold leading-tight mb-1 dark:text-white text-gray-900">${name}</h4>
                            <p class="text-text-muted text-sm line-clamp-1">${desc}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-lg font-bold dark:text-white text-gray-900">BDT ${item.price}</p>
                        <button class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-full bg-primary text-background-dark shadow-lg shadow-primary/30 active:bg-primary/90" data-quick-add="${item.id}">
                            <span class="material-symbols-outlined text-[24px]">add</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    }

    bindQuickAddButtons(container) {
        container.querySelectorAll('[data-quick-add]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const itemId = parseInt(btn.dataset.quickAdd);
                const item = MenuData.items.find(i => i.id === itemId);
                if (item) {
                    appState.addToCart(item, 1);
                    this.showToast(`${item.name[appState.get('language')]} added to cart!`);
                    this.checkComboSuggestion(item);
                }
            });
        });
    }

    bindCardClicks(container) {
        container.querySelectorAll('[data-item-id]').forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target.closest('[data-quick-add]') || e.target.closest('[data-favorite]')) return;
                const itemId = parseInt(card.dataset.itemId);
                appState.setSelectedItem(itemId);
                this.renderDetail(itemId);
                router.navigate('detail');
            });
        });
    }

    renderDetail(itemId) {
        const item = MenuData.items.find(i => i.id === itemId);
        if (!item) return;

        const lang = appState.get('language');
        const name = item.name[lang] || item.name.en;
        const desc = item.description[lang] || item.description.en;

        document.getElementById('detail-name').textContent = name;
        document.getElementById('detail-price').textContent = `৳ ${item.price.toLocaleString()}`;
        document.getElementById('detail-weight').textContent = item.weight;
        document.getElementById('detail-rating').textContent = item.rating;
        document.getElementById('detail-description').textContent = desc;
        document.getElementById('detail-image').style.backgroundImage = `url("${item.image}")`;

        const tagsContainer = document.getElementById('detail-tags');
        if (tagsContainer) {
            const tags = item.tags || [];
            tagsContainer.innerHTML = tags.map(tag => `
                <div class="flex items-center gap-2 px-4 py-2.5 rounded-full bg-slate-200 dark:bg-white/5 border border-transparent dark:border-white/10 shrink-0">
                    <span class="text-sm font-bold text-slate-800 dark:text-white capitalize">${tag}</span>
                </div>
            `).join('');
        }

        if (item.allergens) {
            document.getElementById('detail-allergens').textContent = item.allergens;
            document.getElementById('allergen-section').classList.remove('hidden');
        } else {
            document.getElementById('allergen-section').classList.add('hidden');
        }

        this.detailQuantity = 1;
        this.updateDetailTotal(item);
    }

    updateDetailTotal(item) {
        const total = item.price * this.detailQuantity;
        document.getElementById('detail-quantity').textContent = this.detailQuantity;
        document.getElementById('detail-total').textContent = `৳ ${total.toLocaleString()}`;
    }

    handleAddFromDetail() {
        const itemId = appState.get('ui').selectedItemId;
        const item = MenuData.items.find(i => i.id === itemId);
        if (item) {
            appState.addToCart(item, this.detailQuantity || 1);
            this.showToast(`${item.name[appState.get('language')]} added to cart!`);
            this.checkComboSuggestion(item);
            router.navigate('menu');
        }
    }

    checkComboSuggestion(item) {
        const combo = MenuData.combos.find(c => c.triggerItemId === item.id);
        if (combo) {
            this.showComboSuggestion(combo);
        }
    }

    showComboSuggestion(combo) {
        const lang = appState.get('language');
        const modal = document.getElementById('combo-modal');
        const message = combo.message[lang] || combo.message.en;
        
        document.getElementById('combo-message').textContent = message;
        
        const itemsContainer = document.getElementById('combo-items');
        const items = combo.suggestItemIds.map(id => MenuData.items.find(i => i.id === id)).filter(Boolean);
        
        itemsContainer.innerHTML = items.map(item => `
            <div class="flex items-center gap-3 p-3 bg-white/5 rounded-xl">
                <div class="w-16 h-16 rounded-xl bg-cover bg-center" style="background-image: url('${item.image}')"></div>
                <div class="flex-1">
                    <p class="font-bold text-sm">${item.name[lang]}</p>
                    <p class="text-primary text-sm font-bold">৳ ${item.price}</p>
                </div>
                <button class="h-8 w-8 rounded-full bg-primary text-black flex items-center justify-center" data-combo-add="${item.id}">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                </button>
            </div>
        `).join('');

        modal.classList.remove('hidden');

        itemsContainer.querySelectorAll('[data-combo-add]').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = parseInt(btn.dataset.comboAdd);
                const item = MenuData.items.find(i => i.id === id);
                if (item) {
                    appState.addToCart(item, 1);
                    this.showToast(`${item.name[lang]} added!`);
                }
            });
        });

        document.getElementById('combo-close').addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        document.getElementById('combo-dismiss').addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }

    renderCart() {
        const cart = appState.get('cart');
        const lang = appState.get('language');
        const container = document.getElementById('cart-items');
        
        if (!container) return;

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <span class="material-symbols-outlined text-6xl text-gray-400 mb-4">shopping_cart</span>
                    <p class="text-gray-500">${lang === 'bn' ? 'আপনার কার্ট খালি' : 'Your cart is empty'}</p>
                </div>
            `;
            this.updateCartTotals();
            return;
        }

        container.innerHTML = cart.map((ci, index) => `
            <div class="flex gap-4 p-3 bg-white dark:bg-white/5 rounded-2xl shadow-sm border border-black/5 dark:border-white/5">
                <div class="bg-center bg-no-repeat bg-cover rounded-xl size-[80px] shrink-0" style='background-image: url("${ci.item.image}");'></div>
                <div class="flex flex-1 flex-col justify-between py-1">
                    <div>
                        <div class="flex justify-between items-start">
                            <h3 class="text-black dark:text-white text-base font-bold leading-tight">${ci.item.name[lang]}</h3>
                            <span class="text-black dark:text-white font-bold">৳ ${(ci.item.price * ci.quantity).toLocaleString()}</span>
                        </div>
                        ${ci.customizations ? `<p class="text-gray-500 dark:text-gray-400 text-xs mt-1">${ci.customizations}</p>` : ''}
                    </div>
                    <div class="flex justify-end">
                        <div class="flex items-center gap-3 bg-black/5 dark:bg-black/20 rounded-full p-1">
                            <button class="size-7 flex items-center justify-center rounded-full bg-white dark:bg-white/10 text-black dark:text-white shadow-sm" data-cart-minus="${index}">
                                <span class="material-symbols-outlined text-[16px]">remove</span>
                            </button>
                            <span class="text-sm font-bold text-black dark:text-white w-2 text-center">${ci.quantity}</span>
                            <button class="size-7 flex items-center justify-center rounded-full bg-primary text-black shadow-sm" data-cart-plus="${index}">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        container.querySelectorAll('[data-cart-minus]').forEach(btn => {
            btn.addEventListener('click', () => {
                const index = parseInt(btn.dataset.cartMinus);
                const current = appState.get('cart')[index];
                appState.updateCartQuantity(index, current.quantity - 1);
                this.renderCart();
            });
        });

        container.querySelectorAll('[data-cart-plus]').forEach(btn => {
            btn.addEventListener('click', () => {
                const index = parseInt(btn.dataset.cartPlus);
                const current = appState.get('cart')[index];
                appState.updateCartQuantity(index, current.quantity + 1);
                this.renderCart();
            });
        });

        this.updateCartTotals();
    }

    updateCartTotals() {
        const subtotal = appState.getCartTotal();
        const vat = Math.round(subtotal * 0.05);
        const service = Math.round(subtotal * 0.05);
        const total = subtotal + vat + service;

        document.getElementById('cart-subtotal').textContent = `৳ ${subtotal.toLocaleString()}`;
        document.getElementById('cart-vat').textContent = `৳ ${vat.toLocaleString()}`;
        document.getElementById('cart-service').textContent = `৳ ${service.toLocaleString()}`;
        document.getElementById('cart-total').textContent = `৳ ${total.toLocaleString()}`;
        document.getElementById('cart-pay-total').textContent = `৳ ${total.toLocaleString()}`;
    }

    setBillSplitMode(mode) {
        document.querySelectorAll('[data-bill-split]').forEach(btn => {
            if (btn.dataset.billSplit === mode) {
                btn.classList.add('bg-white', 'dark:bg-white/10', 'shadow-sm');
                btn.classList.remove('text-gray-500');
            } else {
                btn.classList.remove('bg-white', 'dark:bg-white/10', 'shadow-sm');
                btn.classList.add('text-gray-500');
            }
        });
    }

    setPaymentMethod(method) {
        this.selectedPaymentMethod = method;
        document.querySelectorAll('[data-payment-method]').forEach(btn => {
            const btnMethod = btn.dataset.paymentMethod;
            if (btnMethod === method) {
                btn.classList.add('border-2', 'border-primary', 'bg-primary/10');
                btn.classList.remove('border-black/10', 'dark:border-white/10');
                const checkmark = btn.querySelector('.checkmark');
                if (!checkmark) {
                    btn.insertAdjacentHTML('beforeend', `
                        <div class="absolute -top-1.5 -right-1.5 size-4 rounded-full bg-primary flex items-center justify-center shadow-md checkmark">
                            <span class="material-symbols-outlined text-black text-[10px] font-bold">check</span>
                        </div>
                    `);
                }
            } else {
                btn.classList.remove('border-2', 'border-primary', 'bg-primary/10');
                btn.classList.add('border-black/10', 'dark:border-white/10');
                const checkmark = btn.querySelector('.checkmark');
                if (checkmark) checkmark.remove();
            }
        });
    }

    handlePayment() {
        const method = this.selectedPaymentMethod || 'cash';
        const order = appState.createOrder(method);
        
        if (method === 'cash') {
            appState.clearCart();
            this.renderOrderStatus();
            router.navigate('status');
            this.startOrderStatusSimulation();
        } else {
            this.simulateDigitalPayment().then(() => {
                appState.clearCart();
                this.renderOrderStatus();
                router.navigate('status');
                this.startOrderStatusSimulation();
            });
        }
    }

    simulateDigitalPayment() {
        return new Promise(resolve => {
            this.showToast('Processing payment...');
            setTimeout(() => {
                this.showToast('Payment successful!');
                resolve();
            }, 2000);
        });
    }

    renderOrderStatus() {
        const order = appState.get('order');
        if (!order) return;

        const lang = appState.get('language');
        const statusConfig = OrderStatusConfig.statuses;
        const currentIndex = statusConfig.findIndex(s => s.id === order.status);

        const timeline = document.getElementById('status-timeline');
        if (timeline) {
            timeline.innerHTML = statusConfig.map((status, index) => {
                const isComplete = index < currentIndex;
                const isCurrent = index === currentIndex;
                const isPending = index > currentIndex;

                return `
                <div class="flex flex-col items-center h-full">
                    <div class="relative flex items-center justify-center w-8 h-8 z-10 ${isCurrent ? '' : ''}">
                        ${isCurrent ? '<div class="absolute inset-0 bg-primary rounded-full animate-ping opacity-75"></div>' : ''}
                        <div class="relative w-8 h-8 rounded-full ${isComplete || isCurrent ? 'bg-primary text-black' : 'bg-gray-200 dark:bg-white/10 text-gray-400'} flex items-center justify-center">
                            <span class="material-symbols-outlined" style="font-size: 20px;">${isComplete ? 'check' : status.icon}</span>
                        </div>
                    </div>
                    ${index < statusConfig.length - 1 ? `<div class="w-[2px] ${isComplete ? 'bg-primary' : 'bg-gray-200 dark:bg-white/10'} h-full min-h-[40px]"></div>` : ''}
                </div>
                <div class="flex flex-col pb-8 pt-1 pl-4 ${isPending ? 'opacity-40' : ''}">
                    <p class="text-base font-bold leading-none ${isCurrent ? 'text-primary text-lg' : ''}">${status.label[lang]}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${status.chefMessage ? status.chefMessage[lang] : (status.message ? status.message[lang] : '')}</p>
                </div>
                `;
            }).join('');
        }

        const eta = document.getElementById('status-eta');
        if (eta) {
            const remaining = Math.max(0, OrderStatusConfig.etaMinutes - currentIndex * 3);
            eta.innerHTML = `~${remaining}<span class="text-2xl ml-1 text-gray-400 dark:text-gray-500 font-bold">mins</span>`;
        }

        const statusTitle = document.getElementById('status-title');
        const statusSubtitle = document.getElementById('status-subtitle');
        if (statusTitle && statusSubtitle) {
            const titles = {
                received: { en: 'Order Received!', bn: 'অর্ডার গৃহীত!' },
                cooking: { en: 'Fire in the kitchen!', bn: 'রান্নাঘরে আগুন!' },
                plating: { en: 'Almost there!', bn: 'প্রায় হয়ে গেছে!' },
                delivered: { en: 'Enjoy your meal!', bn: 'আপনার খাবার উপভোগ করুন!' }
            };
            const subtitles = {
                received: { en: 'We got your order.', bn: 'আমরা আপনার অর্ডার পেয়েছি।' },
                cooking: { en: 'We are preparing your order.', bn: 'আমরা আপনার অর্ডার প্রস্তুত করছি।' },
                plating: { en: 'Final touches being added.', bn: 'চূড়ান্ত স্পর্শ যোগ করা হচ্ছে।' },
                delivered: { en: 'Your food has been served.', bn: 'আপনার খাবার পরিবেশন করা হয়েছে।' }
            };
            statusTitle.textContent = titles[order.status][lang];
            statusSubtitle.textContent = subtitles[order.status][lang];
        }

        const orderItemsContainer = document.getElementById('status-order-items');
        if (orderItemsContainer && order.items) {
            orderItemsContainer.innerHTML = order.items.map(ci => `
                <div class="flex gap-4 items-center">
                    <div class="w-16 h-16 rounded-xl bg-cover bg-center shrink-0" style="background-image: url('${ci.item.image}');"></div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <p class="font-bold text-sm">${ci.item.name[lang]}</p>
                            <p class="text-sm font-bold opacity-70">x${ci.quantity}</p>
                        </div>
                        ${ci.customizations ? `<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${ci.customizations}</p>` : ''}
                    </div>
                </div>
            `).join('<div class="w-full h-[1px] bg-gray-100 dark:bg-white/5"></div>');
        }
    }

    startOrderStatusSimulation() {
        const statuses = ['received', 'cooking', 'plating', 'delivered'];
        let currentIndex = 0;
        
        if (this.orderStatusTimer) clearInterval(this.orderStatusTimer);

        this.orderStatusTimer = setInterval(() => {
            currentIndex++;
            if (currentIndex >= statuses.length) {
                clearInterval(this.orderStatusTimer);
                this.onOrderDelivered();
                return;
            }
            appState.updateOrderStatus(statuses[currentIndex]);
            this.renderOrderStatus();
        }, 5000);
    }

    onOrderDelivered() {
        setTimeout(() => {
            this.showGameModal();
        }, 2000);
    }

    showGameModal() {
        router.navigate('game');
        this.gameRevealed = false;
    }

    revealReward(cardElement) {
        if (this.gameRevealed) return;
        this.gameRevealed = true;

        const rewards = RewardsData.rewards;
        const random = Math.random();
        let cumulative = 0;
        let selectedReward = rewards[0];
        
        for (const reward of rewards) {
            cumulative += reward.probability;
            if (random <= cumulative) {
                selectedReward = reward;
                break;
            }
        }

        const lang = appState.get('language');

        document.querySelectorAll('[data-game-card]').forEach(card => {
            card.classList.add('opacity-50', 'pointer-events-none');
        });
        cardElement.classList.remove('opacity-50');
        cardElement.classList.add('ring-2', 'ring-primary');

        const rewardSection = document.getElementById('reward-reveal');
        rewardSection.classList.remove('hidden');
        
        document.getElementById('reward-name').textContent = selectedReward.name[lang];
        document.getElementById('reward-description').textContent = selectedReward.description[lang];
        
        if (selectedReward.image) {
            document.getElementById('reward-image').style.backgroundImage = `url('${selectedReward.image}')`;
        }
        
        document.getElementById('reward-timer').textContent = `Valid for this order only. Expires in ${selectedReward.expiresIn}:00`;
    }

    renderFeedback() {
        const lang = appState.get('language');
        const feedback = appState.get('feedback');

        document.querySelectorAll('[data-star-rating]').forEach((star, index) => {
            const icon = star.querySelector('.material-symbols-outlined');
            if (index < feedback.rating) {
                icon.classList.add('filled', 'text-primary');
                icon.classList.remove('text-gray-300', 'dark:text-gray-600');
            } else {
                icon.classList.remove('filled', 'text-primary');
                icon.classList.add('text-gray-300', 'dark:text-gray-600');
            }
        });

        const ratingText = document.getElementById('rating-text');
        if (ratingText) {
            const texts = {
                1: { en: 'Poor', bn: 'খারাপ' },
                2: { en: 'Fair', bn: 'মোটামুটি' },
                3: { en: 'Good', bn: 'ভালো' },
                4: { en: 'Very Good', bn: 'খুব ভালো' },
                5: { en: 'Excellent', bn: 'অসাধারণ' }
            };
            ratingText.textContent = texts[feedback.rating] ? texts[feedback.rating][lang] : '';
        }
    }

    setRating(rating) {
        const feedback = appState.get('feedback');
        feedback.rating = rating;
        appState.set('feedback', feedback);
        this.renderFeedback();
    }

    toggleFeedbackTag(tagElement) {
        const tag = tagElement.dataset.feedbackTag;
        const feedback = appState.get('feedback');
        const index = feedback.tags.indexOf(tag);
        
        if (index >= 0) {
            feedback.tags.splice(index, 1);
            tagElement.classList.remove('bg-primary', 'shadow-lg', 'shadow-primary/20');
            tagElement.classList.add('bg-white', 'dark:bg-surface-dark', 'border-gray-200');
            const checkIcon = tagElement.querySelector('.check-icon');
            if (checkIcon) checkIcon.remove();
        } else {
            feedback.tags.push(tag);
            tagElement.classList.add('bg-primary', 'shadow-lg', 'shadow-primary/20');
            tagElement.classList.remove('bg-white', 'dark:bg-surface-dark', 'border-gray-200');
            if (!tagElement.querySelector('.check-icon')) {
                tagElement.insertAdjacentHTML('beforeend', '<span class="material-symbols-outlined text-black text-lg font-bold check-icon">check</span>');
            }
        }
        
        appState.set('feedback', feedback);
    }

    submitFeedback() {
        const feedback = appState.get('feedback');
        feedback.comment = document.getElementById('feedback-comment')?.value || '';
        
        localStorage.setItem('lastFeedback', JSON.stringify(feedback));
        
        this.showToast('Thank you for your feedback!');
        router.navigate('menu');
    }

    handleReorder() {
        const lastOrder = appState.getLastOrder();
        if (!lastOrder || !lastOrder.items) {
            this.showToast('No previous order found');
            return;
        }

        lastOrder.items.forEach(ci => {
            const currentItem = MenuData.items.find(i => i.id === ci.item.id);
            if (currentItem) {
                appState.addToCart(currentItem, ci.quantity, ci.customizations);
            }
        });

        this.showToast('Previous order added to cart!');
        this.renderCart();
        router.navigate('cart');
    }

    updateMoodFilters() {
        const activeMood = appState.get('activeMood');
        document.querySelectorAll('[data-mood-filter]').forEach(btn => {
            const mood = btn.dataset.moodFilter;
            if (mood === activeMood) {
                btn.classList.add('bg-primary', 'text-background-dark', 'shadow-lg', 'shadow-primary/20');
                btn.classList.remove('bg-white', 'dark:bg-[#2d372a]', 'border-gray-200');
            } else {
                btn.classList.remove('bg-primary', 'text-background-dark', 'shadow-lg', 'shadow-primary/20');
                btn.classList.add('bg-white', 'dark:bg-[#2d372a]', 'border-gray-200');
            }
        });
    }

    updateCategoryNav() {
        const activeFilter = appState.get('activeFilter');
        document.querySelectorAll('[data-category-filter]').forEach(link => {
            const cat = link.dataset.categoryFilter;
            if (cat === activeFilter) {
                link.classList.add('border-b-primary', 'text-black', 'dark:text-white');
                link.classList.remove('border-b-transparent', 'text-text-muted');
            } else {
                link.classList.remove('border-b-primary', 'text-black', 'dark:text-white');
                link.classList.add('border-b-transparent', 'text-text-muted');
            }
        });
    }

    scrollToCategory(category) {
        const section = document.getElementById(`section-${category}`);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    formatNumber(num) {
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'k';
        }
        return num.toString();
    }

    showToast(message) {
        const existing = document.querySelector('.toast-notification');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'toast-notification fixed bottom-24 left-1/2 transform -translate-x-1/2 bg-black/90 text-white px-6 py-3 rounded-full text-sm font-medium z-[100] animate-fade-in';
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('animate-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
}

window.detailQuantityMinus = function() {
    const app = window.restaurantApp;
    if (app.detailQuantity > 1) {
        app.detailQuantity--;
        const itemId = appState.get('ui').selectedItemId;
        const item = MenuData.items.find(i => i.id === itemId);
        app.updateDetailTotal(item);
    }
};

window.detailQuantityPlus = function() {
    const app = window.restaurantApp;
    app.detailQuantity++;
    const itemId = appState.get('ui').selectedItemId;
    const item = MenuData.items.find(i => i.id === itemId);
    app.updateDetailTotal(item);
};

const app = new RestaurantApp();
window.restaurantApp = app;

export default app;
