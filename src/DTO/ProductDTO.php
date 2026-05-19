<?php

declare(strict_types=1);

readonly class ProductDTO {

	public function __construct(
		public int $id,
		public int $categoryId,
		public string $name,
		public string $slug,
		public float $price,
		public ?float $originalPrice,
		public string $description,
		public string $image,
		public bool $featured,
		public string $createdAt,
		public ?string $categoryName = NULL,
		public ?string $categorySlug = NULL,
		public bool $hasVariants = false,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			categoryId: (int) $row['category_id'],
			name: $row['name'],
			slug: $row['slug'],
			price: (float) $row['price'],
			originalPrice: isset($row['original_price']) ? (float) $row['original_price'] : NULL,
			description: $row['description'],
			image: $row['image'],
			featured: (bool) $row['featured'],
			createdAt: $row['created_at'],
			categoryName: $row['category_name'] ?? NULL,
			categorySlug: $row['category_slug'] ?? NULL,
			hasVariants: (bool) ($row['has_variants'] ?? false),
		);
	}

	public function hasDiscount(): bool {
		return $this->originalPrice !== NULL && $this->originalPrice > $this->price;
	}

	public function getDiscountPercent(): int {
		if (!$this->hasDiscount()) {
			return 0;
		}

		return (int) round(100 - ($this->price / $this->originalPrice * 100));
	}

}
