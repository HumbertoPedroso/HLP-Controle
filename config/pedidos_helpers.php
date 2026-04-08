<?php
// config/pedidos_helpers.php - Utilitarios de itens confirmados e status do pedido

function ensure_itens_confirmado_column(PDO $pdo): void {
    $stmt = $pdo->query("SHOW COLUMNS FROM itens_pedido LIKE 'confirmado'");
    $coluna = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coluna) {
        $pdo->exec("ALTER TABLE itens_pedido ADD COLUMN confirmado TINYINT(1) NOT NULL DEFAULT 0");
    }
}

function ensure_itens_confirmado_quantidade_column(PDO $pdo): void {
    $stmt = $pdo->query("SHOW COLUMNS FROM itens_pedido LIKE 'confirmado_quantidade'");
    $coluna = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coluna) {
        $pdo->exec("ALTER TABLE itens_pedido ADD COLUMN confirmado_quantidade INT NOT NULL DEFAULT 0");
    }

    // Compatibilidade: itens antigos marcados como confirmados recebem a quantidade total.
    $pdo->exec("UPDATE itens_pedido SET confirmado_quantidade = quantidade WHERE confirmado = 1 AND confirmado_quantidade = 0");
    $pdo->exec("UPDATE itens_pedido SET confirmado = CASE WHEN confirmado_quantidade >= quantidade THEN 1 ELSE 0 END");
}

function atualizar_status_pedido_por_confirmacao(PDO $pdo, int $pedido_id): bool {
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN confirmado_quantidade >= quantidade THEN 1 ELSE 0 END), 0) AS itens_confirmados
        FROM itens_pedido
        WHERE pedido_id = ?
    ");
    $stmt->execute([$pedido_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $total = intval($result['total'] ?? 0);
    $confirmados = intval($result['itens_confirmados'] ?? 0);
    $all_confirmed = ($total > 0 && $total === $confirmados);

    if ($all_confirmed) {
        $stmt = $pdo->prepare("UPDATE pedidos SET status = 'Pronto' WHERE id = ?");
        $stmt->execute([$pedido_id]);
    } else {
        // Se ainda nao foi entregue, volta para producao quando existir item desmarcado.
        $stmt = $pdo->prepare("UPDATE pedidos SET status = 'Produção' WHERE id = ? AND status <> 'Entregue'");
        $stmt->execute([$pedido_id]);
    }

    return $all_confirmed;
}

