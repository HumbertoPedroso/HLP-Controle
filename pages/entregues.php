<?php
// pages/entregues.php - Pedidos entregues
$page_title = 'Pedidos Entregues';
require_once 'header.php';
require_once '../config/db.php';
$empresa_id = current_empresa_id();

// Filtros
$categoria_filter = $_GET['categoria'] ?? '';
$pagamento_filter = $_GET['pagamento'] ?? '';
$data_filter = $_GET['data'] ?? '';
$search = $_GET['search'] ?? '';
$produto_search = $_GET['produto_search'] ?? '';
$current_url = htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'pedidos_central.php?aba=entregues');

$query = "SELECT DISTINCT p.* FROM pedidos p";
$joins = "";
$params = [];

if ($produto_search) {
    $joins .= " JOIN itens_pedido ip ON p.id = ip.pedido_id JOIN produtos pr ON ip.produto_id = pr.id";
    
    $search_parts = preg_split('/[,.]\s*/', $produto_search, 2);
    $nome_search = trim($search_parts[0] ?? '');
    $tamanho_search = trim($search_parts[1] ?? '');
    
    $conditions = [];
    if ($nome_search) {
        $conditions[] = "pr.nome LIKE ?";
        $params[] = "%$nome_search%";
    }
    if ($tamanho_search) {
        $conditions[] = "pr.tamanho LIKE ?";
        $params[] = "%$tamanho_search%";
    }
    
    if (!empty($conditions)) {
        $query .= $joins . " WHERE p.empresa_id = ? AND " . implode(" AND ", $conditions);
        array_unshift($params, $empresa_id);
    } else {
        $query .= " WHERE p.empresa_id = ?";
        $params[] = $empresa_id;
    }
} else {
    $query .= " WHERE p.empresa_id = ?";
    $params[] = $empresa_id;
}

$query .= " AND p.status = 'Entregue'";

if ($categoria_filter) {
    $query .= " AND p.categoria = ?";
    $params[] = $categoria_filter;
    $page_title .= ' - ' . $categoria_filter;
}
if ($pagamento_filter) {
    $query .= " AND p.pagamento = ?";
    $params[] = $pagamento_filter;
}
if ($data_filter) {
    $query .= " AND p.data = ?";
    $params[] = $data_filter;
}
if ($search && !$produto_search) {
    $query .= " AND p.cliente_nome LIKE ?";
    $params[] = "%$search%";
}

if ($produto_search) {
    $page_title .= ' - Produto: ' . $produto_search;
}

$query .= " ORDER BY p.data DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();
?>
            <h2>Pedidos Entregues</h2>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Nº Pedido</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th>Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?php echo $pedido['id']; ?></td>
                            <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pedido['data'])); ?></td>
                            <td><?php echo htmlspecialchars($pedido['categoria']); ?></td>
                            <td class="status-<?php echo strtolower($pedido['status']); ?>">
                                <form method="post" action="../actions/pedido_crud.php" class="status-form">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?php echo $pedido['id']; ?>">
                                    <input type="hidden" name="redirect_to" value="<?php echo $current_url; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="Produção">Produção</option>
                                        <option value="Pronto">Pronto</option>
                                        <option value="Entregue" selected>Entregue</option>
                                    </select>
                                </form>
                            </td>
                            <td class="pagamento-<?php echo strtolower($pedido['pagamento']); ?>"><?php echo htmlspecialchars($pedido['pagamento']); ?></td>
                            <td>R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php 
                                    $telefone_limpo = preg_replace('/\D/', '', $pedido['telefone']);
                                    if ($telefone_limpo) {
                                        echo '<a href="https://wa.me/55' . $telefone_limpo . '" target="_blank" class="btn btn-success btn-sm" title="WhatsApp"><img src="../assets/img/whatsapp-icon.svg" alt="WhatsApp" class="whatsapp-icon"></a>';
                                    }
                                    ?>
                                    <button class="btn btn-info" onclick="viewDetails(<?php echo $pedido['id']; ?>)">Ver Detalhes</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
<?php require_once 'footer.php'; ?>




