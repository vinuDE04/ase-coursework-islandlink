<?php
require_once 'db.php';

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Invalid request");
}

$id = intval($_GET['id']);
$status = $_GET['status'];

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    header("Location: order_manage.php");
    exit();
} else {
    echo "Failed to update order";
}
?>