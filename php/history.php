<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../html/login.html");
    exit();
}

$driverName = $_SESSION['full_name'] ?? 'Delivery Driver';

/* ===== Stats: Completed Today ===== */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS completed_today
    FROM deliveries
    WHERE LOWER(driver_name) = LOWER(?) 
    AND status = 'Delivered'
    AND DATE(created_at) = CURDATE()
");
$stmt->bind_param("s", $driverName);
$stmt->execute();
$completedToday = $stmt->get_result()->fetch_assoc()['completed_today'];

/* ===== Stats: Completed This Week ===== */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS completed_week
    FROM deliveries
    WHERE LOWER(driver_name) = LOWER(?) 
    AND status = 'Delivered'
    AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
");
$stmt->bind_param("s", $driverName);
$stmt->execute();
$completedWeek = $stmt->get_result()->fetch_assoc()['completed_week'];

/* ===== Stats: Total Earnings ===== */
$stmt = $conn->prepare("
    SELECT SUM(earnings) AS total_earnings
    FROM deliveries
    WHERE LOWER(driver_name) = LOWER(?) 
    AND status = 'Delivered'
");
$stmt->bind_param("s", $driverName);
$stmt->execute();
$totalEarnings = $stmt->get_result()->fetch_assoc()['total_earnings'] ?? 0;

/* ===== Recent Deliveries ===== */
$stmt = $conn->prepare("
    SELECT d.order_id, d.created_at, d.earnings, d.status, o.delivery_address
    FROM deliveries d
    JOIN orders o ON d.order_id = o.id
    WHERE LOWER(d.driver_name) = LOWER(?) 
    AND d.status = 'Delivered'
    ORDER BY d.created_at DESC
    LIMIT 5
");
$stmt->bind_param("s", $driverName);
$stmt->execute();
$recentDeliveries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IslandLink | Delivery History</title>
    <link rel="stylesheet" href="../css/products.css">
    <style>
        .stats { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-box { background:#f3f4f6; padding:20px; border-radius:12px; flex:1; text-align:center; }
        .stat-box h3 { margin:5px 0; font-size:24px; }
        .recent-deliveries { background:#fff; padding:20px; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,.05); }
        .delivery-item { display:flex; justify-content:space-between; padding:15px 0; border-bottom:1px solid #e5e7eb; }
        .delivery-item:last-child { border-bottom:none; }
        .badge { padding:4px 12px; border-radius:20px; font-size:12px; color:#065f46; background:#d1fae5; }
    </style>
</head>
<body>
<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="brand"><div class="logo">IL</div>IslandLink</div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="my_deliveries.php">My Deliveries</a>
            <a class="active">History</a>
        </nav>
    </aside>

    <main class="main">
        <!-- Topbar -->
        <header class="topbar">
            <div></div>
            <div class="top-actions">
                <div class="user">
                    <strong><?php echo htmlspecialchars($driverName); ?></strong>
                    <small>Delivery Driver</small>
                </div>
            </div>
        </header>

        <div class="content">
            <h2>Delivery History</h2>
            <p>Your completed deliveries</p>

            <!-- Stats -->
            <div class="stats">
                <div class="stat-box">
                    <small>Completed Today</small>
                    <h3><?php echo $completedToday; ?></h3>
                </div>
                <div class="stat-box">
                    <small>This Week</small>
                    <h3><?php echo $completedWeek; ?></h3>
                </div>
                <div class="stat-box">
                    <small>Total Earnings</small>
                    <h3>$<?php echo number_format($totalEarnings, 2); ?></h3>
                </div>
            </div>

            <!-- Recent Deliveries -->
            <div class="recent-deliveries">
                <?php foreach($recentDeliveries as $d): ?>
                    <div class="delivery-item">
                        <div>
                            <strong><?php echo htmlspecialchars($d['order_id']); ?></strong><br>
                            <small><?php echo htmlspecialchars($d['delivery_address']); ?></small><br>
                            <small><?php echo htmlspecialchars($d['created_at']); ?></small>
                        </div>
                        <div>
                            $<?php echo number_format($d['earnings'], 2); ?><br>
                            <span class="badge"><?php echo $d['status']; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
