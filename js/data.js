import api from './api.js';

let RestaurantConfig = {
    name: { en: 'Loading...', bn: 'লোড হচ্ছে...' },
    tagline: { en: '', bn: '' }
};

let MenuData = {
    categories: [],
    items: [],
    combos: [],
    moodFilters: [
        { id: 'spicy', label: { en: 'Spicy 🌶️', bn: 'ঝাল 🌶️' }, icon: 'whatshot' },
        { id: 'comfort', label: { en: 'Comfort 🍜', bn: 'আরাম 🍜' }, icon: 'soup_kitchen' },
        { id: 'healthy', label: { en: 'Healthy 🥗', bn: 'স্বাস্থ্যকর 🥗' }, icon: 'eco' },
        { id: 'sweet', label: { en: 'Sweet 🍰', bn: 'মিষ্টি 🍰' }, icon: 'cake' }
    ]
};

const ReviewsData = {
    reviews: []
};

const RewardsData = {
    rewards: []
};

const OrderStatusConfig = {
    statuses: [
        { id: 'received', label: { en: 'Order Received', bn: 'অর্ডার গৃহীত' }, icon: 'check', duration: 3000 },
        { id: 'cooking', label: { en: 'Cooking', bn: 'রান্না হচ্ছে' }, icon: 'skillet', duration: 8000, chefMessage: { en: 'Chef is on it!', bn: 'শেফ এটি করছে!' } },
        { id: 'ready', label: { en: 'Ready', bn: 'প্রস্তুত' }, icon: 'room_service', duration: 4000, message: { en: 'Almost ready', bn: 'প্রায় প্রস্তুত' } },
        { id: 'delivered', label: { en: 'Served', bn: 'পরিবেশিত' }, icon: 'dinner_dining', duration: 0, message: { en: 'Enjoy your meal', bn: 'আপনার খাবার উপভোগ করুন' } }
    ],
    etaMinutes: 12
};

async function loadRestaurantConfig() {
    try {
        const config = await api.getRestaurantConfig();
        RestaurantConfig.name = config.name;
        RestaurantConfig.tagline = config.tagline;
        return RestaurantConfig;
    } catch (error) {
        console.error('Failed to load restaurant config:', error);
        return RestaurantConfig;
    }
}

async function loadMenuData() {
    try {
        const [categories, items] = await Promise.all([
            api.getCategories(),
            api.getMenuItems()
        ]);
        
        MenuData.categories = categories;
        MenuData.items = items;
        
        MenuData.combos = generateCombos(items);
        
        return MenuData;
    } catch (error) {
        console.error('Failed to load menu data:', error);
        return MenuData;
    }
}

function generateCombos(items) {
    const combos = [];
    const burgers = items.filter(i => i.category === 'burgers');
    const starters = items.filter(i => i.category === 'starters');
    const drinks = items.filter(i => i.category === 'drinks');
    
    burgers.forEach(burger => {
        const starterItem = starters[0];
        const drinkItem = drinks[0];
        if (starterItem && drinkItem) {
            combos.push({
                triggerItemId: burger.id,
                suggestItemIds: [starterItem.id, drinkItem.id],
                message: { en: 'Perfect pair! Add fries & drink?', bn: 'পারফেক্ট জোড়া! ফ্রাইস এবং ড্রিংক যোগ করুন?' }
            });
        }
    });
    
    return combos;
}

async function loadAllData() {
    await Promise.all([
        loadRestaurantConfig(),
        loadMenuData()
    ]);
}

export { 
    MenuData, 
    RewardsData, 
    OrderStatusConfig, 
    RestaurantConfig, 
    ReviewsData,
    loadRestaurantConfig,
    loadMenuData,
    loadAllData
};
