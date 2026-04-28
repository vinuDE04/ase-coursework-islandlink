<?php
session_start();
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

$fullName = $_SESSION['full_name'];
$role = $_SESSION['role'];

$userId = $_SESSION['user_id'];

// Fetch data
$stmt = $conn->prepare("SELECT COUNT(*) AS total_orders, SUM(total) AS total_spent FROM orders WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orderData = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT COUNT(*) AS active_deliveries FROM orders WHERE user_id = ? AND status='Out For Delivery'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$deliveryData = $stmt->get_result()->fetch_assoc();

$recentOrders = $conn->query("SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
$popularProducts = $conn->query("SELECT p.name, p.category, p.price, p.stock FROM products p ORDER BY p.stock ASC LIMIT 3")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IslandLink | Retail Dashboard</title>
<link rel="stylesheet" href="../css/products.css">
<style>
/* MAIN LAYOUT */
.layout { display: flex; min-height: 100vh; background: #f5f7fb; }
.main { flex: 1; padding: 30px; }

/* TOPBAR */
.topbar {
    background: #fff;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 30px;
}
.top-actions {
    display: flex;
    gap: 20px;
    align-items: center;
}
.top-actions .user { text-align: right; }
.top-actions .user small { display: block; color: #6b7280; }

/* DASHBOARD CARDS */
.cards { display: flex; gap: 20px; margin-bottom: 40px; }
.card { background: #fff; padding: 20px; border-radius: 12px; flex: 1; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.card h3 { margin-top: 0; font-size: 20px; color: #111827; }
.card p { color: #6b7280; }

/* RECENT ORDERS TABLE */
section h2 { margin-top: 0; margin-bottom: 15px; color: #2563eb; }
.table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
.table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
.table th { background: #f3f4f6; }

/* POPULAR PRODUCTS GRID */
.products-grid { display: flex; gap: 20px; flex-wrap: wrap; }
.product-card { background: #fff; padding: 15px; border-radius: 12px; flex: 1 1 200px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
.product-card h4 { margin: 0; font-size: 16px; }
.product-card p { margin: 5px 0 0; color: #6b7280; }
.product-card span { font-weight: bold; color: #2563eb; }
.product-card p:last-child { font-weight: 600; color: #9a3412; }
</style>
</head>
<body>

<div class="layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">I</div>
            <span>IslandLink</span>
        </div>
        <nav>
            <a class="active">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="../php/cart.php">My Cart</a>
            <a href="../php/my_orders.php">My Orders</a>
            <a href="track_delivery.php">Track Delivery</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main">
        <!-- TOPBAR -->
        <header class="topbar">
            <div></div>
            <div class="top-actions">
                <span>🛒</span>
                <span>🔔</span>
                <div class="user">
                    <strong><?php echo htmlspecialchars($fullName); ?></strong>
                    <small><?php echo htmlspecialchars($role); ?></small>
                </div>
            </div>
        </header>

        <!-- DASHBOARD CARDS -->
        <div class="cards">
            <div class="card">
                <h3>Total Orders</h3>
                <p><?php echo $orderData['total_orders']; ?> orders</p>
            </div>
            <div class="card">
                <h3>Total Spent</h3>
                <p>$<?php echo number_format($orderData['total_spent'],2); ?></p>
            </div>
            <div class="card">
                <h3>Active Deliveries</h3>
                <p><?php echo $deliveryData['active_deliveries']; ?> delivery<?php echo ($deliveryData['active_deliveries'] != 1 ? 'ies' : ''); ?></p>
            </div>
        </div>

        <!-- RECENT ORDERS -->
        <section>
            <h2>Recent Orders</h2>
            <table class="table">
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
                <?php foreach($recentOrders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                    <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                    <td>$<?php echo number_format($order['total'],2); ?></td>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- POPULAR PRODUCTS -->
        <section>
            <h2>Popular Products</h2>
            <div class="products-grid">
                <?php foreach($popularProducts as $prod): ?>
                <div class="product-card">
                    <h4><?php echo htmlspecialchars($prod['name']); ?></h4>
                    <p><?php echo htmlspecialchars($prod['category']); ?></p>
                    <span>$<?php echo number_format($prod['price'],2); ?></span>
                    <p><?php echo $prod['stock']; ?> left</p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>
</div>

</body>
</html>
