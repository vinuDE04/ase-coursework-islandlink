<?php
session_start();
include "db.php"; // adjust path if needed

// Check driver login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../html/login.html");
    exit();
}

// Driver name
$driver_name = $_SESSION['driver_name'] ?? 'Delivery Driver';

// Fetch today's deliveries
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT * FROM deliveries
    WHERE driver_name = ? AND DATE(created_at) = ?
    ORDER BY id ASC
");
$stmt->bind_param("ss", $driver_name, $today);
$stmt->execute();
$result = $stmt->get_result();
$deliveries = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink | My Deliveries</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/products.css"> 
</head>
<body>

<div class="layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">IL</div>
            <span>IslandLink</span>
        </div>
        <nav>
            <a href="../php/delivery_dash.php">Dashboard</a>
            <a href="../php/my_deliveries.php" class="active">My Deliveries</a>
            <a href="../php/history.php">History</a>

        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main">
        <!-- TOPBAR -->
        <div class="topbar">
            <h1>My Deliveries</h1>
            <div class="top-actions">
                <div class="user">
                    <small><?php echo htmlspecialchars($driver_name); ?></small>
                    <strong>Driver</strong>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <?php if(empty($deliveries)): ?>
                <p>No deliveries scheduled for today.</p>
            <?php else: ?>
                <?php foreach($deliveries as $index => $d): ?>
                    <div class="card">
                        <small>Stop # <?php echo $index + 1; ?></small>
                        <h2>Order <?php echo htmlspecialchars($d['order_id']); ?></h2>
                        <p>Driver: <?php echo htmlspecialchars($d['driver_name']); ?></p>
                        <p>Status: <?php echo htmlspecialchars($d['status']); ?></p>
                        <p>Created: <?php echo date('H:i', strtotime($d['created_at'])); ?></p>

                        <div class="card-footer">
                            <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $d['lat'].','.$d['lng']; ?>" target="_blank">
                                Navigate
                            </a>
                            <a href="tel:+94123456789">Call</a>

                            <?php if($d['status'] != 'Completed'): ?>
                            <form method="POST" action="complete_delivery.php">
                                <input type="hidden" name="delivery_id" value="<?php echo $d['id']; ?>">
                                <button type="submit">Complete</button>
                            </form>
                            <?php else: ?>
                                <span class="stock green">Completed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
