<?php
declare(strict_types=1);

readonly class ProductImageDTO
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $image
    ) {}
}
