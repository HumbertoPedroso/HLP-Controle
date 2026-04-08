<?php
// config/auth.php - Funcoes de autenticacao e sessao

function ensure_session_started() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function is_logged_in() {
    ensure_session_started();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login($login_path = '../pages/login.php') {
    ensure_session_started();

    if (!is_logged_in()) {
        $_SESSION['message_error'] = 'Faca login para acessar o sistema.';
        header('Location: ' . $login_path);
        exit;
    }
}

function require_login_json() {
    ensure_session_started();

    if (!is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Nao autenticado']);
        exit;
    }
}

function is_admin(): bool {
    ensure_session_started();
    return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
}

function require_admin($redirect_path = '../pages/dashboard.php') {
    ensure_session_started();
    if (!is_admin()) {
        $_SESSION['message_error'] = 'Acesso restrito ao administrador.';
        header('Location: ' . $redirect_path);
        exit;
    }
}
