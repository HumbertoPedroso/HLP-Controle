<?php
// config/tenant.php - utilitarios de multiempresa

function tenant_bootstrap(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS empresas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(120) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("INSERT IGNORE INTO empresas (id, nome) VALUES (1, 'Empresa Padrão')");

    tenant_ensure_column($pdo, 'usuarios', 'empresa_id', "INT NULL AFTER role");
    tenant_ensure_column($pdo, 'usuarios', 'ativo', "TINYINT(1) NOT NULL DEFAULT 1 AFTER empresa_id");
    $pdo->exec("UPDATE usuarios SET empresa_id = 1 WHERE empresa_id IS NULL");

    tenant_ensure_column($pdo, 'produtos', 'empresa_id', "INT NULL AFTER categoria");
    $pdo->exec("UPDATE produtos SET empresa_id = 1 WHERE empresa_id IS NULL");

    tenant_ensure_column($pdo, 'pedidos', 'empresa_id', "INT NULL AFTER categoria");
    $pdo->exec("UPDATE pedidos SET empresa_id = 1 WHERE empresa_id IS NULL");

    tenant_ensure_column($pdo, 'categorias_produto', 'empresa_id', "INT NULL AFTER id");
    $pdo->exec("UPDATE categorias_produto SET empresa_id = 1 WHERE empresa_id IS NULL");

    // Ajusta indice unico por empresa+nome para categorias.
    $indexes = $pdo->query("SHOW INDEX FROM categorias_produto WHERE Key_name = 'nome'")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($indexes)) {
        $pdo->exec("ALTER TABLE categorias_produto DROP INDEX nome");
    }

    $idx_empresa_nome = $pdo->query("SHOW INDEX FROM categorias_produto WHERE Key_name = 'uk_categoria_empresa_nome'")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($idx_empresa_nome)) {
        $pdo->exec("ALTER TABLE categorias_produto ADD UNIQUE KEY uk_categoria_empresa_nome (empresa_id, nome)");
    }
}

function tenant_ensure_column(PDO $pdo, string $table, string $column, string $definition): void {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$col) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

function current_empresa_id(): int {
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['empresa_id'])) {
        return intval($_SESSION['empresa_id']);
    }
    return 1;
}

function get_or_create_empresa_id(PDO $pdo, string $nome): int {
    $nome = trim($nome);
    if ($nome === '') {
        return 1;
    }

    $stmt = $pdo->prepare("SELECT id FROM empresas WHERE nome = ? LIMIT 1");
    $stmt->execute([$nome]);
    $id = $stmt->fetchColumn();
    if ($id) {
        return intval($id);
    }

    $stmt = $pdo->prepare("INSERT INTO empresas (nome) VALUES (?)");
    $stmt->execute([$nome]);
    return intval($pdo->lastInsertId());
}

