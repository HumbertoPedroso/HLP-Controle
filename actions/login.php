<?php
// actions/login.php - Processa autenticacao
require_once '../config/auth.php';
require_once '../config/db.php';
require_once '../config/usuarios.php';
ensure_session_started();
ensure_usuarios_nome_column($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['message_error'] = 'Informe usuario e senha.';
    header('Location: ../pages/login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("\n        SELECT u.id, u.username, u.nome_usuario, u.password, u.role, u.ativo, u.empresa_id, e.nome AS empresa_nome\n        FROM usuarios u\n        LEFT JOIN empresas e ON e.id = u.empresa_id\n        WHERE u.username = ?\n        LIMIT 1\n    ");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['message_error'] = 'Usuario ou senha invalidos.';
        header('Location: ../pages/login.php');
        exit;
    }

    if (intval($user['ativo']) !== 1) {
        $_SESSION['message_error'] = 'Acesso bloqueado. Procure o administrador.';
        header('Location: ../pages/login.php');
        exit;
    }

    $stored_password = $user['password'];
    $valid_password = false;

    if (password_verify($password, $stored_password)) {
        $valid_password = true;
    } elseif (hash_equals($stored_password, $password)) {
        $valid_password = true;
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $update->execute([$new_hash, $user['id']]);
    }

    if (!$valid_password) {
        $_SESSION['message_error'] = 'Usuario ou senha invalidos.';
        header('Location: ../pages/login.php');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['nome_usuario'] = $user['nome_usuario'] ?: $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['empresa_id'] = intval($user['empresa_id'] ?? 1);
    $_SESSION['empresa_nome'] = $user['empresa_nome'] ?: 'Empresa Padrão';
    $_SESSION['message'] = 'Login realizado com sucesso.';

    header('Location: ../pages/dashboard.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['message_error'] = 'Erro ao autenticar. Tente novamente.';
    header('Location: ../pages/login.php');
    exit;
}
