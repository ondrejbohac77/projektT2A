<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$orderId = $_SESSION['last_order_id'] ?? null;

// Pokud někdo přijde na tuto stránku přímo bez objednávky, pošleme ho domů
if (!$orderId) {
    header('Location: index.php');
    exit;
}

// Po zobrazení můžeme ID smazat, aby se zpráva nezobrazovala při refreshování donekonečna
unset($_SESSION['last_order_id']);

$pageTitle = 'Děkujeme za objednávku | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

<main class="container success-page-wrapper">
    <div class="success-card">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" viewBox="0 0 16 16">
                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
            </svg>
        </div>
        
        <h1 class="success-title">Děkujeme za vaši objednávku!</h1>
        
        <div class="success-message-text">
            <p>Vaše objednávka <strong>#<?= htmlspecialchars((string)$orderId) ?></strong> byla úspěšně přijata.</p>
            <p>Potvrzení a shrnutí objednávky jsme právě odeslali na váš email.</p>
        </div>

        <div class="added-product-preview thank-you-status">
            <p>Naši mechanici už začínají leštit vaše nové kolo. O stavu vyřízení vás budeme informovat.</p>
        </div>

        <div class="success-actions">
            <a href="index.php" class="btn-primary-large">
                Zpět na hlavní stránku
            </a>
        </div>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>