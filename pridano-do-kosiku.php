<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();

// Získáme informace o posledním přidaném produktu ze session
$lastAddedProduct = $_SESSION['last_added_product'] ?? null;
// Získáme URL předchozí stránky pro tlačítko "Zpět k nákupu"
$lastPage = $_SESSION['last_page'] ?? 'produkty.php'; // Výchozí hodnota, pokud referer není k dispozici

// Pokud nejsou data o posledním produktu, přesměrujeme na hlavní stránku
if (!$lastAddedProduct) {
    header('Location: index.php');
    exit;
}

// Po zobrazení můžeme data ze session smazat, aby se nezobrazovala při refreshování
unset($_SESSION['last_added_product']);
unset($_SESSION['last_page']); // Vymažeme i odkaz na předchozí stránku po použití

$pageTitle = 'Přidáno do košíku | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

<main class="container success-page-wrapper">
    <div class="success-card">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
            </svg>
        </div>
        
        <h1 class="success-title">Zboží bylo úspěšně přidáno do košíku</h1>

        <div class="added-product-preview">
            <img src="<?= htmlspecialchars($lastAddedProduct['image']) ?>" alt="<?= htmlspecialchars($lastAddedProduct['productName']) ?>">
            <div class="added-product-info">
                <h2><?= htmlspecialchars($lastAddedProduct['productName']) ?></h2>
                <?php if ($lastAddedProduct['variant']): ?>
                    <p class="variant">Varianta: <?= htmlspecialchars($lastAddedProduct['variant']) ?></p>
                <?php endif; ?>
                <p class="price"><?= number_format($lastAddedProduct['unitPrice'], 0, ',', ' ') ?> Kč</p>
            </div>
        </div>

        <div class="success-actions">
            <a href="<?= htmlspecialchars($lastPage) ?>" class="btn-secondary-large">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right:8px; transform: rotate(180deg);"><path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/></svg>
                Zpět k nákupu
            </a>
            <a href="kosik-krok1.php" class="btn-primary-large">
                Přejít do košíku
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-left:8px;"><path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/></svg>
            </a>
        </div>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>