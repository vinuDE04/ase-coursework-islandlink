<?php
session_start();
include "db.php";

$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

/* Fetch user */
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    /* Verify password */
    if (password_verify($password, $user['password'])) {

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['role']      = $user['role'];

        // Redirect based on role
        switch ($user['role']) {
            case 'retailer':
                header("Location: ../php/retail_dash.php");
                break;
            case 'staff':
                header("Location: ../php/staff_dash.php");
                break;
            case 'driver':
                header("Location: ../php/delivery_dash.php");
                break;
            case 'admin':
                header("Location: ../php/admin_dash.php");
                break;
        }
        exit();

    } else {
        echo "❌ Incorrect password";
    }
} else {
    echo "❌ User not found or role mismatch";
}
?>
