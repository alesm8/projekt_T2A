<?php
declare(strict_types=1);

readonly class OrderItemDTO
{
    public function __construct(
        public int $id,
        public int $orderId,
        public int $productId,
        public ?string $variant,
        public int $quantity,
        public float $unitPrice
    ) {}
}
