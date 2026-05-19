<?php

declare(strict_types=1);

/**
 * Bootstrap – načte všechny třídy projektu.
 *
 * Na začátku každé PHP stránky stačí vložit:
 *   require_once __DIR__ . '/../src/bootstrap.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database
require_once __DIR__ . '/Database.php';

// DTO
require_once __DIR__ . '/DTO/CategoryDTO.php';
require_once __DIR__ . '/DTO/ProductDTO.php';
require_once __DIR__ . '/DTO/ProductImageDTO.php';
require_once __DIR__ . '/DTO/ProductParameterDTO.php';
require_once __DIR__ . '/DTO/ShippingMethodDTO.php';
require_once __DIR__ . '/DTO/PaymentMethodDTO.php';
require_once __DIR__ . '/DTO/CustomerDTO.php';
require_once __DIR__ . '/DTO/OrderDTO.php';
require_once __DIR__ . '/DTO/OrderItemDTO.php';
require_once __DIR__ . '/DTO/CartItemDTO.php';

// Repositories
require_once __DIR__ . '/Repository/CategoryRepository.php';
require_once __DIR__ . '/Repository/ProductRepository.php';
require_once __DIR__ . '/Repository/ShippingMethodRepository.php';
require_once __DIR__ . '/Repository/PaymentMethodRepository.php';
require_once __DIR__ . '/Repository/CustomerRepository.php';
require_once __DIR__ . '/Repository/OrderRepository.php';

// Cart
require_once __DIR__ . '/Cart.php';

// Validator
require_once __DIR__ . '/Validator.php';
