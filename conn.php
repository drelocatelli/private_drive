<?php
require_once 'vendor/autoload.php';  // Certifique-se de incluir o autoload do Composer

// Obter as variáveis do .env
$host = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASSWORD'];
$dbPort = $_ENV['DB_PORT'];

$dsn = "mysql:host=$host;port=$dbPort;dbname=$dbName;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}