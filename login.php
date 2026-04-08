<?php
session_start();

$host = $_POST['host'] ?? '';
$port = $_POST['port'] ?? 3306;
$user = $_POST['username'] ?? '';
$pass = $_POST['password'] ?? '';
$dbname = $_POST['database'] ?? '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $_SESSION['db_host'] = $host;
    $_SESSION['db_port'] = $port;
    $_SESSION['db_user'] = $user;
    $_SESSION['db_pass'] = $pass;
    $_SESSION['db_name'] = $dbname;
    
    header("Location: tables.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['login_error'] = "Connection failed: " . $e->getMessage();
    header("Location: index.php");
    exit;
}
