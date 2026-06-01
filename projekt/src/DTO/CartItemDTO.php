<?php
declare(strict_types=1);

readonly class CartItemDTO
{
    public function __construct(
        public int $productId,
        public string $productName,
        public float $unitPrice,
        public ?string $image,
        public ?string $variant,
        public int $quantity
    ) {}

    public function getTotalPrice(): float
    {
        return $this->unitPrice * $this->quantity;
    }
}
