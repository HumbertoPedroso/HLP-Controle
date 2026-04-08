<?php
// pages/detalhes_pedido.php - Detalhes do pedido
$page_title = 'Detalhes do Pedido';
require_once 'header.php';
require_once '../config/db.php';
require_once '../config/pedidos_helpers.php';

ensure_itens_confirmado_column($pdo);
ensure_itens_confirmado_quantidade_column($pdo);
$empresa_id = current_empresa_id();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: lista_pedidos.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$pedido = $stmt->fetch();

if (!$pedido) {
    $_SESSION['message'] = 'Pedido nao encontrado.';
    header('Location: lista_pedidos.php');
    exit;
}

$stmt = $pdo->prepare("\n    SELECT ip.*, p.nome, p.tamanho\n    FROM itens_pedido ip\n    JOIN produtos p ON ip.produto_id = p.id\n    WHERE ip.pedido_id = ? AND p.empresa_id = ?\n");
$stmt->execute([$id, $empresa_id]);
$itens = $stmt->fetchAll();
?>
            <h2>Detalhes do Pedido #<?php echo $pedido['id']; ?></h2>
            <div class="pedido-info">
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['cliente_nome']); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($pedido['telefone']); ?> 
                    <?php 
                    $telefone_limpo = preg_replace('/\D/', '', $pedido['telefone']);
                    if ($telefone_limpo) {
                        echo '<a href="https://wa.me/55' . $telefone_limpo . '" target="_blank" class="btn btn-success btn-sm" title="WhatsApp"><img src="../assets/img/whatsapp-icon.svg" alt="WhatsApp" class="whatsapp-icon"></a>';
                    }
                    ?>
                </p>
                <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($pedido['data'])); ?></p>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($pedido['categoria']); ?></p>
                <p><strong>Status:</strong> <span class="status-<?php echo strtolower($pedido['status']); ?>"><?php echo htmlspecialchars($pedido['status']); ?></span></p>
                <p><strong>Pagamento:</strong> <span class="pagamento-<?php echo strtolower($pedido['pagamento']); ?>"><?php echo htmlspecialchars($pedido['pagamento']); ?></span></p>
                <p><strong>Observacoes:</strong> <?php echo htmlspecialchars($pedido['observacoes']); ?></p>
            </div>

            <h3>Produtos</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Tamanho</th>
                        <th>Quantidade</th>
                        <th>Qtd Confirmada</th>
                        <th>Preco Unitario</th>
                        <th>Total</th>
                        <th>Confirmado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nome']); ?></td>
                            <td><?php echo htmlspecialchars($item['tamanho'] ?? '-'); ?></td>
                            <td><?php echo $item['quantidade']; ?></td>
                            <td>
                                <input
                                    type="number"
                                    min="0"
                                    max="<?php echo intval($item['quantidade']); ?>"
                                    value="<?php echo intval($item['confirmado_quantidade'] ?? ($item['confirmado'] ? $item['quantidade'] : 0)); ?>"
                                    onchange="updateConfirmadoQuantidade(<?php echo $item['id']; ?>, this.value, <?php echo intval($item['quantidade']); ?>, this)"
                                    style="width: 90px;"
                                >
                            </td>
                            <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></td>
                            <td>
                                <input type="checkbox" class="confirm-checkbox" data-item-id="<?php echo $item['id']; ?>" <?php echo $item['confirmado'] ? 'checked' : ''; ?> onchange="toggleConfirmado(<?php echo $item['id']; ?>, this.checked, this)">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-container">
                <h3>Total do Pedido: R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></h3>
            </div>

            <a href="pedidos_central.php?aba=listar" class="btn btn-secondary">Voltar</a>

            <script>
            function toggleConfirmado(itemId, checked, checkbox) {
                sendConfirmado(itemId, checked ? 1 : 0, null, checkbox);
            }

            function updateConfirmadoQuantidade(itemId, quantidadeConfirmada, quantidadeTotal, input) {
                let quantidade = parseInt(quantidadeConfirmada, 10);
                if (isNaN(quantidade)) quantidade = 0;
                quantidade = Math.max(0, Math.min(quantidadeTotal, quantidade));
                input.value = quantidade;
                sendConfirmado(itemId, null, quantidade, null);
            }

            function sendConfirmado(itemId, confirmado, confirmadoQuantidade, checkbox) {
                const payload = { item_id: itemId };
                if (confirmado !== null) payload.confirmado = confirmado;
                if (confirmadoQuantidade !== null) payload.confirmado_quantidade = confirmadoQuantidade;

                fetch('../actions/toggle_confirmado.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erro ao atualizar: ' + data.message);
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro de conexao');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                    }
                });
            }
            </script>
<?php require_once 'footer.php'; ?>
