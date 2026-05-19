<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$productRepo = new ProductRepository();

// Zpracování akcí z košíku (změna množství, odebrání)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token.');
    }
    if (isset($_POST['update_quantity'])) {
        $productId = (int) $_POST['product_id'];
        $variant = $_POST['variant'] ?? '';
        $quantity = (int) $_POST['quantity'];
        $cart->updateQuantity($productId, $quantity, $variant);
    } elseif (isset($_POST['remove_item'])) {
        $productId = (int) $_POST['product_id'];
        $variant = $_POST['variant'] ?? '';
        $cart->remove($productId, $variant);
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$cartItems = $cart->getItems();
$subtotal = $cart->getTotalPrice();
$total = $subtotal; // Pro krok 1 je celková cena pouze mezisoučet

$pageTitle = 'Košík (1/3) | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

    <main class="container cart-section">
        <div class="checkout-steps">
            <div class="step active">1. Košík</div>
            <div class="step">2. Doprava a platba</div>
            <div class="step">3. Shrnutí</div>
        </div>

        <div class="cart-layout">
            <div class="cart-items">
                <?php if (empty($cartItems)): ?>
                    <p>Váš košík je prázdný.</p>
                    <a href="produkty.php" class="btn-primary">Pokračovat v nákupu</a>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="cart-img">
                                <img src="<?= htmlspecialchars($item->image) ?>" alt="<?= htmlspecialchars($item->productName) ?>">
                            </div>
                            <div class="cart-details">
                                <h4><?= htmlspecialchars($item->productName) ?></h4>
                                <?php if ($item->variant): ?><p class="text-gray"><?= htmlspecialchars($item->variant) ?></p><?php endif; ?>
                            </div>
                            <div class="cart-quantity">
                                <form method="post" style="display: inline-block;">
                                    <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                                    <input type="hidden" name="variant" value="<?= htmlspecialchars($item->variant) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="number" name="quantity" value="<?= $item->quantity ?>" min="1" onchange="this.form.submit()">
                                    <input type="hidden" name="update_quantity" value="1">
                                </form>
                            </div>
                            <div class="cart-price"><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</div>
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="variant" value="<?= htmlspecialchars($item->variant) ?>">
                                <button type="submit" name="remove_item" class="remove-btn">&times;</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="cart-summary-box">
                <h3>Souhrn objednávky</h3>
                <div class="summary-row">
                    <span>Mezisoučet</span>
                    <span><?= number_format($subtotal, 0, ',', ' ') ?> Kč</span>
                </div>
                <div class="summary-total">
                    <span>Celkem</span>
                    <span><?= number_format($total, 0, ',', ' ') ?> Kč</span>
                </div>
                <a href="kosik-krok2.php" class="btn-primary full-width <?= $cart->isEmpty() ? 'disabled' : '' ?>">Pokračovat k dopravě</a>
                <a href="produkty.php" class="back-link">Zpět do obchodu</a>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/partials/footer.php'; ?>