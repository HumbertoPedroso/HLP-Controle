<?php
// config/usuarios.php - utilitarios de estrutura da tabela usuarios

function ensure_usuarios_nome_column(PDO $pdo): void {
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'nome_usuario'");
    $coluna = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coluna) {
        $pdo->exec("ALTER TABLE usuarios ADD COLUMN nome_usuario VARCHAR(100) NULL AFTER username");
        $pdo->exec("UPDATE usuarios SET nome_usuario = username WHERE nome_usuario IS NULL OR nome_usuario = ''");
    }
}

