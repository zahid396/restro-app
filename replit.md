# Restaurant Ordering App

## Overview
An interactive restaurant ordering prototype built with vanilla HTML/CSS/JS. Converts static Google Stitch UI screens into a fully functional app experience.

## Project Structure
```
/
├── index.html          # Single-page app with all screens
├── server.py           # Python static file server (port 5000)
├── js/
│   ├── app.js          # Main app logic & event bindings
│   ├── data.js         # Mock JSON data (menu, combos, rewards)
│   ├── state.js        # State management (cart, orders, language)
│   └── router.js       # Screen navigation & history
└── stitch_qr_entry_menu_home/  # Original static screens (reference)
```

## Features
- Dynamic menu with categories (Starters, Burgers, Mains, Drinks, Desserts)
- Mood-based filters (Spicy, Comfort, Healthy, Sweet)
- EN/BN language toggle
- Add to cart with combo suggestions
- **Bill splitting** with member count input and per-person calculation
- Payment methods (bKash, Nagad, Cash, etc.)
- **Thank you popup** when proceeding with payment showing waiter ETA
- Live order status with animated timeline
- Mini-game rewards system
- Feedback/rating screen
- **Bottom navigation** with Menu, Orders, and Bills tabs
- **Play Games button** in header for quick access to rewards game
- **Restaurant name branding** displayed across all screens/modals (configurable in RestaurantConfig)
- **Reviews section** on dish detail page showing user comments and ratings
- **Responsive grid layout** for menu items (adapts from 2-5 columns based on screen size)

## Recent Changes (Dec 16, 2025)
- **Full responsive design overhaul**:
  - Menu grids now adapt: 2 columns on mobile, 3 on tablets, 4-5 on larger screens
  - Trending cards scale responsively (200px-280px) based on screen width
  - Favorite button repositioned inside image area for consistent alignment
  - Detail, cart, status, game, and feedback screens widened to max-w-2xl for better tablet/desktop experience
  - View order button uses responsive positioning with proper centering on larger screens
  - Sticky category nav adapts positioning based on screen size
  - Game modal expanded to max-w-lg/xl for better usability

## Previous Changes (Dec 15, 2025)
- Fixed z-index issues: "View Order" button now appears above bottom navigation
- Fixed sticky category navigation positioning to stay below header when scrolling
- Fixed game page layout to properly display reward content
- Reduced food item card sizes to show 3 items per line in a grid layout
- Implemented split bill functionality: customers can input number of members and see per-person amount
- Added thank you popup when proceeding with payment showing waiter arrival time

## Running the App
```bash
python server.py
```
Serves on http://0.0.0.0:5000

## URL Parameters
- `?table=N` - Set table number (default: 12)
- `/t/N` - Alternative table number path

## Tech Stack
- Vanilla JavaScript (ES6 modules)
- Tailwind CSS (CDN)
- Material Symbols icons
- No frameworks - plain HTML/CSS/JS per user requirement
