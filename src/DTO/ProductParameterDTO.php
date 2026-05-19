<?php

declare(strict_types=1);

readonly class ProductParameterDTO {

	public function __construct(
		public int $id,
		public int $productId,
		public string $name,
		public string $value,
		public string $type = 'info',
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			productId: (int) $row['product_id'],
			name: $row['name'],
			value: $row['value'],
			type: $row['type'] ?? 'info',
		);
	}

	/**
	 * Zjistí, zda je parametr volitelný (zobrazí se jako dropdown).
	 */
	public function isSelectable(): bool {
		return $this->type === 'select';
	}

	/**
	 * Vrátí jednotlivé možnosti volitelného parametru.
	 *
	 * @return list<string>
	 */
	public function getOptions(): array {
		return array_map(trim(...), explode(',', $this->value));
	}

}
