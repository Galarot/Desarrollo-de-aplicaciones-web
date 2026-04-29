<?php

function ensureUserTableExists(): void
{
    $path = __DIR__ . '/../var/app.db';
    $db = new PDO('sqlite:' . $path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('CREATE TABLE IF NOT EXISTS "user" (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(180) NOT NULL UNIQUE,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        roles TEXT NOT NULL DEFAULT "[]"
    )');
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
