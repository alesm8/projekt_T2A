<?php
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
if ($cart->isEmpty()) {
    header('Location: kosik.php');
    exit;
}

$errors = [];
$data = $_SESSION['customer_data'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token CSRF.');
    }

    $validator = new Validator($_POST);
    $validator->required('name', 'Jméno a příjmení je povinné.')
              ->required('email', 'E-mail je povinný.')
              ->email('email', 'E-mail má neplatný formát.')
              ->required('phone', 'Telefonní číslo je povinné.')
              ->phone('phone', 'Telefon má neplatný formát (např. +420 777 123 456).')
              ->required('street', 'Ulice a číslo popisné jsou povinné.')
              ->required('city', 'Město je povinné.')
              ->required('zip', 'PSČ je povinné.')
              ->zip('zip', 'PSČ musí obsahovat 5 číslic.');

    if ($validator->isValid()) {
        $nameParts = explode(' ', trim($_POST['name']), 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $_SESSION['customer_data'] = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name_raw' => $_POST['name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'street' => $_POST['street'],
            'city' => $_POST['city'],
            'zip' => $_POST['zip'],
            'note' => $_POST['note'] ?? ''
        ];

        header('Location: doprava-platba.php');
        exit;
    }
    
    $errors = $validator->getErrors();
    $data = $_POST; // Preserve input on error
}

$pageTitle = 'Dodací údaje | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <div class="cart-container">
        <!-- Steps Indicator -->
        <div class="cart-steps">
            <a href="kosik.php" class="cart-step">1. Košík</a>
            <div class="cart-step active">2. Dodací údaje</div>
            <div class="cart-step">3. Doprava a platba</div>
            <div class="cart-step">4. Potvrzení</div>
        </div>

        <div class="cart-box">
            <h1>👤 Dodací údaje</h1>
            
            <form class="checkout-form" method="POST" action="dodaci-udaje.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div>
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Jméno a příjmení *</label>
                    <input type="text" name="name" placeholder="Jan Novák" value="<?= htmlspecialchars($data['name_raw'] ?? $data['first_name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <span style="color: #ff5e5e; font-size: 0.9rem;"><?= htmlspecialchars($errors['name']) ?></span>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">E-mail *</label>
                        <input type="email" name="email" placeholder="jan.novak@email.cz" value="<?= htmlspecialchars($data['email'] ?? '') ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <span style="color: #ff5e5e; font-size: 0.9rem;"><?= htmlspecialchars($errors['email']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Telefon *</label>
                        <input type="tel" name="phone" placeholder="+420 777 123 456" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" required>
                        <?php if (isset($errors['phone'])): ?>
                            <span style="color: #ff5e5e; font-size: 0.9rem;"><?= htmlspecialchars($errors['phone']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Ulice a číslo popisné *</label>
                    <input type="text" name="street" placeholder="Hlavní 123" value="<?= htmlspecialchars($data['street'] ?? '') ?>" required>
                    <?php if (isset($errors['street'])): ?>
                        <span style="color: #ff5e5e; font-size: 0.9rem;"><?= htmlspecialchars($errors['street']) ?></span>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div style="flex: 2; min-width: 200px;">
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Město *</label>
                        <input type="text" name="city" placeholder="Praha" value="<?= htmlspecialchars($data['city'] ?? '') ?>" required>
                        <?php if (isset($errors['city'])): ?>
                            <span style="color: #ff5e5e; font-size: 0.9rem;"><?= htmlspecialchars($errors['city']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1; min-width: 100px;">
                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">PSČ *</label>
                        <input type="text" name="zip" placeholder="110 00" value="<?= htmlspecialchars($data['zip'] ?? '') ?>" required>
                        <?php if (isset($errors['zip'])): ?>
                            <span style="color: #ff5e5e; font-size: 0.9rem;"><?= htmlspecialchars($errors['zip']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label style="font-weight: bold; display: block; margin-bottom: 5px;">Poznámka k objednávce</label>
                    <textarea name="note" placeholder="Např. patro, kód od zvonku, doručit odpoledne..." style="min-height: 80px; width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; font-family: inherit;"><?= htmlspecialchars($data['note'] ?? '') ?></textarea>
                </div>

                <hr style="margin: 20px 0;">

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <a href="kosik.php" class="back-link" style="margin-top: 0;">← Zpět do košíku</a>
                    <button type="submit" class="order-btn" style="margin-top: 0; cursor: pointer; border: none;">Pokračovat k dopravě a platbě →</button>
                </div>
            </form>
        </div>
    </div>

<?php
require __DIR__ . '/partials/footer.php';
?>
