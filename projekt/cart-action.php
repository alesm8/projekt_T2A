<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate CSRF token if not set, but verify if POSTed
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Neplatný bezpečnostní token CSRF.');
    }

    $cart = new Cart();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        $variant = $_POST['variant'] ?? null;
        
        $productRepo = new ProductRepository();
        // Look up by ID
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $row = $stmt->fetch();
        
        if ($row) {
            for ($i = 0; $i < $qty; $i++) {
                $cart->add(
                    productId: (int)$row['id'],
                    productName: $row['name'],
                    unitPrice: (float)$row['price'],
                    image: $row['image'],
                    variant: $variant
                );
            }
        }
    } elseif ($action === 'update') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);
        $variant = $_POST['variant'] ?? null;
        
        $cart->updateQuantity($productId, $qty, $variant);
    } elseif ($action === 'remove') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $variant = $_POST['variant'] ?? null;
        
        $cart->remove($productId, $variant);
    }
    
    $redirectTo = $_POST['redirect_to'] ?? 'kosik.php';
    header('Location: ' . $redirectTo);
    exit;
}

http_response_code(400);
exit('Špatný požadavek.');
