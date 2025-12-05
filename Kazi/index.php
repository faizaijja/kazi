<?php

session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'];
$user_type = $_SESSION['user_type'] ?? 'client';


function getDbConnection()
{
    $host = "localhost";
    $dbname = "supermarket";
    $username = "root";
    $password = "";

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {

        error_log("DB Connection failed: " . $e->getMessage());
        die("Database connection failed.");
    }
}

// service statistics
function getProductStats()
{
    $conn = getDbConnection();

    $stats = [
        'total_products' => 0,
        'low_stock' => 0,
        'out_of_stock' => 0,
        'sales_today' => 0
    ];

    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM products");
        $stats['total_products'] = (int) $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) FROM products WHERE quantity <= COALESCE(reorder_level, 10) AND quantity > 0");
        $stats['low_stock'] = (int) $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) FROM products WHERE quantity = 0");
        $stats['out_of_stock'] = (int) $stmt->fetchColumn();

        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) AS sales_today FROM transactions WHERE DATE(transaction_date) = ?");
        $stmt->execute([$today]);
        $stats['sales_today'] = (float) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting product stats: " . $e->getMessage());
    }

    return $stats;
}


function getFeaturedProducts($limit = 10)
{
    $conn = getDbConnection();

    try {
        $query = "SELECT 
                    p.product_id as id, 
                    p.prod_name as name, 
                    p.category, 
                    p.unit_price as price, 
                    p.quantity, 
                    p.image_path, 
                    COALESCE(p.reorder_level, 10) as reorder_level 
                  FROM products p
                  ORDER BY p.product_id DESC
                  LIMIT :limit";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting featured products: " . $e->getMessage());
        return [];
    }
}


function createNotificationsTable()
{
    $conn = getDbConnection();

    try {
        $sql = "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            product_id INT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'system',
            message TEXT NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_is_read (is_read),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $conn->exec($sql);
    } catch (PDOException $e) {
        error_log("Error creating notifications table: " . $e->getMessage());
    }
}


function checkOutOfStockProducts()
{
    $conn = getDbConnection();
    $newNotificationsCreated = 0;

    try {
        // Out of stock products
        $sql = "SELECT product_id, prod_name FROM products WHERE quantity = 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $outOfStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($outOfStockProducts as $product) {
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) FROM notifications 
                WHERE product_id = ? 
                AND type = 'low_stock' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            ");
            $checkStmt->execute([$product['product_id']]);

            if ((int) $checkStmt->fetchColumn() === 0) {
                $insertStmt = $conn->prepare("
                    INSERT INTO notifications (product_id, message, type, is_read, created_at) 
                    VALUES (?, ?, 'low_stock', 0, NOW())
                ");
                $message = "Product '{$product['prod_name']}' is out of stock";
                $insertStmt->execute([$product['product_id'], $message]);
                $newNotificationsCreated++;
            }
        }

        // Low stock products (quantity <= reorder_level and > 0)
        $lowStockSql = "SELECT product_id, prod_name, quantity, COALESCE(reorder_level, 10) AS reorder_level 
                        FROM products 
                        WHERE quantity <= COALESCE(reorder_level, 10) AND quantity > 0";
        $lowStockStmt = $conn->prepare($lowStockSql);
        $lowStockStmt->execute();
        $lowStockProducts = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($lowStockProducts as $product) {
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) FROM notifications 
                WHERE product_id = ? 
                AND type = 'low_stock' 
                AND message LIKE ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            ");
            $likePattern = '%running low on stock%';
            $checkStmt->execute([$product['product_id'], $likePattern]);

            if ((int) $checkStmt->fetchColumn() === 0) {
                $insertStmt = $conn->prepare("
                    INSERT INTO notifications (product_id, message, type, is_read, created_at) 
                    VALUES (?, ?, 'low_stock', 0, NOW())
                ");
                $message = "Product '{$product['prod_name']}' is running low on stock (Only {$product['quantity']} left)";
                $insertStmt->execute([$product['product_id'], $message]);
                $newNotificationsCreated++;
            }
        }

    } catch (PDOException $e) {
        error_log("Error checking out of stock products: " . $e->getMessage());
    }

    return $newNotificationsCreated;
}

// pending notifications for the user
function getUserNotifications($userId)
{
    $conn = getDbConnection();

    try {
        $query = "SELECT n.id, n.type, n.message, n.created_at, n.is_read, n.product_id
                  FROM notifications n
                  WHERE (n.user_id = :uid OR n.user_id IS NULL)
                  ORDER BY n.is_read ASC, n.created_at DESC
                  LIMIT 10";

        $stmt = $conn->prepare($query);
        $stmt->execute([':uid' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting user notifications: " . $e->getMessage());
        return [];
    }
}

// Get unread notification count
function getUnreadNotificationCount($userId)
{
    $conn = getDbConnection();

    try {
        $query = "SELECT COUNT(*) FROM notifications 
                  WHERE (user_id = :uid OR user_id IS NULL) AND is_read = 0";
        $stmt = $conn->prepare($query);
        $stmt->execute([':uid' => $userId]);

        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting notification count: " . $e->getMessage());
        return 0;
    }
}

// Initialize and fetch data
createNotificationsTable();
$newNotifications = checkOutOfStockProducts();
$productStats = getProductStats();
$featuredProducts = getFeaturedProducts(10);
$notificationCount = getUnreadNotificationCount((int) $_SESSION['user_id']);
$userNotifications = getUserNotifications((int) $_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <title>Kazi Portal</title>

    <style>
        /* Inline critical CSS for notifications (kept minimal) */
        :root {
            --danger: #e53935;
            --warning: #FF9800;
            --success: #4CAF50;
        }

        .notification-bell-container {
            position: relative;
            display: inline-block;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-bell svg {
            width: 24px;
            height: 24px;
            color: #555;
            transition: color 0.3s;
        }

        .notification-bell:hover svg {
            color: #333;
        }

        .notification-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background-color: #ff4b4b;
            color: white;
            font-size: 12px;
            height: 18px;
            min-width: 18px;
            padding: 0 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            animation: pulse 0.5s ease-in-out;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .notifications-dropdown {
            position: absolute;
            top: 120%;
            right: 0;
            width: 320px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            z-index: 100;
            max-height: 400px;
            display: none;
            overflow: hidden;
            border: 1px solid #eee;
        }

        .notification-bell-container.active .notifications-dropdown {
            display: block;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
        }

        .notifications-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            padding: 12px 16px;
            border-bottom: 1px solid #f5f5f5;
        }

        .notification-item.unread {
            background-color: #f0f7ff;
        }

        .notification-icon {
            margin-right: 12px;
            display: flex;
            align-items: flex-start;
        }

        .notification-content p {
            margin: 0 0 4px 0;
            font-size: 14px;
            color: #333;
        }

        .notification-time {
            font-size: 12px;
            color: #999;
        }

        /* Simple layout fallback so page doesn't look broken without your custom CSS */
        .layout-container {
            display: flex;
            min-height: 100vh;
            font-family: 'Nunito', sans-serif;
        }

        .side-nav {
            width: 260px;
            background: #fff;
            border-right: 1px solid #eee;
            padding: 20px;
            box-sizing: border-box;
        }

        .header {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background: #f7f9fc;
        }

        .product-card {
            width: 200px;
            display: inline-block;
            margin: 8px;
            vertical-align: top;
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .product-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
        }

        .stock-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-top: 6px;
        }

        .stock-badge.out-of-stock {
            background: #fdecea;
            color: #b71c1c;
        }

        .stock-badge.low-stock {
            background: #fff4e5;
            color: #bf6b00;
        }

        .stock-badge.in-stock {
            background: #e8f5e9;
            color: #1b5e20;
        }
    </style>
</head>

<body>
    <!-- Debug info (toggle with ?debug=1 in URL) -->
    <?php if (isset($_GET['debug'])): ?>
        <div style="background:#fff8e1;padding:12px;border:1px solid #ffe08a;margin:10px;">
            <strong>Debug Info:</strong><br>
            New notifications created: <?php echo (int) $newNotifications; ?><br>
            Total unread notifications: <?php echo (int) $notificationCount; ?><br>
            Out of stock products: <?php echo (int) $productStats['out_of_stock']; ?><br>
            Low stock products: <?php echo (int) $productStats['low_stock']; ?>
        </div>
    <?php endif; ?>

    <div class="layout-container">
        <div class="side-nav">
            <div class="brand">
                <h1 style="margin:0 0 12px 0;">Kazi</h1>

            </div>

            <nav class="nav-links" style="margin-top:12px;">
                <a href="supermarket.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item active" data-id="home">
                        <span>Dashboard</span>
                    </div>
                </a>
                <a href="providers.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item" data-id="inventory"><span>Categories</span></div>
                </a>
                <a href="jobs.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item" data-id="purchases"><span>Clients</span></div>
                </a>
                <a href="providers.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item" data-id="sales"><span>Service Providers</span></div>
                </a>
                <a href="bookings.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item" data-id="transactions"><span>Bookings</span></div>
                </a>
                <a href="settings.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item" data-id="accounting"><span>Settings</span></div>
                </a>
                <a href="reports.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item" data-id="reports"><span>Reports</span></div>
                </a>
                <a href="account_settings.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item" data-id="settings"><span>Account Settings</span></div>
                </a>
            </nav>

            <div style="position: absolute; bottom: 20px; left: 20px;">
                <a href="login.php" style="text-decoration:none;color:inherit;">
                    <button class="logout-btn"
                        style="background:#fff;border:1px solid #eee;padding:8px 12px;border-radius:6px;cursor:pointer;">Logout</button>
                </a>
            </div>
        </div>

        <div style="flex:1; display:flex; flex-direction:column;">
            <div class="header">
                <div style="flex:1"></div>

                <div class="header-actions" style="display:flex;align-items:center;gap:16px;">
                    <div class="notification-bell-container" id="notifContainer">
                        <div class="notification-bell" id="notification-bell" title="Notifications">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notification-count" id="notificationCount"
                                style="<?php echo ($notificationCount > 0) ? 'display:flex' : 'display:none'; ?>">
                                <?php echo (int) $notificationCount; ?>
                            </span>
                        </div>

                        <div class="notifications-dropdown" id="notificationsDropdown" aria-hidden="true">
                            <div class="notifications-header">
                                <h4 style="margin:0;">Notifications</h4>
                                <a href="#" class="mark-all-read" id="markAllRead">Mark all as read</a>
                            </div>
                            <div class="notifications-list" id="notificationsList">
                                <?php if (count($userNotifications) > 0): ?>
                                    <?php foreach ($userNotifications as $notification): ?>
                                        <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>"
                                            data-id="<?php echo (int) $notification['id']; ?>">
                                            <div class="notification-icon">
                                                <?php if ($notification['type'] === 'low_stock'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                                        height="18" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2"></path>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                                        height="18" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                            <div class="notification-content">
                                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <span
                                                    class="notification-time"><?php echo date('M j, Y \a\t g:i A', strtotime($notification['created_at'])); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-notifications" style="padding:16px;text-align:center;color:#888;">
                                        <p>No notifications</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="notifications-footer"
                                style="padding:12px;text-align:center;border-top:1px solid #eee;">
                                <a href="notifications.php">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <div class="user-profile" style="display:flex;align-items:center;gap:10px;">
                        <div class="avatar"
                            style="width:36px;height:36px;border-radius:50%;background:#ddd;display:flex;align-items:center;justify-content:center;">
                            <p style="margin:0;font-weight:700;">GS</p>
                        </div>
                        <div class="user-info">
                            <span class="user-role"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div class="content-area">
                    <div class="featured-section">
                        <h3 style="margin-top:0;">Browse jobs</h3>
                        <div class="product-carousel" id="productCarousel" style="display:flex;flex-wrap:wrap;">
                            <?php foreach ($featuredProducts as $product): ?>
                                <?php
                                // Determine stock status and percentage
                                $stockStatus = 'Available';
                                $stockPercentage = 100;
                                $qty = (int) $product['quantity'];
                                $reorder = (int) $product['reorder_level'];

                                if ($qty <= 0) {
                                    $stockStatus = 'Unavailable';
                                    $stockPercentage = 0;
                                } elseif ($qty <= $reorder) {
                                    $stockStatus = 'Unavailable';
                                    $stockPercentage = 30;
                                }

                                // Handle image path more robustly
                                $imagePath = 'images/placeholder.jpg'; // default
                                if (!empty($product['image_path'])) {
                                    // use basename to avoid directory traversal
                                    $candidate = 'images/' . basename($product['image_path']);
                                    if (file_exists($candidate)) {
                                        $imagePath = $candidate;
                                    }
                                }
                                ?>
                                <div class="product-card" data-id="<?php echo (int) $product['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($imagePath); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                                    <h4 class="product-name" style="margin:8px 0 4px 0;">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </h4>
                                    <p class="price" style="margin:0 0 6px 0;">
                                        <?php echo number_format((float) $product['price'], 0); ?> rwf
                                    </p>
                                    <?php
                                    $badgeClass = 'in-stock';
                                    if (strtolower($stockStatus) === 'unavailable')
                                        $badgeClass = 'out-of-stock';
                                    if (strtolower($stockStatus) === 'available')
                                        $badgeClass = 'low-stock';
                                    ?>
                                    <span class="stock-badge <?php echo $badgeClass; ?>"><?php echo $stockStatus; ?></span>

                                    <div class="stock-indicator" style="margin-top:8px;">
                                        <div class="stock-bar"
                                            style="background:#f1f1f1;border-radius:6px;height:10px;overflow:hidden;">
                                            <div class="stock-progress"
                                                style="width:<?php echo (int) $stockPercentage; ?>%;height:10px;background:<?php echo ($stockPercentage === 0) ? 'var(--danger)' : ($stockPercentage <= 30 ? 'var(--warning)' : 'var(--success)'); ?>;">
                                            </div>
                                        </div>
                                        <span class="stock-text"
                                            style="font-size:12px;display:inline-block;margin-top:6px;"><?php echo (int) $stockPercentage; ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Load your external JS. Ensure this file exists in the same folder or update path -->
    <script src="market.js" defer></script>

    <script>
        // Inline JS for notification behaviour (keeps logic local in case market.js missing)
        document.addEventListener('DOMContentLoaded', function () {
            const notifBell = document.getElementById('notification-bell');
            const notifContainer = document.getElementById('notifContainer'); // wrapper
            const notifDropdown = document.getElementById('notificationsDropdown');
            const notifCount = document.getElementById('notificationCount');

            // Toggle dropdown
            notifBell.addEventListener('click', function (e) {
                e.stopPropagation();
                notifContainer.classList.toggle('active');
                notifDropdown.setAttribute('aria-hidden', !notifContainer.classList.contains('active'));
            });

            // Close when clicked outside
            document.addEventListener('click', function () {
                notifContainer.classList.remove('active');
                notifDropdown.setAttribute('aria-hidden', 'true');
            });

            // Prevent closing when clicking inside dropdown
            notifDropdown.addEventListener('click', function (e) {
                e.stopPropagation();
            });

            // Simple polling to refresh count (you must implement check_notifications.php)
            function checkForNewNotifications() {
                fetch('check_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        if (!data) return;
                        if (data.count > 0) {
                            notifCount.textContent = data.count;
                            notifCount.style.display = 'flex';
                            notifCount.style.animation = 'pulse 0.5s ease-in-out';
                        } else {
                            notifCount.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        // console.warn('No notifications endpoint or error', err);
                    });
            }

            // Call once at load (do not set an aggressive interval by default)
            checkForNewNotifications();
            // Optional: uncomment to poll every 30 seconds
            // setInterval(checkForNewNotifications, 30000);

            // Mark all as read click handler (will expect mark_all_read.php to exist)
            const markAllBtn = document.getElementById('markAllRead');
            markAllBtn && markAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                fetch('mark_all_read.php', { method: 'POST' })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success) {
                            // Visually mark notifications as read
                            document.querySelectorAll('.notification-item.unread').forEach(el => el.classList.remove('unread'));
                            notifCount.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error('Mark all read failed', err);
                    });
            });
        });
    </script>
</body>

</html>