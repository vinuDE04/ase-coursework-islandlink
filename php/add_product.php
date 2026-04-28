<?php
session_start();
include "db.php";

// Only check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Handle image upload
    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];
    $imageDir = "../images/";

    // Create folder if it doesn't exist
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0777, true); // recursive creation with write permissions
    }

    $imagePath = $imageDir . basename($imageName);

    if (move_uploaded_file($imageTmp, $imagePath)) {
        $stmt = $conn->prepare("INSERT INTO products (name, category, description, price, stock, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssdis", $name, $category, $description, $price, $stock, $imageName);
        $stmt->execute();
        $stmt->close();

        header("Location: ../php/products.php");
        exit();
    } else {
        echo "Failed to upload image. Check that the folder exists and is writable.";
    }
}
?>
