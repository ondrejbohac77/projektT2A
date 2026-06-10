# Dokumentace E-shop All for Bikes

## 📋 Obsah
1. [Úvod do projektu](#úvod-do-projektu)
2. [Jak spustit projekt](#jak-spustit-projekt)
3. [Architektura projektu](#architektura-projektu)
4. [Klíčové funkce](#klíčové-funkce)
5. [Struktura databáze](#struktura-databáze)
6. [Příklady použití](#příklady-použití)

---

## 🎯 Úvod do projektu

**All for Bikes** je moderní e-shop na prodej horských kol a souvisejících produktů. Projekt byl vyvíjen pomocí:
- **PHP 8.1+** (s typováním)
- **SQLite databáze** (lightweight, bez nutnosti externího serveru)
- **Čisté objektově orientované programování**
- **Repository pattern** pro správu dat
- **CSRF ochrana** pro bezpečnost

### Co projekt nabízí:
✅ Procházení katalogů kol (kategorie: Downhill, Trail, Enduro, Gravel, E-Bikes...)  
✅ Nákupní košík s možností změny množství  
✅ Třístupňový nákupní proces (košík → informace → potvrzení)  
✅ Správa kategoriálních produktů a jejich parametrů  
✅ Doporučená kola na domovské stránce (random funkce)  
✅ Vyhledávání produktů  
✅ Správa objednávek s dopravou a platbou  

---

## 🚀 Jak spustit projekt

### Instalace a příprava:

1. **Klonovat/Otevřít projekt:**
   ```bash
   cd /workspaces/projektT2A
   ```

2. **Inicializovat databázi** (musí se udělat jednou):
   ```bash
   php database/init.php
   ```
   Tento příkaz:
   - Vytvoří nový soubor `database/eshop.db`
   - Vytvoří všechny tabulky (kategorie, produkty, objednávky apod.)
   - Naplní DB vzorovými daty (20+ modelů kol)

3. **Spustit lokální server PHP:**
   ```bash
   php -S localhost:8000
   ```

4. **Otevřít v prohlížeči:**
   ```
   http://localhost:8000
   ```

5. **Zastavit server:**
   ```bash
   Ctrl + C
   ```

### Souborová struktura po inicializaci:
```
projektT2A/
├── database/
│   ├── init.php           ← Inicializační skript
│   └── eshop.db           ← SQLite databáze (vytvoří se automaticky)
├── src/
│   ├── bootstrap.php      ← Načítač všech tříd
│   ├── Database.php       ← Singleton připojení k DB
│   ├── Cart.php           ← Správa nákupního košíku
│   ├── Validator.php      ← Validace formulářů
│   ├── Repository/        ← Třídy pro správu dat
│   └── DTO/               ← Data Transfer Objects
├── partials/              ← Součásti stránky (header, footer, navigace)
├── assets/                ← CSS, JavaScript, obrázky
├── index.php              ← Domovská stránka
├── produkty.php           ← Katalog produktů
├── kosik-krok1.php        ← Nákupní proces
└── ... další PHP stránky
```

---

## 🏗️ Architektura projektu

### Vrstvená architektura:

```
┌─────────────────────────────────────┐
│   PHP Stránky (.php)                │
│   (index.php, produkty.php atd.)    │
└────────────────┬────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│   Business Logic                    │
│   - Cart.php                        │
│   - Validator.php                   │
└────────────────┬────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│   Repository Layer                  │
│   - ProductRepository               │
│   - CategoryRepository              │
│   - OrderRepository atd.            │
└────────────────┬────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│   Data Layer                        │
│   - Database (PDO connection)       │
│   - DTO objekty                     │
└────────────────┬────────────────────┘
                 ↓
┌─────────────────────────────────────┐
│   SQLite Database (eshop.db)        │
└─────────────────────────────────────┘
```

### Bootstrap systém:

Každá PHP stránka začíná řádkem:
```php
require_once __DIR__ . '/src/bootstrap.php';
```

Bootstrap automaticky:
1. Spustí session (`session_start()`)
2. Vytvoří CSRF token pro bezpečnost
3. Načte všechny třídy (Database, DTO, Repository, Cart, Validator)

Díky tomu máte všechny třídy dostupné hned.

---

## 💡 Klíčové funkce

### 1. **getFeatured() - Doporučená kola**

**Umístění:** `src/Repository/ProductRepository.php`

Tato metoda vrací doporučená kola (featured = 1) s možností random zobrazení.

```php
public function getFeatured(int $limit = 8, bool $random = false): array {
    $order = $random ? 'RANDOM()' : 'p.created_at DESC';
    $stmt = $this->db->prepare(self::BASE_SELECT . "
        WHERE p.featured = 1
        ORDER BY $order
        LIMIT :limit
    ");
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return array_map(fn($row) => ProductDTO::fromRow($row), $stmt->fetchAll());
}
```

**Jak funguje:**
- `$limit`: Kolik produktů vrátit (výchozí 8)
- `$random = true`: Vrátí produkty v náhodném pořadí pomocí SQL `RANDOM()`
- `$random = false`: Vrátí nejnovější produkty (seřazeno podle `created_at DESC`)

**Příklad z `index.php`:**
```php
// Načteme 3 doporučená kola v náhodném pořadí
$featuredProducts = $productRepo->getFeatured(limit: 3, random: true);
```

**Výhody random funkce:**
✅ Každý refresh stránky zobrazí jiná doporučená kola  
✅ Zvýší viditelnost všech produktů  
✅ Lepší UX - uživatel se neomrzí stejným obsahem  

---

### 2. **Cart - Nákupní košík**

**Umístění:** `src/Cart.php`

Správuje obsah nákupního košíku v session (nedotýká se databáze).

#### Hlavní metody:

```php
// Přidá produkt do košíku
$cart->add(
    productId: 1,
    productName: 'Canyon Spectral',
    unitPrice: 2599,
    image: '/images/canyon-spectral.jpg',
    quantity: 1,
    variant: 'L'  // Velikost, barva atd.
);

// Aktualizuje množství
$cart->updateQuantity(productId: 1, quantity: 3, variant: 'L');

// Odebere produkt
$cart->remove(productId: 1, variant: 'L');

// Vyprázdní celý košík
$cart->clear();

// Vrátí počet položek v košíku
$count = $cart->getTotalQuantity();

// Vrátí celkový obsah košíku
$items = $cart->getItems();

// Vrátí celkovou cenu
$total = $cart->getTotalPrice();
```

**Příklad - přidání do košíku (z `index.php`):**
```php
if ($_POST['add_to_cart'] ?? false) {
    $product = $productRepo->getById((int)$_POST['product_id']);
    if ($product) {
        $cart->add(
            productId: $product->id,
            productName: $product->name,
            unitPrice: $product->price,
            image: $product->image
        );
        header('Location: pridano-do-kosiku.php');
    }
}
```

---

### 3. **ProductRepository - Správa produktů**

**Umístění:** `src/Repository/ProductRepository.php`

Třída pro načítání produktů z databáze.

#### Hlavní metody:

```php
$productRepo = new ProductRepository();

// Vrátí všechny produkty
$allProducts = $productRepo->getAll();

// Vrátí produkt podle ID
$product = $productRepo->getById(5);

// Vrátí produkt podle slug (URL-friendly jméno)
$product = $productRepo->getBySlug('canyon-spectral');

// Vrátí doporučená kola (viz výše)
$featured = $productRepo->getFeatured(limit: 3, random: true);

// Vyhledá produkty podle názvu/popisu
$results = $productRepo->search('Trek');

// Vrátí produkty v konkrétní kategorii
$categoryProducts = $productRepo->getByCategory(categoryId: 2, limit: 20);
```

---

### 4. **Nákupní proces (3 kroky)**

**Krok 1:** `kosik-krok1.php` - Přehled košíku, možnost změny množství  
**Krok 2:** `kosik-krok2.php` - Vyplnění zákaznických údajů a volba dopravy/platby  
**Krok 3:** `kosik-krok3.php` - Finální potvrzení objednávky  

Po potvrzení se vytvoří řádek v tabulce `orders` a `order_items`.

---

## 📊 Struktura databáze

### Tabulka `categories` - Kategorie kol
```sql
CREATE TABLE categories (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,           -- "Downhill", "Trail", "Gravel"
    slug TEXT NOT NULL UNIQUE,    -- "downhill", "trail", "gravel"
    image TEXT NOT NULL,          -- Obrázek kategorie
    description TEXT              -- Popis kategorie
)
```

### Tabulka `products` - Produkty (kola)
```sql
CREATE TABLE products (
    id INTEGER PRIMARY KEY,
    category_id INTEGER NOT NULL,          -- Odkaz na kategorii
    name TEXT NOT NULL,                    -- "Canyon Spectral 8"
    slug TEXT NOT NULL UNIQUE,             -- "canyon-spectral-8"
    price REAL NOT NULL,                   -- 2599.00
    original_price REAL,                   -- Sleva před
    description TEXT,                      -- Technické parametry
    image TEXT NOT NULL,                   -- Hlavní obrázek
    featured INTEGER NOT NULL DEFAULT 0,   -- 1 = doporučené, 0 = ne
    created_at TEXT                        -- Datum přidání
)
```

### Tabulka `product_images` - Dodatečné obrázky
```sql
CREATE TABLE product_images (
    id INTEGER PRIMARY KEY,
    product_id INTEGER NOT NULL,  -- Odkaz na produkt
    image TEXT NOT NULL,          -- Cesta k obrázku
    sort_order INTEGER            -- Pořadí zobrazení
)
```

### Tabulka `orders` - Objednávky
```sql
CREATE TABLE orders (
    id INTEGER PRIMARY KEY,
    customer_id INTEGER NOT NULL,        -- Zákazník
    shipping_method_id INTEGER NOT NULL, -- Způsob dopravy
    payment_method_id INTEGER NOT NULL,  -- Způsob platby
    total_price REAL NOT NULL,           -- Celková cena
    status TEXT DEFAULT 'pending',       -- pending, paid, shipped, delivered
    created_at TEXT                      -- Kdy byla objednána
)
```

### Tabulka `order_items` - Položky v objednávce
```sql
CREATE TABLE order_items (
    id INTEGER PRIMARY KEY,
    order_id INTEGER NOT NULL,    -- Odkaz na objednávku
    product_id INTEGER NOT NULL,  -- Které kolo
    quantity INTEGER NOT NULL,    -- Kolik kusů
    unit_price REAL NOT NULL,     -- Cena za kus v té chvíli
    variant TEXT DEFAULT ''       -- Velikost, barva atd.
)
```

### Tabulka `shipping_methods` - Dopravy
```sql
CREATE TABLE shipping_methods (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,     -- "Celorepublikový rozvoz"
    price REAL NOT NULL,    -- Cena dopravy
    delivery_days INTEGER   -- Počet dní na doručení
)
```

### Tabulka `payment_methods` - Způsoby platby
```sql
CREATE TABLE payment_methods (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,     -- "Platba kartou", "Bankovní převod"
    fee REAL DEFAULT 0      -- Poplatek za platbu
)
```

---

## 💻 Příklady použití

### Příklad 1: Zobrazení doporučených produktů na domovské stránce

```php
<?php
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();

// Načteme 3 random doporučená kola
$featuredProducts = $productRepo->getFeatured(limit: 3, random: true);

foreach ($featuredProducts as $product) {
    echo "Kolo: {$product->name}";
    echo "Cena: {$product->price} Kč";
    echo "Obrázek: <img src='{$product->image}' />";
}
?>
```

### Příklad 2: Přidání produktu do košíku

```php
<?php
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();
$productRepo = new ProductRepository();

// Uživatel klikl na "Přidat do košíku"
if ($_POST['add_to_cart'] ?? false) {
    $product = $productRepo->getById((int)$_POST['product_id']);
    
    if ($product) {
        $cart->add(
            productId: $product->id,
            productName: $product->name,
            unitPrice: $product->price,
            image: $product->image,
            quantity: (int)($_POST['quantity'] ?? 1),
            variant: $_POST['variant'] ?? ''
        );
        
        // Chceme zobrazit stránku "Přidáno do košíku"
        $_SESSION['last_added_product'] = [
            'productName' => $product->name,
            'unitPrice' => $product->price,
            'image' => $product->image,
        ];
        
        header('Location: pridano-do-kosiku.php');
        exit;
    }
}
?>
```

### Příklad 3: Vyhledávání produktů

```php
<?php
require_once __DIR__ . '/src/bootstrap.php';

$productRepo = new ProductRepository();

// Hledáme "trek"
$searchResults = $productRepo->search('trek');

echo "Nalezeno: " . count($searchResults) . " produktů\n";

foreach ($searchResults as $product) {
    echo "- {$product->name} ({$product->category_name}): {$product->price} Kč\n";
}
?>
```

### Příklad 4: Správa košíku

```php
<?php
require_once __DIR__ . '/src/bootstrap.php';

$cart = new Cart();

// Přidání 2 kusů
$cart->add(productId: 1, productName: 'Trek Fuel EX', unitPrice: 3299, image: '...', quantity: 2);

// Zobrazit položky
foreach ($cart->getItems() as $item) {
    echo "{$item['product_name']}: {$item['quantity']}x";
}

// Změnit množství
$cart->updateQuantity(productId: 1, quantity: 5);

// Celková cena
echo "Celkem: " . $cart->getTotalPrice() . " Kč";

// Smazat produkt
$cart->remove(productId: 1);
?>
```

---

## 🔒 Bezpečnost

Projekt obsahuje **CSRF ochranu**:

```php
// V bootstrap.php se automaticky vytvoří CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// V HTML formulářích:
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

// V PHP skriptech se token ověřuje:
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Neplatný bezpečnostní token.');
}
```

---

## 🧪 Testování

### Inicializace DB a vzorovými daty:
```bash
php database/init.php
```

### Spuštění serveru:
```bash
php -S localhost:8000
```

### Testování nákupního procesu:
1. Navštiv http://localhost:8000
2. Procházej kategorie (produkty.php)
3. Přidej kola do košíku
4. Projdi všechny 3 kroky nákupu
5. Potvrď objednávku

### Kontrola databáze:
```bash
sqlite3 database/eshop.db
> SELECT * FROM products;
> SELECT * FROM orders;
```

---

## 📝 Klíčové soubory

| Soubor | Účel |
|--------|------|
| `src/bootstrap.php` | Inicializace a načtení všech tříd |
| `src/Database.php` | Singleton pro DB připojení |
| `src/Cart.php` | Správa nákupního košíku |
| `src/Repository/ProductRepository.php` | Načítání produktů |
| `src/Repository/CategoryRepository.php` | Načítání kategorií |
| `src/Repository/OrderRepository.php` | Správa objednávek |
| `src/DTO/*` | Data Transfer Objects (data modely) |
| `database/init.php` | Vytvoření DB a vzorovými daty |
| `index.php` | Domovská stránka |
| `produkty.php` | Katalog produktů |
| `kosik-krok*.php` | Nákupní proces |

---

## 🎨 Frontend

Projekt používá **čistý HTML/CSS/JavaScript** (bez frameworku):
- `assets/base.css` - Základní styly
- `assets/layout.css` - Layout
- `assets/components.css` - Komponenty (tlačítka, formuláře)
- `assets/script.js` - JavaScript funkcionality
- `assets/responzive.css` - Responsivní design

---

## 📞 Kontakt a podpora

Projekt byl vytvořen jako cvičení v moderním PHP vývoji s důrazem na:
- Clean Code
- Object-Oriented Programming
- Repository Pattern
- CSRF Protection
- Type Hinting (PHP 8.1+)

---

**Poslední aktualizace:** 2026-06-10  
**Verze:** 1.0
