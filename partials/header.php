<?php

/**
 * PARTIAL: Hlavička stránky
 *
 * Očekává proměnnou:
 *   $pageTitle (string) – titulek stránky
 *
 * Volitelně:
 *   $cartItemCount (int) – počet položek v košíku (výchozí 0)
 */

$pageTitle ??= 'All for Bikes';

$cart = new Cart();
$cartItemCount = $cart->getTotalQuantity();
$cartTotal = $cart->getTotalPrice();

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/assets/style.css"> 
</head>
<body>

<header class="site-header"> 
    <div class="container header-content">
        <div class="logo-wrapper">
            <a href="index.php" class="logo-link">
                <img src="/assets/images/logo.png" alt="All for Bikes Logo"> 
            </a>
        </div>

        <input type="checkbox" id="nav-toggle" class="nav-toggle">
        <label for="nav-toggle" class="nav-toggle-label">
            <span></span>
        </label>

        <nav class="main-nav"> 
            <ul class="nav-list">
                <li><a href="index.php">Domů</a></li>
                <li><a href="kategorie.php">Kategorie</a></li>
                <li><a href="produkty.php">Kola</a></li>
                <li><a href="o-nas.php">O nás</a></li>
                <li><a href="kontakt.php">Kontakt</a></li>
            </ul>
        </nav>

        <div class="header-search">
            <form action="search.php" method="get" class="search-form">
                <input type="text" name="q" placeholder="Hledat kolo..." class="search-input" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="search-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="user-actions">
            <a href="kosik-krok1.php" class="action-item cart">
                <div class="cart-btn-content">
                    <div class="cart-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>
                        <?php if ($cartItemCount > 0): ?><span class="cart-badge"><?= $cartItemCount ?></span><?php endif; ?>
                    </div>
                    <span class="cart-total-price"><?= number_format($cartTotal, 0, ',', ' ') ?> Kč</span>
                </div>
            </a>
        </div>
    </div>
</header>