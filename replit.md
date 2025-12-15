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
- Bill splitting & payment methods (bKash, Nagad, Cash, etc.)
- Live order status with animated timeline
- Mini-game rewards system
- Feedback/rating screen
- **Bottom navigation** with Menu, Orders, and Bills tabs
- **Play Games button** in header for quick access to rewards game
- **Restaurant name branding** displayed across all screens/modals (configurable in RestaurantConfig)
- **Reviews section** on dish detail page showing user comments and ratings

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
