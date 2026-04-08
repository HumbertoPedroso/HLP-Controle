<?php
// pages/admin_usuarios.php - painel admin de usuarios
$page_title = 'Administração de Usuários';
require_once '../config/auth.php';
require_admin('../pages/dashboard.php');
require_once 'header.php';
require_once '../config/db.php';
require_once '../config/usuarios.php';
require_once '../config/tenant.php';

ensure_usuarios_nome_column($pdo);

$usuarios = $pdo->query("
    SELECT u.id, u.username, u.nome_usuario, u.role, u.ativo, u.created_at, e.nome AS empresa_nome
    FROM usuarios u
    LEFT JOIN empresas e ON e.id = u.empresa_id
    ORDER BY u.created_at DESC
")->fetchAll();

$empresas = $pdo->query("SELECT id, nome FROM empresas ORDER BY nome")->fetchAll();
?>
            <h2>AdministraÃ§Ã£o de Usuários</h2>

            <h3>Adicionar Acesso</h3>
            <form method="post" action="../actions/admin_users.php" style="margin-bottom: 1rem;">
                <input type="hidden" name="action" value="create_user">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Login</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="nome_usuario">Nome do usuário</label>
                        <input type="text" id="nome_usuario" name="nome_usuario" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input type="password" id="password" name="password" minlength="4" required>
                    </div>
                    <div class="form-group">
                        <label for="empresa_id">Empresa</label>
                        <select id="empresa_id" name="empresa_id" required>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo intval($empresa['id']); ?>"><?php echo htmlspecialchars($empresa['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small>Ou informe uma empresa nova abaixo.</small>
                    </div>
                    <div class="form-group">
                        <label for="empresa_nome_nova">Nova empresa (opcional)</label>
                        <input type="text" id="empresa_nome_nova" name="empresa_nome_nova">
                    </div>
                    <div class="form-group">
                        <label for="role">Perfil</label>
                        <select id="role" name="role" required>
                            <option value="user">Usuário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Criar acesso</button>
            </form>

            <h3>Usuários do Site</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Login</th>
                        <th>Nome</th>
                        <th>Empresa</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?php echo intval($u['id']); ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['nome_usuario'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($u['empresa_nome'] ?: 'Empresa PadrÃ£o'); ?></td>
                            <td><?php echo htmlspecialchars($u['role']); ?></td>
                            <td><?php echo intval($u['ativo']) === 1 ? 'Ativo' : 'Bloqueado'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="post" action="../actions/admin_users.php">
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="id" value="<?php echo intval($u['id']); ?>">
                                        <button type="submit" class="btn btn-warning">
                                            <?php echo intval($u['ativo']) === 1 ? 'Bloquear' : 'Desbloquear'; ?>
                                        </button>
                                    </form>
                                    <form method="post" action="../actions/admin_users.php" onsubmit="return confirm('Excluir este usuário?');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="id" value="<?php echo intval($u['id']); ?>">
                                        <button type="submit" class="btn btn-danger">Excluir Conta</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
<?php require_once 'footer.php'; ?>

