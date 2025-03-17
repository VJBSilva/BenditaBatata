<?php
require 'conexao.php';

// Verifica se o usuário está logado e é um administrador
if (!isset($_COOKIE['usuario_id']) || $_COOKIE['tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $pedidoId = $data['id'];

    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET status = 'excluido' WHERE id = ?");
        $stmt->execute([$pedidoId]);

        echo json_encode(['status' => 'success', 'message' => 'Pedido excluído com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao excluir o pedido: ' . $e->getMessage()]);
    }
}
?>
