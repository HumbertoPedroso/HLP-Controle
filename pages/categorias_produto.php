<?php
// pages/categorias_produto.php - gerenciamento de categorias de produtos
$page_title = 'Categorias de Produtos';
require_once 'header.php';
require_once '../config/db.php';
require_once '../config/categorias.php';

$empresa_id = current_empresa_id();
ensure_categorias_produto_table($pdo, $empresa_id);
$stmt = $pdo->prepare("SELECT id, nome FROM categorias_produto WHERE empresa_id = ? ORDER BY nome");
$stmt->execute([$empresa_id]);
$categorias = $stmt->fetchAll();
?>
            <h2>Editar Categoria de Produtos</h2>

            <form method="post" action="../actions/produto_crud.php" style="margin-bottom: 1rem;">
                <input type="hidden" name="action" value="create_category">
                <input type="hidden" name="redirect_to" value="../pages/categorias_produto.php">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nome_categoria">Adicionar categoria</label>
                        <input type="text" id="nome_categoria" name="nome_categoria" maxlength="50" required>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;">
                        <button type="submit" class="btn btn-primary">Adicionar Categoria</button>
                    </div>
                </div>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($categoria['nome']); ?></td>
                            <td>
                                <form method="post" action="../actions/produto_crud.php" onsubmit="return confirm('Deseja excluir esta categoria?');">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="id" value="<?php echo intval($categoria['id']); ?>">
                                    <input type="hidden" name="redirect_to" value="../pages/categorias_produto.php">
                                    <button type="submit" class="btn btn-danger">Excluir Categoria</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <a href="produtos.php" class="btn btn-secondary">Voltar</a>
<?php require_once 'footer.php'; ?>
