<?php
declare(strict_types=1);

readonly class OrderDTO
{
    public function __construct(
        public int $id,
        public int $customerId,
        public int $shippingMethodId,
        public int $paymentMethodId,
        public ?string $note,
        public float $shippingPrice,
        public float $paymentPrice,
        public float $totalPrice,
        public string $status,
        public string $createdAt
    ) {}
}
