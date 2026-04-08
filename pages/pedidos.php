<?php
// pages/pedidos.php - Cadastro/EdiÃ§Ã£o de pedidos
$page_title = 'Novo Pedido';
require_once 'header.php';
require_once '../config/db.php';
require_once '../config/categorias.php';
$empresa_id = current_empresa_id();

// Verificar se Ã© ediÃ§Ã£o
$pedido_id = intval($_GET['id'] ?? 0);
$pedido = null;
$itens = [];

if ($pedido_id > 0) {
    $page_title = 'Editar Pedido';
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$pedido_id, $empresa_id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        $_SESSION['message'] = 'Pedido não encontrado.';
        header('Location: lista_pedidos.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT ip.*, p.nome FROM itens_pedido ip JOIN produtos p ON ip.produto_id = p.id WHERE ip.pedido_id = ? AND p.empresa_id = ?");
    $stmt->execute([$pedido_id, $empresa_id]);
    $itens = $stmt->fetchAll();
}

// Buscar produtos
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE empresa_id = ? ORDER BY nome");
$stmt->execute([$empresa_id]);
$produtos = $stmt->fetchAll();
$categorias = get_categorias_produto($pdo, $empresa_id);
?>
            <h2><?php echo $pedido_id ? 'Editar Pedido' : 'Novo Pedido'; ?></h2>
            <form id="pedidoForm" action="../actions/pedido_crud.php" method="post">
                <input type="hidden" name="action" value="<?php echo $pedido_id ? 'update' : 'create'; ?>">
                <?php if ($pedido_id): ?>
                    <input type="hidden" name="id" value="<?php echo $pedido_id; ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cliente_nome">Nome do Cliente:</label>
                        <input type="text" id="cliente_nome" name="cliente_nome" value="<?php echo htmlspecialchars($pedido['cliente_nome'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($pedido['telefone'] ?? ''); ?>" class="mask-telefone">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="data">Data:</label>
                        <input type="date" id="data" name="data" value="<?php echo $pedido['data'] ?? date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="categoria">Categoria:</label>
                        <select id="categoria" name="categoria" required>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo htmlspecialchars($categoria); ?>" <?php echo ($pedido['categoria'] ?? '') == $categoria ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pagamento">Pagamento:</label>
                        <select id="pagamento" name="pagamento" required>
                            <option value="Pendente" <?php echo ($pedido['pagamento'] ?? 'Pendente') == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="Pago" <?php echo ($pedido['pagamento'] ?? '') == 'Pago' ? 'selected' : ''; ?>>Pago</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="observacoes">Observações:</label>
                    <textarea id="observacoes" name="observacoes"><?php echo htmlspecialchars($pedido['observacoes'] ?? ''); ?></textarea>
                </div>

                <h3>Produtos</h3>
                <div id="produtos-container">
                    <?php if ($pedido_id && !empty($itens)): ?>
                        <?php foreach ($itens as $index => $item): ?>
                            <div class="produto-item">
                                <select name="produtos[<?php echo $index; ?>][id]" class="produto-select" required>
                                    <option value="">Selecione um produto</option>
                                    <?php foreach ($produtos as $produto): ?>
                                        <option value="<?php echo $produto['id']; ?>" data-preco="<?php echo $produto['preco']; ?>" <?php echo $produto['id'] == $item['produto_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($produto['nome'] . ' - ' . $produto['tamanho'] . ' - R$ ' . number_format($produto['preco'], 2, ',', '.')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="produtos[<?php echo $index; ?>][quantidade]" class="quantidade" min="1" value="<?php echo $item['quantidade']; ?>" required>
                                <button type="button" class="btn btn-danger remove-produto" style="padding: 8px 16px; font-size: 14px;">Remover</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addProduto()" style="padding: 12px 24px; font-size: 16px; margin: 10px 0;">Adicionar Produto</button>

                <div class="total-container">
                    <h3>Total: R$ <span id="total">0.00</span></h3>
                </div>

                <button type="submit" class="btn btn-primary">Salvar Pedido</button>
            </form>
<?php require_once 'footer.php'; ?>

<script>
let produtoIndex = <?php echo count($itens); ?>;
</script>

