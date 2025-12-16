<?php require_once __DIR__ . '/config.php'; requireLogin(); $admin = getAdminUser(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - Restaurant Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --sidebar-width: 260px;
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', system-ui, sans-serif; }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #1e1e2e 0%, #2d2d44 100%);
            padding: 20px 0;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 10px 25px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar-brand h4 { color: #fff; margin: 0; font-weight: 700; }
        .sidebar-brand small { color: rgba(255,255,255,0.5); }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: var(--primary-color);
        }
        .sidebar-menu li a .material-icons-outlined { margin-right: 12px; font-size: 22px; }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px 30px;
            min-height: 100vh;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .page-title { font-size: 24px; font-weight: 700; color: #1e1e2e; margin: 0; }
        .notification-bell { position: relative; cursor: pointer; }
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 50px;
            min-width: 18px;
            text-align: center;
        }
        .card-stat {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .card-stat:hover { transform: translateY(-2px); }
        .card-stat .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .card-stat h3 { font-size: 28px; font-weight: 700; margin: 10px 0 5px; }
        .card-stat p { color: #6b7280; margin: 0; font-size: 14px; }
        .order-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }
        .order-card.status-received { border-left-color: #f59e0b; }
        .order-card.status-cooking { border-left-color: #3b82f6; }
        .order-card.status-ready { border-left-color: #8b5cf6; }
        .order-card.status-delivered { border-left-color: #10b981; }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-received { background: #fef3c7; color: #92400e; }
        .status-cooking { background: #dbeafe; color: #1e40af; }
        .status-ready { background: #ede9fe; color: #5b21b6; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .table-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .table-card .table { margin: 0; }
        .table-card .table th { background: #f8f9fa; font-weight: 600; border: none; }
        .table-card .table td { vertical-align: middle; border-color: #f1f1f1; }
        .btn-action { padding: 6px 12px; font-size: 13px; }
        .empty-state { text-align: center; padding: 40px; color: #9ca3af; }
        .empty-state .material-icons-outlined { font-size: 64px; margin-bottom: 15px; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .pulse { animation: pulse 2s infinite; }
        .new-order-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: none;
        }
    </style>
</head>
<body>
    <div class="new-order-alert" id="newOrderAlert">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-bell me-2"></i>New Order!</strong> A new order has been placed.
            <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
        </div>
    </div>
    <audio id="notificationSound" preload="auto">
        <source src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" type="audio/mpeg">
    </audio>
    
    <nav class="sidebar">
        <div class="sidebar-brand">
            <h4>🍽️ Restaurant</h4>
            <small>Admin Panel</small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">dashboard</span> Dashboard
            </a></li>
            <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">receipt_long</span> Orders
            </a></li>
            <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">category</span> Categories
            </a></li>
            <li><a href="menu-items.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'menu-items.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">restaurant_menu</span> Menu Items
            </a></li>
            <li><a href="tables.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tables.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">table_restaurant</span> Tables & QR
            </a></li>
            <li><a href="payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">payments</span> Payment Methods
            </a></li>
            <li><a href="reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">reviews</span> Reviews
            </a></li>
            <li><a href="rewards.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'rewards.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">emoji_events</span> Rewards
            </a></li>
            <li><a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <span class="material-icons-outlined">settings</span> Settings
            </a></li>
            <li><a href="logout.php">
                <span class="material-icons-outlined">logout</span> Logout
            </a></li>
        </ul>
    </nav>
    
    <main class="main-content">
