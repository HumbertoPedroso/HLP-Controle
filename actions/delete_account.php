<?php
// actions/delete_account.php - exclui a conta do usuario logado
require_once '../config/auth.php';
ensure_session_started();
require_login('../pages/login.php');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/perfil.php');
    exit;
}

$user_id = intval($_SESSION['user_id'] ?? 0);
$password_confirm = $_POST['password_confirm'] ?? '';

if ($user_id <= 0 || $password_confirm === '') {
    $_SESSION['message_error'] = 'Informe sua senha para excluir a conta.';
    header('Location: ../pages/perfil.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $stored_password = $stmt->fetchColumn();

    if (!$stored_password) {
        $_SESSION['message_error'] = 'Conta nao encontrada.';
        header('Location: ../pages/perfil.php');
        exit;
    }

    $valid_password = false;
    if (password_verify($password_confirm, $stored_password)) {
        $valid_password = true;
    } elseif (hash_equals($stored_password, $password_confirm)) {
        $valid_password = true;
    }

    if (!$valid_password) {
        $_SESSION['message_error'] = 'Senha incorreta.';
        header('Location: ../pages/perfil.php');
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    session_start();
    $_SESSION['message'] = 'Conta excluída com sucesso.';
    header('Location: ../pages/login.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['message_error'] = 'Erro ao excluir conta.';
    header('Location: ../pages/perfil.php');
    exit;
}
