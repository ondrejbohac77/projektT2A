<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$categoryRepo = new CategoryRepository();
$cart = new Cart();

$categories = $categoryRepo->getAll();

$pageTitle = 'Kategorie | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

    <main class="container category-section">
        <h1 class="section-title">Vyberte si kategorii</h1>

        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
            <a href="produkty.php?category=<?= htmlspecialchars($category->slug) ?>" class="category-card">
                <div class="category-image">
                    <img src="<?= htmlspecialchars($category->image) ?>" alt="<?= htmlspecialchars($category->name) ?>">
                </div>
                <div class="category-info">
                    <h3><?= htmlspecialchars($category->name) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </main>

<?php require __DIR__ . '/partials/footer.php'; ?>