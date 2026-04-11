<?php
session_start();

function getDbConnection() {
    if (!isset($_SESSION['db_host']) || !isset($_SESSION['db_user']) || !isset($_SESSION['db_pass'])) {
        return null;
    }

    $host = $_SESSION['db_host'];
    $user = $_SESSION['db_user'];
    $pass = $_SESSION['db_pass'];
    $name = $_SESSION['db_name'] ?? '';
    $port = $_SESSION['db_port'] ?? 3306;

    try {
        if (empty($name)) {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        } else {
            $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
        }
        
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

$pdo = getDbConnection();
