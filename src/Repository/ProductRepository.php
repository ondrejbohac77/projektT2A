<?php

declare(strict_types=1);

final class ProductRepository {

	private PDO $db;

	/**
	 * Společný SELECT pro všechny dotazy na produkty.
	 * Obsahuje JOIN na kategorie a subquery pro zjištění, zda má produkt volitelné varianty.
	 */
	private const string BASE_SELECT = '
		SELECT p.*, c.name AS category_name, c.slug AS category_slug,
			EXISTS (
				SELECT 1 FROM product_parameters pp
				WHERE pp.product_id = p.id AND pp.type = \'select\'
			) AS has_variants
		FROM products p
		JOIN categories c ON p.category_id = c.id
	';

	public function __construct() {
		$this->db = Database::getConnection();
	}

	/**
	 * Vrátí všechny produkty.
	 *
	 * @return list<ProductDTO>
	 */
	public function getAll(): array {
		$stmt = $this->db->query(self::BASE_SELECT . ' ORDER BY p.created_at DESC');

		return array_map(ProductDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Najde produkt podle ID.
	 */
	public function getById(int $id): ?ProductDTO {
		$stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE p.id = :id');
		$stmt->execute(['id' => $id]);

		$row = $stmt->fetch();

		return $row ? ProductDTO::fromRow($row) : NULL;
	}

	/**
	 * Najde produkt podle slugu.
	 */
	public function getBySlug(string $slug): ?ProductDTO {
		$stmt = $this->db->prepare(self::BASE_SELECT . ' WHERE p.slug = :slug');
		$stmt->execute(['slug' => $slug]);

		$row = $stmt->fetch();

		return $row ? ProductDTO::fromRow($row) : NULL;
	}

	/**
	 * Vrátí produkty v dané kategorii.
	 *
	 * @return list<ProductDTO>
	 */
	public function getByCategory(int $categoryId): array {
		$stmt = $this->db->prepare(self::BASE_SELECT . '
			WHERE p.category_id = :categoryId
			ORDER BY p.created_at DESC
		');
		$stmt->execute(['categoryId' => $categoryId]);

		return array_map(ProductDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí produkty v kategorii podle slugu kategorie.
	 *
	 * @return list<ProductDTO>
	 */
	public function getByCategorySlug(string $slug): array {
		$stmt = $this->db->prepare(self::BASE_SELECT . '
			WHERE c.slug = :slug
			ORDER BY p.created_at DESC
		');
		$stmt->execute(['slug' => $slug]);

		return array_map(ProductDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí doporučené (featured) produkty pro hlavní stránku.
	 *
	 * @return list<ProductDTO>
	 */
	public function getFeatured(int $limit = 8, bool $random = false): array {
		$order = $random ? 'RANDOM()' : 'p.created_at DESC';
		$stmt = $this->db->prepare(self::BASE_SELECT . "
			WHERE p.featured = 1
			ORDER BY $order
			LIMIT :limit
		");
		$stmt->bindValue('limit', $limit, PDO::PARAM_INT);
		$stmt->execute();

		return array_map(ProductDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vyhledá produkty podle názvu nebo popisu.
	 *
	 * @return list<ProductDTO>
	 */
	public function search(string $query): array {
		$escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $query);

		$stmt = $this->db->prepare(self::BASE_SELECT . "
			WHERE p.name LIKE :query ESCAPE '\\'
			   OR p.description LIKE :query ESCAPE '\\'
			ORDER BY p.name
		");
		$stmt->execute(['query' => '%' . $escaped . '%']);

		return array_map(ProductDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí obrázky galerie pro daný produkt.
	 *
	 * @return list<ProductImageDTO>
	 */
	public function getImages(int $productId): array {
		$stmt = $this->db->prepare('
            SELECT * FROM product_images
            WHERE product_id = :productId
            ORDER BY sort_order
        ');
		$stmt->execute(['productId' => $productId]);

		return array_map(ProductImageDTO::fromRow(...), $stmt->fetchAll());
	}

	/**
	 * Vrátí parametry daného produktu (velikost, barva, materiál...).
	 *
	 * @return list<ProductParameterDTO>
	 */
	public function getParameters(int $productId): array {
		$stmt = $this->db->prepare('
            SELECT * FROM product_parameters
            WHERE product_id = :productId
            ORDER BY type DESC, name
        ');
		$stmt->execute(['productId' => $productId]);

		return array_map(ProductParameterDTO::fromRow(...), $stmt->fetchAll());
	}

}
