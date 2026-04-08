<?php
// actions/admin_users.php - acoes administrativas de usuarios
require_once '../config/auth.php';
ensure_session_started();
require_login('../pages/login.php');
require_admin('../pages/dashboard.php');
require_once '../config/db.php';
require_once '../config/usuarios.php';
require_once '../config/tenant.php';

ensure_usuarios_nome_column($pdo);

$action = $_POST['action'] ?? '';
$current_user_id = intval($_SESSION['user_id'] ?? 0);

switch ($action) {
    case 'create_user':
        $username = trim($_POST['username'] ?? '');
        $nome_usuario = trim($_POST['nome_usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $empresa_id = intval($_POST['empresa_id'] ?? 0);
        $empresa_nome_nova = trim($_POST['empresa_nome_nova'] ?? '');
        $role = $_POST['role'] ?? 'user';

        if ($username === '' || $nome_usuario === '' || $password === '') {
            $_SESSION['message_error'] = 'Preencha todos os campos para criar acesso.';
            break;
        }
        if (!in_array($role, ['admin', 'user'], true)) {
            $_SESSION['message_error'] = 'Perfil inválido.';
            break;
        }
        if (strlen($password) < 4) {
            $_SESSION['message_error'] = 'Senha precisa ter ao menos 4 caracteres.';
            break;
        }

        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            if (intval($stmt->fetchColumn()) > 0) {
                $_SESSION['message_error'] = 'Login já existe.';
                break;
            }

            if ($empresa_nome_nova !== '') {
                $empresa_id = get_or_create_empresa_id($pdo, $empresa_nome_nova);
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE id = ?");
                $stmt->execute([$empresa_id]);
                if (intval($stmt->fetchColumn()) === 0) {
                    $_SESSION['message_error'] = 'Empresa inválida.';
                    break;
                }
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (username, nome_usuario, password, role, empresa_id, ativo) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$username, $nome_usuario, $hash, $role, $empresa_id]);
            $_SESSION['message'] = 'Acesso criado com sucesso.';
        } catch (PDOException $e) {
            $_SESSION['message_error'] = 'Erro ao criar acesso.';
        }
        break;

    case 'toggle_active':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['message_error'] = 'Usuário inválido.';
            break;
        }
        if ($id === $current_user_id) {
            $_SESSION['message_error'] = 'VocÃª não pode bloquear seu prÃ³prio usuário.';
            break;
        }
        try {
            $stmt = $pdo->prepare("UPDATE usuarios SET ativo = CASE WHEN ativo = 1 THEN 0 ELSE 1 END WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = 'Status de acesso atualizado.';
        } catch (PDOException $e) {
            $_SESSION['message_error'] = 'Erro ao alterar status.';
        }
        break;

    case 'delete_user':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['message_error'] = 'Usuário inválido.';
            break;
        }
        if ($id === $current_user_id) {
            $_SESSION['message_error'] = 'VocÃª não pode excluir seu prÃ³prio usuário aqui.';
            break;
        }
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['message'] = 'Conta excluída com sucesso.';
        } catch (PDOException $e) {
            $_SESSION['message_error'] = 'Erro ao excluir conta.';
        }
        break;
}

header('Location: ../pages/admin_usuarios.php');
exit;

