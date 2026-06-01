<?php
require_once __DIR__ . '/src/bootstrap.php';

$orderData = $_SESSION['last_order'] ?? null;

// Clear the order details from session to prevent displaying on reload if they leave the page
if ($orderData) {
    unset($_SESSION['last_order']);
}

$pageTitle = 'Objednávka potvrzena | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <div class="cart-container">
        <!-- Steps Indicator -->
        <div class="cart-steps">
            <div class="cart-step">1. Košík</div>
            <div class="cart-step">2. Dodací údaje</div>
            <div class="cart-step">3. Doprava a platba</div>
            <div class="cart-step active">4. Potvrzení</div>
        </div>

        <div class="cart-box" style="text-align: center;">
            <span style="font-size: 5rem;">🎉</span>
            <h1 style="color: var(--primary-color); margin-top: 15px; margin-bottom: 20px;">Děkujeme za objednávku!</h1>
            
            <?php if ($orderData): ?>
                <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 25px; opacity: 0.9;">
                    Vaše objednávka **č. <?= htmlspecialchars((string)$orderData['id']) ?>** byla úspěšně odeslána k vyřízení. Potvrzení objednávky a platební údaje jsme vám odeslali na zadanou e-mailovou adresu.
                </p>

                <div style="background: rgba(255,255,255,0.4); padding: 20px; border-radius: 20px; text-align: left; max-width: 450px; margin: 0 auto 30px;">
                    <h3 style="color: var(--heading-color); margin-bottom: 10px; border-bottom: 1px solid var(--border-color); padding-bottom: 5px;">Shrnutí doručení</h3>
                    <p><strong>Příjemce:</strong> <?= htmlspecialchars($orderData['customer_name']) ?></p>
                    <p><strong>Doprava:</strong> <?= htmlspecialchars($orderData['shipping_name']) ?></p>
                    <p><strong>Platba:</strong> <?= htmlspecialchars($orderData['payment_name']) ?></p>
                    <p style="margin-top: 10px; font-weight: bold; color: var(--primary-color);">Celková cena: <?= number_format($orderData['total_price'], 0, ',', ' ') ?> Kč</p>
                </div>
            <?php else: ?>
                <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 25px; opacity: 0.9;">
                    Objednávka byla úspěšně dokončena. Děkujeme vám za nákup v našem e-shopu!
                </p>
            <?php endif; ?>

            <a href="index.php" class="btn" style="padding: 15px 30px; font-size: 1.1rem;">Zpět na hlavní stránku</a>
        </div>
    </div>

<?php
require __DIR__ . '/partials/footer.php';
?>
