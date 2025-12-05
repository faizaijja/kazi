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
    $dbname = "kazi";
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

// Service provider statistics
function getServiceProviderStats()
{
    $conn = getDbConnection();

    $stats = [
        'total_providers' => 0,
        'available_providers' => 0,
        'verified_providers' => 0,
        'bookings_today' => 0
    ];

    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM providers");
        $stats['total_providers'] = (int) $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) FROM providers WHERE availability_status = 'available'");
        $stats['available_providers'] = (int) $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) FROM providers WHERE verification_status = 'verified'");
        $stats['verified_providers'] = (int) $stmt->fetchColumn();

        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = ?");
        $stmt->execute([$today]);
        $stats['bookings_today'] = (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting service provider stats: " . $e->getMessage());
    }

    return $stats;
}

// Get featured service providers
function getFeaturedServiceProviders($limit = 10)
{
    $conn = getDbConnection();

    try {
        $query = "SELECT 
                    provider_id AS id,
                    business_name AS name,
                    bio,
                    years_of_experience,
                    hourly_rate AS price,
                    availability_status,
                    rating_average,
                    total_jobs_completed,
                    verification_status,
                    category_id
                  FROM service_providers
                  ORDER BY rating_average DESC, total_jobs_completed DESC
                  LIMIT :limit";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();

        $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Optional defaults
        foreach ($providers as &$provider) {
            $provider['bio'] = $provider['bio'] ?? '';
            $provider['category'] = 'General Services'; // Default
            $provider['profile_picture'] = 'images/default-avatar.png'; // Default
            $provider['rating_average'] = $provider['rating_average'] ?? 0;
            $provider['years_of_experience'] = $provider['years_of_experience'] ?? 0;
            $provider['total_jobs_completed'] = $provider['total_jobs_completed'] ?? 0;
        }

        return $providers;

    } catch (PDOException $e) {
        error_log("Error getting featured service providers: " . $e->getMessage());
        return [];
    }
}

// Notification functions remain the same
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
    } catch (PDOException $e) {
        error_log("Error checking out of stock products: " . $e->getMessage());
    }

    return $newNotificationsCreated;
}

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
$serviceProviderStats = getServiceProviderStats();
$featuredProviders = getFeaturedServiceProviders(10);
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
        :root {
            --danger: #e53935;
            --warning: #FF9800;
            --success: #4CAF50;
            --primary: #2196F3;
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

        .provider-card {
            width: 280px;
            display: inline-block;
            margin: 12px;
            vertical-align: top;
            background: #fff;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .provider-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .provider-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto 12px;
            display: block;
            border: 3px solid #f0f0f0;
        }

        .provider-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px 0;
            text-align: center;
        }

        .provider-category {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .provider-bio {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
            margin: 8px 0;
            height: 60px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .provider-stats {
            display: flex;
            justify-content: space-around;
            margin: 12px 0;
            padding: 12px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-weight: 700;
            font-size: 16px;
            color: #333;
        }

        .stat-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
        }

        .provider-rate {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            margin: 8px 0;
        }

        .availability-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }

        .availability-badge.available {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .availability-badge.unavailable {
            background: #fdecea;
            color: #c62828;
        }

        .availability-badge.busy {
            background: #fff4e5;
            color: #e65100;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: #1a1f33;
            margin-top: 8px;
        }

        .book-btn {
            width: 100%;
            padding: 10px;
            background: #1a1f33;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            transition: background 0.2s;
        }

        .book-btn:hover {
            background: #1976D2;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-card h4 {
            margin: 0 0 8px 0;
            color: #666;
            font-size: 14px;
            font-weight: 600;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="layout-container">
        <div class="side-nav">
            <div class="brand">
                <h1 style="margin:0 0 12px 0;">Kazi</h1>
            </div>

            <nav class="nav-links" style="margin-top:12px;">
                <a href="supermarket.php" style="text-decoration:none;color:inherit;display:block;padding:8px 0;">
                    <div class="nav-item active" data-id="home"><span>Dashboard</span></div>
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
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18"
                                                    height="18" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                </svg>
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
                            <span
                                class="user-role"><?php echo htmlspecialchars(ucfirst($_SESSION['user_type'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div class="content-area">


                    <div class="featured-section">
                        <h3 style="margin-top:0;">Browse Service Providers</h3>
                        <div class="provider-carousel" id="providerCarousel"
                            style="display:flex;flex-wrap:wrap; gap:16px;">
                            <?php if (!empty($featuredProviders)): ?>
                                <?php foreach ($featuredProviders as $provider): ?>
                                    <?php
                                    // Ensure defaults
                                    $providerName = htmlspecialchars($provider['name'] ?? 'Unnamed Provider');
                                    $providerCategory = htmlspecialchars($provider['category'] ?? 'General Services');
                                    $providerBio = htmlspecialchars($provider['bio'] ?? 'No bio available.');
                                    $yearsExp = (int) ($provider['years_of_experience'] ?? 0);
                                    $jobsCompleted = (int) ($provider['total_jobs_completed'] ?? 0);
                                    $rating = number_format((float) ($provider['rating_average'] ?? 0), 1);
                                    $price = number_format((float) ($provider['price'] ?? 0), 0);

                                    // Image handling
                                    $imagePath = 'images/default-avatar.png';
                                    if (!empty($provider['image_path'])) {
                                        $candidate = 'images/' . basename($provider['image_path']);
                                        if (file_exists($candidate)) {
                                            $imagePath = $candidate;
                                        }
                                    }

                                    // Availability badge
                                    $availabilityClass = 'unavailable';
                                    $availabilityText = 'Unavailable';
                                    if (!empty($provider['availability_status'])) {
                                        switch ($provider['availability_status']) {
                                            case 'available':
                                                $availabilityClass = 'available';
                                                $availabilityText = 'Available';
                                                break;
                                            case 'busy':
                                                $availabilityClass = 'busy';
                                                $availabilityText = 'Busy';
                                                break;
                                        }
                                    }

                                    $verified = ($provider['verification_status'] ?? '') === 'verified';
                                    ?>
                                    <div class="provider-card" data-id="<?php echo (int) $provider['id']; ?>">
                                        <img src="<?php echo $imagePath; ?>" alt="<?php echo $providerName; ?>"
                                            class="provider-img">
                                        <h4 class="provider-name"><?php echo $providerName; ?></h4>
                                        <p class="provider-category"><?php echo $providerCategory; ?></p>
                                        <p class="provider-bio"><?php echo $providerBio; ?></p>

                                        <div class="provider-stats">
                                            <div class="stat-item">
                                                <div class="stat-value">⭐ <?php echo $rating; ?></div>
                                                <div class="stat-label">Rating</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-value"><?php echo $yearsExp; ?>y</div>
                                                <div class="stat-label">Experience</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-value"><?php echo $jobsCompleted; ?></div>
                                                <div class="stat-label">Jobs</div>
                                            </div>
                                        </div>

                                        <div class="provider-rate"><?php echo $price; ?> RWF/hr</div>

                                        <div style="text-align:center;">
                                            <span
                                                class="availability-badge <?php echo $availabilityClass; ?>"><?php echo $availabilityText; ?></span>
                                            <?php if ($verified): ?>
                                                <div class="verified-badge">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                                        <path
                                                            d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" />
                                                    </svg>
                                                    Verified
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <button class="book-btn"
                                            onclick="bookProvider(<?php echo (int) $provider['id']; ?>)">Book Now</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="padding:16px;text-align:center;color:#888;width:100%;">No service providers found.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="market.js" defer></script>

    <script>
        function bookProvider(providerId) {
            window.location.href = 'book_provider.php?provider_id=' + providerId;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const notifBell = document.getElementById('notification-bell');
            const notifContainer = document.getElementById('notifContainer');
            const notifDropdown = document.getElementById('notificationsDropdown');
            const notifCount = document.getElementById('notificationCount');

            notifBell.addEventListener('click', function (e) {
                e.stopPropagation();
                notifContainer.classList.toggle('active');
                notifDropdown.setAttribute('aria-hidden', !notifContainer.classList.contains('active'));
            });

            document.addEventListener('click', function () {
                notifContainer.classList.remove('active');
                notifDropdown.setAttribute('aria-hidden', 'true');
            });

            notifDropdown.addEventListener('click', function (e) {
                e.stopPropagation();
            });

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
                    .catch(err => { });
            }

            checkForNewNotifications();

            const markAllBtn = document.getElementById('markAllRead');
            markAllBtn && markAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                fetch('mark_all_read.php', { method: 'POST' })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success) {
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

