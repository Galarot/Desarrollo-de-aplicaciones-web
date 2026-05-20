<?php

function ensureUserTableExists(): void
{
    $dbUrl = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? '';
    
    try {
        if (str_starts_with($dbUrl, 'mysql:')) {
            // Para MySQL en Docker
            $matches = [];
            if (preg_match('/mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/(.+)/', $dbUrl, $matches)) {
                $dsn = "mysql:host={$matches[3]};port={$matches[4]};dbname={$matches[5]}";
                $db = new PDO($dsn, $matches[1], $matches[2]);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $db->exec('CREATE TABLE IF NOT EXISTS `user` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(180) NOT NULL UNIQUE,
                    username VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    roles JSON NOT NULL
                )');
                
                $db->exec('CREATE TABLE IF NOT EXISTS `daily_progress` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    game_mode VARCHAR(20) NOT NULL,
                    seed INT NOT NULL,
                    completed BOOLEAN NOT NULL DEFAULT 0,
                    FOREIGN KEY (user_id) REFERENCES `user`(id)
                )');
            }
        } else {
            // Fallback a SQLite
            $path = __DIR__ . '/../var/app.db';
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            $db = new PDO('sqlite:' . $path);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->exec('CREATE TABLE IF NOT EXISTS "user" (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email VARCHAR(180) NOT NULL UNIQUE,
                username VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                roles TEXT NOT NULL DEFAULT "[]"
            )');

            $db->exec('CREATE TABLE IF NOT EXISTS "daily_progress" (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                game_mode VARCHAR(20) NOT NULL,
                seed INTEGER NOT NULL,
                completed BOOLEAN NOT NULL DEFAULT 0,
                FOREIGN KEY (user_id) REFERENCES "user" (id)
            )');
        }
    } catch (Exception $e) {
        // Ignorar errores si la base de datos no está lista
    }
}

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? __FILE__)) {
    try {
        ensureUserTableExists();
        $db = new PDO('sqlite:' . __DIR__ . '/../var/app.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $tables = $db->query('SELECT name FROM sqlite_master WHERE type="table"')->fetchAll(PDO::FETCH_COLUMN);
        echo 'OK - Tables: ' . implode(', ', $tables);
    } catch (Exception $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
}
