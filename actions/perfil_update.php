<?php
// actions/perfil_update.php - atualiza login/senha/nome
require_once '../config/auth.php';
ensure_session_started();
require_login('../pages/login.php');
require_once '../config/db.php';
require_once '../config/usuarios.php';

ensure_usuarios_nome_column($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/perfil.php');
    exit;
}

$user_id = intval($_SESSION['user_id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$nome_usuario = trim($_POST['nome_usuario'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $nome_usuario === '') {
    $_SESSION['message_error'] = 'Preencha login e nome de usuario.';
    header('Location: ../pages/perfil.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ? AND id <> ?");
    $stmt->execute([$username, $user_id]);
    if (intval($stmt->fetchColumn()) > 0) {
        $_SESSION['message_error'] = 'Esse login ja esta em uso.';
        header('Location: ../pages/perfil.php');
        exit;
    }

    if ($password !== '') {
        if (strlen($password) < 4) {
            $_SESSION['message_error'] = 'A nova senha precisa ter ao menos 4 caracteres.';
            header('Location: ../pages/perfil.php');
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET username = ?, nome_usuario = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $nome_usuario, $hash, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET username = ?, nome_usuario = ? WHERE id = ?");
        $stmt->execute([$username, $nome_usuario, $user_id]);
    }

    $_SESSION['username'] = $username;
    $_SESSION['nome_usuario'] = $nome_usuario;
    $_SESSION['message'] = 'Perfil atualizado com sucesso.';
    header('Location: ../pages/perfil.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['message_error'] = 'Erro ao atualizar perfil.';
    header('Location: ../pages/perfil.php');
    exit;
}

