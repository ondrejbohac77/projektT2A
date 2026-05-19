<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// Načtení vyhledávacího dotazu
$query = trim($_GET['q'] ?? '');
$products = [];

if ($query !== '') {
    $products = $productRepo->search($query);
}

$pageTitle = 'Výsledky hledání: ' . $query . ' | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

<main class="container recommended-section">
    <h1 class="section-title">
        <?php if ($query !== ''): ?>
            Hledání: "<?= htmlspecialchars($query) ?>"
        <?php else: ?>
            Zadejte hledaný výraz
        <?php endif; ?>
    </h1>

    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <?php require __DIR__ . '/partials/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>