<?php
declare(strict_types=1);

class OrderRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * @param int $customerId
     * @param int $shippingMethodId
     * @param int $paymentMethodId
     * @param string|null $note
     * @param CartItemDTO[] $cartItems
     * @return OrderDTO
     */
    public function create(
        int $customerId,
        int $shippingMethodId,
        int $paymentMethodId,
        ?string $note,
        array $cartItems
    ): OrderDTO {
        $this->db->beginTransaction();

        try {
            // Get shipping price
            $stmt = $this->db->prepare("SELECT price FROM shipping_methods WHERE id = ?");
            $stmt->execute([$shippingMethodId]);
            $shippingPrice = (float)($stmt->fetchColumn() ?: 0.0);

            // Get payment price
            $stmt = $this->db->prepare("SELECT price FROM payment_methods WHERE id = ?");
            $stmt->execute([$paymentMethodId]);
            $paymentPrice = (float)($stmt->fetchColumn() ?: 0.0);

            // Calculate total price of goods
            $goodsPrice = 0.0;
            foreach ($cartItems as $item) {
                $goodsPrice += $item->getTotalPrice();
            }
            $totalPrice = $goodsPrice + $shippingPrice + $paymentPrice;

            // Insert order record
            $createdAt = date('Y-m-d H:i:s');
            $status = 'Nová'; // default state

            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    customer_id, shipping_method_id, payment_method_id, 
                    note, shipping_price, payment_price, total_price, 
                    status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $customerId,
                $shippingMethodId,
                $paymentMethodId,
                $note,
                $shippingPrice,
                $paymentPrice,
                $totalPrice,
                $status,
                $createdAt
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // Insert order items
            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, variant, quantity, unit_price)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($cartItems as $item) {
                $stmtItem->execute([
                    $orderId,
                    $item->productId,
                    $item->variant ?? '',
                    $item->quantity,
                    $item->unitPrice
                ]);
            }

            $this->db->commit();

            return new OrderDTO(
                id: $orderId,
                customerId: $customerId,
                shippingMethodId: $shippingMethodId,
                paymentMethodId: $paymentMethodId,
                note: $note,
                shippingPrice: $shippingPrice,
                paymentPrice: $paymentPrice,
                totalPrice: $totalPrice,
                status: $status,
                createdAt: $createdAt
            );
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getById(int $id): ?OrderDTO
    {
        $stmt = $this->db->prepare("
            SELECT id, customer_id, shipping_method_id, payment_method_id, 
                   note, shipping_price, payment_price, total_price, 
                   status, created_at 
            FROM orders WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new OrderDTO(
            id: (int)$row['id'],
            customerId: (int)$row['customer_id'],
            shippingMethodId: (int)$row['shipping_method_id'],
            paymentMethodId: (int)$row['payment_method_id'],
            note: $row['note'],
            shippingPrice: (float)$row['shipping_price'],
            paymentPrice: (float)$row['payment_price'],
            totalPrice: (float)$row['total_price'],
            status: $row['status'],
            createdAt: $row['created_at']
        );
    }
}
