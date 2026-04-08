<?php
// pages/lista_pedidos.php - Listagem de pedidos com filtros
$page_title = 'Todos os Pedidos';
require_once 'header.php';
require_once '../config/db.php';
require_once '../config/categorias.php';
$empresa_id = current_empresa_id();

// Filtros
$status_filter = $_GET['status'] ?? '';
$categoria_filter = $_GET['categoria'] ?? $_GET['categoria'] ?? ''; // Para compatibilidade
$data_filter = $_GET['data'] ?? '';
$search = $_GET['search'] ?? '';
$produto_search = $_GET['produto_search'] ?? '';
$pagamento_filter = $_GET['pagamento'] ?? '';
$categorias = get_categorias_produto($pdo, $empresa_id);
$current_url = htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'pedidos_central.php?aba=listar');

$query = "SELECT DISTINCT p.* FROM pedidos p";
$joins = "";
$params = [];

if ($produto_search) {
    $joins .= " JOIN itens_pedido ip ON p.id = ip.pedido_id JOIN produtos pr ON ip.produto_id = pr.id";
    
    // Parse busca: nome, tamanho ou nome. tamanho
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

if ($status_filter) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}
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
            <h2>Todos os Pedidos</h2>
            
            <!-- Menu Hamburguer -->
            <button class="hamburger-btn" onclick="toggleSidebar()">Filtros</button>
            
            <!-- Sidebar com Filtros -->
            <div id="sidebar" class="sidebar">
                <div class="sidebar-header">
                    <h3>Filtros de Pesquisa</h3>
                    <button class="close-btn" onclick="toggleSidebar()">×</button>
                </div>
                <form method="get" action="?aba=listar" class="sidebar-form">
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="">Todos</option>
                            <option value="Produção" <?php echo $status_filter == 'Produção' ? 'selected' : ''; ?>>Produção</option>
                            <option value="Pronto" <?php echo $status_filter == 'Pronto' ? 'selected' : ''; ?>>Pronto</option>
                            <option value="Entregue" <?php echo $status_filter == 'Entregue' ? 'selected' : ''; ?>>Entregue</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pagamento">Pagamento:</label>
                        <select id="pagamento" name="pagamento">
                            <option value="">Todos</option>
                            <option value="Pendente" <?php echo $pagamento_filter == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="Pago" <?php echo $pagamento_filter == 'Pago' ? 'selected' : ''; ?>>Pago</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="categoria">Categoria:</label>
                        <select id="categoria" name="categoria">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo htmlspecialchars($categoria); ?>" <?php echo $categoria_filter == $categoria ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="data">Data:</label>
                        <input type="date" id="data" name="data" value="<?php echo $data_filter; ?>">
                    </div>
                    <div class="form-group">
                        <label for="search">Buscar por nome:</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <label for="produto_search">Buscar por produto:</label>
                        <input type="text" id="produto_search" name="produto_search" value="<?php echo htmlspecialchars($produto_search); ?>" placeholder="Ex: camiseta, m ou camiseta. m">
                    </div>
                    <div class="sidebar-actions">
                        <button type="submit" class="btn btn-secondary">Aplicar Filtros</button>
                        <a href="?aba=listar" class="btn btn-secondary">Limpar</a>
                    </div>
                </form>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>N° Pedido</th>
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
                                        <option value="P" <?php echo $pedido['status'] == 'P' ? 'selected' : ''; ?>>P</option>
                                        <option value="Pronto" <?php echo $pedido['status'] == 'Pronto' ? 'selected' : ''; ?>>Pronto</option>
                                        <option value="Entregue" <?php echo $pedido['status'] == 'Entregue' ? 'selected' : ''; ?>>Entregue</option>
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
                                    <a href="pedidos.php?id=<?php echo $pedido['id']; ?>" class="btn btn-warning">Editar</a>
                                    <button class="btn btn-danger" onclick="deletePedido(<?php echo $pedido['id']; ?>)">Excluir</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
<?php require_once 'footer.php'; ?>



