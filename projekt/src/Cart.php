<?php
declare(strict_types=1);

class Cart
{
    private PDO $db;
    private string $sessionId;

    public function __construct()
    {
        $this->db = Database::getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->sessionId = session_id();
    }

    public function add(int $productId, string $productName, float $unitPrice, ?string $image, ?string $variant = null): void
    {
        $variantStr = $variant ?? '';
        
        // Check if item already exists in database cart
        $stmt = $this->db->prepare("
            SELECT quantity FROM cart 
            WHERE session_id = ? AND product_id = ? AND variant = ?
        ");
        $stmt->execute([$this->sessionId, $productId, $variantStr]);
        $row = $stmt->fetch();

        if ($row) {
            $newQuantity = $row['quantity'] + 1;
            $this->updateQuantity($productId, $newQuantity, $variant);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO cart (session_id, product_id, variant, quantity)
                VALUES (?, ?, ?, 1)
            ");
            $stmt->execute([$this->sessionId, $productId, $variantStr]);
        }
    }

    public function updateQuantity(int $productId, int $quantity, ?string $variant = null): void
    {
        $variantStr = $variant ?? '';
        if ($quantity <= 0) {
            $this->remove($productId, $variant);
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE cart SET quantity = ? 
            WHERE session_id = ? AND product_id = ? AND variant = ?
        ");
        $stmt->execute([$quantity, $this->sessionId, $productId, $variantStr]);
    }

    public function remove(int $productId, ?string $variant = null): void
    {
        $variantStr = $variant ?? '';
        $stmt = $this->db->prepare("
            DELETE FROM cart 
            WHERE session_id = ? AND product_id = ? AND variant = ?
        ");
        $stmt->execute([$this->sessionId, $productId, $variantStr]);
    }

    /**
     * @return CartItemDTO[]
     */
    public function getItems(): array
    {
        $stmt = $this->db->prepare("
            SELECT c.product_id, p.name AS product_name, p.price AS unit_price, p.image, c.variant, c.quantity
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.session_id = ?
        ");
        $stmt->execute([$this->sessionId]);
        $rows = $stmt->fetchAll();

        $items = [];
        foreach ($rows as $row) {
            $items[] = new CartItemDTO(
                productId: (int)$row['product_id'],
                productName: $row['product_name'],
                unitPrice: (float)$row['unit_price'],
                image: $row['image'],
                variant: $row['variant'] !== '' ? $row['variant'] : null,
                quantity: (int)$row['quantity']
            );
        }
        return $items;
    }

    public function getTotalPrice(): float
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += $item->getTotalPrice();
        }
        return $total;
    }

    public function getTotalQuantity(): int
    {
        $stmt = $this->db->prepare("
            SELECT SUM(quantity) as total_qty FROM cart 
            WHERE session_id = ?
        ");
        $stmt->execute([$this->sessionId]);
        $row = $stmt->fetch();
        return (int)($row['total_qty'] ?? 0);
    }

    public function isEmpty(): bool
    {
        return $this->getTotalQuantity() === 0;
    }

    public function clear(): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM cart WHERE session_id = ?
        ");
        $stmt->execute([$this->sessionId]);
    }
}
