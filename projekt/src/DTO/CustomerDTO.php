<?php
declare(strict_types=1);

readonly class CustomerDTO
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public string $street,
        public string $city,
        public string $zip
    ) {}
}
