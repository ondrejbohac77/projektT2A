<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// 3) Zpracování akce "přidat do košíku" (přišla z formuláře)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token.');
    }

    $productId = (int) $_POST['product_id'];
    $product = $productRepo->getById($productId);

    if ($product !== null) {
        $cart->add(
            productId: $product->id,
            productName: $product->name,
            unitPrice: $product->price,
            image: $product->image,
        );
        // Uložíme info o posledním přidaném produktu pro stránku "Přidáno do košíku"
        $_SESSION['last_added_product'] = [
            'productName' => $product->name,
            'unitPrice' => $product->price,
            'image' => $product->image,
            'variant' => '', // Bez varianty pro jednoduché přidání z hlavní stránky
        ];
        // Uložíme URL předchozí stránky pro tlačítko "Zpět k nákupu"
        $_SESSION['last_page'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    }

    header('Location: pridano-do-kosiku.php');
    exit;
}

// Načteme doporučená kola z DB
$featuredProducts = $productRepo->getFeatured(limit: 3, random: true); 

$pageTitle = 'Domů | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>
<main>
    <section class="banner-wrapper">
         <img src="/assets/images/banner-allforbikes.png" alt="All for Bikes Banner" class="hero-image"> 
    </section>
    <section class="container recommended-section">
        <h2 class="section-title">Doporučená kola</h2> 
        <div class="product-grid"> 
            <?php foreach ($featuredProducts as $product): ?>
                <?php require __DIR__ . '/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/partials/footer.php'; ?>