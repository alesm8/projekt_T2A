<?php
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
if ($cart->isEmpty()) {
    header('Location: kosik.php');
    exit;
}

$custData = $_SESSION['customer_data'] ?? null;
if (!$custData) {
    header('Location: dodaci-udaje.php');
    exit;
}

$shippingRepo = new ShippingMethodRepository();
$paymentRepo = new PaymentMethodRepository();

$shippingMethods = $shippingRepo->getAll();
$paymentMethods = $paymentRepo->getAll();

$totalGoods = $cart->getTotalPrice();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token CSRF.');
    }

    $shippingId = (int)($_POST['shipping'] ?? 0);
    $paymentId = (int)($_POST['payment'] ?? 0);

    $selectedShipping = $shippingRepo->getById($shippingId);
    $selectedPayment = $paymentRepo->getById($paymentId);

    if ($selectedShipping && $selectedPayment) {
        try {
            $customerRepo = new CustomerRepository();
            $orderRepo = new OrderRepository();

            // 1. Create customer
            $customer = $customerRepo->create(
                firstName: $custData['first_name'],
                lastName: $custData['last_name'],
                email: $custData['email'],
                phone: $custData['phone'],
                street: $custData['street'],
                city: $custData['city'],
                zip: $custData['zip']
            );

            // 2. Create order
            $order = $orderRepo->create(
                customerId: $customer->id,
                shippingMethodId: $selectedShipping->id,
                paymentMethodId: $selectedPayment->id,
                note: $custData['note'] ?: null,
                cartItems: $cart->getItems()
            );

            // 3. Store information for confirmation page
            $_SESSION['last_order'] = [
                'id' => $order->id,
                'total_price' => $order->totalPrice,
                'shipping_name' => $selectedShipping->name,
                'payment_name' => $selectedPayment->name,
                'customer_name' => $customer->firstName . ' ' . $customer->lastName
            ];

            // 4. Clear cart and session checkout data
            $cart->clear();
            unset($_SESSION['customer_data']);

            header('Location: potvrzeni.php');
            exit;
        } catch (Exception $e) {
            $errorMsg = "Nepodařilo se vytvořit objednávku: " . $e->getMessage();
        }
    } else {
        $errorMsg = "Prosím vyberte platný způsob dopravy a platby.";
    }
}

$pageTitle = 'Doprava a platba | Čajový svět';
require __DIR__ . '/partials/header.php';
?>

    <div class="cart-container">
        <!-- Steps Indicator -->
        <div class="cart-steps">
            <a href="kosik.php" class="cart-step">1. Košík</a>
            <a href="dodaci-udaje.php" class="cart-step">2. Dodací údaje</a>
            <div class="cart-step active">3. Doprava a platba</div>
            <div class="cart-step">4. Potvrzení</div>
        </div>

        <div class="cart-box">
            <h1>🚚 Doprava a platba</h1>
            
            <?php if (isset($errorMsg)): ?>
                <div style="background-color: #ff5e5e; color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <form class="checkout-form" method="POST" action="doprava-platba.php" id="checkoutForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div>
                    <h3>Způsob dopravy *</h3>
                    <div class="shipping-methods">
                        <?php foreach ($shippingMethods as $index => $method): ?>
                            <label style="display: flex; align-items: center; padding: 12px; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 10px; cursor: pointer; gap: 10px;">
                                <input type="radio" name="shipping" value="<?= $method->id ?>" data-price="<?= $method->price ?>" <?= $index === 0 ? 'checked' : '' ?> onchange="updateTotal();">
                                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                                    <span><?= htmlspecialchars($method->name) ?> (<?= htmlspecialchars($method->deliveryDays) ?>)</span>
                                    <strong><?= $method->price > 0 ? number_format($method->price, 0, ',', ' ') . ' Kč' : 'ZDARMA' ?></strong>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <h3>Způsob platby *</h3>
                    <div class="payment-methods">
                        <?php foreach ($paymentMethods as $index => $method): ?>
                            <label style="display: flex; align-items: center; padding: 12px; border: 1px solid var(--border-color); border-radius: 10px; margin-bottom: 10px; cursor: pointer; gap: 10px;">
                                <input type="radio" name="payment" value="<?= $method->id ?>" data-price="<?= $method->price ?>" <?= $index === 0 ? 'checked' : '' ?> onchange="updateTotal();">
                                <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                                    <span><?= htmlspecialchars($method->name) ?></span>
                                    <strong><?= $method->price > 0 ? '+' . number_format($method->price, 0, ',', ' ') . ' Kč' : 'ZDARMA' ?></strong>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <hr style="margin: 20px 0;">

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <a href="dodaci-udaje.php" class="back-link" style="margin-top: 0;">← Zpět k údajům</a>
                    <button type="submit" class="order-btn" style="margin-top: 0; cursor: pointer; border: none;" id="submitBtn">Dokončit objednávku (Celkem: <?= number_format($totalGoods, 0, ',', ' ') ?> Kč) →</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const totalGoods = <?= $totalGoods ?>;
        function updateTotal() {
            let shippingPrice = 0;
            let paymentPrice = 0;

            const selectedShipping = document.querySelector('input[name="shipping"]:checked');
            if (selectedShipping) {
                shippingPrice = parseFloat(selectedShipping.getAttribute('data-price') || 0);
            }

            const selectedPayment = document.querySelector('input[name="payment"]:checked');
            if (selectedPayment) {
                paymentPrice = parseFloat(selectedPayment.getAttribute('data-price') || 0);
            }

            const total = totalGoods + shippingPrice + paymentPrice;
            document.getElementById('submitBtn').innerText = 'Dokončit objednávku (Celkem: ' + total.toLocaleString('cs-CZ') + ' Kč) →';
        }
        window.addEventListener('DOMContentLoaded', updateTotal);
    </script>

<?php
require __DIR__ . '/partials/footer.php';
?>
