<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

try {
    $db = Database::getConnection();
    echo "Inicializace databáze...\n";

    // Drop tables if they exist
    $db->exec("DROP TABLE IF EXISTS cart");
    $db->exec("DROP TABLE IF EXISTS order_items");
    $db->exec("DROP TABLE IF EXISTS orders");
    $db->exec("DROP TABLE IF EXISTS customers");
    $db->exec("DROP TABLE IF EXISTS payment_methods");
    $db->exec("DROP TABLE IF EXISTS shipping_methods");
    $db->exec("DROP TABLE IF EXISTS product_parameters");
    $db->exec("DROP TABLE IF EXISTS product_images");
    $db->exec("DROP TABLE IF EXISTS products");
    $db->exec("DROP TABLE IF EXISTS categories");

    // 1. Categories Table
    $db->exec("
        CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            image TEXT,
            description TEXT
        )
    ");

    // 2. Products Table
    $db->exec("
        CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            price REAL NOT NULL,
            description TEXT,
            image TEXT,
            is_featured INTEGER NOT NULL DEFAULT 0,
            category_id INTEGER NOT NULL,
            discount_percent INTEGER NOT NULL DEFAULT 0,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
        )
    ");

    // 3. Product Gallery Images
    $db->exec("
        CREATE TABLE product_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            image TEXT NOT NULL,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
    ");

    // 4. Product Parameters Table
    $db->exec("
        CREATE TABLE product_parameters (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            value TEXT NOT NULL,
            type TEXT NOT NULL, -- 'select' or 'info'
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
    ");

    // 5. Shipping Methods Table
    $db->exec("
        CREATE TABLE shipping_methods (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price REAL NOT NULL,
            delivery_days TEXT NOT NULL
        )
    ");

    // 6. Payment Methods Table
    $db->exec("
        CREATE TABLE payment_methods (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price REAL NOT NULL
        )
    ");

    // 7. Customers Table
    $db->exec("
        CREATE TABLE customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT NOT NULL,
            street TEXT NOT NULL,
            city TEXT NOT NULL,
            zip TEXT NOT NULL
        )
    ");

    // 8. Orders Table
    $db->exec("
        CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_id INTEGER NOT NULL,
            shipping_method_id INTEGER NOT NULL,
            payment_method_id INTEGER NOT NULL,
            note TEXT,
            shipping_price REAL NOT NULL,
            payment_price REAL NOT NULL,
            total_price REAL NOT NULL,
            status TEXT NOT NULL,
            created_at TEXT NOT NULL,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
            FOREIGN KEY (shipping_method_id) REFERENCES shipping_methods(id) ON DELETE RESTRICT,
            FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE RESTRICT
        )
    ");

    // 9. Order Items Table
    $db->exec("
        CREATE TABLE order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            variant TEXT,
            quantity INTEGER NOT NULL,
            unit_price REAL NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
        )
    ");

    // 10. Database Cart Table
    $db->exec("
        CREATE TABLE cart (
            session_id TEXT NOT NULL,
            product_id INTEGER NOT NULL,
            variant TEXT NOT NULL DEFAULT '',
            quantity INTEGER NOT NULL DEFAULT 1,
            PRIMARY KEY (session_id, product_id, variant),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )
    ");

    echo "Tabulky úspěšně vytvořeny.\nVkládám testovací data...\n";

    // Insert Categories
    $categories = [
        [1, 'Zelené čaje', 'zelene-caje', 'assets/images/zeleny-a-jasmin.jpg', 'Tradiční čínské a japonské zelené čaje plné antioxidantů.'],
        [2, 'Černé čaje', 'cerne-caje', 'assets/images/cerny.jpg', 'Silné a plné čaje s vyšším obsahem kofeinu.'],
        [3, 'Ovocné čaje', 'ovocne-caje', 'assets/images/ovocny.jpg', 'Voňavé směsi plné sušeného ovoce a lesních plodů.'],
        [4, 'Bylinné čaje', 'bylinne-caje', 'assets/images/bylinny.jpg', 'Přírodní směsi bylin pro zklidnění, podporu trávení a lepší spánek.'],
        [5, 'Oolongy', 'oolongy', 'assets/images/oolong.jpg', 'Spojení toho nejlepšího ze zelených a černých čajů.'],
        [6, 'Matcha čaje', 'matcha-caje', 'assets/images/matcha.jpg', 'Tradiční mleté japonské čaje nejvyšší kvality.']
    ];

    $stmt = $db->prepare("INSERT INTO categories (id, name, slug, image, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }

    // Insert Products – všechny produkty ze starého webu + varianty pro každou kategorii
    $products = [
        // --- Zelené čaje (category_id = 1) ---
        [1,  'Zelený & Jasmínový čaj',  'zeleny-a-jasminovy-caj',  159.0,
             'Kombinace jemných čajových lístků a omamné vůně jasmínu. Ideální pro lehké nastartování dne a zlepšení soustředění díky kofeinu, který se uvolňuje postupně.',
             'assets/images/zeleny-a-jasmin.jpg', 1, 1, 0],
        [9,  'Zelený čaj sencha',        'zeleny-caj-sencha',        139.0,
             'Japonský sencha je nejoblíbenější zelený čaj na světě. Svěží, travnatá chuť s jemnou hořkostí a vysokým obsahem vitamínu C.',
             'assets/images/zeleny-a-jasmin.jpg', 0, 1, 0],
        [10, 'Zelený čaj gunpowder',     'zeleny-caj-gunpowder',     149.0,
             'Čínský zelený čaj srolovaný do kuliček. Silnější chuť se zemitým podtónem a výraznějším obsahem kofeinu.',
             'assets/images/zeleny-a-jasmin.jpg', 0, 1, 5],

        // --- Černé čaje (category_id = 2) ---
        [3,  'Černý čaj',                'cerny-caj',                179.0,
             'Klasika s vysokým obsahem kofeinu. Silný nálev, který spolehlivě nahradí ranní kávu, prohřeje organismus a dodá energii na celé dopoledne.',
             'assets/images/cerny.jpg', 1, 2, 0],
        [11, 'Darjeeling',               'darjeeling',               189.0,
             'Indická šampionka mezi černými čaji. Lehká a květinová chuť s charakteristickou muskatovou vůní. Sklizeno v oblasti Darjeeling.',
             'assets/images/cerny.jpg', 0, 2, 0],
        [12, 'Earl Grey',                'earl-grey',                169.0,
             'Tradiční anglický černý čaj aromatizovaný bergamotovým olejem. Elegantní citrusová vůně a plná chuť. Skvělý s mlékem.',
             'assets/images/cerny.jpg', 0, 2, 10],

        // --- Ovocné čaje (category_id = 3) ---
        [2,  'Ovocný čaj',               'ovocny-caj',               149.0,
             'Plná chuť lesních plodů a tropického ovoce. Přirozeně bez kofeinu, takže je skvělý pro děti nebo jako osvěžující pití na večer.',
             'assets/images/ovocny.jpg', 1, 3, 10],
        [13, 'Šípkový čaj',              'sipkovy-caj',              129.0,
             'Čistý šípek plný vitamínu C. Přirozeně kyselá, osvěžující chuť – skvělý horký i jako ledový čaj. Sezónní favorit.',
             'assets/images/ovocny.jpg', 0, 3, 0],
        [14, 'Tropický mix',             'tropicky-mix',             159.0,
             'Směs manga, papáji a marakuji. Exotická vůně, sladká chuť bez přidaného cukru. Bez kofeinu, vhodný pro celou rodinu.',
             'assets/images/ovocny.jpg', 0, 3, 0],

        // --- Bylinné čaje (category_id = 4) ---
        [5,  'Bylinný čaj',              'bylinny-caj',              159.0,
             'Směs meduňky, máty a heřmánku. Nejlepší volba pro uklidnění mysli bez kofeinu. Podporuje trávení a klidný spánek.',
             'assets/images/bylinny.jpg', 1, 4, 0],
        [7,  'Wellness čaj',             'wellness-caj',             169.0,
             'Speciálně namíchaná směs pro detoxikaci. Obsahuje bylinky pro rovnováhu organismu. Přirozeně nízký obsah kofeinu.',
             'assets/images/wellness.jpg', 1, 4, 0],
        [8,  'Čaj na hubnutí',           'caj-na-hubnuti',           159.0,
             'Přírodní podpora při spalování tuků. Obsahuje kofein a antioxidanty, které pomáhají tělu efektivněji využívat energii.',
             'assets/images/hubnuti.jpg', 1, 4, 0],

        // --- Oolongy (category_id = 5) ---
        [6,  'Oolong čaj',               'oolong-caj',               189.0,
             'Tradiční čínský „polozelený" čaj. Obsahuje střední množství kofeinu, zrychluje metabolismus a posiluje imunitu.',
             'assets/images/oolong.jpg', 1, 5, 5],
        [15, 'Tie Guan Yin',             'tie-guan-yin',             219.0,
             'Prémiový oolong z provincie Anxi. Jemná orchidejová vůně, hebká chuť a dlouhá příjemná dochuť. Lze louhovat i vícekrát.',
             'assets/images/oolong.jpg', 0, 5, 0],
        [16, 'Phoenix Dancong',          'phoenix-dancong',          239.0,
             'Vzácný oolong z pohoří Fenghuang. Komplexní ovocná a medová chuť s jemným kouřovým podtónem. Pro milovníky čaje.',
             'assets/images/oolong.jpg', 0, 5, 0],

        // --- Matcha čaje (category_id = 6) ---
        [4,  'Matcha čaj',               'matcha-caj',               199.0,
             'Mletý japonský poklad. Obsahuje obrovské množství antioxidantů a dodává stabilní energii díky kofeinu po dobu 4–6 hodin.',
             'assets/images/matcha.jpg', 1, 6, 0],
        [17, 'Matcha ceremonial grade',  'matcha-ceremonial',        299.0,
             'Nejvyšší ceremoniální kvalita matchy z oblasti Uji. Sytě zelená barva, jemná sladkost bez hořkosti. Pro přípravu tradiční čajové ceremonie.',
             'assets/images/matcha.jpg', 0, 6, 0],
        [18, 'Matcha latte mix',         'matcha-latte-mix',         179.0,
             'Speciální směs matchy a sušeného mléka pro rychlou přípravu lahodného matcha latte. Stačí přidat horkou vodu nebo rostlinné mléko.',
             'assets/images/matcha.jpg', 0, 6, 15],
    ];

    $stmt = $db->prepare("INSERT INTO products (id, name, slug, price, description, image, is_featured, category_id, discount_percent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $prod) {
        $stmt->execute($prod);
    }

    // Insert Product Gallery Images
    $images = [
        [1,  1, 'assets/images/zeleny-a-jasmin.jpg'],
        [2,  1, 'assets/images/wellness.jpg'],
        [3,  1, 'assets/images/matcha.jpg'],
        [4,  3, 'assets/images/cerny.jpg'],
        [5,  3, 'assets/images/oolong.jpg'],
        [6,  4, 'assets/images/matcha.jpg'],
        [7,  6, 'assets/images/oolong.jpg'],
        [8,  7, 'assets/images/wellness.jpg'],
        [9,  7, 'assets/images/bylinny.jpg'],
    ];

    $stmt = $db->prepare("INSERT INTO product_images (id, product_id, image) VALUES (?, ?, ?)");
    foreach ($images as $img) {
        $stmt->execute($img);
    }

    // Insert Product Parameters for all products
    $parameters = [
        // === Zelený & Jasmínový čaj (id=1) ===
        [1,  1,  'Země původu',    'Čína (provincie Fujian)',              'info'],
        [2,  1,  'Doba louhování', '2–3 minuty',                           'info'],
        [3,  1,  'Teplota vody',   '75°C – 80°C',                          'info'],
        [4,  1,  'Složení',        'Zelený čaj pravý, květy jasmínu (1%)', 'info'],
        [5,  1,  'Hmotnost balení','100g',                                  'select'],
        [6,  1,  'Hmotnost balení','250g',                                  'select'],
        [7,  1,  'Hmotnost balení','500g',                                  'select'],

        // === Ovocný čaj (id=2) ===
        [8,  2,  'Země původu',    'Česká republika',                      'info'],
        [9,  2,  'Doba louhování', '5–8 minut',                            'info'],
        [10, 2,  'Teplota vody',   '100°C',                                'info'],
        [11, 2,  'Složení',        'Šípky, hibiskus, jablko, lesní plody', 'info'],
        [12, 2,  'Hmotnost balení','100g',                                  'select'],
        [13, 2,  'Hmotnost balení','250g',                                  'select'],

        // === Černý čaj (id=3) ===
        [14, 3,  'Země původu',    'Indie (oblast Assam)',                  'info'],
        [15, 3,  'Doba louhování', '3–5 minut',                            'info'],
        [16, 3,  'Teplota vody',   '95°C',                                  'info'],
        [17, 3,  'Složení',        'Černý čaj Assam TGFOP',                'info'],
        [18, 3,  'Hmotnost balení','100g',                                  'select'],
        [19, 3,  'Hmotnost balení','250g',                                  'select'],
        [20, 3,  'Hmotnost balení','500g',                                  'select'],

        // === Matcha čaj (id=4) ===
        [21, 4,  'Země původu',    'Japonsko (oblast Uji)',                 'info'],
        [22, 4,  'Teplota vody',   '70°C – 80°C',                          'info'],
        [23, 4,  'Způsob přípravy','Šleháním bambusovým metličkou',         'info'],
        [24, 4,  'Hmotnost balení','30g',                                   'select'],
        [25, 4,  'Hmotnost balení','80g',                                   'select'],

        // === Bylinný čaj (id=5) ===
        [26, 5,  'Země původu',    'Česká republika',                      'info'],
        [27, 5,  'Doba louhování', '5–10 minut',                           'info'],
        [28, 5,  'Teplota vody',   '100°C',                                'info'],
        [29, 5,  'Složení',        'Meduňka, máta peprná, heřmánek',       'info'],
        [30, 5,  'Hmotnost balení','50g',                                   'select'],
        [31, 5,  'Hmotnost balení','100g',                                  'select'],

        // === Oolong čaj (id=6) ===
        [32, 6,  'Země původu',    'Čína (provincie Anxi)',                 'info'],
        [33, 6,  'Doba louhování', '3–4 minuty',                           'info'],
        [34, 6,  'Teplota vody',   '85°C',                                  'info'],
        [35, 6,  'Hmotnost balení','100g',                                  'select'],
        [36, 6,  'Hmotnost balení','250g',                                  'select'],

        // === Wellness čaj (id=7) ===
        [37, 7,  'Země původu',    'Česká republika',                      'info'],
        [38, 7,  'Doba louhování', '7–10 minut',                           'info'],
        [39, 7,  'Teplota vody',   '100°C',                                'info'],
        [40, 7,  'Složení',        'Kopřiva, pampeliška, zelený čaj, zázvor','info'],
        [41, 7,  'Hmotnost balení','50g',                                   'select'],
        [42, 7,  'Hmotnost balení','100g',                                  'select'],

        // === Čaj na hubnutí (id=8) ===
        [43, 8,  'Země původu',    'Česká republika',                      'info'],
        [44, 8,  'Doba louhování', '5–7 minut',                            'info'],
        [45, 8,  'Teplota vody',   '90°C',                                  'info'],
        [46, 8,  'Složení',        'Zelený čaj, skořice, zázvor, guarana', 'info'],
        [47, 8,  'Hmotnost balení','50g',                                   'select'],
        [48, 8,  'Hmotnost balení','100g',                                  'select'],

        // === Zelený čaj sencha (id=9) ===
        [49, 9,  'Země původu',    'Japonsko (oblast Shizuoka)',            'info'],
        [50, 9,  'Doba louhování', '1–2 minuty',                           'info'],
        [51, 9,  'Teplota vody',   '70°C – 75°C',                          'info'],
        [52, 9,  'Hmotnost balení','100g',                                  'select'],
        [53, 9,  'Hmotnost balení','250g',                                  'select'],

        // === Zelený čaj gunpowder (id=10) ===
        [54, 10, 'Země původu',    'Čína (provincie Zhejiang)',             'info'],
        [55, 10, 'Doba louhování', '2–3 minuty',                           'info'],
        [56, 10, 'Teplota vody',   '80°C',                                  'info'],
        [57, 10, 'Hmotnost balení','100g',                                  'select'],
        [58, 10, 'Hmotnost balení','250g',                                  'select'],

        // === Darjeeling (id=11) ===
        [59, 11, 'Země původu',    'Indie (oblast Darjeeling)',             'info'],
        [60, 11, 'Doba louhování', '3–4 minuty',                           'info'],
        [61, 11, 'Teplota vody',   '90°C',                                  'info'],
        [62, 11, 'Hmotnost balení','100g',                                  'select'],
        [63, 11, 'Hmotnost balení','250g',                                  'select'],

        // === Earl Grey (id=12) ===
        [64, 12, 'Země původu',    'Srí Lanka + bergamotový olej',         'info'],
        [65, 12, 'Doba louhování', '3–5 minut',                            'info'],
        [66, 12, 'Teplota vody',   '95°C',                                  'info'],
        [67, 12, 'Hmotnost balení','100g',                                  'select'],
        [68, 12, 'Hmotnost balení','250g',                                  'select'],

        // === Šípkový čaj (id=13) ===
        [69, 13, 'Země původu',    'Česká republika',                      'info'],
        [70, 13, 'Doba louhování', '10–15 minut',                          'info'],
        [71, 13, 'Teplota vody',   '100°C',                                'info'],
        [72, 13, 'Složení',        'Šípky sušené celé',                    'info'],
        [73, 13, 'Hmotnost balení','100g',                                  'select'],
        [74, 13, 'Hmotnost balení','250g',                                  'select'],

        // === Tropický mix (id=14) ===
        [75, 14, 'Země původu',    'Česká republika',                      'info'],
        [76, 14, 'Doba louhování', '5–8 minut',                            'info'],
        [77, 14, 'Teplota vody',   '100°C',                                'info'],
        [78, 14, 'Složení',        'Mango, papája, marakuja, ananas',      'info'],
        [79, 14, 'Hmotnost balení','100g',                                  'select'],

        // === Tie Guan Yin (id=15) ===
        [80, 15, 'Země původu',    'Čína (provincie Fujian, Anxi)',         'info'],
        [81, 15, 'Doba louhování', '2–3 minuty',                           'info'],
        [82, 15, 'Teplota vody',   '90°C',                                  'info'],
        [83, 15, 'Počet louhování','4–6×',                                  'info'],
        [84, 15, 'Hmotnost balení','50g',                                   'select'],
        [85, 15, 'Hmotnost balení','100g',                                  'select'],

        // === Phoenix Dancong (id=16) ===
        [86, 16, 'Země původu',    'Čína (pohoří Fenghuang, Guangdong)',   'info'],
        [87, 16, 'Doba louhování', '2–3 minuty',                           'info'],
        [88, 16, 'Teplota vody',   '95°C',                                  'info'],
        [89, 16, 'Počet louhování','5–8×',                                  'info'],
        [90, 16, 'Hmotnost balení','50g',                                   'select'],
        [91, 16, 'Hmotnost balení','100g',                                  'select'],

        // === Matcha ceremonial grade (id=17) ===
        [92, 17, 'Země původu',    'Japonsko (oblast Uji, Kjóto)',          'info'],
        [93, 17, 'Způsob přípravy','Šleháním bamb. metličkou Chasen',       'info'],
        [94, 17, 'Teplota vody',   '70°C – 75°C',                          'info'],
        [95, 17, 'Hmotnost balení','30g',                                   'select'],
        [96, 17, 'Hmotnost balení','50g',                                   'select'],

        // === Matcha latte mix (id=18) ===
        [97, 18, 'Složení',        'Matcha 40%, sušené kokosové mléko',    'info'],
        [98, 18, 'Způsob přípravy','Přidat 200ml horké vody nebo mléka',   'info'],
        [99, 18, 'Hmotnost balení','150g',                                  'select'],
        [100,18, 'Hmotnost balení','300g',                                  'select'],
    ];

    $stmt = $db->prepare("INSERT INTO product_parameters (id, product_id, name, value, type) VALUES (?, ?, ?, ?, ?)");
    foreach ($parameters as $param) {
        $stmt->execute($param);
    }

    // Insert Shipping Methods
    $shipping = [
        [1, '📦 Zásilkovna (Na pobočku)', 69.0, '2–3 pracovní dny'],
        [2, '📯 Česká pošta (Do ruky)', 99.0, '2–3 pracovní dny'],
        [3, '🚚 DPD Kurýr', 129.0, '1–2 pracovní dny']
    ];

    $stmt = $db->prepare("INSERT INTO shipping_methods (id, name, price, delivery_days) VALUES (?, ?, ?, ?)");
    foreach ($shipping as $ship) {
        $stmt->execute($ship);
    }

    // Insert Payment Methods
    $payment = [
        [1, '💳 Kartou online', 0.0],
        [2, '💵 Dobírka při převzetí', 30.0],
        [3, '🏦 Bankovní převod', 0.0]
    ];

    $stmt = $db->prepare("INSERT INTO payment_methods (id, name, price) VALUES (?, ?, ?)");
    foreach ($payment as $pay) {
        $stmt->execute($pay);
    }

    echo "Databáze úspěšně inicializována!\n";
} catch (Exception $e) {
    echo "Chyba při inicializaci: " . $e->getMessage() . "\n";
    exit(1);
}
