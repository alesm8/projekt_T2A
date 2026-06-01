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

    // Insert Products
    $products = [
        [1, 'Zelený & Jasmínový čaj', 'zeleny-a-jasminovy-caj', 159.0, 'Kombinace jemných čajových lístků a omamné vůně jasmínu. Ideální pro lehké nastartování dne.', 'assets/images/zeleny-a-jasmin.jpg', 1, 1, 0],
        [2, 'Ovocný čaj', 'ovocny-caj', 149.0, 'Plná chuť lesních plodů a tropického ovoce. Přirozeně bez kofeinu, takže je skvělý pro děti.', 'assets/images/ovocny.jpg', 1, 3, 10],
        [3, 'Černý čaj', 'cerny-caj', 179.0, 'Klasika s vysokým obsahem kofeinu. Silný nálev, který spolehlivě nahradí ranní kávu.', 'assets/images/cerny.jpg', 1, 2, 0],
        [4, 'Matcha čaj', 'matcha-caj', 199.0, 'Mletý japonský poklad. Obsahuje obrovské množství antioxidantů.', 'assets/images/matcha.jpg', 1, 6, 0],
        [5, 'Bylinný čaj', 'bylinny-caj', 159.0, 'Směs meduňky, máty a heřmánku. Nejlepší volba pro uklidnění mysli.', 'assets/images/bylinny.jpg', 1, 4, 0],
        [6, 'Oolong čaj', 'oolong-caj', 189.0, 'Tradiční čínský polozelený čaj. Zrychluje metabolismus a posiluje imunitu.', 'assets/images/oolong.jpg', 1, 5, 5],
        [7, 'Wellness čaj', 'wellness-caj', 169.0, 'Speciálně namíchaná směs pro detoxikaci. Obsahuje bylinky pro rovnováhu.', 'assets/images/wellness.jpg', 1, 4, 0],
        [8, 'Čaj na hubnutí', 'caj-na-hubnuti', 159.0, 'Přírodní podpora při spalování tuků. Obsahuje kofein a antioxidanty.', 'assets/images/hubnuti.jpg', 1, 4, 0]
    ];

    $stmt = $db->prepare("INSERT INTO products (id, name, slug, price, description, image, is_featured, category_id, discount_percent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $prod) {
        $stmt->execute($prod);
    }

    // Insert Product Gallery Images
    $images = [
        [1, 1, 'assets/images/zeleny-a-jasmin.jpg'],
        [2, 1, 'assets/images/wellness.jpg'],
        [3, 1, 'assets/images/matcha.jpg']
    ];

    $stmt = $db->prepare("INSERT INTO product_images (id, product_id, image) VALUES (?, ?, ?)");
    foreach ($images as $img) {
        $stmt->execute($img);
    }

    // Insert Product Parameters
    $parameters = [
        // Zelený & Jasmínový čaj info
        [1, 1, 'Země původu', 'Čína (provincie Fujian)', 'info'],
        [2, 1, 'Doba louhování', '2–3 minuty', 'info'],
        [3, 1, 'Teplota vody', '75°C - 80°C', 'info'],
        [4, 1, 'Složení', 'Zelený čaj pravý, květy jasmínu (1%)', 'info'],
        // Zelený & Jasmínový čaj select variants
        [5, 1, 'Hmotnost balení', '100g', 'select'],
        [6, 1, 'Hmotnost balení', '250g', 'select'],
        [7, 1, 'Hmotnost balení', '500g', 'select'],

        // Ovocný čaj info
        [8, 2, 'Země původu', 'Česká republika', 'info'],
        [9, 2, 'Doba louhování', '5–8 minut', 'info'],
        [10, 2, 'Teplota vody', '100°C', 'info'],

        // Černý čaj info
        [11, 3, 'Země původu', 'Indie (oblast Assam)', 'info'],
        [12, 3, 'Doba louhování', '3–5 minut', 'info'],
        [13, 3, 'Teplota vody', '95°C', 'info'],

        // Matcha čaj info
        [14, 4, 'Země původu', 'Japonsko (oblast Uji)', 'info'],
        [15, 4, 'Teplota vody', '70°C - 80°C', 'info'],
        
        // Oolong čaj info
        [16, 6, 'Země původu', 'Čína (provincie Anxi)', 'info'],
        [17, 6, 'Doba louhování', '3–4 minuty', 'info'],
        [18, 6, 'Teplota vody', '85°C', 'info']
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
