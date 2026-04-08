<?php
// actions/toggle_confirmado.php - Atualizar confirmado de item e status do pedido
require_once '../config/auth.php';
require_login_json();

ensure_session_started();
require_once '../config/db.php';
require_once '../config/pedidos_helpers.php';

ensure_itens_confirmado_column($pdo);
ensure_itens_confirmado_quantidade_column($pdo);
$empresa_id = current_empresa_id();

$data = json_decode(file_get_contents('php://input'), true);
$item_id = intval($data['item_id'] ?? 0);
$confirmado = intval($data['confirmado'] ?? 0);
$confirmado_quantidade = isset($data['confirmado_quantidade']) ? intval($data['confirmado_quantidade']) : null;

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'ID do item invalido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT ip.pedido_id, ip.quantidade
        FROM itens_pedido ip
        JOIN pedidos p ON p.id = ip.pedido_id
        WHERE ip.id = ? AND p.empresa_id = ?
    ");
    $stmt->execute([$item_id, $empresa_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item nao encontrado']);
        exit;
    }

    $quantidade_total = intval($item['quantidade']);
    if ($confirmado_quantidade === null) {
        $confirmado_quantidade = $confirmado ? $quantidade_total : 0;
    }
    $confirmado_quantidade = max(0, min($quantidade_total, $confirmado_quantidade));
    $confirmado_flag = $confirmado_quantidade >= $quantidade_total ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE itens_pedido SET confirmado = ?, confirmado_quantidade = ? WHERE id = ?");
    $stmt->execute([$confirmado_flag, $confirmado_quantidade, $item_id]);

    $stmt = $pdo->prepare("
        SELECT ip.pedido_id
        FROM itens_pedido ip
        JOIN pedidos p ON p.id = ip.pedido_id
        WHERE ip.id = ? AND p.empresa_id = ?
    ");
    $stmt->execute([$item_id, $empresa_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Item nao encontrado']);
        exit;
    }

    $pedido_id = intval($pedido['pedido_id']);
    $all_confirmed = atualizar_status_pedido_por_confirmacao($pdo, $pedido_id);

    echo json_encode([
        'success' => true,
        'all_confirmed' => $all_confirmed,
        'confirmado_quantidade' => $confirmado_quantidade,
        'quantidade_total' => $quantidade_total
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
