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

/* ===== Fetch delivery data ===== */
/* Active deliveries */
$activeDeliveries = [];
$sqlActive = "SELECT id, order_id, status, driver_name, lat, lng, created_at
              FROM deliveries
              WHERE LOWER(status) = 'out for delivery'
              ORDER BY created_at ASC";
$resActive = $conn->query($sqlActive);
while ($row = $resActive->fetch_assoc()) {
    $activeDeliveries[] = $row;
}

/* Pending assignments (deliveries without driver) */
$pendingAssignments = [];
$sqlPending = "SELECT id, order_id, lat, lng, created_at
               FROM deliveries
               WHERE driver_name IS NULL OR driver_name = ''
               ORDER BY created_at ASC";
$resPending = $conn->query($sqlPending);
while ($row = $resPending->fetch_assoc()) {
    $pendingAssignments[] = $row;
}

/* Available drivers */
$availableDrivers = [];
$sqlDrivers = "SELECT id, full_name FROM users WHERE role='driver'";
$resDrivers = $conn->query($sqlDrivers);
while ($row = $resDrivers->fetch_assoc()) {
    $availableDrivers[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>IslandLink | Delivery Schedule</title>
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
      <a class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100" href="../php/staff_dash.php">Dashboard</a>
      <a class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100" href="../php/order_manage.php">Order Management</a>
      <a class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100" href="../php/inventory_manage.php">Inventory</a>
      <a class="flex items-center gap-3 px-3 py-2 rounded bg-blue-100 text-blue-600 font-medium" href="#">Delivery Schedule</a>
      <a class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100" href="../php/customer_manage.php">Customers</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-8">

    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold">Delivery Schedule</h1>
        <p class="text-sm text-gray-500">Manage and assign deliveries</p>
      </div>
      
      <div class="text-right">
        <p class="font-medium"><?= htmlspecialchars($staffName) ?></p>
        <p class="text-sm text-gray-500"><?= htmlspecialchars($staffRole) ?></p>
      </div>
      <button
  onclick="openModal()"
  class="ml-6 px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
  Schedule Delivery
</button>

    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl border">
        <p class="text-sm text-gray-500">Active Deliveries</p>
        <p class="text-2xl font-bold mt-2"><?= count($activeDeliveries) ?></p>
      </div>
      <div class="bg-white p-6 rounded-xl border">
        <p class="text-sm text-gray-500">Pending Assignments</p>
        <p class="text-2xl font-bold mt-2"><?= count($pendingAssignments) ?></p>
      </div>
      <div class="bg-white p-6 rounded-xl border">
        <p class="text-sm text-gray-500">Available Drivers</p>
        <p class="text-2xl font-bold mt-2"><?= count($availableDrivers) ?></p>
      </div>
    </div>

    <!-- Active Deliveries Table -->
    <div class="bg-white rounded-xl border mb-8 overflow-hidden">
      <div class="p-6 border-b font-semibold">Active Deliveries</div>
      <table class="w-full text-sm">
        <thead class="text-left text-gray-500">
          <tr class="border-b">
            <th class="px-6 py-4">Order Number</th>
            <th class="px-6 py-4">Driver</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4">Address</th>
            <th class="px-6 py-4">Scheduled Time</th>
            <th class="px-6 py-4">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($activeDeliveries as $d): ?>
          <tr class="border-b">
            <td class="px-6 py-4 font-medium"><?= htmlspecialchars($d['order_id']) ?></td>
           <td class="px-6 py-4"><?= htmlspecialchars($d['driver_name'] ?: 'Unassigned') ?></td>

            <td class="px-6 py-4">
              <span class="px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-700"><?= htmlspecialchars($d['status']) ?></span>
            </td>
            <td class="px-6 py-4"><?= htmlspecialchars($d['lat'] . ', ' . $d['lng']) ?></td>
            <td class="px-6 py-4"><?= date("H:i A", strtotime($d['created_at'])) ?></td>
            <td class="px-6 py-4">
              <a href="track_delivery.php?id=<?= $d['id'] ?>" class="px-4 py-1 border rounded hover:bg-gray-100">Track</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pending Assignments Table -->
    <div class="bg-white rounded-xl border mb-8 overflow-hidden">
      <div class="p-6 border-b font-semibold">Pending Assignments</div>
      <table class="w-full text-sm">
        <thead class="text-left text-gray-500">
          <tr class="border-b">
            <th class="px-6 py-4">Order Number</th>
            <th class="px-6 py-4">Address</th>
            <th class="px-6 py-4">Scheduled Time</th>
            <th class="px-6 py-4">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($pendingAssignments as $p): ?>
          <tr class="border-b">
            <td class="px-6 py-4"><?= htmlspecialchars($p['order_id']) ?></td>
<td class="px-6 py-4"><?= htmlspecialchars($p['lat'] . ', ' . $p['lng']) ?></td>
<td class="px-6 py-4"><?= date("H:i A", strtotime($p['created_at'])) ?></td>

            <td class="px-6 py-4">
              <a href="assign_driver.php?id=<?= $p['id'] ?>" class="px-4 py-1 border rounded hover:bg-gray-100">Assign Driver</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>
<!-- Schedule Delivery Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
  <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 relative">

    <!-- Close Button -->
    <button onclick="closeModal()" class="absolute top-3 right-4 text-gray-400 hover:text-gray-600 text-xl">
      &times;
    </button>

    <h2 class="text-xl font-bold mb-4">Schedule Delivery</h2>

    <form action="schedule_delivery.php" method="POST" class="space-y-4">

      <!-- Order ID -->
      <div>
        <label class="block text-sm font-medium mb-1">Order ID</label>
        <input
          type="number"
          name="order_id"
          required
          class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Driver -->
      <div>
        <label class="block text-sm font-medium mb-1">Assign Driver</label>
        <select
          name="driver_name"
          class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
          <option value="">-- Select Driver --</option>
          <?php foreach ($availableDrivers as $driver): ?>
            <option value="<?= htmlspecialchars($driver['full_name']) ?>">
              <?= htmlspecialchars($driver['full_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Latitude -->
      <div>
        <label class="block text-sm font-medium mb-1">Latitude</label>
        <input
          type="text"
          name="lat"
          required
          class="w-full border rounded-lg px-4 py-2">
      </div>

      <!-- Longitude -->
      <div>
        <label class="block text-sm font-medium mb-1">Longitude</label>
        <input
          type="text"
          name="lng"
          required
          class="w-full border rounded-lg px-4 py-2">
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-3 pt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg">
          Cancel
        </button>
        <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          Schedule
        </button>
      </div>

    </form>
  </div>
</div>
<script>
function openModal() {
  document.getElementById('scheduleModal').classList.remove('hidden');
  document.getElementById('scheduleModal').classList.add('flex');
}

function closeModal() {
  document.getElementById('scheduleModal').classList.add('hidden');
  document.getElementById('scheduleModal').classList.remove('flex');
}
</script>


</body>
</html>
