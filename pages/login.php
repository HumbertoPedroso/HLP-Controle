<?php
// pages/login.php - Tela de login
require_once '../config/auth.php';
ensure_session_started();

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$message_error = '';
if (isset($_SESSION['message_error'])) {
    $message_error = $_SESSION['message_error'];
    unset($_SESSION['message_error']);
}

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$css_file = __DIR__ . '/../assets/css/style.css';
$css_version = file_exists($css_file) ? filemtime($css_file) : time();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HLP Controle</title>
    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">
    <link rel="shortcut icon" href="../favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo $css_version; ?>">
</head>
<body class="login-body">
    <div class="login-container">
        <h1>HLP Controle</h1>
        <h2>Entrar no sistema</h2>

        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($message_error): ?>
            <div class="message error"><?php echo htmlspecialchars($message_error); ?></div>
        <?php endif; ?>

        <form method="post" action="../actions/login.php">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Entrar</button>
        </form>

        <hr style="margin: 1.5rem 0;">

        <h2>Criar Conta</h2>
        <form method="post" action="../actions/register.php">
            <div class="form-group">
                <label for="register_username">Usuario</label>
                <input type="text" id="register_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="register_password">Senha</label>
                <input type="password" id="register_password" name="password" minlength="4" required>
            </div>
            <div class="form-group">
                <label for="register_nome_usuario">Nome de usuario</label>
                <input type="text" id="register_nome_usuario" name="nome_usuario" required>
            </div>
            <div class="form-group">
                <label for="register_empresa_nome">Empresa</label>
                <input type="text" id="register_empresa_nome" name="empresa_nome" required>
            </div>
            <button type="submit" class="btn btn-secondary" style="width: 100%;">Criar Conta</button>
        </form>
    </div>
</body>
</html>

