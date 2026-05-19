<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();
$cart = new Cart();

// 1) Načtení produktu podle slugu
$slug = $_GET['slug'] ?? '';
$product = $productRepo->getBySlug($slug);

if (!$product) {
    header('Location: 404.html');
    exit;
}

// 2) Zpracování přidání do košíku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token.');
    }

    $variant = '';
    if (isset($_POST['variants']) && is_array($_POST['variants'])) {
        $variantParts = [];
        foreach ($_POST['variants'] as $name => $value) {
            $variantParts[] = "$name: $value";
        }
        $variant = implode(', ', $variantParts);
    }

    $cart->add(
        productId: $product->id,
        productName: $product->name,
        unitPrice: $product->price,
        image: $product->image,
        variant: $variant
    );

    $_SESSION['last_added_product'] = [
        'productName' => $product->name,
        'unitPrice' => $product->price,
        'image' => $product->image,
        'variant' => $variant,
    ];
    // Uložíme URL předchozí stránky pro tlačítko "Zpět k nákupu"
    $_SESSION['last_page'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';

    header('Location: pridano-do-kosiku.php');
    exit;
}

// 3) Načtení parametrů a obrázků
$images = $productRepo->getImages($product->id);
$params = $productRepo->getParameters($product->id);
$selectableParams = array_filter($params, fn($p) => $p->isSelectable());
$infoParams = array_filter($params, fn($p) => !$p->isSelectable());

$pageTitle = $product->name . ' | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
echo '<main class="container product-detail-section">';
require __DIR__ . '/partials/produkt-detail.php';
echo '</main>';
require __DIR__ . '/partials/footer.php';