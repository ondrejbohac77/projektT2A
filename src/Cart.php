<?php

declare(strict_types=1);

final class Cart {

	private const string SESSION_KEY = 'cart';

	public function __construct() {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}

	/**
	 * Přidá produkt do košíku (nebo zvýší množství, pokud už v košíku je).
	 * Stejný produkt v různých variantách = samostatné položky.
	 */
	public function add(int $productId, string $productName, float $unitPrice, string $image, int $quantity = 1, string $variant = ''): void {
		$items = $this->getRawItems();
		$key = $this->makeKey($productId, $variant);

		if (isset($items[$key])) {
			$items[$key]['quantity'] += $quantity;
		} else {
			$items[$key] = [
				'product_id'   => $productId,
				'product_name' => $productName,
				'unit_price'   => $unitPrice,
				'image'        => $image,
				'quantity'     => $quantity,
				'variant'      => $variant,
			];
		}

		$_SESSION[self::SESSION_KEY] = $items;
	}

	/**
	 * Nastaví množství konkrétního produktu (a varianty) v košíku.
	 */
	public function updateQuantity(int $productId, int $quantity, string $variant = ''): void {
		$items = $this->getRawItems();
		$key = $this->makeKey($productId, $variant);

		if ($quantity <= 0) {
			unset($items[$key]);
		} elseif (isset($items[$key])) {
			$items[$key]['quantity'] = $quantity;
		}

		$_SESSION[self::SESSION_KEY] = $items;
	}

	/**
	 * Odebere produkt (a variantu) z košíku.
	 */
	public function remove(int $productId, string $variant = ''): void {
		$items = $this->getRawItems();
		$key = $this->makeKey($productId, $variant);
		unset($items[$key]);
		$_SESSION[self::SESSION_KEY] = $items;
	}

	/**
	 * Vrátí všechny položky košíku jako DTO objekty.
	 *
	 * @return list<CartItemDTO>
	 */
	public function getItems(): array {
		return array_values(
			array_map(
				fn(array $item): CartItemDTO => new CartItemDTO(
					productId: $item['product_id'],
					productName: $item['product_name'],
					unitPrice: $item['unit_price'],
					image: $item['image'],
					quantity: $item['quantity'],
					variant: $item['variant'] ?? '',
				),
				$this->getRawItems(),
			),
		);
	}

	/**
	 * Vrátí celkovou cenu košíku.
	 */
	public function getTotalPrice(): float {
		return array_sum(
			array_map(
				fn(CartItemDTO $item): float => $item->getTotalPrice(),
				$this->getItems(),
			),
		);
	}

	/**
	 * Vrátí celkový počet kusů v košíku.
	 */
	public function getTotalQuantity(): int {
		return array_sum(
			array_map(
				fn(array $item): int => $item['quantity'],
				$this->getRawItems(),
			),
		);
	}

	/**
	 * Vyprázdní celý košík.
	 */
	public function clear(): void {
		$_SESSION[self::SESSION_KEY] = [];
	}

	/**
	 * Zjistí, zda je košík prázdný.
	 */
	public function isEmpty(): bool {
		return $this->getRawItems() === [];
	}

	/**
	 * Vytvoří klíč pro položku košíku (productId nebo productId|variant).
	 */
	private function makeKey(int $productId, string $variant): string {
		return $variant === '' ? (string) $productId : $productId . '|' . $variant;
	}

	/**
	 * @return array<string, array{product_id: int, product_name: string, unit_price: float, image: string, quantity: int, variant: string}>
	 */
	private function getRawItems(): array {
		$items = $_SESSION[self::SESSION_KEY] ?? [];

		// Migrace ze starého formátu (bez variant) – vyprázdní košík
		if ($items !== [] && !array_key_exists('variant', reset($items))) {
			$_SESSION[self::SESSION_KEY] = [];
			return [];
		}

		return $items;
	}

}
