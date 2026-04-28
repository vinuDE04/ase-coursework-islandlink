<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $order_id = $_POST['order_id'];
    $driver_name = $_POST['driver_name'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $status = $driver_name ? 'Out For Delivery' : 'Pending';

    $stmt = $conn->prepare("
        INSERT INTO deliveries (order_id, driver_name, lat, lng, status, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("issss", $order_id, $driver_name, $lat, $lng, $status);
    $stmt->execute();

    header("Location: del_schedule.php");
    exit;
}
