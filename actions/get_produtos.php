<?php
// actions/get_produtos.php - Retorna produtos em JSON para AJAX
require_once '../config/auth.php';
require_login_json();

header('Content-Type: application/json');
require_once '../config/db.php';

$categoria = $_GET['categoria'] ?? '';
$empresa_id = current_empresa_id();

$query = "SELECT id, nome, tamanho, preco FROM produtos WHERE empresa_id = ?";
$params = [$empresa_id];

if ($categoria) {
    $query .= " AND categoria = ?";
    $params[] = $categoria;
}

$query .= " ORDER BY nome";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

echo json_encode($produtos);
?>
