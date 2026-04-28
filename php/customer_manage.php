<?php
session_start();
require_once 'db.php';

/* ===== Logged-in staff ===== */
$staffName = "Staff";
$staffRole = "staff";
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $u = $res->fetch_assoc();
        $staffName = $u['full_name'];
        $staffRole = $u['role'];
    }
}

/* ===== Fetch customers ===== */
$customers = [];
$sql = "
SELECT 
    u.id,
    u.full_name,
    u.email,
    u.phone,
    COUNT(o.id) AS total_orders,
    IFNULL(SUM(o.total),0) AS total_spent
FROM users u
LEFT JOIN orders o ON u.id = o.user_id
WHERE u.role = 'customer'
GROUP BY u.id
ORDER BY total_spent DESC
";

$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink | Customer Management</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800">

<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 bg-white border-r px-6 py-6">
    <div class="text-xl font-bold mb-8 flex items-center gap-2">
      <div class="w-8 h-8 rounded bg-blue-600 text-white flex items-center justify-center">I</div>
      IslandLink
    </div>

    <nav class="space-y-2 text-sm">
      <a class="block px-3 py-2 rounded hover:bg-gray-100" href="#">Dashboard</a>
      <a class="block px-3 py-2 rounded hover:bg-gray-100" href="#">Order Management</a>
      <a class="block px-3 py-2 rounded hover:bg-gray-100" href="inventory_manage.php">Inventory</a>
      <a class="block px-3 py-2 rounded hover:bg-gray-100" href="del_schedule.php">Delivery Schedule</a>
      <a class="block px-3 py-2 rounded bg-blue-100 text-blue-600 font-medium" href="#">Customers</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="flex-1 p-8">

    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold">Customer Management</h1>
        <p class="text-sm text-gray-500">View and manage customer information</p>
      </div>
      <div class="text-right">
        <p class="font-medium"><?= htmlspecialchars($staffName) ?></p>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($staffRole) ?></p>
      </div>
    </div>

    <!-- Search -->
    <input
      type="text"
      placeholder="Search customers..."
      class="w-full mb-6 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
    >

    <!-- Customer Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

      <?php foreach ($customers as $c): ?>
      <div class="bg-white rounded-xl border p-6 shadow-sm">

        <div class="flex justify-between items-start mb-3">
          <h3 class="text-lg font-semibold"><?= htmlspecialchars($c['full_name']) ?></h3>
          <span class="text-xs text-gray-400">Customer ID: #<?= $c['id'] ?></span>
        </div>

        <p class="text-sm text-gray-600"><?= htmlspecialchars($c['email']) ?></p>
        <p class="text-sm text-gray-600"><?= htmlspecialchars($c['phone']) ?></p>

        <div class="flex justify-between items-center text-sm mb-3">
          <span>Total Orders</span>
          <span class="font-semibold"><?= $c['total_orders'] ?></span>
        </div>

        <div class="flex justify-between items-center text-sm mb-4">
          <span>Total Spent</span>
          <span class="font-bold text-green-600">
            $<?= number_format($c['total_spent'], 2) ?>
          </span>
        </div>

        <div class="flex gap-2">
          <a href="customer_orders.php?id=<?= $c['id'] ?>"
             class="flex-1 text-center px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            View Orders
          </a>
          <a href="mailto:<?= htmlspecialchars($c['email']) ?>"
             class="flex-1 text-center px-3 py-2 text-sm border rounded-lg hover:bg-gray-100">
            Contact
          </a>
        </div>

      </div>
      <?php endforeach; ?>

    </div>

  </main>
</div>

</body>
</html>
