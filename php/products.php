<?php
session_start();
include "../php/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

$fullName = $_SESSION['full_name'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IslandLink | Retail Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/products.css">
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
            <a href="../php/retail_dash.php" >Dashboard</a>
            <a class="active">Products</a>
            <a href="../php/cart.php">My Cart</a>
            <a href="../php/my_orders.php">My Orders</a>
            <a hrer="../php/track_delivery.php">Track Delivery</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <!-- TOP BAR -->
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

    <?php if(isset($_GET['added']) && $_GET['added'] == 1): ?>
        <div class="alert success">
            ✅ Product added to cart!
        </div>
    <?php endif; ?>

    <div class="catalog-header">
        <div>
            <h2>Product Catalog</h2>
            <p>Browse and order products for your store</p>
        </div>
        <div class="catalog-actions">
            <input type="text" placeholder="Search products...">
            <button id="addProductBtn"> Add Product</button>
        </div>
    </div>


<!-- ADD PRODUCT MODAL -->
<div id="addProductModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="closeModal" class="close">&times;</span>
        <h2>Add New Product</h2>
        <form action="../php/add_product.php" method="POST" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="Beverages">Beverages</option>
                <option value="Pantry">Pantry</option>
                <option value="Fresh Produce">Fresh Produce</option>
                <option value="Bakery">Bakery</option>
            </select>

            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" id="price" required>

            <label for="stock">Stock:</label>
            <input type="number" name="stock" id="stock" required>

            <label for="image">Product Image:</label>
            <input type="file" name="image" id="image" accept="image/*" required>

            <button type="submit" name="add_product">Add Product</button>
        </form>
    </div>
</div>

            <!-- FILTERS -->
            <div class="filters">
    <button class="active" data-category="all">All</button>
    <button data-category="Beverages">Beverages</button>
    <button data-category="Pantry">Pantry</button>
    <button data-category="Fresh Produce">Fresh Produce</button>
    <button data-category="Bakery">Bakery</button>
</div>

            <!-- PRODUCT GRID -->
            <div class="products">

<?php
$result = $conn->query("SELECT * FROM products");

while ($row = $result->fetch_assoc()):
    $stockClass = $row['stock'] <= 50 ? 'orange' : 'green';
?>

    <div class="card">
        <span class="stock <?php echo $stockClass; ?>">
            <?php echo $row['stock']; ?> in stock
        </span>

        <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" alt="">

        <small><?php echo htmlspecialchars($row['category']); ?></small>

        <h3><?php echo htmlspecialchars($row['name']); ?></h3>

        <p><?php echo htmlspecialchars($row['description']); ?></p>

        <div class="card-footer">
    <strong>$<?php echo number_format($row['price'], 2); ?></strong>
    <form action="../php/add_to_cart.php" method="POST" style="display:inline;">
        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
        <input type="hidden" name="quantity" value="1">
        <button type="submit">Add</button>
    </form>
</div>

    </div>

<?php endwhile; ?>

</div>

        </section>
    </main>
</div>
<script>
const modal = document.getElementById('addProductModal');
const btn = document.getElementById('addProductBtn');
const close = document.getElementById('closeModal');

btn?.addEventListener('click', () => modal.style.display = 'block');
close?.addEventListener('click', () => modal.style.display = 'none');
window.addEventListener('click', e => {
    if(e.target == modal) modal.style.display = 'none';
});
</script>
<script>
const searchInput = document.querySelector('.catalog-actions input');
const productCards = document.querySelectorAll('.card');

searchInput.addEventListener('keyup', function () {
    const value = this.value.toLowerCase();

    productCards.forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        const category = card.querySelector('small').textContent.toLowerCase();

        if (name.includes(value) || category.includes(value)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>
<script>
const filterButtons = document.querySelectorAll('.filters button');

filterButtons.forEach(button => {
    button.addEventListener('click', () => {

        // Remove active class
        filterButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        const category = button.getAttribute('data-category');

        productCards.forEach(card => {
            const productCategory = card.querySelector('small').textContent;

            if (category === 'all' || productCategory === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>
</body>
</html>
