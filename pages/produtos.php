<?php
// pages/produtos.php - CRUD de produtos
$page_title = 'Produtos';
require_once 'header.php';
require_once '../config/db.php';
require_once '../config/categorias.php';

// Buscar produtos
$empresa_id = current_empresa_id();
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE empresa_id = ? ORDER BY nome");
$stmt->execute([$empresa_id]);
$produtos = $stmt->fetchAll();
$categorias = get_categorias_produto($pdo, $empresa_id);
$tem_categorias = !empty($categorias);
?>
            <h2>Gerenciar Produtos</h2>
            <div style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
                <button class="btn btn-primary" onclick="showForm()" <?php echo $tem_categorias ? '' : 'disabled'; ?>>Novo Produto</button>
                <a href="categorias_produto.php" class="btn btn-secondary">Editar Categoria de Produtos</a>
                <button class="btn btn-secondary" onclick="showCategoryForm()">Nova Categoria</button>
            </div>
            <?php if (!$tem_categorias): ?>
                <div class="message error">Crie uma categoria primeiro para poder cadastrar produtos.</div>
            <?php endif; ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Tamanho</th>
                        <th>Preço</th>
                        <th>Categoria</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <td><?php echo htmlspecialchars($produto['tamanho'] ?? '-'); ?></td>
                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($produto['categoria']); ?></td>
                            <td>
                                <button class="btn btn-secondary" onclick="editProduto(<?php echo $produto['id']; ?>)">Editar</button>
                                <button class="btn btn-danger" onclick="deleteProduto(<?php echo $produto['id']; ?>)">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Modal para criar/editar produto -->
            <div id="produtoModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h3 id="modalTitle">Novo Produto</h3>
                    <form id="produtoForm" action="../actions/produto_crud.php" method="post">
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="produtoId">
                        <div class="form-group">
                            <label for="nome">Nome:</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>
                        <div class="form-group">
                            <label for="tamanho">Tamanho:</label>
                            <input type="text" id="tamanho" name="tamanho">
                        </div>
                        <div class="form-group">
                            <label for="preco">Preço:</label>
                            <input type="number" id="preco" name="preco" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="categoria">Categoria:</label>
                            <select id="categoria" name="categoria" required>
                                <?php if ($tem_categorias): ?>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo htmlspecialchars($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">Sem categorias cadastradas</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" <?php echo $tem_categorias ? '' : 'disabled'; ?>>Salvar</button>
                    </form>
                </div>
            </div>

            <!-- Modal para criar categoria -->
            <div id="categoriaModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeCategoryModal()">&times;</span>
                    <h3>Nova Categoria</h3>
                    <form action="../actions/produto_crud.php" method="post">
                        <input type="hidden" name="action" value="create_category">
                        <div class="form-group">
                            <label for="nome_categoria">Nome da categoria:</label>
                            <input type="text" id="nome_categoria" name="nome_categoria" maxlength="50" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar Categoria</button>
                    </form>
                </div>
            </div>
<?php require_once 'footer.php'; ?>
