<?php
declare(strict_types=1);

class CategoryRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * @return CategoryDTO[]
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT id, name, slug, image, description FROM categories ORDER BY id");
        $rows = $stmt->fetchAll();
        
        $categories = [];
        foreach ($rows as $row) {
            $categories[] = new CategoryDTO(
                id: (int)$row['id'],
                name: $row['name'],
                slug: $row['slug'],
                image: $row['image'],
                description: $row['description']
            );
        }
        return $categories;
    }

    public function getById(int $id): ?CategoryDTO
    {
        $stmt = $this->db->prepare("SELECT id, name, slug, image, description FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        
        return new CategoryDTO(
            id: (int)$row['id'],
            name: $row['name'],
            slug: $row['slug'],
            image: $row['image'],
            description: $row['description']
        );
    }

    public function getBySlug(string $slug): ?CategoryDTO
    {
        $stmt = $this->db->prepare("SELECT id, name, slug, image, description FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        
        return new CategoryDTO(
            id: (int)$row['id'],
            name: $row['name'],
            slug: $row['slug'],
            image: $row['image'],
            description: $row['description']
        );
    }
}
