<?php
// actions/register.php - cria conta de usuario
require_once '../config/db.php';
require_once '../config/usuarios.php';
require_once '../config/auth.php';
require_once '../config/tenant.php';
ensure_session_started();

ensure_usuarios_nome_column($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$nome_usuario = trim($_POST['nome_usuario'] ?? '');
$empresa_nome = trim($_POST['empresa_nome'] ?? '');

if ($username === '' || $password === '' || $nome_usuario === '' || $empresa_nome === '') {
    $_SESSION['message_error'] = 'Preencha usuario, senha, nome de usuario e empresa.';
    header('Location: ../pages/login.php');
    exit;
}

if (strlen($password) < 4) {
    $_SESSION['message_error'] = 'A senha precisa ter ao menos 4 caracteres.';
    header('Location: ../pages/login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    if (intval($stmt->fetchColumn()) > 0) {
        $_SESSION['message_error'] = 'Esse usuario ja existe.';
        header('Location: ../pages/login.php');
        exit;
    }

    $empresa_id = get_or_create_empresa_id($pdo, $empresa_nome);
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (username, nome_usuario, password, role, empresa_id, ativo) VALUES (?, ?, ?, 'user', ?, 1)");
    $stmt->execute([$username, $nome_usuario, $hash, $empresa_id]);

    $_SESSION['message'] = 'Conta criada com sucesso. Faca login.';
    header('Location: ../pages/login.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['message_error'] = 'Erro ao criar conta.';
    header('Location: ../pages/login.php');
    exit;
}
