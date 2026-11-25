<?php

class Database
{
    public static function getConnection()
    {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return $pdo;
        } catch (PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());
            http_response_code(500);
            echo 'Cannot connect to the nursery_school database. In XAMPP, start MySQL and confirm its port '
                . 'matches DB_PORT in config/config.php (current setting: ' . (int) DB_PORT
                . '). Import database/schema.sql and database/seed.sql if the database has not been created.';
            exit;
        }
    }
}
