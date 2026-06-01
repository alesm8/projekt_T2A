<?php
declare(strict_types=1);

class ProductRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function mapRowToDTO(array $row): ProductDTO
    {
        $productId = (int)$row['id'];
        
        // Determine if product has variants
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM product_parameters WHERE product_id = ? AND type = 'select'");
        $stmt->execute([$productId]);
        $hasVariants = ((int)$stmt->fetchColumn()) > 0;

        return new ProductDTO(
            id: $productId,
            name: $row['name'],
            slug: $row['slug'],
            price: (float)$row['price'],
            description: $row['description'],
            image: $row['image'],
            isFeatured: (bool)$row['is_featured'],
            categoryId: (int)$row['category_id'],
            categoryName: $row['category_name'] ?? null,
            discountPercent: (int)($row['discount_percent'] ?? 0),
            hasVariants: $hasVariants
        );
    }

    /**
     * @return ProductDTO[]
     */
    public function getFeatured(int $limit = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_featured = 1 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $products = [];
        foreach ($stmt->fetchAll() as $row) {
            $products[] = $this->mapRowToDTO($row);
        }
        return $products;
    }

    /**
     * @return ProductDTO[]
     */
    public function getByCategory(int $categoryId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.category_id = ?
        ");
        $stmt->execute([$categoryId]);
        
        $products = [];
        foreach ($stmt->fetchAll() as $row) {
            $products[] = $this->mapRowToDTO($row);
        }
        return $products;
    }

    /**
     * @return ProductDTO[]
     */
    public function getByCategorySlug(string $categorySlug): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name 
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE c.slug = ?
        ");
        $stmt->execute([$categorySlug]);
        
        $products = [];
        foreach ($stmt->fetchAll() as $row) {
            $products[] = $this->mapRowToDTO($row);
        }
        return $products;
    }

    public function getBySlug(string $slug): ?ProductDTO
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ?
        ");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        return $this->mapRowToDTO($row);
    }

    /**
     * @return ProductImageDTO[]
     */
    public function getImages(int $productId): array
    {
        $stmt = $this->db->prepare("SELECT id, product_id, image FROM product_images WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        $images = [];
        foreach ($stmt->fetchAll() as $row) {
            $images[] = new ProductImageDTO(
                id: (int)$row['id'],
                productId: (int)$row['product_id'],
                image: $row['image']
            );
        }
        return $images;
    }

    /**
     * @return ProductParameterDTO[]
     */
    public function getParameters(int $productId): array
    {
        $stmt = $this->db->prepare("SELECT id, product_id, name, value, type FROM product_parameters WHERE product_id = ?");
        $stmt->execute([$productId]);
        
        $params = [];
        foreach ($stmt->fetchAll() as $row) {
            $params[] = new ProductParameterDTO(
                id: (int)$row['id'],
                productId: (int)$row['product_id'],
                name: $row['name'],
                value: $row['value'],
                type: $row['type']
            );
        }
        return $params;
    }

    /**
     * @return ProductDTO[]
     */
    public function search(string $query): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name AS category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE ? OR p.description LIKE ?
        ");
        $likeQuery = '%' . $query . '%';
        $stmt->execute([$likeQuery, $likeQuery]);
        
        $products = [];
        foreach ($stmt->fetchAll() as $row) {
            $products[] = $this->mapRowToDTO($row);
        }
        return $products;
    }
}
