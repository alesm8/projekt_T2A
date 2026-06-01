<?php
declare(strict_types=1);

class PaymentMethodRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * @return PaymentMethodDTO[]
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, name, price FROM payment_methods ORDER BY price");
        $rows = $stmt->fetchAll();

        $methods = [];
        foreach ($rows as $row) {
            $methods[] = new PaymentMethodDTO(
                id: (int)$row['id'],
                name: $row['name'],
                price: (float)$row['price']
            );
        }
        return $methods;
    }

    public function getById(int $id): ?PaymentMethodDTO
    {
        $stmt = $this->db->prepare("SELECT id, name, price FROM payment_methods WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new PaymentMethodDTO(
            id: (int)$row['id'],
            name: $row['name'],
            price: (float)$row['price']
        );
    }
}
