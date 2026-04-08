<?php
// actions/update_empresa.php - atualiza nome da empresa atual
require_once '../config/auth.php';
ensure_session_started();
require_login('../pages/login.php');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/perfil.php');
    exit;
}

$empresa_id = intval($_SESSION['empresa_id'] ?? 0);
$empresa_nome = trim($_POST['empresa_nome'] ?? '');

if ($empresa_id <= 0 || $empresa_nome === '') {
    $_SESSION['message_error'] = 'Informe um nome válido para a empresa.';
    header('Location: ../pages/perfil.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE nome = ? AND id <> ?");
    $stmt->execute([$empresa_nome, $empresa_id]);
    if (intval($stmt->fetchColumn()) > 0) {
        $_SESSION['message_error'] = 'Já existe uma empresa com esse nome.';
        header('Location: ../pages/perfil.php');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE empresas SET nome = ? WHERE id = ?");
    $stmt->execute([$empresa_nome, $empresa_id]);

    $_SESSION['empresa_nome'] = $empresa_nome;
    $_SESSION['message'] = 'Nome da empresa atualizado com sucesso.';
    header('Location: ../pages/perfil.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['message_error'] = 'Erro ao atualizar nome da empresa.';
    header('Location: ../pages/perfil.php');
    exit;
}
