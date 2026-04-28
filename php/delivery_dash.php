<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../html/login.html");
    exit();
}

$driverName = $_SESSION['full_name'];

// Current delivery
$stmt = $conn->prepare("
    SELECT d.*, o.order_number, o.delivery_address, u.full_name AS customer_name
    FROM deliveries d
    JOIN orders o ON d.order_id = o.id
    JOIN users u ON o.user_id = u.id
    WHERE d.driver_name = ? AND LOWER(d.status) = 'out for delivery'
    ORDER BY d.created_at ASC
    LIMIT 1
");
$stmt->bind_param("s", $driverName);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();

// Next deliveries
$stmt2 = $conn->prepare("
    SELECT d.*, o.delivery_address, u.full_name AS customer_name
    FROM deliveries d
    JOIN orders o ON d.order_id = o.id
    JOIN users u ON o.user_id = u.id
    WHERE d.driver_name = ? AND LOWER(d.status) = 'pending'
    ORDER BY d.created_at ASC
");
$stmt2->bind_param("s", $driverName);
$stmt2->execute();
$nextStops = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>IslandLink | My Deliveries</title>
    <link rel="stylesheet" href="../css/products.css">
    <style>
        .page {
            padding: 30px;
        }

        .delivery-card {
    background:#fff;
    padding:25px;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,.05);
    margin-bottom:20px;
}
        .btn {
            padding: 12px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-blue {
            background: #2563eb;
            color: #fff;
        }

        .btn-green {
            background: #14b8a6;
            color: #fff;
            width: 100%;
            margin-top: 15px;
        }

        .row {
            display: flex;
            gap: 10px;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
        }

        .badge.pending {
            background: #fef3c7;
            color: #92400e;
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <div class="logo">I</div>IslandLink
            </div>
            <nav>
                <a class="active">Dashboard</a>
                <a href="../php/my_deliveries.php">My Deliveries</a>
                <a>History</a>
            </nav>
        </aside>

        <main class="main">

<header class="topbar">
    <div></div>
    <div class="top-actions">
        <div class="user">
            <strong><?php echo htmlspecialchars($driverName); ?></strong>
            <small>Delivery Driver</small>
        </div>
    </div>
</header>

<div class="content">

<h2>My Deliveries</h2>

<?php if($current): ?>
<div class="delivery-card">
    <h4>Current Stop</h4>
    <p><strong>Estimated Arrival:</strong> <?php echo htmlspecialchars($current['eta'] ?? 'N/A'); ?></p>

    <p>
        <strong><?php echo htmlspecialchars($current['customer_name']); ?></strong><br>
        <?php echo htmlspecialchars($current['delivery_address']); ?>
    </p>

    <div class="row">
        <?php if ($current['lat'] && $current['lng']): ?>
<a class="btn btn-blue"
   href="https://www.google.com/maps?q=<?php echo $current['lat']; ?>,<?php echo $current['lng']; ?>"
   target="_blank">Start Navigation</a>
<?php else: ?>
<p>Location not available</p>
<?php endif; ?>
    </div>

    <form method="post" action="../php/mark_delivered.php">
        <input type="hidden" name="delivery_id" value="<?php echo $current['id']; ?>">
        <button class="btn btn-green">✔ Mark as Delivered</button>
    </form>
</div>
<?php endif; ?>

<h4>Up Next</h4>
<?php foreach($nextStops as $stop): ?>
<div class="delivery-card">
    <strong><?php echo htmlspecialchars($stop['customer_name']); ?></strong><br>
    <?php echo htmlspecialchars($stop['delivery_address']); ?>
    <span class="badge pending">Pending</span>
</div>
<?php endforeach; ?>

</div>
</main>
</body>
</html>