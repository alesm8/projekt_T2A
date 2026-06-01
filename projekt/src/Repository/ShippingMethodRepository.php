<?php
declare(strict_types=1);

class ShippingMethodRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * @return ShippingMethodDTO[]
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, name, price, delivery_days FROM shipping_methods ORDER BY price");
        $rows = $stmt->fetchAll();

        $methods = [];
        foreach ($rows as $row) {
            $methods[] = new ShippingMethodDTO(
                id: (int)$row['id'],
                name: $row['name'],
                price: (float)$row['price'],
                deliveryDays: $row['delivery_days']
            );
        }
        return $methods;
    }

    public function getById(int $id): ?ShippingMethodDTO
    {
        $stmt = $this->db->prepare("SELECT id, name, price, delivery_days FROM shipping_methods WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new ShippingMethodDTO(
            id: (int)$row['id'],
            name: $row['name'],
            price: (float)$row['price'],
            deliveryDays: $row['delivery_days']
        );
    }
}
