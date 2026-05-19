<?php

declare(strict_types=1);

readonly class CustomerDTO {

	public function __construct(
		public int $id,
		public string $firstName,
		public string $lastName,
		public string $email,
		public string $phone,
		public string $street,
		public string $city,
		public string $zip,
		public string $createdAt,
	) {
	}

	/**
	 * @param array<string, mixed> $row
	 */
	public static function fromRow(array $row): self {
		return new self(
			id: (int) $row['id'],
			firstName: $row['first_name'],
			lastName: $row['last_name'],
			email: $row['email'],
			phone: $row['phone'],
			street: $row['street'],
			city: $row['city'],
			zip: $row['zip'],
			createdAt: $row['created_at'],
		);
	}

	public function getFullName(): string {
		return $this->firstName . ' ' . $this->lastName;
	}

	public function getFullAddress(): string {
		return "{$this->street}, {$this->zip} {$this->city}";
	}

}
