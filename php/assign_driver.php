<?php
require_once 'db.php';

/* ===== HANDLE FORM SUBMIT (POST) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['delivery_id'], $_POST['driver_name'])) {
        die("Invalid form data");
    }

    $deliveryId = intval($_POST['delivery_id']);
    $driverName = $_POST['driver_name'];

    $stmt = $conn->prepare("
        UPDATE deliveries 
        SET driver_name = ?, status = 'Out for Delivery'
        WHERE id = ?
    ");
    $stmt->bind_param("si", $driverName, $deliveryId);

    if ($stmt->execute()) {
        header("Location: del_schedule.php");
        exit();
    } else {
        echo "Error assigning driver";
    }
}

/* ===== SHOW FORM (GET) ===== */
if (!isset($_GET['id'])) {
    die("Invalid request");
}

$deliveryId = intval($_GET['id']);

// Fetch drivers
$drivers = $conn->query("SELECT id, full_name FROM users WHERE role='driver'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Driver</title>
    <style>
        body { font-family: Arial; padding: 30px; }
        form { max-width: 400px; }
        select, button { width: 100%; padding: 10px; margin-top: 10px; }
    </style>
</head>
<body>

<h2>Assign Driver</h2>

<form method="POST">
    <input type="hidden" name="delivery_id" value="<?= $deliveryId ?>">

    <label>Select Driver:</label>
    <select name="driver_name" required>
        <option value="">-- Choose Driver --</option>
        <?php while ($d = $drivers->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($d['full_name']) ?>">
                <?= htmlspecialchars($d['full_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Assign</button>
</form>

</body>
</html>