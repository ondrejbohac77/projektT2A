<?php

declare(strict_types=1);

final class CartItemDTO {

	public function __construct(
		public int $productId,
		public string $productName,
		public float $unitPrice,
		public string $image,
		public int $quantity,
		public string $variant = '',
	) {
	}

	public function getTotalPrice(): float {
		return $this->quantity * $this->unitPrice;
	}

}
