<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$shippingRepo = new ShippingMethodRepository();
$paymentRepo = new PaymentMethodRepository();
$validator = new Validator();

// Pokud je košík prázdný, přesměruj zpět na krok 1
if ($cart->isEmpty()) {
    header('Location: kosik-krok1.php');
    exit;
}

// Načtení dostupných metod dopravy a platby
$shippingMethods = $shippingRepo->getAll();
$paymentMethods = $paymentRepo->getAll();

// Inicializace proměnných formuláře
$firstName = $_SESSION['checkout_data']['first_name'] ?? '';
$lastName = $_SESSION['checkout_data']['last_name'] ?? '';
$email = $_SESSION['checkout_data']['email'] ?? '';
$phone = $_SESSION['checkout_data']['phone'] ?? '';
$street = $_SESSION['checkout_data']['street'] ?? '';
$city = $_SESSION['checkout_data']['city'] ?? '';
$zip = $_SESSION['checkout_data']['zip'] ?? '';
$selectedShipping = $_SESSION['checkout_data']['shipping_method_id'] ?? ($shippingMethods[0]->id ?? null);
$selectedPayment = $_SESSION['checkout_data']['payment_method_id'] ?? ($paymentMethods[0]->id ?? null);

// Výpočet výchozích cen pro zobrazení v souhrnu při načtení stránky
$shippingPrice = 0;
foreach ($shippingMethods as $method) {
    if ($method->id === $selectedShipping) {
        $shippingPrice = $method->price;
        break;
    }
}

$paymentPrice = 0;
foreach ($paymentMethods as $method) {
    if ($method->id === $selectedPayment) {
        $paymentPrice = $method->price;
        break;
    }
}
$totalPrice = $cart->getTotalPrice() + $shippingPrice + $paymentPrice;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token.');
    }

    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $zip = $_POST['zip'] ?? '';
    $selectedShipping = (int) ($_POST['shipping_method_id'] ?? 0);
    $selectedPayment = (int) ($_POST['payment_method_id'] ?? 0);

    $validator->required('first_name', $firstName, 'Jméno je povinné.')
              ->required('last_name', $lastName, 'Příjmení je povinné.')
              ->required('email', $email, 'E-mail je povinný.')
              ->email('email', $email, 'Neplatný formát e-mailu.')
              ->required('phone', $phone, 'Telefon je povinný.')
              ->pattern('phone', $phone, '/^\+?[0-9\s]{9,}$/', 'Neplatný formát telefonu.') // Základní pattern pro telefon
              ->required('street', $street, 'Ulice a číslo je povinné.')
              ->required('city', $city, 'Město je povinné.')
              ->required('zip', $zip, 'PSČ je povinné.')
              ->pattern('zip', $zip, '/^\d{3}\s?\d{2}$/', 'Neplatný formát PSČ (např. 123 45).')
              ->required('shipping_method_id', (string)$selectedShipping, 'Zvolte způsob dopravy.')
              ->in('shipping_method_id', $selectedShipping, array_map(fn($s) => $s->id, $shippingMethods), 'Neplatný způsob dopravy.')
              ->required('payment_method_id', (string)$selectedPayment, 'Zvolte způsob platby.')
              ->in('payment_method_id', $selectedPayment, array_map(fn($p) => $p->id, $paymentMethods), 'Neplatný způsob platby.');

    if ($validator->isValid()) {
        // Uložíme data do session pro další krok
        $_SESSION['checkout_data'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'street' => $street,
            'city' => $city,
            'zip' => $zip,
            'shipping_method_id' => $selectedShipping,
            'payment_method_id' => $selectedPayment,
        ];
        header('Location: kosik-krok3.php');
        exit;
    } else {
        $errors = $validator->getErrors();
    }
}

$pageTitle = 'Doprava a platba (2/3) | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

    <main class="container cart-section">
        <div class="checkout-steps">
            <div class="step finished">✓ Košík</div>
            <div class="step active">2. Doprava a platba</div>
            <div class="step">3. Shrnutí</div>
        </div>

        <form method="post" class="cart-layout">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="checkout-form-container">
                <h2>Způsob dopravy</h2>
                <div class="radio-group">
                    <?php foreach ($shippingMethods as $method): ?>
                        <label class="radio-card">
                            <input 
                                type="radio" 
                                name="shipping_method_id" 
                                value="<?= $method->id ?>" 
                                data-price="<?= $method->price ?>"
                                <?= $selectedShipping === $method->id ? 'checked' : '' ?>
                            >
                            <span class="radio-info">
                                <span class="radio-title"><?= htmlspecialchars($method->name) ?></span>
                                <span class="radio-desc"><?= htmlspecialchars($method->deliveryDays) ?></span>
                            </span>
                            <span class="radio-price"><?= $method->price > 0 ? number_format($method->price, 0, ',', ' ') . ' Kč' : 'Zdarma' ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h2 style="margin-top: 40px;">Způsob platby</h2>
                <div class="radio-group">
                    <?php foreach ($paymentMethods as $method): ?>
                        <label class="radio-card">
                            <input 
                                type="radio" 
                                name="payment_method_id" 
                                value="<?= $method->id ?>" 
                                data-price="<?= $method->price ?>"
                                <?= $selectedPayment === $method->id ? 'checked' : '' ?>
                            >
                            <span class="radio-info">
                                <span class="radio-title"><?= htmlspecialchars($method->name) ?></span>
                            </span>
                            <span class="radio-price"><?= $method->price > 0 ? number_format($method->price, 0, ',', ' ') . ' Kč' : 'Zdarma' ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h2 style="margin-top: 40px;">Osobní údaje</h2>
                <div class="contact-form no-bg">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Jméno</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Příjmení</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Ulice a číslo</label>
                        <input type="text" name="street" value="<?= htmlspecialchars($street) ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Město</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($city) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>PSČ</label>
                            <input type="text" name="zip" value="<?= htmlspecialchars($zip) ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cart-summary-box">
                <h3>Souhrn objednávky</h3>
                <div class="summary-row">
                    <span>Zboží</span>
                    <span><?= number_format($cart->getTotalPrice(), 0, ',', ' ') ?> Kč</span>
                </div>
                <div class="summary-row">
                    <span>Doprava</span>
                    <span id="summary-shipping-price"><?= $shippingPrice > 0 ? number_format($shippingPrice, 0, ',', ' ') . ' Kč' : 'Zdarma' ?></span>
                </div>
                <div class="summary-row">
                    <span>Platba</span>
                    <span id="summary-payment-price"><?= $paymentPrice > 0 ? number_format($paymentPrice, 0, ',', ' ') . ' Kč' : 'Zdarma' ?></span>
                </div>
                <div class="summary-total">
                    <span>Celkem</span>
                    <span id="summary-total-price"><?= number_format($totalPrice, 0, ',', ' ') ?> Kč</span>
                </div>
                
                <button type="submit" class="btn-primary full-width">
                    Pokračovat na shrnutí
                </button>
                
                <a href="kosik-krok1.php" class="back-link">Zpět do košíku</a>
            </div>
        </form>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartBasePrice = <?= (float)$cart->getTotalPrice() ?>;
    const shippingRadios = document.querySelectorAll('input[name="shipping_method_id"]');
    const paymentRadios = document.querySelectorAll('input[name="payment_method_id"]');
    const summaryShipping = document.getElementById('summary-shipping-price');
    const summaryPayment = document.getElementById('summary-payment-price');
    const summaryTotal = document.getElementById('summary-total-price');

    function formatPrice(price) {
        return price > 0 ? price.toLocaleString('cs-CZ') + ' Kč' : 'Zdarma';
    }

    function updateSummary() {
        let shippingPrice = 0;
        let paymentPrice = 0;

        shippingRadios.forEach(r => { if (r.checked) shippingPrice = parseFloat(r.dataset.price || 0); });
        paymentRadios.forEach(r => { if (r.checked) paymentPrice = parseFloat(r.dataset.price || 0); });

        summaryShipping.textContent = formatPrice(shippingPrice);
        summaryPayment.textContent = formatPrice(paymentPrice);
        summaryTotal.textContent = (cartBasePrice + shippingPrice + paymentPrice).toLocaleString('cs-CZ') + ' Kč';
    }

    shippingRadios.forEach(r => r.addEventListener('change', updateSummary));
    paymentRadios.forEach(r => r.addEventListener('change', updateSummary));
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>