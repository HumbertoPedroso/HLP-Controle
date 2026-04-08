<?php
// config/categorias.php - Utilitarios para categorias dinamicas
require_once __DIR__ . '/tenant.php';

function ensure_categorias_produto_table(PDO $pdo, ?int $empresa_id = null): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categorias_produto (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(50) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Sem categorias padrão: cada empresa cria as próprias categorias.
}

function ensure_pedidos_categoria_varchar(PDO $pdo): void {
    $stmt = $pdo->query("SHOW COLUMNS FROM pedidos LIKE 'categoria'");
    $coluna = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($coluna && stripos($coluna['Type'], 'enum(') === 0) {
        $pdo->exec("ALTER TABLE pedidos MODIFY categoria VARCHAR(50) NOT NULL");
    }
}

function get_categorias_produto(PDO $pdo, ?int $empresa_id = null): array {
    ensure_pedidos_categoria_varchar($pdo);
    $empresa_id = $empresa_id ?: current_empresa_id();
    ensure_categorias_produto_table($pdo, $empresa_id);
    $stmt = $pdo->prepare("SELECT nome FROM categorias_produto WHERE empresa_id = ? ORDER BY nome");
    $stmt->execute([$empresa_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
