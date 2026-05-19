<?php

declare(strict_types=1);

readonly class ShippingMethodDTO {

	public function __construct(
		public int $id,
		public string $name,
		public float $price,
		public string $deliveryDays,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			name: $row['name'],
			price: (float) $row['price'],
			deliveryDays: $row['delivery_days'],
		);
	}

	/**
	 * Je doprava zdarma?
	 */
	public function isFree(): bool {
		return $this->price === 0.0;
	}

}
