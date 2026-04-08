<?php
// install.php - Script de instalaÃ§Ã£o do banco de dados
require_once 'config/db.php';

// Tentar conectar sem especificar banco
try {
    $pdo_install = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo_install->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar banco se não existir
    $pdo_install->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo "Banco de dados '$dbname' criado/verificado com sucesso.<br>";

    // Selecionar banco
    $pdo_install->exec("USE `$dbname`");

    // Criar tabelas
    $sql = file_get_contents('create_tables.sql');
    // Remover a linha CREATE DATABASE e USE
    $sql = preg_replace('/CREATE DATABASE.*;\s*USE.*;\s*/s', '', $sql);

    // Executar o SQL
    $pdo_install->exec($sql);

    echo "Tabelas criadas com sucesso!<br>";
    echo "InstalaÃ§Ã£o concluÃ­da. VocÃª pode acessar o sistema em: <a href='index.php'>index.php</a><br>";
    echo "Usuário: admin<br>Senha: admin123";

} catch (PDOException $e) {
    die("Erro na instalaÃ§Ã£o: " . $e->getMessage());
}
?>
