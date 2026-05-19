<?php

declare(strict_types=1);

final class CustomerRepository {

	private PDO $db;

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Najde zákazníka podle ID.
	 */
	public function getById(int $id): ?CustomerDTO {
		$stmt = $this->db->prepare('SELECT * FROM customers WHERE id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? CustomerDTO::fromRow($row) : NULL;
	}

	/**
	 * Najde zákazníka podle e-mailu.
	 */
	public function getByEmail(string $email): ?CustomerDTO {
		$stmt = $this->db->prepare('SELECT * FROM customers WHERE email = :email');
		$stmt->execute(['email' => $email]);

		$row = $stmt->fetch();

		return $row ? CustomerDTO::fromRow($row) : NULL;
	}

	/**
	 * Vytvoří nového zákazníka a vrátí jeho DTO.
	 */
	public function create(
		string $firstName,
		string $lastName,
		string $email,
		string $phone,
		string $street,
		string $city,
		string $zip,
	): CustomerDTO {
		$stmt = $this->db->prepare('
            INSERT INTO customers (first_name, last_name, email, phone, street, city, zip)
            VALUES (:firstName, :lastName, :email, :phone, :street, :city, :zip)
        ');

		$stmt->execute([
			'firstName' => $firstName,
			'lastName'  => $lastName,
			'email'     => $email,
			'phone'     => $phone,
			'street'    => $street,
			'city'      => $city,
			'zip'       => $zip,
		]);

		return $this->getById((int) $this->db->lastInsertId())
			?? throw new \RuntimeException('Nepodařilo se vytvořit zákazníka.');
	}

}
