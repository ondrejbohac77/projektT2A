<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$validator = new Validator();

$name = '';
$email = '';
$message = '';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token.');
    }

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    $validator->required('name', $name, 'Jméno je povinné.')
              ->required('email', $email, 'E-mail je povinný.')
              ->email('email', $email, 'Neplatný formát e-mailu.')
              ->required('message', $message, 'Zpráva je povinná.');

    if ($validator->isValid()) {
        // Zde by se odeslal e-mail nebo uložila zpráva do DB
        // Pro účely ukázky jen nastavíme success na true
        $success = true;
        // Po úspěšném odeslání formuláře vyprázdníme pole
        $name = $email = $message = '';
    } else {
        $errors = $validator->getErrors();
    }
}

$pageTitle = 'Kontakt | All for Bikes';
$cartItemCount = $cart->getTotalQuantity();

require __DIR__ . '/partials/header.php';
?>

    <div class="contact-info-blocks">
        <div class="info-item">
            <h3 class="about-subtitle">Kde nás najdete?</h3>
            <p>All for Bikes HQ<br>Cyklistická 123, Praha</p>
        </div>
        <div class="info-item">
            <h3 class="about-subtitle">Spojte se s námi</h3>
            <p>Email: info@allforbikes.cz<br>Tel: +420 123 456 789</p>
        </div>
        <div class="info-item">
            <h3 class="about-subtitle">Otevírací doba</h3>
            <p>Po - Pá: 09:00 - 18:00<br>So: 10:00 - 15:00</p>
        </div>
    </div>

    <main class="container contact-main">
        <header class="contact-header">
            <h1 class="section-title">Napište nám</h1>
            <p class="contact-subtitle">Máte dotaz k novému modelu nebo potřebujete vyladit své kolo? Náš tým je tu pro vás.</p>
        </header>
        
        <div class="contact-centered-layout">
            <div class="contact-form-wrapper">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <form class="contact-form" method="post">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Vaše jméno</label>
                            <input type="text" id="name" name="name" placeholder="Jan Novák" value="<?= htmlspecialchars($name) ?>" required>
                            <?php if (isset($errors['name'])): ?><p class="error-message"><?= $errors['name'] ?></p><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="email">E-mailová adresa</label>
                            <input type="email" id="email" name="email" placeholder="vas@email.cz" value="<?= htmlspecialchars($email) ?>" required>
                            <?php if (isset($errors['email'])): ?><p class="error-message"><?= $errors['email'] ?></p><?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Vaše zpráva</label>
                        <textarea id="message" name="message" rows="6" placeholder="S čím vám můžeme pomoci?" required><?= htmlspecialchars($message) ?></textarea>
                        <?php if (isset($errors['message'])): ?><p class="error-message"><?= $errors['message'] ?></p><?php endif; ?>
                    </div>

                    <div class="form-submit">
                        <br>
                        <button type="submit" class="btn-primary">Odeslat Zprávu</button>
                    </div>
                    <?php if ($success): ?>
                        <p class="success-message">Vaše zpráva byla úspěšně odeslána!</p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/partials/footer.php'; ?>