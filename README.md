# E-shop – 2. fáze (PHP + SQLite)

Tento repozitář obsahuje druhou fázi projektu e-shopu **Čajový Svět**, kde je HTML/CSS frontend propojen s PHP a SQLite databází.

## Spuštění v Codespaces / Lokálně

### 1. Inicializace databáze
Pokud spouštíte projekt poprvé, nebo chcete resetovat data v databázi, spusťte:
```bash
php projekt/database/init.php
```
Tento příkaz vytvoří databázový soubor `projekt/database/eshop.db` se všemi potřebnými tabulkami a naplní je vzorovými daty produktů (zelené čaje, ovocné čaje, oolongy, matcha), dopravy a plateb.

### 2. Spuštění vývojového serveru
Webový server spustíte z kořenové složky příkazem:
```bash
php -S 0.0.0.0:8080 -t projekt
```
Po spuštění otevřete v prohlížeči port `8080` (nebo odkaz z terminálu).

---

## Struktura projektu

```
projekt/
├── database/
│   ├── init.php              ← skript pro vytvoření/reset databáze
│   └── eshop.db              ← SQLite databáze (generuje se automaticky, ignorováno gitem)
├── src/
│   ├── bootstrap.php         ← načte všechny třídy a startuje session (stačí jeden require)
│   ├── Database.php          ← připojení k databázi (PDO)
│   ├── Cart.php              ← košík s perzistencí v SQLite databázi (podle session_id)
│   ├── Validator.php         ← validátor formulářů (fluent interface)
│   ├── DTO/                  ← datové objekty (readonly třídy)
│   │   ├── CategoryDTO.php
│   │   ├── ProductDTO.php
│   │   ├── ProductImageDTO.php
│   │   ├── ProductParameterDTO.php
│   │   ├── ShippingMethodDTO.php
│   │   ├── PaymentMethodDTO.php
│   │   ├── CustomerDTO.php
│   │   ├── OrderDTO.php
│   │   ├── OrderItemDTO.php
│   │   └── CartItemDTO.php
│   └── Repository/           ← třídy pro práci s databází
│       ├── CategoryRepository.php
│       ├── ProductRepository.php
│       ├── ShippingMethodRepository.php
│       ├── PaymentMethodRepository.php
│       ├── CustomerRepository.php
│       └── OrderRepository.php
├── partials/                 ← znovupoužitelné části stránek
│   ├── header.php            ← hlavička s navigací a dynamickým čítačem košíku
│   └── footer.php            ← patička
├── assets/
│   ├── css/                  ← CSS styly e-shopu
│   └── images/               ← obrázky produktů a kategorií
├── index.php                 ← hlavní stránka (výpis doporučených produktů s popupy z DB)
├── kategorie.php             ← přehled kategorií produktů z DB
├── produkty.php              ← výpis produktů z konkrétní kategorie z DB
├── produkt.php               ← detail produktu s variantami (výběr hmotnosti), galerií a specifikacemi
├── kosik.php                 ← nákupní košík s možností úpravy množství a smazání položek
├── dodaci-udaje.php          ← formulář s dodacími údaji, validací a ukládáním do session
├── doprava-platba.php        ← výběr dopravy a platby, zápis objednávky a zákazníka do DB a vyčištění košíku
├── potvrzeni.php             ← stránka s rekapitulací úspěšně dokončené objednávky
├── vyhledavani.php           ← vyhledávání produktů v databázi podle názvu a popisu
├── kontakt.php               ← kontaktní formulář a informace o prodejně
├── o-nas.php                 ← informace o e-shopu
├── cart-action.php           ← bezpečný kontroler pro akce s košíkem (přidat, upravit, smazat) s CSRF ochranou
└── 404.php                   ← chybová stránka
```

---

## Databázové tabulky

| Tabulka | Popis |
|---------|-------|
| `categories` | Kategorie produktů (název, slug, obrázek, popis) |
| `products` | Produkty (název, cena, popis, obrázek, příznak doporučený, sleva) |
| `product_images` | Galerie obrázků produktu |
| `product_parameters` | Parametry produktu – `type`: `'select'` = volitelný (dropdown), `'info'` = pouze informační |
| `shipping_methods` | Číselník způsobů dopravy (název, cena, doba doručení) |
| `payment_methods` | Číselník způsobů platby (název, cena/poplatek) |
| `cart` | Obsah košíků (spárováno s `session_id`, ukládá se v DB) |
| `customers` | Zákazníci (jméno, email, telefon, adresa) |
| `orders` | Objednávky (zákazník, doprava, platba, cena dopravy/platby, celková cena, stav) |
| `order_items` | Položky objednávky (produkt, varianta, množství, jednotková cena) |

---

## Prohlížení databáze v terminálu

Pro přímý náhled do databáze z terminálu Codespaces použijte `sqlite3`:

```bash
sqlite3 projekt/database/eshop.db

# Výpis všech tabulek
.tables

# Zobrazení objednávek
SELECT * FROM orders;

# Zobrazení zákazníků
SELECT * FROM customers;

# Zobrazení obsahu košíků
SELECT * FROM cart;

# Opuštění konzole
.quit
```

---

## Bezpečnost a funkce

- **CSRF Ochrana:** Všechny akce, které modifikují stav (přidání do košíku, úprava množství, odebrání a odeslání objednávky), jsou chráněny pomocí CSRF tokenu generovaného v `header.php` a ověřovaného na straně serveru.
- **Validace:** Formulář pro dodací údaje využívá fluent rozhraní třídy `Validator` k ověření e-mailu, telefonu (české a slovenské předvolby) a formátu PSČ (5 číslic).
- **Perzistentní košík v DB:** Položky v košíku jsou ukládány do SQLite tabulky `cart` spárované s `session_id()` uživatele, což zaručuje bezpečné uložení košíku přímo v databázi.
- **Zápis objednávky:** Po dokončení v `doprava-platba.php` se automaticky založí záznam o zákazníkovi v `customers`, vytvoří objednávka v `orders`, zapíšou položky do `order_items` (v jedné bezpečné transakci), vyčistí se košík v databázi a uživatel je přesměrován na `potvrzeni.php`.