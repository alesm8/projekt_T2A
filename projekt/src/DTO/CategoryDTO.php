<?php
declare(strict_types=1);

readonly class CategoryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $image,
        public ?string $description
    ) {}
}
