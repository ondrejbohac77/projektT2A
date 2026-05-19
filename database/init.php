<?php

declare(strict_types=1);

/**
 * Inicializace databáze – vytvoří tabulky a naplní vzorovými daty.
 *
 * Spuštění: php projekt/database/init.php
 *
 * POZOR: Smaže existující databázi a vytvoří novou!
 */

$dbPath = __DIR__ . '/eshop.db';

// Smazat existující databázi
if (file_exists($dbPath)) {
    if (@unlink($dbPath)) {
        echo "Stará databáze smazána.\n";
    } else {
        echo "Varování: Soubor databáze je uzamčen. Pokouším se vyčistit tabulky ručně.\n";
    }
}

$db = new PDO('sqlite:' . $dbPath, options: [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$db->exec('PRAGMA journal_mode = WAL');
$db->exec('PRAGMA foreign_keys = ON');

// Smazání tabulek, pokud existují (pro případ, že unlink selhal)
$db->exec('DROP TABLE IF EXISTS order_items');
$db->exec('DROP TABLE IF EXISTS orders');
$db->exec('DROP TABLE IF EXISTS product_parameters');
$db->exec('DROP TABLE IF EXISTS product_images');
$db->exec('DROP TABLE IF EXISTS products');
$db->exec('DROP TABLE IF EXISTS customers');
$db->exec('DROP TABLE IF EXISTS shipping_methods');
$db->exec('DROP TABLE IF EXISTS payment_methods');
$db->exec('DROP TABLE IF EXISTS categories');

// ============================================================
// Vytvoření tabulek
// ============================================================

$db->exec('
    CREATE TABLE categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        image TEXT NOT NULL DEFAULT "",
        description TEXT NOT NULL DEFAULT ""
    )
');

$db->exec('
    CREATE TABLE products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        price REAL NOT NULL,
        original_price REAL,
        description TEXT NOT NULL DEFAULT "",
        image TEXT NOT NULL DEFAULT "",
        featured INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )
');

$db->exec('
    CREATE TABLE product_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        image TEXT NOT NULL,
        sort_order INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )
');

$db->exec('
    CREATE TABLE product_parameters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        value TEXT NOT NULL,
        type TEXT NOT NULL DEFAULT "info",
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )
');

$db->exec('
    CREATE TABLE customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL DEFAULT "",
        street TEXT NOT NULL DEFAULT "",
        city TEXT NOT NULL DEFAULT "",
        zip TEXT NOT NULL DEFAULT "",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
');

$db->exec('
    CREATE TABLE shipping_methods (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL DEFAULT 0,
        delivery_days TEXT NOT NULL DEFAULT ""
    )
');

$db->exec('
    CREATE TABLE payment_methods (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL DEFAULT 0
    )
');

$db->exec('
    CREATE TABLE orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id INTEGER NOT NULL,
        shipping_method_id INTEGER NOT NULL,
        payment_method_id INTEGER NOT NULL,
        shipping_price REAL NOT NULL DEFAULT 0,
        payment_price REAL NOT NULL DEFAULT 0,
        note TEXT NOT NULL DEFAULT "",
        total_price REAL NOT NULL,
        status TEXT NOT NULL DEFAULT "new",
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id),
        FOREIGN KEY (shipping_method_id) REFERENCES shipping_methods(id),
        FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
    )
');

$db->exec('
    CREATE TABLE order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        product_name TEXT NOT NULL,
        variant TEXT NOT NULL DEFAULT "",
        quantity INTEGER NOT NULL,
        unit_price REAL NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    )
');

echo "Tabulky vytvořeny.\n";

// ============================================================
// Vzorová data – téma: Sportovní e-shop
// ============================================================

// Kategorie
$categories = [
	['Trail', 'trail', 'assets/images/Trek_Fuel_EX.png', 'Kola pro každodenní jízdu na trailech.'],
	['Downhill', 'downhill', 'assets/images/Santa-Cruz-V10.8.png', 'Nekompromisní stroje pro sjezd.'],
	['Enduro', 'enduro', 'assets/images/Speacialized_S-works_Enduro_png.jpg', 'Kola pro nejtěžší terény.'],
	['Dirt', 'dirt', 'assets/images/Dirt_jumper _YT.png', 'Pro skoky a pumptrack.'],
	['Gravel', 'gravel', 'assets/images/canyon-grizl-3.png', 'Rychlá kola na štěrk i asfalt.'],
	['E-Bike', 'ebikes', 'assets/images/Mondraker_crusher.png', 'Elektrický pohon pro delší jízdy.'],
];

$catStmt = $db->prepare('INSERT INTO categories (name, slug, image, description) VALUES (?, ?, ?, ?)');
foreach ($categories as $cat) {
	$catStmt->execute($cat);
}

echo "Kategorie vloženy.\n";

// Produkty
$products = [
	[3, 'Santa Cruz Bronson', 'santa-cruz-bronson', 180000, 200000, 'Celoodpružené trailové/enduro kolo Santa Cruz Bronson S zvládne vše. Díky 160 mm přednímu a 150 mm zadnímu odpružení, 1×12 pohonu SRAM GX Eagle, hydraulickým kotoučovým brzdám a progresivní geometrii s mullet koly je Bronson S vysoce výkonné a úžasně všestranné MTB kolo, které tě nezklame na technických trailech ani při strmější jízdě.', '/assets/images/Santa_Cruz_Bronson_2025.png', 1],
	[2, 'Mondraker Summun', 'mondraker-summun', 400000, NULL, 'Celoodpružené sjezdové kolo Mondraker Summum RR Mullet zvládne vše, co nejtvrdší downhill vyžaduje. Díky ~200 mm přednímu a ~200 mm zadnímu odpružení, robustnímu rámu s Zero Suspension, hydraulickým kotoučovým brzdám Shimano Saint a prvotřídní geometrii je Summum RR Mullet extrémně výkonné a připravené na ty nejtěžší sjezdy bez kompromisů.', '/assets/images/Mondraker_downhill.png', 1],
	[1, 'Trek Fuel EX', 'trek-fuel-ex', 69000, 95000, 'Celoodpružené trailové kolo Fuel EX zvládne vše. Díky 150mm přednímu a 140mm zadnímu odpružení na traily, pohonu Shimano 1x12, teleskopické sedlovce na snížení sedla při sjezdech a hydraulickým kotoučovým brzdám je Fuel EX vysoce výkonné a úžasně všestranné MTB kolo za výbornou cenu.', '/assets/images/Trek_Fuel_EX.png', 1],
	[3, 'Specialized S-Works Enduro', 'specialized-s-works-enduro', 400000, NULL, 'Celoodpružené trail-enduro kolo Specialized S-Works Enduro zvládne vše. Díky 170 mm přednímu a 170 mm zadnímu odpružení, vysoce výkonnému 12-rychlostnímu pohonu SRAM XX1 Eagle AXS, hydraulickým kotoučovým brzdám a špičkové FOX Factory sadě je Enduro extrémně výkonné a úžasně všestranné MTB kolo, připravené na technické výjezdy i agresivní sjezdy.', '/assets/images/Speacialized_S-works_Enduro_png.jpg', 0],
	[2, 'Santa Cruz V10.8', 'santa-cruz-v10-8', 299999, NULL, 'Celoodpružené sjezdové kolo Santa Cruz V10 8 CC zvládne vše, co nejrychlejší downhill vyžaduje. Díky 200 mm přednímu a 208 mm zadnímu odpružení, pevné karbonové konstrukci, hydraulickým kotoučovým brzdám a prvotřídnímu osazení je V10 extrémně výkonné, stabilní a připravené na ty nejtěžší sjezdy a bike park dny bez kompromisů.', '/assets/images/Santa-Cruz-V10.8.png', 1],
	[1, 'Canyon Spectral 8', 'canyon-spectral-8', 70000, 101199, 'Celoodpružené trailové kolo Canyon Spectral CF 8 zvládne vše. Díky 150mm přednímu a 140mm zadnímu odpružení na traily, pohonu Shimano 1×12, teleskopické sedlovce pro snadné snížení sedla ve sjezdech a hydraulickým kotoučovým brzdám je Spectral CF 8 vysoce výkonné a úžasně všestranné MTB kolo za skvělou cenu.', '/assets/images/canyon-spectral-2025.png', 0],
	[4, 'Propain Trickshot 2', 'propain-trickshot-2', 27000, 35000, 'Dirt jump kolo Propain Trickshot 2 zvládne vše, co na pumptracku i dirtu chceš. Díky 100 mm přednímu odpružení, lehkému a pevného hliníkovému rámu, 26″ kolům a hydraulickým kotoučovým brzdám je Trickshot 2 extrémně zábavné, svižné a skvěle ovladatelné kolo, které ti dodá jistotu při skocích, trikách i flow sessions.', '/assets/images/PROPAIN-Trickshot.png', 0],
	[4, 'YT DirtLove Core 3', 'yt-dirtlove-core-3', 41209, NULL, 'Dirt jump kolo Dirtlove CORE 3 AL zvládne vše, co na pumptracku i dirtu chceš. Díky 100 mm přednímu odpružení, pevnému hliníkovému rámu, 26″ kolům, single-speed pohonu a hydraulickým kotoučovým brzdám je Dirtlove CORE 3 AL extrémně zábavné, svižné a skvěle ovladatelné kolo, které ti dodá jistotu při skocích, trikách i flow session.', '/assets/images/Dirt_jumper _YT.png', 1],
	[4, 'Marin Alcatraz 2', 'marin-alcatraz-2', 30899, NULL, 'Dirt jump kolo Marin Alcatraz 2 zvládne vše, co na dirtu a pumptracku chceš. Díky 100 mm přednímu odpružení, pevnému hliníkovému rámu Series 3, 26″ kolům, single-speed převodu a hydraulickým kotoučovým brzdám Shimano MT201 je Alcatraz 2 extrémně zábavné, agilní a skvěle ovladatelné kolo, které ti dodá jistotu při skocích, trikách i flow session.', '/assets/images/Marin-Bikes-dirt.png', 0],
	[5, 'Mondraker Arid', 'mondraker-arid', 154999, NULL, 'Gravel bike Mondraker Arid zvládne vše, co chceš objevit mimo asfalt. Díky lehké a tuhé karbonové/hliníkové konstrukci, progresivní gravel geometrii inspirované MTB, široké kompatibilitě plášťů, internímu úložnému systému Carry-On a možnostem osazení moderními 1× převody je Arid vysoce výkonný a úžasně všestranný gravel bike, který tě provede dlouhými výlety, rychlými úseky i těmi nejdrsnějšími štěrkovými cestami.', '/assets/images/mondraker-arid-carbon.png', 0],
	[5, 'Giant Revolt', 'giant-revolt', 144499, 160000, 'Gravel kolo Giant Revolt zvládne vše, co off-road jízda chce. Díky moderní geometrii s flip-chipem pro nastavení rozvoru, komfortní konstrukci s D-Fuse sedlovkou, velkému prostoru pro pláště až 53 mm a schopnosti jet stejně dobře po silnici i po štěrku je Revolt vysoce výkonné a úžasně všestranné gravel kolo pro dobrodružství i každodenní výlety.', '/assets/images/Giant-Revolt-Advanced-2022.png', 0],
	[5, 'Canyon Grizl 9', 'canyon-grizl-9', 176999, NULL, 'Gravel kolo Canyon Grizl CF 9 w/ ECLIPS zvládne vše, co dobrodružství mimo asfalt chce. Díky lehké karbonové konstrukci, technologii ECLIPS s integrovaným dynamem a světly, elektronickému řazení SRAM RED XPLR AXS a karbonovým gravel kolům DT Swiss je Grizl CF 9 vysoce výkonné a úžasně všestranné gravel kolo připravené na dlouhé výlety, bikepacking i technické úseky.', '/assets/images/canyon-grizl-3.png', 0],
	[6, 'Mondraker Crusher RR', 'mondraker-crusher-rr', 222499, 250000, 'Elektrické trail/enduro kolo Mondraker Crusher RR zvládne vše. Díky 150 mm zadnímu zdvihu, silnému Shimano STEPS EP801 motoru s 720 Wh baterií, hydraulickým kotoučovým brzdám, přesnému 12 rychlostnímu Di2 přehazování a progresivní e-MTB geometrii je Crusher RR vysoce výkonné a úžasně všestranné e-MTB kolo, které zvládne technické sjezdy i dlouhé výjezdy bez kompromisů.', '/assets/images/Mondraker_crusher.png', 1],
	[6, 'Pivot Shuttle LT', 'pivot-shuttle-lt', 200000, NULL, 'Elektrické enduro kolo Pivot Shuttle LT zvládne vše, co trail i all-mountain chce. Díky 160 mm zadnímu a 170 mm přednímu zdvihu, výkonnému Bosch e-motoru s 750 Wh baterií, hydraulickým kotoučovým brzdám, progresivní geometrii a špičkovým komponentům je Shuttle LT extrémně výkonný a úžasně všestranný e-MTB, připravený na dlouhé výjezdy i agresivní sjezdy bez kompromisů.', '/assets/images/Pivot_Shuttle_2025_E-MTB.png', 0],
	[6, 'Canyon StriveON', 'canyon-striveon', 180000, NULL, 'Elektrické enduro kolo Canyon Strive:ON CFR LTD zvládne vše, co náročné trailové ježdění chce. Díky výkonnému Bosch CX Race motoru, progresivní karbonové konstrukci, hydraulickým kotoučovým brzdám, mullet kolům a špičkovému odpružení je Strive:ON CFR LTD extrémně výkonné a úžasně všestranné e-MTB kolo pro agresivní sjezdy i technické výjezdy.', '/assets/images/2023-Canyon-StriveON-CFR-eMTB.png', 0],
	[1, 'Mondraker Foxy', 'mondraker-foxy', 87499, NULL, 'Celoodpružené trailové kolo Mondraker Foxy Carbon R zvládne vše. Díky 160 mm přednímu a 150 mm zadnímu odpružení, pohonu SRAM 1×12, teleskopické sedlovce pro snadné snížení sedla ve sjezdech a hydraulickým kotoučovým brzdám je Foxy Carbon R vysoce výkonné a úžasně všestranné MTB kolo, které tě nezklame ani na náročných tratích.', '/assets/images/2023-Mondraker-Foxy-RR.png', 0],
	[2, 'Propain Rage 3', 'propain-rage-3', 184725, 200000, 'Celoodpružené sjezdové kolo Propain Rage 3 R CF zvládne vše, co nejtvrdší downhill vyžaduje. Díky 200 mm přednímu a 215 mm zadnímu odpružení, robustnímu carbonovému rámu s PRO10 systémem, nastavitelné geometrii a hydraulickým kotoučovým brzdám je Rage 3 R CF extrémně výkonné, stabilní a připravené na ty nejtěžší sjezdy a bike park dny bez kompromisů.', '/assets/images/PROPAIN-Rage-3-R-CF.png', 0],
	[3, 'Kona Process X CR', 'kona-process-x-cr', 84850, NULL, 'Celoodpružené enduro kolo Kona Process X CR zvládne vše. Díky 170 mm přednímu a 162 mm zadnímu odpružení, 12 rychlostnímu pohonu Shimano, teleskopické sedlovce a hydraulickým kotoučovým brzdám je Process X CR vysoce výkonné a úžasně všestranné MTB kolo, které tě nezklame ani na technických sjezdech a náročných trasách.', 'assets/images/KonaProcess-X.png', 0],
];

$prodStmt = $db->prepare('
    INSERT INTO products (category_id, name, slug, price, original_price, description, image, featured)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
');

foreach ($products as $prod) {
	$prodStmt->execute($prod);
}

echo "Produkty vloženy (" . count($products) . ").\n";

// Obrázky produktů (galerie) – ukázkově pro pár produktů
$images = [
	[1, '/assets/images/Santa_Cruz_Bronson_2025.png', 1],
	[1, '/assets/images/Santa_Cruz_Bronson_2025.png', 2],
	[2, '/assets/images/Mondraker_downhill.png', 1],
	[2, '/assets/images/Mondraker_downhill.png', 2],
	[3, '/assets/images/Trek_Fuel_EX.png', 1],
	[3, '/assets/images/Trek_Fuel_EX.png', 2],
	[4, '/assets/images/Speacialized_S-works_Enduro_png.jpg', 1],
	[4, '/assets/images/Speacialized_S-works_Enduro_png.jpg', 2],
	[5, '/assets/images/Santa-Cruz-V10.8.png', 1],
	[5, '/assets/images/Santa-Cruz-V10.8.png', 2],
	[6, '/assets/images/canyon-spectral-2025.png', 1],
	[6, '/assets/images/canyon-spectral-2025.png', 2],
	[7, '/assets/images/PROPAIN-Trickshot.png', 1],
	[7, '/assets/images/PROPAIN-Trickshot.png', 2],
	[8, '/assets/images/Dirt_jumper _YT_2.jpg', 1],
	[8, '/assets/images/Dirt_jumper _YT_3.jpg', 2],
	[9, '/assets/images/Marin-Bikes-dirt.png', 1],
	[9, '/assets/images/Marin-Bikes-dirt.png', 2],
	[10, '/assets/images/mondraker-arid-carbon.png', 1],
	[10, '/assets/images/mondraker-arid-carbon.png', 2],
	[11, '/assets/images/Giant-Revolt-Advanced-2022.png', 1],
	[11, '/assets/images/Giant-Revolt-Advanced-2022.png', 2],
	[12, '/assets/images/canyon-grizl-3.png', 1],
	[12, '/assets/images/canyon-grizl-3.png', 2],
	[13, '/assets/images/Mondraker_crusher.png', 1],
	[13, '/assets/images/Mondraker_crusher.png', 2],
	[14, '/assets/images/Pivot_Shuttle_2025_E-MTB.png', 1],
	[14, '/assets/images/Pivot_Shuttle_2025_E-MTB.png', 2],
	[15, '/assets/images/2023-Canyon-StriveON-CFR-eMTB.png', 1],
	[15, '/assets/images/2023-Canyon-StriveON-CFR-eMTB.png', 2],
	[16, '/assets/images/2023-Mondraker-Foxy-RR.png', 1],
	[16, '/assets/images/2023-Mondraker-Foxy-RR.png', 2], // Ponecháno jako příklad, pokud by se chtěly další obrázky
	[17, '/assets/images/PROPAIN-Rage-3-R-CF_2.webp', 1],
	[17, '/assets/images/PROPAIN-Rage-3-R-CF_3.jpg', 2],
	[18, '/assets/images/KonaProcess-X.png', 1],
	[18, '/assets/images/KonaProcess-X.png', 2],
];

$imgStmt = $db->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)'); // Upraveno pro nové obrázky
foreach ($images as $img) {
	$imgStmt->execute($img);
}
echo "Obrázky vloženy.\n";

// Parametry produktů – type: 'select' = volitelný (dropdown), 'info' = pouze informační
$parameters = [
	// Santa Cruz Bronson (product_id = 1)
	[1, 'Velikost', 'S, M, L, XL', 'select'],
	[1, 'Barva', 'Modrá, Černá', 'select'],
	[1, 'Zdvih přední', '160 mm', 'info'],
	[1, 'Zdvih zadní', '150 mm', 'info'],
	[1, 'Materiál rámu', 'Carbon C', 'info'],

	// Mondraker Summun (product_id = 2)
	[2, 'Velikost', 'S, M, L', 'select'],
	[2, 'Barva', 'Žlutá, Černá', 'select'],
	[2, 'Zdvih přední', '200 mm', 'info'],
	[2, 'Zdvih zadní', '200 mm', 'info'],
	[2, 'Materiál rámu', 'Hliník', 'info'],

	// Trek Fuel EX (product_id = 3)
	[3, 'Velikost', 'S, M, L, XL', 'select'],
	[3, 'Barva', 'Červená, Šedá', 'select'],
	[3, 'Zdvih přední', '150 mm', 'info'],
	[3, 'Zdvih zadní', '140 mm', 'info'],
	[3, 'Materiál rámu', 'Hliník Alpha Platinum', 'info'],

	// Specialized S-Works Enduro (product_id = 4)
	[4, 'Velikost', 'S2, S3, S4, S5', 'select'],
	[4, 'Barva', 'Černá, Stříbrná', 'select'],
	[4, 'Zdvih přední', '170 mm', 'info'],
	[4, 'Zdvih zadní', '170 mm', 'info'],
	[4, 'Materiál rámu', 'Carbon FACT 11m', 'info'],

	// Santa Cruz V10.8 (product_id = 5)
	[5, 'Velikost', 'S, M, L', 'select'],
	[5, 'Barva', 'Modrá, Oranžová', 'select'],
	[5, 'Zdvih přední', '200 mm', 'info'],
	[5, 'Zdvih zadní', '208 mm', 'info'],
	[5, 'Materiál rámu', 'Carbon CC', 'info'],

	// Canyon Spectral 8 (product_id = 6)
	[6, 'Velikost', 'S, M, L, XL', 'select'],
	[6, 'Barva', 'Zelená, Černá', 'select'],
	[6, 'Zdvih přední', '150 mm', 'info'],
	[6, 'Zdvih zadní', '140 mm', 'info'],
	[6, 'Materiál rámu', 'Carbon', 'info'],

	// Propain Trickshot 2 (product_id = 7)
	[7, 'Velikost', 'Regular, Long', 'select'],
	[7, 'Barva', 'Šedá, Černá', 'select'],
	[7, 'Zdvih vidlice', '100 mm', 'info'],
	[7, 'Materiál rámu', 'Hliník', 'info'],

	// YT DirtLove Core 3 (product_id = 8)
	[8, 'Velikost', 'Regular, Long', 'select'],
	[8, 'Barva', 'Černá, Bílá', 'select'],
	[8, 'Zdvih vidlice', '100 mm', 'info'],
	[8, 'Materiál rámu', 'Hliník', 'info'],

	// Marin Alcatraz 2 (product_id = 9)
	[9, 'Velikost', 'Regular', 'select'],
	[9, 'Barva', 'Modrá, Černá', 'select'],
	[9, 'Zdvih vidlice', '100 mm', 'info'],
	[9, 'Materiál rámu', 'Hliník Series 3', 'info'],

	// Mondraker Arid (product_id = 10)
	[10, 'Velikost', 'S, M, L, XL', 'select'],
	[10, 'Barva', 'Zelená, Písková', 'select'],
	[10, 'Materiál rámu', 'Carbon/Hliník', 'info'],
	[10, 'Max. šířka pláště', '50 mm', 'info'],

	// Giant Revolt (product_id = 11)
	[11, 'Velikost', 'S, M, ML, L, XL', 'select'],
	[11, 'Barva', 'Modrá, Šedá', 'select'],
	[11, 'Materiál rámu', 'Hliník ALUXX', 'info'],
	[11, 'Max. šířka pláště', '53 mm', 'info'],

	// Canyon Grizl 9 (product_id = 12)
	[12, 'Velikost', 'XS, S, M, L, XL', 'select'],
	[12, 'Barva', 'Zelená, Černá', 'select'],
	[12, 'Materiál rámu', 'Carbon', 'info'],
	[12, 'Pohon', 'SRAM RED XPLR AXS', 'info'],

	// Mondraker Crusher RR (product_id = 13)
	[13, 'Velikost', 'S, M, L, XL', 'select'],
	[13, 'Barva', 'Černá, Oranžová', 'select'],
	[13, 'Zdvih zadní', '150 mm', 'info'],
	[13, 'Motor', 'Shimano STEPS EP801', 'info'],

	// Pivot Shuttle LT (product_id = 14)
	[14, 'Velikost', 'S, M, L, XL', 'select'],
	[14, 'Barva', 'Modrá, Černá', 'select'],
	[14, 'Zdvih přední', '170 mm', 'info'],
	[14, 'Zdvih zadní', '160 mm', 'info'],
	[14, 'Motor', 'Bosch Performance CX Race', 'info'],

	// Canyon StriveON (product_id = 15)
	[15, 'Velikost', 'S, M, L, XL', 'select'],
	[15, 'Barva', 'Černá, Šedá', 'select'],
	[15, 'Zdvih přední', '170 mm', 'info'],
	[15, 'Zdvih zadní', '160 mm', 'info'],
	[15, 'Motor', 'Bosch CX Race', 'info'],

	// Mondraker Foxy (product_id = 16)
	[16, 'Velikost', 'S, M, L, XL', 'select'],
	[16, 'Barva', 'Černá, Červená', 'select'],
	[16, 'Zdvih přední', '160 mm', 'info'],
	[16, 'Zdvih zadní', '150 mm', 'info'],
	[16, 'Materiál rámu', 'Carbon', 'info'],

	// Propain Rage 3 (product_id = 17)
	[17, 'Velikost', 'S, M, L', 'select'],
	[17, 'Barva', 'Černá, Zelená', 'select'],
	[17, 'Zdvih přední', '200 mm', 'info'],
	[17, 'Zdvih zadní', '215 mm', 'info'],
	[17, 'Materiál rámu', 'Carbon', 'info'],

	// Kona Process X CR (product_id = 18)
	[18, 'Velikost', 'S, M, L, XL', 'select'],
	[18, 'Barva', 'Černá, Zelená', 'select'],
	[18, 'Zdvih přední', '170 mm', 'info'],
	[18, 'Zdvih zadní', '162 mm', 'info'],
	[18, 'Materiál rámu', 'Carbon', 'info'],
];

$paramStmt = $db->prepare('INSERT INTO product_parameters (product_id, name, value, type) VALUES (?, ?, ?, ?)');
foreach ($parameters as $param) {
	$paramStmt->execute($param);
}
echo "Parametry vloženy.\n";

// Způsoby dopravy
$shippingMethods = [
	['Osobní odběr', 0, 'Ihned k vyzvednutí v showroomu Praha'],
	['PPL Kurýr', 0, 'Doručení na adresu (1-2 pracovní dny)'],
	['Zásilkovna', 69, 'Doručení na výdejní místo (2-3 pracovní dny)'],
	['DPD Kurýr', 99, 'Doručení na adresu (1-2 pracovní dny)'],
];

$shipStmt = $db->prepare('INSERT INTO shipping_methods (name, price, delivery_days) VALUES (?, ?, ?)');
foreach ($shippingMethods as $method) {
	$shipStmt->execute($method);
}
echo "Způsoby dopravy vloženy.\n";

// Způsoby platby
$paymentMethods = [
	['Platba kartou online', 0],
	['Bankovním převodem', 0],
	['Dobírkou', 49],
	['Apple Pay / Google Pay', 0],
];

$payStmt = $db->prepare('INSERT INTO payment_methods (name, price) VALUES (?, ?)');
foreach ($paymentMethods as $method) {
	$payStmt->execute($method);
}
echo "Způsoby platby vloženy.\n";

// Vzorový zákazník
$db->exec('
    INSERT INTO customers (first_name, last_name, email, phone, street, city, zip) VALUES ("Jan", "Novák", "jan.novak@email.cz", "+420 777 123 456", "Cyklistická 123", "Praha", "11000")
');

echo "Vzorový zákazník vytvořen.\n";

// Vzorová objednávka (PPL Kurýr = id 2, cena 0 Kč; Platba kartou online = id 1, cena 0 Kč)
// Celková cena: 180000 (Bronson) + 0 (doprava) + 0 (platba) = 180000
$db->exec('
    INSERT INTO orders (customer_id, shipping_method_id, payment_method_id, shipping_price, payment_price, note, total_price, status)
    VALUES (1, 2, 1, 0, 0, "Prosím zabalit jako dárek.", 180000, "new")
');

$db->exec('
    INSERT INTO order_items (order_id, product_id, product_name, variant, quantity, unit_price) VALUES (1, 1, "Santa Cruz Bronson", "Velikost: M, Barva: Modrá", 1, 180000)
');

// Indexy pro rychlejší vyhledávání
$db->exec('CREATE INDEX idx_products_category ON products(category_id)');
$db->exec('CREATE INDEX idx_products_slug ON products(slug)');
$db->exec('CREATE INDEX idx_products_featured ON products(featured)');
$db->exec('CREATE INDEX idx_categories_slug ON categories(slug)');
$db->exec('CREATE INDEX idx_order_items_order ON order_items(order_id)');
$db->exec('CREATE INDEX idx_product_images_product ON product_images(product_id)');
$db->exec('CREATE INDEX idx_product_params_product ON product_parameters(product_id)');

echo "\nDatabáze úspěšně inicializována!\n";
echo "Soubor: $dbPath\n";
