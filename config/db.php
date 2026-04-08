<?php
// config/db.php - Conexão com o banco de dados MySQL

$host = 'localhost';
$dbname = 'hlp_controle';
$username = 'root'; // Altere se necessário
$password = 'humberto'; // Senha do MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

require_once __DIR__ . '/tenant.php';
tenant_bootstrap($pdo);

// Função para escapar strings (proteção básica contra SQL Injection)
function escape($string) {
    global $pdo;
    return $pdo->quote($string);
}
?>
