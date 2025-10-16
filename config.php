<?php
// Configuration and Database Initialization

// Define the database file path.
define('DB_PATH', __DIR__ . '/database/database.sqlite'); // <-- DEĞİŞİKLİK BURADA
define('DB_DIR', __DIR__ . '/database'); // <-- BURAYI DA GÜNCELLEYELİM

/**
 * Initializes the database connection and creates tables if they don't exist.
 * Also seeds the database with initial data for demonstration.
 * @return PDO
 */
function get_db_connection() {
    if (!is_dir(DB_DIR)) {
        mkdir(DB_DIR, 0777, true);
    }

    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Check if the database needs to be initialized.
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if ($stmt->fetch() === false) {
            // Create Tables
            $pdo->exec("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT, fullname TEXT NOT NULL, email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL, role TEXT NOT NULL DEFAULT 'User', company_id INTEGER,
                balance REAL NOT NULL DEFAULT 0.0, FOREIGN KEY (company_id) REFERENCES companies(id)
            )");
            $pdo->exec("CREATE TABLE companies (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
            $pdo->exec("CREATE TABLE trips (
                id INTEGER PRIMARY KEY AUTOINCREMENT, company_id INTEGER NOT NULL, departure_location TEXT NOT NULL,
                arrival_location TEXT NOT NULL, departure_time TEXT NOT NULL, price REAL NOT NULL,
                seat_count INTEGER NOT NULL, FOREIGN KEY (company_id) REFERENCES companies(id)
            )");
            $pdo->exec("CREATE TABLE bookings (
                id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, trip_id INTEGER NOT NULL,
                seat_number INTEGER NOT NULL, price_paid REAL NOT NULL, booking_time TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (trip_id) REFERENCES trips(id)
            )");
            $pdo->exec("CREATE TABLE coupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, discount_rate REAL NOT NULL,
                usage_limit INTEGER NOT NULL, expiry_date TEXT NOT NULL, company_id INTEGER,
                FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
            )");

            // Seed Data
            $pdo->exec("INSERT INTO companies (name) VALUES ('Kamil Koç'), ('Metro Turizm'), ('Pamukkale Turizm')");
            $hashed_pass = password_hash('123456', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (fullname, email, password, role, balance) VALUES ('Admin User', 'admin@example.com', '$hashed_pass', 'Admin', 1000)");
            $pdo->exec("INSERT INTO users (fullname, email, password, role, company_id) VALUES ('Kamil Koç Admin', 'kamilex@example.com', '$hashed_pass', 'Firma Admin', 1)");
            $pdo->exec("INSERT INTO users (fullname, email, password, role, balance) VALUES ('Normal User', 'user@example.com', '$hashed_pass', 'User', 500)");
            $pdo->exec("INSERT INTO trips (company_id, departure_location, arrival_location, departure_time, price, seat_count) VALUES (1, 'İstanbul', 'Ankara', '2025-10-25 08:00:00', 250.0, 40)");
            $pdo->exec("INSERT INTO trips (company_id, departure_location, arrival_location, departure_time, price, seat_count) VALUES (2, 'İzmir', 'Antalya', '2025-10-26 10:30:00', 300.0, 42)");
            $pdo->exec("INSERT INTO coupons (code, discount_rate, usage_limit, expiry_date) VALUES ('GENEL10', 0.10, 100, '2025-12-31')");
            $pdo->exec("INSERT INTO coupons (code, discount_rate, usage_limit, expiry_date, company_id) VALUES ('KAMIL15', 0.15, 50, '2025-11-30', 1)");
        }
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Global database connection
$pdo = get_db_connection();
