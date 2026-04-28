<?php
session_start();
include "../php/db.php";

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

$fullName = $_SESSION['full_name'];
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Fetch orders for this user
$orders = $conn->query("
    SELECT * FROM orders
    WHERE user_id = $userId
    ORDER BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Fetch items for each order
$orderItems = [];
foreach ($orders as $order) {
    $orderId = $order['id'];
    $itemsResult = $conn->query("
        SELECT p.name, c.quantity
        FROM order_items c
        JOIN products p ON c.product_id = p.id
        WHERE c.order_id = $orderId
    ");
    $orderItems[$orderId] = $itemsResult->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IslandLink | My Orders</title>
<link rel="stylesheet" href="../css/products.css">
<style>
/* ORDERS PAGE STYLES */
.orders-container { padding: 30px; }
.order-card { background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; }
.order-header div { margin-bottom: 8px; }
.order-items { margin-top: 10px; }
.order-items p { margin: 4px 0; }
.status { padding: 4px 12px; border-radius: 20px; font-weight: bold; font-size: 14px; display: inline-block; margin-top: 4px; }
.status.Delivered { background: #dcfce7; color: #166534; }
.status.OutForDelivery { background: #fef9c3; color: #a16207; }
.status.Pending { background: #fee2e2; color: #991b1b; }
.view-btn { margin-top: 10px; padding: 8px 14px; background: #2563eb; color: #fff; border: none; border-radius: 8px; cursor: pointer; display: block; }
.order-total { font-weight: bold; margin-top: 8px; }
.order-address { font-size: 14px; color: #6b7280; margin-top: 4px; }
/* Align items nicely on smaller screens */
@media (max-width: 600px) {
    .order-header { flex-direction: column; align-items: flex-start; }
    .order-total { margin-top: 6px; }
}
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
            <a href="../php/retail_dash.php">Dashboard</a>
            <a href="../php/products.php">Products</a>
            <a href="../php/cart.php">My Cart</a>
            <a class="active">My Orders</a>
            <a href="track_delivery.php">Track Delivery</a>
        </nav>
    </aside>

    <!-- MAIN -->
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

        <!-- CONTENT -->
        <section class="orders-container">
            <h2>My Orders</h2>
            <p>Track and manage your orders</p>

            <?php if (empty($orders)): ?>
                <p>No orders found.</p>
            <?php else: ?>
                <?php foreach($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong><br>
                                Placed on <?php echo htmlspecialchars($order['created_at']); ?><br>
                                <span class="status <?php echo str_replace(' ', '', $order['status']); ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </div>
                            <div class="order-total">
                                Total Amount: $<?php echo number_format($order['total'], 2); ?>
                            </div>
                        </div>
                        <div class="order-items">
                            <strong>Items (<?php echo count($orderItems[$order['id']]); ?> products)</strong>
                            <?php foreach($orderItems[$order['id']] as $item): ?>
                                <p><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></p>
                            <?php endforeach; ?>
                            <!-- Only show address if it exists -->
                            <?php if(isset($order['delivery_address'])): ?>
                                <p class="order-address">Delivery Address: <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                            <?php endif; ?>
                        </div>
                        <button class="view-btn">View Details</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
