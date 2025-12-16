# Restaurant Ordering App

## Overview
An interactive restaurant ordering app built with vanilla HTML/CSS/JS frontend and PHP + PostgreSQL backend. All menu data, orders, and reviews are now powered by a real database and REST API.

## Project Structure
```
/
├── index.html          # Single-page app with all screens
├── router.php          # PHP router for API and static files
├── api/
│   ├── index.php       # API router with endpoint definitions
│   ├── includes/
│   │   ├── config.php  # Database configuration
│   │   ├── db.php      # PDO database connection
│   │   └── utils.php   # Helper functions (CORS, JSON, etc.)
│   ├── menu/
│   │   ├── categories.php  # GET /api/menu/categories
│   │   ├── items.php       # GET /api/menu/items
│   │   └── item.php        # GET /api/menu/item/{id}
│   ├── order/
│   │   ├── create.php      # POST /api/order/create
│   │   ├── get.php         # GET /api/order/{id}
│   │   ├── status.php      # POST /api/order/status
│   │   └── split.php       # POST /api/order/split
│   ├── restaurant/
│   │   ├── config.php      # GET /api/restaurant/config
│   │   └── table.php       # GET /api/table/{id}
│   ├── review/
│   │   └── submit.php      # POST /api/review/submit
│   └── game/
│       └── reward.php      # GET /api/game/reward
├── js/
│   ├── app.js          # Main app logic & event bindings
│   ├── api.js          # API service layer for backend calls
│   ├── data.js         # Data loader (fetches from API)
│   ├── state.js        # State management (cart, orders, language)
│   └── router.js       # Screen navigation & history
└── attached_assets/    # Original static screens (reference)
```

## Database Schema (PostgreSQL)
- `restaurants` - Restaurant configuration and branding
- `restaurant_tables` - Table management
- `categories` - Menu categories with EN/BN names
- `menu_items` - Full menu with prices, images, tags, mood filters
- `orders` - Order tracking with status lifecycle
- `order_items` - Items in each order
- `bill_splits` - Bill splitting records
- `reviews` - Customer feedback and ratings
- `games_rewards` - Probability-based rewards for mini-game

## API Endpoints
### Restaurant & Table
- `GET /api/restaurant/config` - Get restaurant name and branding
- `GET /api/table/{tableNumber}` - Get table info

### Menu
- `GET /api/menu/categories?lang=en|bn` - Get all categories
- `GET /api/menu/items?lang=en|bn` - Get all menu items
- `GET /api/menu/item/{id}?lang=en|bn` - Get single item details

### Orders
- `POST /api/order/create` - Create new order
- `GET /api/order/{orderId}` - Get order details
- `POST /api/order/status` - Update order status
- `POST /api/order/split` - Split bill among members

### Rewards & Reviews
- `GET /api/game/reward` - Get random reward based on probability
- `POST /api/review/submit` - Submit feedback

## Features
- Dynamic menu with categories (Starters, Burgers, Mains, Drinks, Desserts)
- Mood-based filters (Spicy, Comfort, Healthy, Sweet)
- EN/BN language toggle
- Add to cart with combo suggestions
- Bill splitting with member count input and per-person calculation
- Payment methods (bKash, Nagad, Cash, etc.)
- Thank you popup when proceeding with payment showing waiter ETA
- Live order status with animated timeline
- Mini-game rewards system (probability-based from database)
- Feedback/rating screen with API submission
- Bottom navigation with Menu, Orders, and Bills tabs
- Restaurant name branding from database
- Responsive grid layout for menu items

## Recent Changes (Dec 16, 2025)
- **Backend Integration Complete**:
  - Added PHP 8 + PostgreSQL backend
  - Created REST API for all frontend data
  - Frontend now fetches from `/api/` endpoints
  - Orders stored in database with full lifecycle tracking
  - Rewards system uses probability-based selection from database
  - Reviews submitted to database via API

## Running the App
```bash
php -S 0.0.0.0:5000 router.php
```
Serves on http://0.0.0.0:5000

## URL Parameters
- `?table=N` - Set table number (default: 12)
- `/t/N` - Alternative table number path

## Tech Stack
- Frontend: Vanilla JavaScript (ES6 modules), Tailwind CSS (CDN), Material Symbols
- Backend: PHP 8.4, PostgreSQL
- No frameworks - plain PHP for backend per requirement
