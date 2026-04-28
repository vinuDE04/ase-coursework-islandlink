<?php
session_start();
require_once 'db.php';

/* ===== Get logged-in staff info ===== */
$staffName = "Staff";
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $staffName = $res->fetch_assoc()['full_name'];
    }
}

/* ===== Fetch orders ===== */
$orders = [];
$sql = "SELECT id, order_number, total, status, created_at 
        FROM orders 
        ORDER BY created_at DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

/* ===== Fetch order items ===== */
function getOrderItems($conn, $orderId) {
    $items = [];
    $stmt = $conn->prepare("
        SELECT p.name AS product_name, oi.quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $items[] = $r;
    }
    return $items;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink | Order Management</title>
<link rel="stylesheet" href="../css/staff_dash.css">

</head>
<body>

<div class="layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2>IslandLink</h2>
        <nav>
            <a href="staff_dash.php">Dashboard</a>
            <a class="active" href="order_manage.php">Order Management</a>
            <a href="../php/inventory_mange.php">Inventory</a>
            <a href="#">Delivery Schedule</a>
            <a href="#">Customers</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <div class="main">
        <!-- TOPBAR -->
        <div class="topbar">
            <h2>Order Management</h2>
            <div class="user">
                🔔 <strong><?= htmlspecialchars($staffName) ?></strong>
                <small>staff</small>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <p class="subtitle">Process and manage customer orders</p>
            <input type="text" placeholder="Search orders..." class="search-box">

            <div class="orders">
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                        <?php
$statusClass = strtolower(trim($order['status']));
$statusClass = str_replace(' ', '', $statusClass);
?>
<span class="status <?= $statusClass ?>">
    <?= htmlspecialchars($order['status']) ?>
</span>
                    </div>

                    <small><?= date("Y-m-d", strtotime($order['created_at'])) ?></small>

                    <div class="order-items">
                        <h4>Order Items</h4>
                        <?php
                        $items = getOrderItems($conn, $order['id']);
                        foreach ($items as $item):
                        ?>
                        <p>
                            <?= htmlspecialchars($item['product_name']) ?><br>
                            <small>Quantity: <?= $item['quantity'] ?></small>
                        </p>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-footer">
                        <strong>$<?= number_format($order['total'], 2) ?></strong>
                        <?php if ($order['status'] === 'Pending'): ?>
                            <a href="update_order.php?id=<?= $order['id'] ?>&status=Confirmed" class="btn confirm">Confirm</a>
                            <a href="update_order.php?id=<?= $order['id'] ?>&status=Cancelled" class="btn cancel">Cancel</a>
                        <?php endif; ?>
                        <a href="order_view.php?id=<?= $order['id'] ?>" class="btn view">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

</main>
</div>

</body>
</html>