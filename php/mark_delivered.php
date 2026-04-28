<?php
include "../php/db.php";

$id = $_POST['delivery_id'];

$conn->query("UPDATE deliveries SET status='Delivered' WHERE id=$id");

// Move next stop to active
$conn->query("
    UPDATE deliveries
    SET status='Out For Delivery'
    WHERE status='Pending'
    ORDER BY created_at ASC
    LIMIT 1
");

header("Location: ../php/delivery_dash.php");
