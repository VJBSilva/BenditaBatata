<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodifica o corpo da requisição JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'])) {
        $idPedido = $data['id'];

        try {
            $stmt = $pdo->prepare("UPDATE pedidos SET status = 'finalizado' WHERE id = ?");
            $stmt->execute([$idPedido]);

            echo "Pedido finalizado com sucesso!";
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Erro ao finalizar o pedido: " . $e->getMessage();
        }
    } else {
        http_response_code(400);
        echo "Erro: ID do pedido não recebido.";
    }
} else {
    http_response_code(405);
    echo "Método não permitido.";
}
?>