<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "isdn";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_errno) {
    die("Failed to connect to MySQL: " . $conn->connect_error);
}

// Optional: set charset
$conn->set_charset("utf8");
?>
