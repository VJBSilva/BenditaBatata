<?php
require 'conexao.php';

$pedidoId = $_GET['id'];

try {
    // Buscar apenas o status do pedido
    $stmt = $pdo->prepare("SELECT status FROM pedidos WHERE id = ?");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pedido) {
        // Retornar o status como JSON
        header('Content-Type: application/json');
        echo json_encode(['status' => $pedido['status']]);
    } else {
        // Pedido não encontrado
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Pedido não encontrado.']);
    }
} catch (PDOException $e) {
    // Erro no banco de dados
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar status do pedido: ' . $e->getMessage()]);
}
?>
