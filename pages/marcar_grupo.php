<?php
// pages/marcar_grupo.php - Marcar produtos confirmados em grupo
$page_title = 'Marcar Produtos em Grupo';
require_once 'header.php';
require_once '../config/db.php';
require_once '../config/pedidos_helpers.php';

ensure_itens_confirmado_column($pdo);
ensure_itens_confirmado_quantidade_column($pdo);
$empresa_id = current_empresa_id();

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produto_id = intval($_POST['produto_id'] ?? 0);
    $pedidos = $_POST['pedidos'] ?? [];

    if ($produto_id && !empty($pedidos)) {
        try {
            $placeholders_ids = str_repeat('?,', count($pedidos) - 1) . '?';
            $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE empresa_id = ? AND id IN ($placeholders_ids)");
            $stmt->execute(array_merge([$empresa_id], $pedidos));
            $pedidos_validos = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($pedidos_validos)) {
                throw new Exception('Nenhum pedido válido selecionado.');
            }

            $placeholders = str_repeat('?,', count($pedidos_validos) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE itens_pedido SET confirmado = 1, confirmado_quantidade = quantidade WHERE produto_id = ? AND pedido_id IN ($placeholders)");
            $params = array_merge([$produto_id], $pedidos_validos);
            $stmt->execute($params);

            foreach ($pedidos_validos as $pedido_id) {
                atualizar_status_pedido_por_confirmacao($pdo, intval($pedido_id));
            }

            $message = 'Produtos marcados como confirmados com sucesso.';
        } catch (Exception $e) {
            $message = 'Erro: ' . $e->getMessage();
        }
    } else {
        $message = 'Selecione um produto e pelo menos um pedido.';
    }
}

$produto_search = trim($_GET['produto_search'] ?? '');
$produto_selecionado = intval($_GET['produto'] ?? 0);

$query_produtos = "SELECT id, nome, tamanho FROM produtos WHERE empresa_id = ?";
$params_produtos = [$empresa_id];

if ($produto_search !== '') {
    $query_produtos .= " AND CONCAT(nome, ' ', COALESCE(tamanho, '')) LIKE ?";
    $params_produtos[] = '%' . $produto_search . '%';
}

$query_produtos .= " ORDER BY nome, tamanho";
$stmt = $pdo->prepare($query_produtos);
$stmt->execute($params_produtos);
$produtos = $stmt->fetchAll();

$pedidos_com_produto = [];
if ($produto_selecionado) {
    $stmt = $pdo->prepare("\n        SELECT DISTINCT p.id, p.cliente_nome, p.data, ip.confirmado, ip.quantidade\n        FROM pedidos p\n        JOIN itens_pedido ip ON p.id = ip.pedido_id\n        WHERE ip.produto_id = ? AND ip.confirmado = 0 AND p.empresa_id = ?\n        ORDER BY p.data DESC\n    ");
    $stmt->execute([$produto_selecionado, $empresa_id]);
    $pedidos_com_produto = $stmt->fetchAll();
}
?>
            <h2>Marcar Produtos Confirmados em Grupo</h2>

            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="get" action="pedidos_central.php">
                <input type="hidden" name="aba" value="marcar_grupo">
                <div class="form-row">
                    <div class="form-group">
                        <label for="produto_search">Buscar produto (nome/tamanho):</label>
                        <input type="text" id="produto_search" name="produto_search" value="<?php echo htmlspecialchars($produto_search); ?>" placeholder="Ex: calca m">
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;gap:8px;">
                        <button type="submit" class="btn btn-secondary">Buscar</button>
                        <a class="btn btn-secondary" href="?aba=marcar_grupo">Limpar</a>
                    </div>
                </div>

                <div class="form-group">
                    <label for="produto">Selecione o Produto:</label>
                    <select id="produto" name="produto" onchange="this.form.submit()">
                        <option value="">Escolha um produto</option>
                        <?php foreach ($produtos as $produto): ?>
                            <option value="<?php echo $produto['id']; ?>" <?php echo $produto_selecionado == $produto['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($produto['nome'] . ' - ' . ($produto['tamanho'] ?: '-')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <?php if ($pedidos_com_produto): ?>
                <form method="post" action="pedidos_central.php?aba=marcar_grupo">
                    <input type="hidden" name="produto_id" value="<?php echo $produto_selecionado; ?>">
                    <input type="hidden" name="produto_search" value="<?php echo htmlspecialchars($produto_search); ?>">
                    <h3>Pedidos com este produto nao confirmado:</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Selecionar</th>
                                <th>Nº Pedido</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Quantidade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos_com_produto as $pedido): ?>
                                <tr>
                                    <td><input type="checkbox" name="pedidos[]" value="<?php echo $pedido['id']; ?>"></td>
                                    <td><?php echo $pedido['id']; ?></td>
                                    <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pedido['data'])); ?></td>
                                    <td><?php echo $pedido['quantidade']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">Marcar como Confirmado</button>
                </form>
            <?php elseif ($produto_selecionado): ?>
                <p>Nenhum pedido encontrado com este produto nao confirmado.</p>
            <?php endif; ?>

            <a href="pedidos_central.php?aba=listar" class="btn btn-secondary">Voltar</a>
<?php require_once 'footer.php'; ?>
