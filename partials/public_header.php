<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epaladeniya Agro City</title>
    <link rel="stylesheet" href="/fertilizer-shop/assets/css/style.css">
    <link rel="stylesheet" href="/fertilizer-shop/assets/css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script id="dropdown-fix-js">
document.addEventListener("DOMContentLoaded", function () {
    const dropdown = document.querySelector(".dropdown");
    const button = document.querySelector(".dropbtn");
    const menu = document.querySelector(".dropdown-content");

    // Toggle dropdown on click
    button.addEventListener("click", function (e) {
        e.stopPropagation();
        menu.style.display = (menu.style.display === "block") ? "none" : "block";
    });

    // Prevent closing when clicking inside menu
    menu.addEventListener("click", function (e) {
        e.stopPropagation();
    });

    // Close when clicking outside
    document.addEventListener("click", function () {
        menu.style.display = "none";
    });
});
</script>




</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="/fertilizer-shop/public/index.php">Agro<span>City</span></a>
        </div>
        <div class="nav-links">
            <div class="dropdown">
                <button class="dropbtn">Products <i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-content">
                    <a href="/fertilizer-shop/public/products.php?category=fertilizer"><i class="fas fa-flask"></i> Fertilizer</a>
                    <a href="/fertilizer-shop/public/products.php?category=seeds"><i class="fas fa-seedling"></i> Seeds Varieties</a>
                    <a href="/fertilizer-shop/public/products.php?category=insecticides"><i class="fas fa-bug"></i> Insecticides</a>
                    <a href="/fertilizer-shop/public/products.php?category=herbicides"><i class="fas fa-leaf"></i> Herbicides / Weedicides</a>
                    <a href="/fertilizer-shop/public/products.php?category=fungicides"><i class="fas fa-spray-can"></i> Fungicides</a>
                    <a href="/fertilizer-shop/public/products.php?category=tools"><i class="fas fa-tools"></i> Garden Tools</a>
                </div>
            </div>
            <a href="/fertilizer-shop/public/products.php">All Products</a>
            <a href="/fertilizer-shop/public/about.php">About Us</a>
            <div class="search-bar">
                <form action="/fertilizer-shop/public/products.php" method="GET">
                    <input type="text" name="search" placeholder="Search by name, ID, or description..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <a href="/fertilizer-shop/public/cart.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span id="cart-count"><?php echo Cart::count(); ?></span>
            </a>
            <?php if (isset($_SESSION['customer_no']) || isset($_SESSION['customer_id'])): ?>
                <a href="/fertilizer-shop/public/account/dashboard.php"><i class="fas fa-user"></i> My Account</a>
            <?php else: ?>
                <a href="/fertilizer-shop/public/login.php"><i class="fas fa-user"></i> Login</a>
            <?php endif; ?>
        </div>
    </nav>
    <main>
