<?php
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$items = $cart->getItems();
$total = $cart->getTotalPrice();

$pageTitle = 'Košík | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <div class="cart-container">
        <!-- Steps Indicator -->
        <div class="cart-steps">
            <div class="cart-step active">1. Košík</div>
            <div class="cart-step">2. Dodací údaje</div>
            <div class="cart-step">3. Doprava a platba</div>
            <div class="cart-step">4. Potvrzení</div>
        </div>

        <div class="cart-box">
            <h1>🛒 Tvůj výběr</h1>
            
            <?php if (empty($items)): ?>
                <p style="text-align: center; font-size: 1.2rem; margin: 40px 0; opacity: 0.7;">Tvůj košík je prázdný.</p>
                <div style="text-align: center;">
                    <a href="index.php" class="btn">Zpět k výběru čajů</a>
                </div>
            <?php else: ?>
                <div class="cart-items">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding: 15px 0;">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <img src="<?= htmlspecialchars($item->image ?? 'assets/images/zeleny-a-jasmin.jpg') ?>" alt="<?= htmlspecialchars($item->productName) ?>" style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover;">
                                <div class="item-info">
                                    <span class="item-name"><strong><?= htmlspecialchars($item->productName) ?></strong></span>
                                    <?php if ($item->variant): ?>
                                        <span class="item-variant" style="display: block; font-size: 0.85rem; opacity: 0.7;">Varianta: <?= htmlspecialchars($item->variant) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Quantity adjustment and delete forms -->
                            <div style="display: flex; align-items: center; gap: 20px;">
                                <form action="cart-action.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                                    <?php if ($item->variant): ?>
                                        <input type="hidden" name="variant" value="<?= htmlspecialchars($item->variant) ?>">
                                    <?php endif; ?>
                                    <input type="number" name="quantity" class="item-qty-input" value="<?= $item->quantity ?>" min="1" onchange="this.form.submit();" style="width: 60px; padding: 5px; text-align: center; border-radius: 5px; border: 1px solid #ccc;">
                                </form>

                                <span class="item-price" style="font-weight: bold; min-width: 80px; text-align: right;"><?= number_format($item->getTotalPrice(), 0, ',', ' ') ?> Kč</span>

                                <form action="cart-action.php" method="POST" style="margin: 0;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $item->productId ?>">
                                    <?php if ($item->variant): ?>
                                        <input type="hidden" name="variant" value="<?= htmlspecialchars($item->variant) ?>">
                                    <?php endif; ?>
                                    <button type="submit" style="background: none; border: none; color: #ff5e5e; cursor: pointer; font-size: 1.2rem;" title="Odebrat položku">✖</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-total" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 0; font-size: 1.3rem;">
                    <span>Celkem k úhradě:</span>
                    <strong><?= number_format($total, 0, ',', ' ') ?> Kč</strong>
                </div>

                <hr>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <a href="index.php" class="back-link" style="margin-top: 0;">← Zpět k výběru čajů</a>
                    <a href="dodaci-udaje.php" class="order-btn" style="margin-top: 0; text-decoration: none; text-align: center;">Pokračovat k objednávce →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php
require __DIR__ . '/partials/footer.php';
?>
