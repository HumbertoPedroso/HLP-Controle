<?php
// pages/relacao_produtos.php - Relação de produtos pedidos
$page_title = 'Relação de Produtos';
require_once 'header.php';
require_once '../config/db.php';
$empresa_id = current_empresa_id();

// Buscar produtos com quantidades pedidas
$query = "
    SELECT 
        p.nome,
        p.tamanho,
        p.categoria,
        SUM(ip.quantidade) AS total_quantidade,
        COUNT(DISTINCT ip.pedido_id) AS pedidos_count
    FROM itens_pedido ip
    JOIN produtos p ON ip.produto_id = p.id
    JOIN pedidos ped ON ip.pedido_id = ped.id
    WHERE ped.status = 'Produção' AND ped.empresa_id = ? AND p.empresa_id = ?
    GROUP BY p.id
    ORDER BY p.nome, p.tamanho
";
$stmt = $pdo->prepare($query);
$stmt->execute([$empresa_id, $empresa_id]);
$produtos_pedidos = $stmt->fetchAll();
?>
            <h2>Relação de Produtos Pedidos</h2>
            <p>Produtos que estão em pedidos em produção.</p>
            
            <?php if (empty($produtos_pedidos)): ?>
                <p>Nenhum produto pedido no momento.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Tamanho</th>
                            <th>Categoria</th>
                            <th>Quantidade Total</th>
                            <th>Nº de Pedidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos_pedidos as $produto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                <td><?php echo htmlspecialchars($produto['tamanho'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($produto['categoria']); ?></td>
                                <td><?php echo $produto['total_quantidade']; ?> unidades</td>
                                <td><?php echo $produto['pedidos_count']; ?> pedidos</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
<?php require_once 'footer.php'; ?>
