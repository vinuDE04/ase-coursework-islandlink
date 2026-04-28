<?php
session_start();
require_once 'db.php';

$staffName = "Staff";
$staffRole = "staff";

if (isset($_SESSION['user_id'])) {

    $userId = $_SESSION['user_id']; // ✅ define this first

    // ✅ Get user details
    $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result(); // ✅ THIS was missing

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $staffName = $user['full_name'];
        $staffRole = $user['role'];
    }

    $stmt->close();
}

/* ===== DASHBOARD STATS ===== */

// Pending orders
$pendingOrders = $conn->query(
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'Pending'"
)->fetch_assoc()['total'];

// Low stock items (below 50)
$lowStockItems = $conn->query(
    "SELECT COUNT(*) AS total FROM products WHERE stock < 50"
)->fetch_assoc()['total'];

// Active deliveries
$activeDeliveries = $conn->query(
    "SELECT COUNT(*) AS total FROM deliveries WHERE status = 'Out for Delivery'"
)->fetch_assoc()['total'];

// Latest orders
$orders = $conn->query("
    SELECT 
        o.id,
        o.order_number,
        u.full_name,
        o.total,
        o.status,
        COUNT(oi.id) AS items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 3
");

// Low stock products
$inventory = $conn->query(
    "SELECT name, stock FROM products WHERE stock < 50 ORDER BY stock ASC LIMIT 2"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink – Staff Dashboard</title>
<style>
<?php include '../css/staff_dash.css'; ?>
</style>
</head>
<body>
<div class="layout">
<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="brand">
        <div class="logo">I</div>
        <strong>IslandLink</strong>
    </div>
    <nav>
        <a class="active" href="../php/staff-dash.php">Dashboard</a>
        <a href="../php/order_manage.php">Order Management</a>
        <a href="#">Inventory</a>
        <a href="#">Delivery Schedule</a>
        <a href="#">Customers</a>
    </nav>
</aside>

<div class="main">

<!-- TOPBAR -->
<div class="topbar">
    <div class="user">
        🔔
        <strong><?= htmlspecialchars($staffName) ?></strong>
        <small><?= htmlspecialchars($staffRole) ?></small>
    </div>
</div>


<!-- CONTENT -->
<div class="content">
<h2>Staff Dashboard</h2>
<small><?= date('d/m/Y') ?></small>

<!-- STATS -->
<div class="stats">
    <div class="card">
        <small>Pending Orders</small>
        <h2><?= $pendingOrders ?></h2>
    </div>
    <div class="card">
        <small>Low Stock Items</small>
        <h2><?= $lowStockItems ?></h2>
    </div>
    <div class="card">
        <small>Active Deliveries</small>
        <h2><?= $activeDeliveries ?></h2>
    </div>
</div>

<!-- GRID -->
<div class="grid">

<!-- INCOMING ORDERS -->
<div class="card-section">
<h3>Incoming Orders</h3>

<?php if ($orders->num_rows > 0): ?>
<?php while ($o = $orders->fetch_assoc()): ?>
<div class="order-card">
    <div>
        <strong><?= htmlspecialchars($o['order_number']) ?></strong>
        <p><?= htmlspecialchars($o['full_name']) ?></p>
        <small><?= (int)$o['items'] ?> items · $<?= number_format($o['total'], 2) ?></small>
    </div>

    <span class="status <?= strtolower(str_replace(' ', '', $o['status'])) ?>">
        <?= htmlspecialchars($o['status']) ?>
    </span>

</div>
<?php endwhile; ?>
<?php else: ?>
<p>No recent orders</p>
<?php endif; ?>


</div>

<!-- INVENTORY ALERTS -->
<div class="card-section">
<h3>Inventory Alerts</h3>

<?php if ($inventory->num_rows > 0): ?>
<?php while ($p = $inventory->fetch_assoc()): ?>
<div class="inventory-card">
    <div>
        <strong><?= htmlspecialchars($p['name']) ?></strong>
        <p>Stock: <?= $p['stock'] ?> units</p>
    </div>
    <a href="restock.php?product=<?= urlencode($p['name']) ?>" class="btn confirm">
    Restock
</a>
</div>
<?php endwhile; ?>
<?php else: ?>
<p>No low-stock items</p>
<?php endif; ?>

</div>

</div>
</div>
</div>
</body>
</html>
