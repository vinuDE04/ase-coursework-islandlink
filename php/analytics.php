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

// --- KPI STATS ---
$users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$revenue = $conn->query("SELECT SUM(total) AS total FROM orders")->fetch_assoc()['total'] ?? 0;
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'];

// --- Revenue Over Time (Monthly) ---
$result = $conn->query("SELECT DATE_FORMAT(created_at,'%Y-%m') AS month, SUM(total) AS revenue 
                        FROM orders GROUP BY month ORDER BY month ASC");
$labelsRevenue = [];
$valuesRevenue = [];
while ($row = $result->fetch_assoc()) {
    $labelsRevenue[] = $row['month'];
    $valuesRevenue[] = $row['revenue'];
}

// --- Orders by Status ---
$result = $conn->query("SELECT status, COUNT(*) AS count FROM orders GROUP BY status");
$orderStatusLabels = [];
$orderStatusValues = [];
while ($row = $result->fetch_assoc()) {
    $orderStatusLabels[] = $row['status'];
    $orderStatusValues[] = $row['count'];
}

// --- Dummy Weekly Users Chart from orders (for simplicity) ---
$result = $conn->query("SELECT DAYNAME(created_at) AS day, COUNT(*) AS users 
                        FROM orders GROUP BY day ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
$labelsUsers = [];
$valuesUsers = [];
while ($row = $result->fetch_assoc()) {
    $labelsUsers[] = $row['day'];
    $valuesUsers[] = $row['users'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink | Admin Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body { font-family: Arial, sans-serif; }
</style>
</head>
<body class="bg-slate-50 text-slate-800">

<!-- TOPBAR -->
<header class="h-16 bg-white border-b flex items-center justify-between px-6">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center font-bold">I</div>
        <span class="font-bold text-lg">IslandLink</span>
    </div>

    <div class="flex items-center gap-6">
        <div class="relative">🔔</div>
        <div class="text-right">
            <p class="text-sm font-medium"><?= htmlspecialchars($adminName) ?></p>
            <p class="text-xs text-slate-500"><?= htmlspecialchars($adminRole) ?></p>
        </div>
    </div>
</header>

<div class="flex min-h-[calc(100vh-64px)]">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r p-4 space-y-2">
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100" href="">Dashboard</a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-blue-50 text-blue-600 font-medium " href="#">Analytics</a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100" href="#">User Management</a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-100" href="#">Reports</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8">

        <h1 class="text-2xl font-bold mb-6">Admin Analytics</h1>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white border rounded-xl p-6">
                <p class="text-sm text-slate-500">Total Users</p>
                <h2 class="text-2xl font-bold mt-1"><?= $users ?></h2>
            </div>
            <div class="bg-white border rounded-xl p-6">
                <p class="text-sm text-slate-500">Revenue</p>
                <h2 class="text-2xl font-bold mt-1">$<?= number_format($revenue,2) ?></h2>
            </div>
            <div class="bg-white border rounded-xl p-6">
                <p class="text-sm text-slate-500">Total Orders</p>
                <h2 class="text-2xl font-bold mt-1"><?= $totalOrders ?></h2>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="bg-white border rounded-xl p-6">
                <h3 class="font-semibold mb-4">Weekly Orders</h3>
                <canvas id="usersChart"></canvas>
            </div>
            <div class="bg-white border rounded-xl p-6">
                <h3 class="font-semibold mb-4">Revenue Over Time</h3>
                <canvas id="revenueChart"></canvas>
            </div>
            <div class="bg-white border rounded-xl p-6">
                <h3 class="font-semibold mb-4">Orders by Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

    </main>
</div>

<script>
// --- Weekly Orders Chart ---
new Chart(document.getElementById('usersChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labelsUsers) ?>,
        datasets: [{
            label: 'Orders',
            data: <?= json_encode($valuesUsers) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// --- Revenue Over Time Chart ---
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labelsRevenue) ?>,
        datasets: [{
            label: 'Revenue',
            data: <?= json_encode($valuesRevenue) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            fill: true
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// --- Orders by Status Chart ---
new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($orderStatusLabels) ?>,
        datasets: [{
            data: <?= json_encode($orderStatusValues) ?>,
            backgroundColor: [
                'rgba(54, 162, 235, 0.6)',
                'rgba(255, 99, 132, 0.6)',
                'rgba(255, 206, 86, 0.6)',
                'rgba(75, 192, 192, 0.6)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: { responsive: true }
});
</script>

</body>
</html>
