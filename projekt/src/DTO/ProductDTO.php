<?php
declare(strict_types=1);

readonly class ProductDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public float $price,
        public ?string $description,
        public ?string $image,
        public bool $isFeatured,
        public int $categoryId,
        public ?string $categoryName = null,
        public int $discountPercent = 0,
        public bool $hasVariants = false
    ) {}

    public function hasDiscount(): bool
    {
        return $this->discountPercent > 0;
    }

    public function getDiscountPercent(): int
    {
        return $this->discountPercent;
    }
}
