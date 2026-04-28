<?php
session_start();
include "db.php";

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cartId = intval($_POST['cart_id']);

    // Delete item from cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
    $stmt->execute();
}

// Redirect back to cart page
header("Location: ../php/cart.php");
exit();
