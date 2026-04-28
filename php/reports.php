<?php
session_start();
require_once 'db.php';

// Admin info
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

// Summary stats
$totalOrders = $conn->query("SELECT COUNT(*) total FROM orders")->fetch_assoc()['total'];
$totalRevenue = $conn->query("
  SELECT SUM(total) total 
  FROM orders 
  WHERE status = 'Delivered'
")->fetch_assoc()['total'] ?? 0;

// Fetch all orders
$ordersResult = $conn->query("SELECT id, order_number, total, status, created_at FROM orders ORDER BY created_at DESC");
$orders = [];
while ($row = $ordersResult->fetch_assoc()) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink | Reports</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 h-screen">

<div class="flex h-full">

  <!-- SIDEBAR -->
  <aside class="sidebar w-64 bg-white border-r p-5 flex-shrink-0">
    <div class="brand flex items-center gap-2 mb-6">
      <span class="font-bold text-lg">IslandLink</span>
    </div>
    <nav class="flex flex-col gap-2">
      <a href="index.php" class="px-4 py-2 rounded hover:bg-blue-50">Dashboard</a>
      <a href="user_manage.php" class="px-4 py-2 rounded hover:bg-blue-50">Users</a>
      <a href="reports.php" class="px-4 py-2 rounded bg-blue-50 text-blue-600 font-semibold">Reports</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col h-full">

    <!-- TOPBAR -->
    <header class="topbar bg-white border-b px-6 py-4 flex justify-between items-center flex-shrink-0">
      <h1 class="text-xl font-bold">Reports</h1>
      <div class="flex items-center gap-4">
        <div class="text-right">
          <p class="font-medium"><?= htmlspecialchars($adminName) ?></p>
          <small class="text-gray-500"><?= htmlspecialchars($adminRole) ?></small>
        </div>
      </div>
    </header>

    <!-- CONTENT -->
    <main class="flex-1 overflow-auto p-6 space-y-6">

      <!-- Generate Report Form -->
      <div class="bg-white shadow rounded p-6">
        <h2 class="text-lg font-semibold mb-4">Generate New Report</h2>
        <form action="../php/generate_report.php" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          
          <!-- Report Type -->
          <div class="flex flex-col">
            <label class="text-gray-600 mb-1" for="report_type">Report Type</label>
            <select id="report_type" name="report_type" class="border border-gray-300 rounded p-2">
              <option value="orders">Orders</option>
              <option value="revenue">Revenue</option>
              <option value="invoices">Invoices</option>
            </select>
          </div>

          <!-- Start Date -->
          <div class="flex flex-col">
            <label class="text-gray-600 mb-1" for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="border border-gray-300 rounded p-2">
          </div>

          <!-- End Date -->
          <div class="flex flex-col">
            <label class="text-gray-600 mb-1" for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" class="border border-gray-300 rounded p-2">
          </div>

          <!-- Generate Button -->
          <div class="flex">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">
              Generate & Download
            </button>
          </div>

        </form>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded p-4">
          <span class="text-gray-500 text-sm">Total Orders</span>
          <h3 class="text-xl font-semibold"><?= $totalOrders ?></h3>
        </div>
        <div class="bg-white shadow rounded p-4">
          <span class="text-gray-500 text-sm">Total Revenue</span>
          <h3 class="text-xl font-semibold">$<?= number_format($totalRevenue,2) ?></h3>
        </div>
      </div>

      <!-- Orders Table -->
      <div class="overflow-x-auto bg-white rounded-xl shadow p-4">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach($orders as $order): ?>
            <tr>
              <td class="px-6 py-4"><?= htmlspecialchars($order['order_number']) ?></td>
              <td class="px-6 py-4">$<?= number_format($order['total'],2) ?></td>
              <td class="px-6 py-4">
                <?php
                  $status = strtolower($order['status']);
                  $statusClasses = [
  'delivered' => 'bg-green-100 text-green-800',
  'pending' => 'bg-yellow-100 text-yellow-800',
  'cancelled' => 'bg-red-100 text-red-800'
];
                  $class = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $class ?>"><?= ucfirst($status) ?></span>
              </td>
              <td class="px-6 py-4"><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($orders)): ?>
            <tr>
              <td colspan="4" class="px-6 py-4 text-center text-gray-500">No orders found</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </main>

  </div>

</div>

</body>
</html>
