<?php
session_start();
$delivery_id = $_POST['delivery_id'] ?? null;

if($delivery_id){
    $mysqli = new mysqli("localhost", "root", "", "isdn");
    $stmt = $mysqli->prepare("UPDATE deliveries SET status='Completed' WHERE id=?");
    $stmt->bind_param("i", $delivery_id);
    $stmt->execute();
}

header("Location: my_deliveries.php");
exit;
