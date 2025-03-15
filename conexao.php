<?php
$host = 'vjbsdb.database.windows.net'; // Substitua pelo nome do seu servidor
$dbname = 'vjbsdb'; // Nome do banco de dados
$username = 'vjbsdb'; // Nome de usuário do administrador
$password = '@Aberto09'; // Senha do administrador

try {
    $pdo = new PDO("sqlsrv:Server=$host;Database=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>
