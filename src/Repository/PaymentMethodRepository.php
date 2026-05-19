<?php

declare(strict_types=1);

final class PaymentMethodRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Vrátí všechny způsoby platby.
	 *
	 * @return list<PaymentMethodDTO>
	 */
	public function getAll(): array {
		$stmt = $this->db->query('SELECT * FROM payment_methods ORDER BY price');

		return array_map(PaymentMethodDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Najde způsob platby podle ID.
	 */
	public function getById(int $id): ?PaymentMethodDTO {
		$stmt = $this->db->prepare('SELECT * FROM payment_methods WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? PaymentMethodDTO::fromRow($row) : NULL;
	}

}
