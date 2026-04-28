<?php
session_start();
require_once 'db.php';

$adminName = "System Admin";
$adminRole = "admin";

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $u = $res->fetch_assoc();
        $adminName = $u['full_name'];
        $adminRole = $u['role'];
    }
}

/* ===== KPI DATA ===== */

// Total Sales
$res = $conn->query("SELECT IFNULL(SUM(total),0) AS total_sales FROM orders");
$totalSales = $res->fetch_assoc()['total_sales'];

// Orders Completed
$res = $conn->query("SELECT COUNT(*) AS completed_orders FROM orders WHERE status='Delivered'");
$completedOrders = $res->fetch_assoc()['completed_orders'];

// Delivery Efficiency (% delivered)
$res = $conn->query("SELECT 
    (SUM(CASE WHEN status='Delivered' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS efficiency 
    FROM orders");
$deliveryEfficiency = round($res->fetch_assoc()['efficiency'], 1);

// Active Users (customers)
$res = $conn->query("SELECT COUNT(*) AS active_users FROM users WHERE role='customer'");
$activeUsers = $res->fetch_assoc()['active_users'];


/* ===== CHART DATA ===== */

// Weekly Sales (last 7 days)
$weeklySales = [];
$weeklyLabels = [];

$sql = "
SELECT DATE(created_at) as order_date, SUM(total) as daily_total
FROM orders
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
GROUP BY DATE(created_at)
ORDER BY order_date
";

$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $weeklyLabels[] = $row['order_date'];
    $weeklySales[] = $row['daily_total'];
}

// Order Status Distribution
$statusLabels = [];
$statusData = [];

$res = $conn->query("
SELECT status, COUNT(*) AS count
FROM orders
GROUP BY status
");

while ($row = $res->fetch_assoc()) {
    $statusLabels[] = $row['status'];
    $statusData[] = $row['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink | Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="bg-slate-50 text-slate-800">

<!-- TOP BAR -->
<header class="h-16 bg-white border-b flex items-center justify-between px-6">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center font-bold">I</div>
        <span class="font-bold text-lg">IslandLink</span>
    </div>

    <div class="flex items-center gap-6">
        <div class="relative">
            🔔
        </div>

        <div class="text-right">
            <p class="text-sm font-medium"><?= htmlspecialchars($adminName) ?></p>
            <p class="text-xs text-slate-500"><?= htmlspecialchars($adminRole) ?></p>
        </div>

    </div>
</header>

<div class="flex min-h-[calc(100vh-64px)]">

<!-- SIDEBAR -->
<aside class="w-64 bg-white border-r p-4 space-y-2">
    <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-blue-50 text-blue-600 font-medium" href="#">Dashboard</a>
    <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100" href="../php/analytics.php">Analytics</a>
    <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100" href="../php/user_manage.php">User Management</a>
    <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100" href="../php/reports.php">Reports</a>
</aside>

<!-- MAIN CONTENT -->
<main class="flex-1 p-8">

<!-- TITLE -->
<h1 class="text-2xl font-bold mb-6">Executive Overview</h1>

<!-- KPI CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

<!-- CARD -->
<div class="bg-white border rounded-xl p-6">
    <p class="text-sm text-slate-500">Total Sales</p>
    <h2 class="text-2xl font-bold mt-1">
    $<?= number_format($totalSales, 2) ?>
</h2>

    <p class="text-sm text-green-600 mt-2">▲ 12.5% from last month</p>
</div>

<div class="bg-white border rounded-xl p-6">
    <p class="text-sm text-slate-500">Orders Completed</p>
    <h2 class="text-2xl font-bold mt-1">
    <?= $completedOrders ?>
</h2>

    <p class="text-sm text-green-600 mt-2">▲ 8.2% from last month</p>
</div>

<div class="bg-white border rounded-xl p-6">
    <p class="text-sm text-slate-500">Delivery Efficiency</p>
    <h2 class="text-2xl font-bold mt-1">
    <?= $deliveryEfficiency ?>%
</h2>

    <p class="text-sm text-red-600 mt-2">▼ 2.1% from last month</p>
</div>

<div class="bg-white border rounded-xl p-6">
    <p class="text-sm text-slate-500">Active Users</p>
    <h2 class="text-2xl font-bold mt-1">
    <?= $activeUsers ?>
</h2>

    <p class="text-sm text-green-600 mt-2">▲ 15.3% from last month</p>
</div>

</div>

<!-- CHARTS -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

<!-- Weekly Sales -->
<div class="bg-white border rounded-xl p-6">
    <h3 class="font-semibold mb-4">Weekly Sales Trend</h3>
    <div class="h-72 flex items-center justify-center text-slate-400">
        <canvas id="weeklySalesChart"></canvas>

    </div>
</div>

<!-- Sales by Category -->
<div class="bg-white border rounded-xl p-6">
    <h3 class="font-semibold mb-4">Sales by Category</h3>
    <div class="h-72 flex items-center justify-center text-slate-400">
        <canvas id="orderStatusChart"></canvas>

    </div>
</div>

</div>

</main>
</div>
<script>
/* Weekly Sales Chart */
new Chart(document.getElementById('weeklySalesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($weeklyLabels) ?>,
        datasets: [{
            label: 'Sales ($)',
            data: <?= json_encode($weeklySales) ?>,
            borderWidth: 3,
            tension: 0.4,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

/* Order Status Chart */
new Chart(document.getElementById('orderStatusChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{
            data: <?= json_encode($statusData) ?>
        }]
    },
    options: {
        responsive: true
    }
});
</script>

</body>
</html>
