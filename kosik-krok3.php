<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$productRepo = new ProductRepository();
$shippingRepo = new ShippingMethodRepository();
$paymentRepo = new PaymentMethodRepository();

// 1) Kontrola, zda máme data z předchozích kroků
$checkoutData = $_SESSION['checkout_data'] ?? null;
if ($cart->isEmpty() || !$checkoutData) {
    header('Location: kosik-krok1.php');
    exit;
}

// 2) Načtení detailů dopravy a platby pro zobrazení
$shipping = $shippingRepo->getById((int)$checkoutData['shipping_method_id']);
$payment = $paymentRepo->getById((int)$checkoutData['payment_method_id']);

if (!$shipping || !$payment) {
    header('Location: kosik-krok2.php');
    exit;
}

$cartItems = $cart->getItems();
$cartTotal = $cart->getTotalPrice();
$totalPrice = $cartTotal + $shipping->price + $payment->price;

// 3) Finální odeslání objednávky
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token.');
    }

    $db = Database::getConnection();
    
    try {
        $db->beginTransaction();

        // A) Uložení zákazníka
        $stmtCust = $db->prepare('
            INSERT INTO customers (first_name, last_name, email, phone, street, city, zip)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmtCust->execute([
            $checkoutData['first_name'],
            $checkoutData['last_name'],
            $checkoutData['email'],
            $checkoutData['phone'],
            $checkoutData['street'],
            $checkoutData['city'],
            $checkoutData['zip']
        ]);
        $customerId = (int)$db->lastInsertId();

        // B) Uložení objednávky
        $stmtOrder = $db->prepare('
            INSERT INTO orders (customer_id, shipping_method_id, payment_method_id, shipping_price, payment_price, total_price, status)
            VALUES (?, ?, ?, ?, ?, ?, "new")
        ');
        $stmtOrder->execute([
            $customerId,
            $shipping->id,
            $payment->id,
            $shipping->price,
            $payment->price,
            $totalPrice
        ]);
        $orderId = (int)$db->lastInsertId();

        // C) Uložení položek objednávky
        $stmtItem = $db->prepare('
            INSERT INTO order_items (order_id, product_id, product_name, variant, quantity, unit_price)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        foreach ($cartItems as $item) {
            $stmtItem->execute([
                $orderId,
                $item->productId,
                $item->productName,
                $item->variant,
                $item->quantity,
                $item->unitPrice
            ]);
        }

        $db->commit();

        // D) Vyčištění košíku a session
        $cart->clear();
        unset($_SESSION['checkout_data']);
        
        // Uložíme ID objednávky pro děkovačku
        $_SESSION['last_order_id'] = $orderId;

        header('Location: objednavka-odeslana.php');
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Omlouváme se, při ukládání objednávky došlo k chybě: " . $e->getMessage();
    }
}

$pageTitle = 'Shrnutí objednávky (3/3) | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

<main class="container cart-section">
    <div class="checkout-steps">
        <div class="step finished">✓ Košík</div>
        <div class="step finished">✓ Doprava a platba</div>
        <div class="step active">3. Shrnutí</div>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message" style="margin-bottom: 20px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="cart-layout">
        <div class="checkout-form-container">
            <div class="summary-block">
                <h2>Kontaktní údaje</h2>
                <p>
                    <strong><?= htmlspecialchars($checkoutData['first_name'] . ' ' . $checkoutData['last_name']) ?></strong><br>
                    <?= htmlspecialchars($checkoutData['street']) ?><br>
                    <?= htmlspecialchars($checkoutData['zip'] . ' ' . $checkoutData['city']) ?><br>
                    Email: <?= htmlspecialchars($checkoutData['email']) ?><br>
                    Tel: <?= htmlspecialchars($checkoutData['phone']) ?>
                </p>
                <a href="kosik-krok2.php" class="edit-link">Upravit údaje</a>
            </div>

            <div class="summary-block" style="margin-top: 30px;">
                <h2>Doprava a platba</h2>
                <p>Doprava: <strong><?= htmlspecialchars($shipping->name) ?></strong> (<?= number_format($shipping->price, 0, ',', ' ') ?> Kč)</p>
                <p>Platba: <strong><?= htmlspecialchars($payment->name) ?></strong> (<?= number_format($payment->price, 0, ',', ' ') ?> Kč)</p>
                <a href="kosik-krok2.php" class="edit-link">Změnit metody</a>
            </div>

            <div class="summary-block" style="margin-top: 30px;">
                <h2>Položky v košíku</h2>
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-details">
                            <h4><?= htmlspecialchars($item->productName) ?> (<?= $item->quantity ?>x)</h4>
                            <?php if ($item->variant): ?><p class="text-gray"><?= htmlspecialchars($item->variant) ?></p><?php endif; ?>
                        </div>
                        <div class="cart-price"><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="cart-summary-box">
            <h3>Celková rekapitulace</h3>
            <div class="summary-row">
                <span>Zboží</span>
                <span><?= number_format($cartTotal, 0, ',', ' ') ?> Kč</span>
            </div>
            <div class="summary-row">
                <span>Doprava</span>
                <span><?= number_format($shipping->price, 0, ',', ' ') ?> Kč</span>
            </div>
            <div class="summary-row">
                <span>Platba</span>
                <span><?= number_format($payment->price, 0, ',', ' ') ?> Kč</span>
            </div>
            <div class="summary-total">
                <span>Celkem k úhradě</span>
                <span><?= number_format($totalPrice, 0, ',', ' ') ?> Kč</span>
            </div>
            
            <form method="post" action="kosik-krok3.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" name="confirm_order" class="btn-primary full-width">Závazně objednat</button>
            </form>
        </div>
    </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>