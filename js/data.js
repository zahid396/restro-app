const RestaurantConfig = {
    name: { en: 'The Green Kitchen', bn: 'দ্য গ্রিন কিচেন' },
    tagline: { en: 'Fresh & Flavorful', bn: 'তাজা ও সুস্বাদু' }
};

const ReviewsData = {
    reviews: [
        { id: 1, itemId: 1, user: 'Sarah M.', avatar: '👩', rating: 5, comment: 'Best truffle fries I\'ve ever had! The parmesan is so generous.', date: '2 days ago' },
        { id: 2, itemId: 1, user: 'Ahmed K.', avatar: '👨', rating: 4, comment: 'Really crispy and flavorful. Will order again!', date: '1 week ago' },
        { id: 3, itemId: 3, user: 'Fatima R.', avatar: '👩', rating: 5, comment: 'The naga chili hits different! Perfectly spicy 🌶️', date: '3 days ago' },
        { id: 4, itemId: 3, user: 'John D.', avatar: '👨', rating: 5, comment: 'Incredible burger. The caramelized onions are amazing.', date: '5 days ago' },
        { id: 5, itemId: 3, user: 'Priya S.', avatar: '👩', rating: 4, comment: 'Loved the spice level! Not for the faint-hearted.', date: '1 week ago' },
        { id: 6, itemId: 5, user: 'Michael B.', avatar: '👨', rating: 5, comment: 'Perfectly cooked steak. Medium rare was on point!', date: '2 days ago' },
        { id: 7, itemId: 6, user: 'Lisa W.', avatar: '👩', rating: 5, comment: 'Creamy, delicious pasta. Comfort food at its best.', date: '4 days ago' },
        { id: 8, itemId: 7, user: 'Rafiq H.', avatar: '👨', rating: 5, comment: 'The truffle aroma is heavenly. Worth every penny!', date: '1 week ago' },
        { id: 9, itemId: 10, user: 'Emma T.', avatar: '👩', rating: 5, comment: 'Best tiramisu in town! So authentic.', date: '3 days ago' },
        { id: 10, itemId: 12, user: 'David L.', avatar: '👨', rating: 5, comment: 'Truffle mayo takes this burger to another level!', date: '6 days ago' }
    ]
};

const MenuData = {
    categories: [
        { id: 'starters', name: { en: 'Starters', bn: 'স্টার্টার্স' }, icon: 'restaurant' },
        { id: 'burgers', name: { en: 'Burgers', bn: 'বার্গার' }, icon: 'lunch_dining' },
        { id: 'mains', name: { en: 'Mains', bn: 'মূল খাবার' }, icon: 'dinner_dining' },
        { id: 'drinks', name: { en: 'Drinks', bn: 'পানীয়' }, icon: 'local_cafe' },
        { id: 'desserts', name: { en: 'Desserts', bn: 'মিষ্টি' }, icon: 'cake' }
    ],
    items: [
        {
            id: 1,
            name: { en: 'Truffle Parmesan Fries', bn: 'ট্রাফল পারমেসান ফ্রাইস' },
            description: { en: 'Crispy fries, truffle oil, parmesan, parsley.', bn: 'মচমচে ফ্রাইস, ট্রাফল তেল, পারমেসান, পার্সলে।' },
            price: 350,
            category: 'starters',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuASvqeIKQRh9Q31Bu0HrCm398agH3Z0MSnWqOWIXqJmw-oDSjdpExTCTZDQ2IILG-hKLqg5OsXiRSxhSNLkYPBOVk8yqn9oiYig9QLMm5_9BGwI5ZmjcB6nWDz6xW_WaVOO7FhjhzXKFjfcn_C2v7JNDtH-J0dq6r0Zmc3dzdrDgfDp49vDGZdsdaB8QYqp3TgKK3b52vOHGxPcUbvWgxaCjy1on66rLK1iPC9hD5v35Js8B9PvunFtb3uvp-V8iqi2l2FexZWV1YQ',
            tags: ['vegetarian'],
            mood: ['comfort'],
            taste: ['savory'],
            likes: 1200,
            comments: 342,
            trending: true,
            rating: 4.7,
            weight: '250g'
        },
        {
            id: 2,
            name: { en: 'Korean Sticky Wings', bn: 'কোরিয়ান স্টিকি উইংস' },
            description: { en: 'Double fried, spicy gochujang glaze.', bn: 'ডাবল ফ্রাইড, ঝাল গোচুজং গ্লেজ।' },
            price: 420,
            category: 'starters',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCREJagm-pDKqRenNFIbgJDCcenYDyA-akebHwH2iEese5ovoUX9fNTb10pepUkoh43K3nAIExqCAq1AVVwxaPnyyECf24kHYtvUg64HlGgq6NbiBG6axVgG5iqk5RplCGvCzO-wxaUzpf5ruNB8VJCf33au8gFCu07YuUYc5spT1pwg0DNtazqxDbY62YfWXvZuTqC5QkrSbvquG8aqNbO_6gIuABnJHktcRFSYgaZuiDOWwbfpXA5H0lfq90haVTDajawRFL0q1U',
            tags: ['halal', 'spicy'],
            mood: ['spicy'],
            taste: ['spicy', 'savory'],
            likes: 856,
            comments: 120,
            trending: false,
            rating: 4.5,
            weight: '300g'
        },
        {
            id: 3,
            name: { en: 'Naga Blast Burger', bn: 'নাগা ব্লাস্ট বার্গার' },
            description: { en: 'Double patty, naga chili paste, cheddar, caramelized onions.', bn: 'ডাবল প্যাটি, নাগা মরিচের পেস্ট, চেডার, ক্যারামেলাইজড পেঁয়াজ।' },
            price: 450,
            category: 'burgers',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCkiTXC1fAD_SxQbCwM4xSPN_4od-eqZpaiwGUeRiUsQqc-99fQbMarOEtcuQRA_fiGQCgRCm0Gxr_4Oa21u_xe3yCJgvBIqp7NUI53hhppIS1loufgr0y-4N4LCyAJfgOaq1A6RghqCTyAfeWKLcMGYFTGkOTiHnk4sCFugnCKYUfUYEf3B4yXqWXmbVGWIcUFg1qf0JMRO_aBqRkL1znETwUqepeliVAou4PU6YCzwA5cRk_8nG4b9dZAZrZC5gkxr3C6jt1s_nE',
            tags: ['halal', 'spicy', 'chefs-choice'],
            mood: ['spicy'],
            taste: ['spicy', 'savory'],
            likes: 2100,
            comments: 510,
            trending: true,
            rating: 4.9,
            weight: '350g'
        },
        {
            id: 4,
            name: { en: 'Crispy Chicken Deluxe', bn: 'ক্রিসপি চিকেন ডিলাক্স' },
            description: { en: 'Mayo, lettuce, tomato.', bn: 'মেয়ো, লেটুস, টমেটো।' },
            price: 380,
            category: 'burgers',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuA_l8KhTUi4NrQXQKnoPouAZODVFBJ06S3rllyWjaxvKiBZYQXBVwsCZ905BKGEpT6c6gzom09jaPDFn4PfpTpUT0geb98KNwtSHZ3Wjjudf2gBd-IkeNJQ4uvJTDmATl9RQTINRW8LGi9pjRv2SLKwyeTKXPNnN7nkOOaQjN1AUdpmuxDXrTeD2u-xJmCjO1IAwKdGmuH1XyHy5M9Wa5o8NI1D0P_W1SpBGMMx-gTVsndIgzLHs0QyFURSWjgONU3zCqs8EEdjtJ4',
            tags: ['halal'],
            mood: ['comfort'],
            taste: ['savory'],
            likes: 650,
            comments: 89,
            trending: false,
            rating: 4.3,
            weight: '280g'
        },
        {
            id: 5,
            name: { en: 'Ribeye Steak', bn: 'রিবেয়ে স্টেক' },
            description: { en: '300g, Medium Rare, with seasonal vegetables.', bn: '৩০০ গ্রাম, মিডিয়াম রেয়ার, মৌসুমী সবজি সহ।' },
            price: 1200,
            category: 'mains',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCpLT_j0X7bGL6jeLtuyFLiTRunl95xgnu8OpJtKB8Got5TsoLJ8f0EcYu0JJNiL0Z7gTh0ZIEkq3EAGhaOyCq6iJGo3IQlswM1Xy-ZrttBUVhgaxYrVF449RCKWvic2fVtnUsf1C8QMfuyrPGdA8t75R6orszXFJHURn0WfcuUxKYo-huMmxVKmhXrRvr0e3Dbjm3sealaS-h5B68duZi_nrINnC8at9gaZwUSrZZZtxeGVodcV2KKrpk2cxRrzKq-KmmOu6isr1Y',
            tags: ['premium', 'halal'],
            mood: ['healthy'],
            taste: ['savory'],
            likes: 1800,
            comments: 234,
            trending: true,
            rating: 4.8,
            weight: '300g'
        },
        {
            id: 6,
            name: { en: 'Creamy Mushroom Pasta', bn: 'ক্রিমি মাশরুম পাস্তা' },
            description: { en: 'Mushroom, Alfredo sauce, herbs.', bn: 'মাশরুম, আলফ্রেডো সস, হার্বস।' },
            price: 550,
            category: 'mains',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCDUInpQjMfLZj48RT4hAcnHRHwnzatf8fF7GuXHikJpPWGjxNHiAZ-ECSefDIGf6_0DgD28_buBj25Li8Pc2Ua4hKMwAA8dSX2Tj2vT5ekui7E8f4a8vIV7ZNLeR6nJI3VBdlKVnSpycHaR8H5Due4S-9fd_S5BaUcT81MuzpXUJSfzw0yAt5wUl-aDzBnW5M03nLtibuQoA1SS-JWb7PsxT1yDNVakWnQcthLWBXKF605sszAVCKD2J5b4si0bwqsANVN4N5ALHk',
            tags: ['vegetarian'],
            mood: ['comfort'],
            taste: ['savory', 'creamy'],
            likes: 2400,
            comments: 510,
            trending: true,
            rating: 4.6,
            weight: '400g'
        },
        {
            id: 7,
            name: { en: 'Truffle Mushroom Risotto', bn: 'ট্রাফল মাশরুম রিসোটো' },
            description: { en: 'Creamy arborio rice slow-cooked with wild porcini mushrooms, finished with black truffle oil and aged parmesan shavings.', bn: 'বন্য পোর্চিনি মাশরুম দিয়ে ধীরে রান্না করা ক্রিমি আরবোরিও চাল, কালো ট্রাফল তেল এবং পুরানো পারমেসান শেভিংস দিয়ে শেষ।' },
            price: 2400,
            category: 'mains',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuD_vKVns49_mx96kMtAXIRZyEAi4CSMrOTOF4iMapD4y5NaLQ4jE3o_BndUla5k864K9Ncu07Zx-GVmxrFSgNr6b7DxV7JuV31fOAKWgkV2xc_kvS-7Pn7OmyLtqsg4fptguL7pWW5_4wj8l4UG3lsZSer4xWAq08KsqBPyf11qC0RnFoyV9-kK_VQKQkZart8CQ3GPYfOgHDGb8H_3W0mC2wtm2V1_-q_zfxDfQIXhXnaDBaWT01UOoMnk5bhlsVgMdnAykievLZo',
            tags: ['vegetarian', 'chefs-choice', 'premium'],
            mood: ['comfort'],
            taste: ['savory', 'creamy'],
            likes: 3200,
            comments: 620,
            trending: true,
            rating: 4.8,
            weight: '450g',
            allergens: 'Contains dairy (parmesan, butter) and mushrooms. Prepared in a facility that handles nuts.'
        },
        {
            id: 8,
            name: { en: 'Coke Zero', bn: 'কোক জিরো' },
            description: { en: 'Ice cold, zero sugar.', bn: 'বরফ ঠান্ডা, জিরো সুগার।' },
            price: 120,
            category: 'drinks',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAi4d1ldMzHTGQ_gXLza9nCOMlsXh0zD2gOBBac0MbQCeD7BF0fMDXtD7Qc-05hpS3V03uFlWyfuIjOHMFKMjU9gdh2WoA75HlSjdTtn8lIjAFx8yTTdwyui8twlvUMoPIbSrwGNH-Un5357b7fzBXAr8_8JgSTUFKtBG3R8NMmU27eYlkyk3VdISrNcEe6aLPJrO1PIeF9oeZBlzexCtnD763ou--6BpVlV019Q7ppMREs390A0JNEFDjJT29rgl3WHhAW8TKZmPg',
            tags: [],
            mood: [],
            taste: ['sweet'],
            likes: 450,
            comments: 45,
            trending: false,
            rating: 4.2,
            weight: '330ml'
        },
        {
            id: 9,
            name: { en: 'Churros with Chocolate', bn: 'চুরোস উইথ চকলেট' },
            description: { en: 'Crispy sugared churros with rich chocolate dip.', bn: 'চিনিযুক্ত মচমচে চুরোস সমৃদ্ধ চকলেট ডিপ সহ।' },
            price: 350,
            category: 'desserts',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuD7rqgUPnF9t_knGxGprZUyChy0LkBL5JGy2x1zjjVl0tEUyjwwFcZRMV9tl5eV0_FIntUOqSrrnqo5JSg8usZzMvObNsekCGCFak674YRJx9yf7wfEJjTlLJkJwdoJPTkwgEMd5laduN-t6qZJMNB7QiyZvRIIUbddX6_quqSlPU6PPMKqrhRdP2EkcHsEoJCfoQJM5Z7XIIhMeYxpih3wwI-TAtstTQSUxcX2oX7I800ImkNhVLhdYGw3ATL9Nwj-hdyLyal8B8o',
            tags: ['vegetarian'],
            mood: ['sweet'],
            taste: ['sweet'],
            likes: 980,
            comments: 156,
            trending: false,
            rating: 4.5,
            weight: '150g'
        },
        {
            id: 10,
            name: { en: 'Tiramisu', bn: 'তিরামিসু' },
            description: { en: 'Classic Italian dessert with mascarpone and espresso.', bn: 'মাস্কারপোন এবং এসপ্রেসো সহ ক্লাসিক ইতালীয় মিষ্টি।' },
            price: 450,
            category: 'desserts',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC6q0ydBD_-J_dqDnmzYdP_M7jqUmJmle7lOYx0HJR6Jynzf4a4qaQdPiARzow4B0yblQ4LBMzmo6Wn1hPfp0MtR9KAHP-E25agjOigabEz_bBG_Tx-W4kAFAqt1gaSoltnnQ7gyMFnN7ejJf79d9lgGcREATPXu9jKNsHIDz4KaAaD7SAJxfodZMgdoRl1rS2TlEzkbPWJ1Qe8sp-AwrrgwmMt0oBr3Sbe33W3QL_jfzGSF6PmBXf2bAtEY9lrUR_TMJ0Zp55l5vA',
            tags: ['vegetarian'],
            mood: ['sweet'],
            taste: ['sweet', 'creamy'],
            likes: 1500,
            comments: 230,
            trending: true,
            rating: 4.9,
            weight: '180g'
        },
        {
            id: 11,
            name: { en: 'Sweet Potato Fries', bn: 'সুইট পটেটো ফ্রাইস' },
            description: { en: 'Crispy sweet potato with spicy mayo dip.', bn: 'মচমচে মিষ্টি আলু স্পাইসি মেয়ো ডিপ সহ।' },
            price: 300,
            category: 'starters',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAkJz8Bo2Eq4py1R7AXGo22-iYvnwP9w4AgOr7ztu-XURO69mOX4Ynd_4N-Oes4ISUVtyjqBWhsCnSsTzRBJGz8JSKffx0haqi5VTEdius4y_2c8QK9BYbsWJB2W0QGdqDHb3gII7xGcom5iHwMcWl1u81M_W_B5Qae57buHachdfVYq1E_amwFDYvkHRxmKUrkMxZJfh07_vQqBGlL0CZpL_mxieCJi2sx_NmuE22O9SacQCwS1nqkhP2zYyrwxuSjD95VhRBY-IM',
            tags: ['vegetarian', 'healthy'],
            mood: ['healthy', 'comfort'],
            taste: ['savory', 'sweet'],
            likes: 720,
            comments: 98,
            trending: false,
            rating: 4.4,
            weight: '200g'
        },
        {
            id: 12,
            name: { en: 'Truffle Burger', bn: 'ট্রাফল বার্গার' },
            description: { en: 'Premium beef patty with truffle mayo and melted cheese.', bn: 'প্রিমিয়াম বিফ প্যাটি ট্রাফল মেয়ো এবং গলানো পনির সহ।' },
            price: 850,
            category: 'burgers',
            image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC27_N8Z30C8wjxyYzoIVyRr3ShoPfZDih1YZdWVIXV8nVDDMqKpKZWVQ2jXXlxFc8--H4kLnD5F2tQLxG_PPIBRNdI9hqk5rjcMBWQmCMlZKda3N5olXyjl209lHwUQR28E9RJzkEhcmw0z_HdXQl8Y-aBfSAHT8TW1NUh-aDJ7ojQ6IYcKK1jOOyxmDsHHXpV3mGq-sItLrPIMjV6eiUCfa4Z-hxADwrvU5layFuSeCwPGige_fjbJ1mG9feBTYx5sEvmhsXxYME',
            tags: ['premium', 'halal', 'chefs-choice'],
            mood: ['comfort'],
            taste: ['savory', 'creamy'],
            likes: 1900,
            comments: 320,
            trending: true,
            rating: 4.8,
            weight: '380g'
        }
    ],
    combos: [
        { triggerItemId: 3, suggestItemIds: [1, 8], message: { en: 'Perfect pair! Add fries & drink?', bn: 'পারফেক্ট জোড়া! ফ্রাইস এবং ড্রিংক যোগ করুন?' } },
        { triggerItemId: 4, suggestItemIds: [11, 8], message: { en: 'Complete your meal!', bn: 'আপনার খাবার সম্পূর্ণ করুন!' } },
        { triggerItemId: 5, suggestItemIds: [8, 10], message: { en: 'Add a drink and dessert?', bn: 'একটি পানীয় এবং মিষ্টি যোগ করুন?' } },
        { triggerItemId: 12, suggestItemIds: [1, 8], message: { en: 'Upgrade your burger experience!', bn: 'আপনার বার্গার অভিজ্ঞতা আপগ্রেড করুন!' } }
    ],
    moodFilters: [
        { id: 'spicy', label: { en: 'Spicy 🌶️', bn: 'ঝাল 🌶️' }, icon: 'whatshot' },
        { id: 'comfort', label: { en: 'Comfort 🍜', bn: 'আরাম 🍜' }, icon: 'soup_kitchen' },
        { id: 'healthy', label: { en: 'Healthy 🥗', bn: 'স্বাস্থ্যকর 🥗' }, icon: 'eco' },
        { id: 'sweet', label: { en: 'Sweet 🍰', bn: 'মিষ্টি 🍰' }, icon: 'cake' }
    ]
};

const RewardsData = {
    rewards: [
        { id: 1, name: { en: 'Free Tiramisu', bn: 'ফ্রি তিরামিসু' }, description: { en: 'Delicious house-made classic.', bn: 'সুস্বাদু ঘরে তৈরি ক্লাসিক।' }, image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC6q0ydBD_-J_dqDnmzYdP_M7jqUmJmle7lOYx0HJR6Jynzf4a4qaQdPiARzow4B0yblQ4LBMzmo6Wn1hPfp0MtR9KAHP-E25agjOigabEz_bBG_Tx-W4kAFAqt1gaSoltnnQ7gyMFnN7ejJf79d9lgGcREATPXu9jKNsHIDz4KaAaD7SAJxfodZMgdoRl1rS2TlEzkbPWJ1Qe8sp-AwrrgwmMt0oBr3Sbe33W3QL_jfzGSF6PmBXf2bAtEY9lrUR_TMJ0Zp55l5vA', probability: 0.15, expiresIn: 15 },
        { id: 2, name: { en: '10% Off Next Order', bn: 'পরবর্তী অর্ডারে ১০% ছাড়' }, description: { en: 'Valid on your next visit.', bn: 'আপনার পরবর্তী ভিজিটে বৈধ।' }, image: null, discount: 10, probability: 0.35, expiresIn: 30 },
        { id: 3, name: { en: 'Free Coke Zero', bn: 'ফ্রি কোক জিরো' }, description: { en: 'Refreshing & zero calories.', bn: 'সতেজ এবং শূন্য ক্যালোরি।' }, image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAi4d1ldMzHTGQ_gXLza9nCOMlsXh0zD2gOBBac0MbQCeD7BF0fMDXtD7Qc-05hpS3V03uFlWyfuIjOHMFKMjU9gdh2WoA75HlSjdTtn8lIjAFx8yTTdwyui8twlvUMoPIbSrwGNH-Un5357b7fzBXAr8_8JgSTUFKtBG3R8NMmU27eYlkyk3VdISrNcEe6aLPJrO1PIeF9oeZBlzexCtnD763ou--6BpVlV019Q7ppMREs390A0JNEFDjJT29rgl3WHhAW8TKZmPg', probability: 0.30, expiresIn: 15 },
        { id: 4, name: { en: 'Free Churros', bn: 'ফ্রি চুরোস' }, description: { en: 'Sweet treat on us!', bn: 'আমাদের পক্ষ থেকে মিষ্টি ট্রিট!' }, image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuD7rqgUPnF9t_knGxGprZUyChy0LkBL5JGy2x1zjjVl0tEUyjwwFcZRMV9tl5eV0_FIntUOqSrrnqo5JSg8usZzMvObNsekCGCFak674YRJx9yf7wfEJjTlLJkJwdoJPTkwgEMd5laduN-t6qZJMNB7QiyZvRIIUbddX6_quqSlPU6PPMKqrhRdP2EkcHsEoJCfoQJM5Z7XIIhMeYxpih3wwI-TAtstTQSUxcX2oX7I800ImkNhVLhdYGw3ATL9Nwj-hdyLyal8B8o', probability: 0.20, expiresIn: 15 }
    ]
};

const OrderStatusConfig = {
    statuses: [
        { id: 'received', label: { en: 'Order Received', bn: 'অর্ডার গৃহীত' }, icon: 'check', duration: 3000 },
        { id: 'cooking', label: { en: 'Cooking', bn: 'রান্না হচ্ছে' }, icon: 'skillet', duration: 8000, chefMessage: { en: 'Chef Mike is on it!', bn: 'শেফ মাইক এটি করছে!' } },
        { id: 'plating', label: { en: 'Plating', bn: 'প্লেটিং' }, icon: 'room_service', duration: 4000, message: { en: 'Almost ready', bn: 'প্রায় প্রস্তুত' } },
        { id: 'delivered', label: { en: 'Served', bn: 'পরিবেশিত' }, icon: 'dinner_dining', duration: 0, message: { en: 'Enjoy your meal', bn: 'আপনার খাবার উপভোগ করুন' } }
    ],
    etaMinutes: 12
};

export { MenuData, RewardsData, OrderStatusConfig, RestaurantConfig, ReviewsData };
