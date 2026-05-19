<?php

declare(strict_types=1);

readonly class ProductImageDTO {

	public function __construct(
		public int $id,
		public int $productId,
		public string $image,
		public int $sortOrder,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			productId: (int) $row['product_id'],
			image: $row['image'],
			sortOrder: (int) $row['sort_order'],
		);
	}

}
