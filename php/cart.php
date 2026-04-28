<?php
session_start();
include "../php/db.php";

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

$userId = $_SESSION['user_id'];
$fullName = $_SESSION['full_name'];
$role = $_SESSION['role'];

// Fetch cart items
$cartItems = [];
$subtotal = 0;

$stmt = $conn->prepare("
    SELECT c.id AS cart_id, p.id AS product_id, p.name, p.category, p.price, p.image, c.quantity
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $subtotal += $row['price'] * $row['quantity'];
}

$tax = $subtotal * 0.10; // 10% tax
$shipping = $subtotal > 0 ? 0 : 0; // FREE if subtotal > 0
$total = $subtotal + $tax + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IslandLink | My Cart</title>
<link rel="stylesheet" href="../css/products.css">
<style>
/* CART PAGE STYLES */
.cart-container {
    display: flex;
    gap: 20px;
}

.cart-items {
    flex: 2;
}

.cart-item {
    display: flex;
    gap: 15px;
    background: #fff;
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 15px;
    align-items: center;
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

.cart-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 12px;
}

.cart-item-details {
    flex: 1;
}

.cart-item-details h4 {
    margin: 0;
    font-size: 16px;
    color: #111827;
}

.cart-item-details small {
    color: #6b7280;
}

.cart-item-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    align-items: flex-end;
}

.cart-item-actions button {
    border: none;
    background: #2563eb;
    color: #fff;
    padding: 6px 12px;
    border-radius: 8px;
    cursor: pointer;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-controls button {
    padding: 4px 8px;
}

.order-summary {
    flex: 1;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    height: fit-content;
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

.order-summary h3 {
    margin-top: 0;
}

.order-summary div {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
}

.order-summary button {
    width: 100%;
    background: #2563eb;
    color: #fff;
    border: none;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
}
</style>
</head>
<body>

<div class="layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">I</div>
            <span>IslandLink</span>
        </div>

        <nav>
            <a href="../php/retail_dash.php">Dashboard</a>
            <a href="../php/products.php">Products</a>
            <a class="active" href="../php/cart.php">My Cart</a>
            <a href="../php/my_orders.php">My Orders</a>
            <a href="track_delivery.php">Track Delivery</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="topbar">
            <div></div>
            <div class="top-actions">
                <span>🛒</span>
                <span>🔔</span>
                <div class="user">
                    <strong><?php echo htmlspecialchars($fullName); ?></strong>
                    <small><?php echo htmlspecialchars($role); ?></small>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <section class="content">
            <h2>Shopping Cart</h2>
            <p><?php echo count($cartItems); ?> item<?php echo count($cartItems) !== 1 ? 's' : ''; ?> in your cart</p>

            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="../images/<?php echo htmlspecialchars($item['image']); ?>" alt="">
                        <div class="cart-item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <small><?php echo htmlspecialchars($item['category']); ?></small>
                            <p>$<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <div class="cart-item-actions">
                            <div class="quantity-controls">
                                <form action="../php/update_cart.php" method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" name="decrease">-</button>
                                    <span><?php echo $item['quantity']; ?></span>
                                    <button type="submit" name="increase">+</button>
                                </form>
                            </div>
                            <form action="../php/remove_cart.php" method="POST">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <button type="submit">Remove</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

<div class="order-summary">
    <h3>Order Summary</h3>
    <div>
        <span>Subtotal</span>
        <span>$<?php echo number_format($subtotal,2); ?></span>
    </div>
    <div>
        <span>Tax (10%)</span>
        <span>$<?php echo number_format($tax,2); ?></span>
    </div>
    <div>
        <span>Shipping</span>
        <span><?php echo $shipping == 0 ? 'FREE' : '$'.number_format($shipping,2); ?></span>
    </div>
    <hr>
    <div>
        <strong>Total</strong>
        <strong>$<?php echo number_format($total,2); ?></strong>
    </div>
<hr>

<h4>Delivery Address</h4>

<textarea
    name="delivery_address"
    form="checkoutForm"
    rows="3"
    placeholder="Enter your full delivery address"
    required
    style="
        width:100%;
        padding:10px;
        border-radius:8px;
        border:1px solid #d1d5db;
        margin-bottom:15px;
        resize:none;
    "
></textarea>

    <!-- Form to proceed to checkout -->
    <form action="../php/checkout.php" method="post" id="checkoutForm">
    <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
    <input type="hidden" name="tax" value="<?php echo $tax; ?>">
    <input type="hidden" name="shipping" value="<?php echo $shipping; ?>">
    <input type="hidden" name="total" value="<?php echo $total; ?>">

    <button type="submit">Proceed to Checkout ➔</button>
</form>

</div>


            </div>
        </section>
    </main>
</div>

</body>
</html>
