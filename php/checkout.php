<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

$userId = $_SESSION['user_id'];

$subtotal = $_POST['subtotal'];
$tax = $_POST['tax'];
$shipping = $_POST['shipping'];
$total = $_POST['total'];
$address = trim($_POST['delivery_address']);

$orderNumber = "ORD-" . strtoupper(uniqid());

$stmt = $conn->prepare("
    INSERT INTO orders
    (user_id, order_number, subtotal, tax, shipping, total, delivery_address)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isdddds",
    $userId,
    $orderNumber,
    $subtotal,
    $tax,
    $shipping,
    $total,
    $address
);

$stmt->execute();

/* OPTIONAL: clear cart after order */
$conn->query("DELETE FROM cart WHERE user_id = $userId");

header("Location: ../php/checkout_payment.php");
exit();
