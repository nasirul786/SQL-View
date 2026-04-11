<?php
session_start();

// Handle direct login from URL parameters
if (isset($_GET['host']) && (isset($_GET['user']) || isset($_GET['username']))) {
    $host = $_GET['host'];
    $port = $_GET['port'] ?? 3306;
    $user = $_GET['user'] ?? $_GET['username'];
    $pass = $_GET['pass'] ?? $_GET['password'] ?? '';
    $dbname = $_GET['db'] ?? $_GET['database'] ?? '';

    try {
        if (empty($dbname)) {
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
        } else {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        }
        $testPdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Save to session
        $_SESSION['db_host'] = $host;
        $_SESSION['db_port'] = $port;
        $_SESSION['db_user'] = $user;
        $_SESSION['db_pass'] = $pass;
        $_SESSION['db_name'] = $dbname;

        // Strip sensitive params from URL and redirect
        $params = $_GET;
        unset($params['host'], $params['port'], $params['user'], $params['username'], $params['pass'], $params['password'], $params['db'], $params['database']);
        $newUrl = strtok($_SERVER["REQUEST_URI"], '?');
        if (!empty($params)) {
            $newUrl .= '?' . http_build_query($params);
        }
        header("Location: " . $newUrl);
        exit;
    } catch (PDOException $e) {
        $_SESSION['login_error'] = "URL login failed: " . $e->getMessage();
    }
}

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
