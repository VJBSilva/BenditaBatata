<?php
require 'conexao.php';

if (isset($_POST['id'])) {
    $idPedido = $_POST['id'];

    $stmt = $pdo->prepare("UPDATE pedidos SET status = 'finalizado' WHERE id = ?");
    $stmt->execute([$idPedido]);

    echo "Pedido finalizado com sucesso!";
} else {
    http_response_code(400);
    echo "Erro: ID do pedido não recebido.";
}
?>