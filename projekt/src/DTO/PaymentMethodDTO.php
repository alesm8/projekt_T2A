<?php
declare(strict_types=1);

readonly class PaymentMethodDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public float $price
    ) {}
}
