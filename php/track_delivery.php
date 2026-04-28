<?php
session_start();
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}
if (!isset($_GET['id'])) {
    die("Invalid delivery request");
}

$deliveryId = intval($_GET['id']);
$fullName = $_SESSION['full_name'];
$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Fetch active deliveries for this user
$stmt = $conn->prepare("
    SELECT d.*, o.order_number
    FROM deliveries d
    JOIN orders o ON d.order_id = o.id
    WHERE d.id = ?
");

$stmt->bind_param("i", $deliveryId);
$stmt->execute();

$deliveries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IslandLink | Track Delivery</title>
<link rel="stylesheet" href="../css/products.css">
<style>
.main { padding: 30px; }
.map-container { width: 100%; height: 500px; border-radius: 12px; overflow: hidden; margin-top: 20px; }
.delivery-info { margin-top: 15px; font-size: 14px; color: #374151; }
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
            <a href="../php/my_orders.php">My Orders</a>
            <a class="active">Track Delivery</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
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

        <h2>Track Delivery</h2>
        <p>See the real-time location of your active deliveries</p>

        <?php if(empty($deliveries)): ?>
            <p>No active deliveries at the moment.</p>
        <?php else: ?>
            <?php foreach($deliveries as $delivery): ?>
                <div class="delivery-info">
                    <strong>Order:</strong> <?php echo htmlspecialchars($delivery['order_number']); ?> <br>
                    <strong>Driver:</strong> <?php echo htmlspecialchars($delivery['driver_name']); ?> <br>
                    <strong>Status:</strong> <?php echo htmlspecialchars($delivery['status']); ?>
                </div>
                <div id="map-<?php echo $delivery['id']; ?>" class="map-container"></div>

                <script>
            

                function initMap<?php echo $delivery['id']; ?>() {
    const deliveryLocation = { lat: <?php echo $delivery['lat']; ?>, lng: <?php echo $delivery['lng']; ?> };
    
    const map = new google.maps.Map(document.getElementById('map-<?php echo $delivery['id']; ?>'), {
        zoom: 14,
        center: deliveryLocation
    });
    
    const directionsService = new google.maps.DirectionsService();
    const directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);

    // Example: Driver current location (static, replace with real if available)
    const driverLocation = { lat: <?php echo $delivery['driver_lat'] ?? $delivery['lat']; ?>, lng: <?php echo $delivery['driver_lng'] ?? $delivery['lng']; ?> };

    directionsService.route({
        origin: driverLocation,
        destination: deliveryLocation,
        travelMode: 'DRIVING'
    }, (result, status) => {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);
        } else {
            console.error('Directions request failed:', status);
        }
    });

    // Optional: Marker for driver
    new google.maps.Marker({ position: driverLocation, map: map, title: 'Driver' });
    new google.maps.Marker({ position: deliveryLocation, map: map, title: 'Delivery' });
}

                </script>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Google Maps JS -->
        <script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3UhcJwV9ZJC7w2PvAu1yZvFKkGvesZ2M&callback=initAllMaps">
</script>

        <script>
        function initAllMaps() {
            <?php foreach($deliveries as $delivery): ?>
                initMap<?php echo $delivery['id']; ?>();
            <?php endforeach; ?>
        }
        </script>
    </main>
</div>
</body>
</html>
