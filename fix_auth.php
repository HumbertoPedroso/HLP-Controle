<?php
// fix_auth.php - Corrigir autenticação MySQL
$host = 'localhost';
$username = 'root';
$password = 'humberto';

try {
    // Tentar conectar com método antigo
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password, [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Alterar método de autenticação
    $pdo->exec("ALTER USER '$username'@'localhost' IDENTIFIED WITH mysql_native_password BY '$password'");
    $pdo->exec("FLUSH PRIVILEGES");

    echo "Autenticação corrigida com sucesso! Agora você pode executar o install.php";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
    echo "Tente executar manualmente no phpMyAdmin:<br>";
    echo "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'humberto';<br>";
    echo "FLUSH PRIVILEGES;";
}
?>