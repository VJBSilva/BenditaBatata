<?php
session_start();
require 'conexao.php'; // Arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // Busca o usuário no banco de dados
    $stmt = $pdo->prepare("SELECT id, senha FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se o usuário existe e se a senha está correta
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Login bem-sucedido: define o cookie
        setcookie('usuario_id', $usuario['id'], time() + (86400 * 30), "/"); // Cookie válido por 30 dias
        header("Location: menu.php");
        exit();
    } else {
        // Login falhou
        header("Location: index.php?erro=Credenciais inválidas");
        exit();
    }
}
?>
