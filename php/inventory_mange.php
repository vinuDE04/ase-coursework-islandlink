<?php
session_start();
require_once 'db.php';

/* ===== Get logged-in staff info ===== */
$staffName = "Staff";
$staffRole = "staff";
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT full_name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $staffName = $user['full_name'];
        $staffRole = $user['role'];
    }
}

/* ===== Fetch inventory ===== */
$products = [];
$sql = "SELECT id, name, category, price, stock FROM products ORDER BY stock ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

/* ===== Count totals for summary cards ===== */
$totalProducts = count($products);
$lowStockItems = 0;
$inventoryValue = 0;
foreach ($products as $p) {
    if ($p['stock'] < 50) $lowStockItems++;
    $inventoryValue += $p['price'] * $p['stock'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>IslandLink | Inventory Management</title>
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
      <a class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100" href="#">Dashboard</a>
      <a class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100" href="#">Order Management</a>
      <a class="flex items-center gap-3 px-3 py-2 rounded bg-blue-100 text-blue-600 font-medium" href="#">Inventory</a>
      <a class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100" href="../php/del_schedule.php">Delivery Schedule</a>
      <a class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100" href="#">Customers</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-8">

    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold">Inventory Management</h1>
        <p class="text-sm text-gray-500">Monitor and manage stock levels</p>
      </div>

      <input
        type="text"
        placeholder="Search products..."
        class="w-96 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl border">
        <p class="text-sm text-gray-500">Total Products</p>
        <p class="text-2xl font-bold mt-2"><?= $totalProducts ?></p>
      </div>

      <div class="bg-white p-6 rounded-xl border">
        <p class="text-sm text-gray-500">Low Stock Items</p>
        <p class="text-2xl font-bold mt-2 text-orange-500"><?= $lowStockItems ?></p>
      </div>

      <div class="bg-white p-6 rounded-xl border">
        <p class="text-sm text-gray-500">Inventory Value</p>
        <p class="text-2xl font-bold mt-2 text-green-600">$<?= number_format($inventoryValue, 2) ?></p>
      </div>
    </div>

    <!-- Product Table -->
    <div class="bg-white rounded-xl border">
      <div class="p-6 border-b font-semibold">Product Inventory</div>

      <table class="w-full text-sm">
        <thead class="text-left text-gray-500">
          <tr class="border-b">
            <th class="px-6 py-4">Product</th>
            <th class="px-6 py-4">Category</th>
            <th class="px-6 py-4">Price</th>
            <th class="px-6 py-4">Stock</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4">Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php foreach ($products as $p): ?>
          <tr class="border-b">
            <td class="px-6 py-4 font-medium"><?= htmlspecialchars($p['name']) ?></td>
            <td class="px-6 py-4"><?= htmlspecialchars($p['category']) ?></td>
            <td class="px-6 py-4">$<?= number_format($p['price'], 2) ?></td>
            <td class="px-6 py-4 <?= $p['stock'] < 50 ? 'text-red-500 font-semibold' : '' ?>">
              <?= $p['stock'] ?> units
            </td>
            <td class="px-6 py-4">
              <span class="px-3 py-1 text-xs rounded-full <?= $p['stock'] < 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' ?>">
                <?= $p['stock'] < 50 ? 'Low Stock' : 'In Stock' ?>
              </span>
            </td>
            <td class="px-6 py-4">
              <a href="restock.php?id=<?= $p['id'] ?>" class="px-4 py-1 border rounded hover:bg-gray-100">Restock</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

</body>
</html>
