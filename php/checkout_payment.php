<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IslandLink | Secure Checkout</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../css/products.css">

<style>
.checkout-container {
    max-width: 900px;
    margin: auto;
    padding: 30px;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 25px;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.06);
}

.card h3 {
    margin-top: 0;
}

.payment-icons {
    display: flex;
    gap: 12px;
    margin: 15px 0 20px;
    align-items: center; /* vertically center icons */
}

.pay-icon {
    width: 60px;  /* container size */
    height: 38px;
    background: #f9fafb;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e5e7eb;
}

.pay-icon img {
    max-width: 90%;   /* make image smaller inside the box */
    max-height: 80%;  /* maintain aspect ratio */
    object-fit: contain;
}



.input-group {
    margin-bottom: 15px;
}

.input-group label {
    font-size: 14px;
    color: #374151;
}

.input-group input {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    margin-top: 5px;
}

.row {
    display: flex;
    gap: 10px;
}

.pay-btn {
    width: 100%;
    background: linear-gradient(135deg,#2563eb,#1e40af);
    color: #fff;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

.secure {
    text-align: center;
    color: #6b7280;
    font-size: 13px;
    margin-top: 10px;
}
</style>
</head>
<?php
if (isset($_GET['status'])) {
    $status_msg = htmlspecialchars($_GET['status']); // safe output
    echo "<div style='
        max-width:900px;
        margin:20px auto;
        padding:15px;
        background-color:#d1fae5;
        color:#065f46;
        border-radius:10px;
        text-align:center;
        font-weight:600;
        font-size:16px;
    '>$status_msg</div>";
}
?>

<body>

<div class="layout">
<!-- Toast popup -->
<div id="payment-toast" style="
    display:none;
    position:fixed;
    top:20px;
    right:20px;
    background-color:#2563eb;
    color:white;
    padding:15px 25px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
    font-weight:600;
    z-index:9999;
">
</div>

<aside class="sidebar">
    <div class="brand">
        <div class="logo">I</div>IslandLink
    </div>
</aside>

<main class="main">
<header class="topbar">
    <strong>Secure Checkout</strong>
</header>

<section class="checkout-container">

<div class="checkout-grid">

<!-- PAYMENT FORM -->
<div class="card">
    <h3>Payment Details</h3>

   <div class="payment-icons">
    <div class="pay-icon"><img src="../images/visa.png" alt="Visa"></div>
    <div class="pay-icon"><img src="../images/mastercard.png" alt="MasterCard"></div>
    <div class="pay-icon"><img src="../images/amex.png" alt="Amex"></div>
</div>


    <form action="" method="POST">

        <!-- Card Number -->
        <div class="input-group">
            <label>Card Number</label>
            <input type="text" name="card_number" placeholder="1234 5678 9012 3456" required>

        </div>

        <!-- Name -->
        <div class="input-group">
            <label>Cardholder Name</label>
            <input type="text" name="cardholder_name" placeholder="John Doe" required>
        </div>

        <!-- Exp + CVV -->
        <div class="row">
            <div class="input-group">
                <label>Expiry</label>
                <input type="text" name="expiry" placeholder="MM / YY" required>

            </div>
            <div class="input-group">
                <label>CVV</label>
                <input type="password" name="cvv" placeholder="123" required>

            </div>
        </div>

        <!-- Address -->
        <div class="input-group">
            <label>Billing Address</label>
            <input type="text" name="billing_address" placeholder="123 Main Street, Colombo" required>

        </div>

        <button class="pay-btn">Pay Securely</button>

        <div class="secure">
            🔒 Secured by IslandLink Payments<br>
            This is a demo payment gateway
        </div>

    </form>
</div>

<!-- ORDER SUMMARY -->
<div class="card">
    <h3>Order Summary</h3>

    <p><strong>Subtotal:</strong> $129.90</p>
    <p><strong>Tax:</strong> $12.99</p>
    <p><strong>Shipping:</strong> FREE</p>

    <hr>

    <h2>$142.89</h2>

    <p style="color:#6b7280;font-size:14px;">
        Estimated delivery: 2–3 business days
    </p>
</div>

</div>

</section>
</main>
</div>
<script>
// DOM Loaded
window.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');

    // Form submit event
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // prevent page reload

        // Read values
        const cardNumber = form.card_number.value.trim();
        const cardName = form.cardholder_name.value.trim();
        const expiry = form.expiry.value.trim();
        const cvv = form.cvv.value.trim();
        const address = form.billing_address.value.trim();

        // Validations
        if(!/^\d{16}$/.test(cardNumber.replace(/\s+/g,''))){
            alert("❌ Invalid Card Number! Must be 16 digits.");
            return;
        }

        if(cardName.length < 3){
            alert("❌ Invalid Cardholder Name!");
            return;
        }

        if(!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)){
            alert("❌ Invalid Expiry! Use MM/YY format.");
            return;
        }

        if(!/^\d{3,4}$/.test(cvv)){
            alert("❌ Invalid CVV!");
            return;
        }

        if(address.length < 5){
            alert("❌ Invalid Billing Address!");
            return;
        }

        // Simulate payment processing
        const success = Math.random() > 0.2; // 80% success

        if(success){
            alert("🎉 Payment Successful! Thank you for your order.");
            form.reset(); // optional: clear form after success
        } else {
            alert("❌ Payment Failed! Please try again.");
        }
    });
});
</script>


</body>
</html>
