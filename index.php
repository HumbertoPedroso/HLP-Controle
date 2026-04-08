<?php
// index.php - Página inicial, verifica autenticação
require_once 'config/auth.php';
ensure_session_started();

if (!isset($_SESSION['user_id'])) {
    header('Location: pages/login.php');
    exit;
}

header('Location: pages/dashboard.php');
exit;
?>
