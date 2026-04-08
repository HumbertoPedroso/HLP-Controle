<?php
// pages/perfil.php - edição de perfil
$page_title = 'Meu Perfil';
require_once 'header.php';
require_once '../config/db.php';
require_once '../config/usuarios.php';

ensure_usuarios_nome_column($pdo);

$user_id = intval($_SESSION['user_id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, username, nome_usuario FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();
$empresa_id = intval($_SESSION['empresa_id'] ?? 1);
$stmt = $pdo->prepare("SELECT nome FROM empresas WHERE id = ?");
$stmt->execute([$empresa_id]);
$empresa_nome = $stmt->fetchColumn() ?: 'Empresa Padrão';

if (!$usuario) {
    $_SESSION['message_error'] = 'Usuário não encontrado.';
    header('Location: dashboard.php');
    exit;
}
?>
            <h2>Editar Perfil</h2>
            <form method="post" action="../actions/perfil_update.php">
                <div class="form-group">
                    <label for="nome_usuario">Nome de usuário</label>
                    <input type="text" id="nome_usuario" name="nome_usuario" value="<?php echo htmlspecialchars($usuario['nome_usuario'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Login (usuário)</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($usuario['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Nova senha (opcional)</label>
                    <input type="password" id="password" name="password" minlength="4">
                </div>

                <button type="submit" class="btn btn-primary">Salvar perfil</button>
            </form>

            <hr style="margin: 1.5rem 0;">
            <h3>Editar Nome da Empresa</h3>
            <form method="post" action="../actions/update_empresa.php">
                <div class="form-group">
                    <label for="empresa_nome">Nome da empresa</label>
                    <input type="text" id="empresa_nome" name="empresa_nome" value="<?php echo htmlspecialchars($empresa_nome); ?>" required>
                </div>
                <button type="submit" class="btn btn-secondary">Salvar empresa</button>
            </form>

            <hr style="margin: 1.5rem 0;">

            <h3>Excluir Conta</h3>
            <p>Esta ação remove sua conta permanentemente.</p>
            <form method="post" action="../actions/delete_account.php" onsubmit="return confirm('Tem certeza que deseja excluir sua conta?');">
                <div class="form-group">
                    <label for="password_confirm">Confirme sua senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn btn-danger">Excluir minha conta</button>
            </form>
<?php require_once 'footer.php'; ?>
