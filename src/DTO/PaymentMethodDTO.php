<?php

declare(strict_types=1);

readonly class PaymentMethodDTO {

	public function __construct(
		public int $id,
		public string $name,
		public float $price,
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
		);
	}

	/**
	 * Je platba bez poplatku?
	 */
	public function isFree(): bool {
		return $this->price === 0.0;
	}

}
