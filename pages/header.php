<?php
// includes/header.php - Menu superior
require_once '../config/auth.php';
require_login('login.php');

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$message_error = '';
if (isset($_SESSION['message_error'])) {
    $message_error = $_SESSION['message_error'];
    unset($_SESSION['message_error']);
}

$css_file = __DIR__ . '/../assets/css/style.css';
$css_version = file_exists($css_file) ? filemtime($css_file) : time();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Sistema HLP Controle'; ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">
    <link rel="shortcut icon" href="../favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo $css_version; ?>">
</head>
<body><header class="header">
        <div class="container">
            <h1 class="logo">HLP Controle</h1>
            <nav class="nav">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="produtos.php" class="nav-link">Produtos</a>
                <a href="relacao_produtos.php" class="nav-link">Relação Produtos</a>
                <div class="nav-item">
                    <a href="pedidos_central.php" class="nav-link">Pedidos</a>
                </div>
                <a href="perfil.php" class="nav-link">Perfil</a>
                <?php if (is_admin()): ?>
                    <a href="admin_usuarios.php" class="nav-link">Admin</a>
                <?php endif; ?>
                <a href="../actions/logout.php" class="nav-link logout">Sair</a>
            </nav>
        </div>
    </header><main class="main">
        <div class="container">
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($message_error): ?>
                <div class="message error"><?php echo $message_error; ?></div>
            <?php endif; ?>

