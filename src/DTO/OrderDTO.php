<?php

declare(strict_types=1);

readonly class OrderDTO {

	/**
	 * @param list<OrderItemDTO> $items
	 */
	public function __construct(
		public int $id,
		public int $customerId,
		public int $shippingMethodId,
		public int $paymentMethodId,
		public float $shippingPrice,
		public float $paymentPrice,
		public string $note,
		public float $totalPrice,
		public string $status,
		public string $createdAt,
		public ?CustomerDTO $customer = NULL,
		public ?ShippingMethodDTO $shippingMethod = NULL,
		public ?PaymentMethodDTO $paymentMethod = NULL,
		public array $items = [],
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			customerId: (int) $row['customer_id'],
			shippingMethodId: (int) $row['shipping_method_id'],
			paymentMethodId: (int) $row['payment_method_id'],
			shippingPrice: (float) $row['shipping_price'],
			paymentPrice: (float) $row['payment_price'],
			note: $row['note'] ?? '',
			totalPrice: (float) $row['total_price'],
			status: $row['status'],
			createdAt: $row['created_at'],
		);
	}

}
