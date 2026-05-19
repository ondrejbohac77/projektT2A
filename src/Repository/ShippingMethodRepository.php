<?php

declare(strict_types=1);

final class ShippingMethodRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Vrátí všechny způsoby dopravy.
	 *
	 * @return list<ShippingMethodDTO>
	 */
	public function getAll(): array {
		$stmt = $this->db->query('SELECT * FROM shipping_methods ORDER BY price');

		return array_map(ShippingMethodDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Najde způsob dopravy podle ID.
	 */
	public function getById(int $id): ?ShippingMethodDTO {
		$stmt = $this->db->prepare('SELECT * FROM shipping_methods WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? ShippingMethodDTO::fromRow($row) : NULL;
	}

}
