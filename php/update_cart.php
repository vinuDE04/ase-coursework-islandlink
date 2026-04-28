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

    // Fetch current quantity
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $quantity = $row['quantity'];

        // Increase or decrease
        if (isset($_POST['increase'])) {
            $quantity++;
        } elseif (isset($_POST['decrease']) && $quantity > 1) {
            $quantity--;
        }

        // Update quantity in DB
        $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $quantity, $cartId);
        $update->execute();
    }
}

// Redirect back to cart page
header("Location: ../php/cart.php");
exit();
