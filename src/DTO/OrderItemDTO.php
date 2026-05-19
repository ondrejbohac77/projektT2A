<?php

declare(strict_types=1);

readonly class OrderItemDTO {

	public function __construct(
		public int $id,
		public int $orderId,
		public int $productId,
		public string $productName,
		public int $quantity,
		public float $unitPrice,
		public string $variant = '',
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			orderId: (int) $row['order_id'],
			productId: (int) $row['product_id'],
			productName: $row['product_name'],
			quantity: (int) $row['quantity'],
			unitPrice: (float) $row['unit_price'],
			variant: $row['variant'] ?? '',
		);
	}

	public function getTotalPrice(): float {
		return $this->quantity * $this->unitPrice;
	}

}
