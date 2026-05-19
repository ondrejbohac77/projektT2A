<?php

declare(strict_types=1);

final class OrderRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Najde objednávku podle ID (včetně zákazníka, dopravy, platby a položek).
	 */
	public function getById(int $id): ?OrderDTO {
		$stmt = $this->db->prepare('SELECT * FROM orders WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		if (!$row) {
			return NULL;
		}

		$order = OrderDTO::fromRow($row);

		$customerRepo = new CustomerRepository();
		$shippingRepo = new ShippingMethodRepository();
		$paymentRepo = new PaymentMethodRepository();

		return new OrderDTO(
			id: $order->id,
			customerId: $order->customerId,
			shippingMethodId: $order->shippingMethodId,
			paymentMethodId: $order->paymentMethodId,
			shippingPrice: $order->shippingPrice,
			paymentPrice: $order->paymentPrice,
			note: $order->note,
			totalPrice: $order->totalPrice,
			status: $order->status,
			createdAt: $order->createdAt,
			customer: $customerRepo->getById($order->customerId),
			shippingMethod: $shippingRepo->getById($order->shippingMethodId),
			paymentMethod: $paymentRepo->getById($order->paymentMethodId),
			items: $this->getItems($order->id),
		);
	}

	/**
	 * Vytvoří novou objednávku z položek košíku.
	 *
	 * @param list<CartItemDTO> $cartItems
	 */
	public function create(
		int $customerId,
		int $shippingMethodId,
		int $paymentMethodId,
		string $note,
		array $cartItems,
	): OrderDTO {
		$shippingRepo = new ShippingMethodRepository();
		$paymentRepo = new PaymentMethodRepository();

		$shipping = $shippingRepo->getById($shippingMethodId)
			?? throw new \RuntimeException("Způsob dopravy s ID $shippingMethodId neexistuje.");
		$payment = $paymentRepo->getById($paymentMethodId)
			?? throw new \RuntimeException("Způsob platby s ID $paymentMethodId neexistuje.");

		$itemsPrice = array_sum(
			array_map(fn(CartItemDTO $item): float => $item->getTotalPrice(), $cartItems),
		);

		$shippingPrice = $shipping->price;
		$paymentPrice = $payment->price;
		$totalPrice = $itemsPrice + $shippingPrice + $paymentPrice;

		$this->db->beginTransaction();

		try {
			$stmt = $this->db->prepare("
                INSERT INTO orders (customer_id, shipping_method_id, payment_method_id, shipping_price, payment_price, note, total_price, status)
                VALUES (:customerId, :shippingMethodId, :paymentMethodId, :shippingPrice, :paymentPrice, :note, :totalPrice, 'new')
            ");

			$stmt->execute([
				'customerId'       => $customerId,
				'shippingMethodId' => $shippingMethodId,
				'paymentMethodId'  => $paymentMethodId,
				'shippingPrice'    => $shippingPrice,
				'paymentPrice'     => $paymentPrice,
				'note'             => $note,
				'totalPrice'       => $totalPrice,
			]);

			$orderId = (int) $this->db->lastInsertId();

			$itemStmt = $this->db->prepare('
                INSERT INTO order_items (order_id, product_id, product_name, variant, quantity, unit_price)
                VALUES (:orderId, :productId, :productName, :variant, :quantity, :unitPrice)
            ');

			foreach ($cartItems as $item) {
				$itemStmt->execute([
					'orderId'     => $orderId,
					'productId'   => $item->productId,
					'productName' => $item->productName,
					'variant'     => $item->variant,
					'quantity'    => $item->quantity,
					'unitPrice'   => $item->unitPrice,
				]);
			}

			$this->db->commit();
		} catch (\Throwable $e) {
			$this->db->rollBack();
			throw $e;
		}

		return $this->getById($orderId);
	}

	/**
	 * Vrátí položky objednávky.
	 *
	 * @return list<OrderItemDTO>
	 */
	private function getItems(int $orderId): array {
		$stmt = $this->db->prepare('
            SELECT * FROM order_items
            WHERE order_id = :orderId
            ORDER BY id
        ');
		$stmt->execute(['orderId' => $orderId]);

		return array_map(OrderItemDTO::fromRow(...), $stmt->fetchAll());
	}

}
