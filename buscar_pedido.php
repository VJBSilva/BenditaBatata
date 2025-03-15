<?php
require 'conexao.php';

$pedidoId = $_GET['id'];

error_log("Buscando pedido ID: $pedidoId"); // Log para depuração

try {
    // Buscar os dados do pedido
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = 1");
    $stmt->execute([$pedidoId]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pedido) {
        error_log("Pedido encontrado: " . print_r($pedido, true)); // Log para depuração

        // Buscar os itens do pedido
        $stmt = $pdo->prepare("
            SELECT itens_pedido.*, produtos.nome AS produto_nome, GROUP_CONCAT(itens_pedido_adicionais.adicional_id) AS adicionais
            FROM itens_pedido
            JOIN produtos ON itens_pedido.produto_id = produtos.id
            LEFT JOIN itens_pedido_adicionais ON itens_pedido.id = itens_pedido_adicionais.item_pedido_id
            WHERE itens_pedido.pedido_id = ?
            GROUP BY itens_pedido.id
        ");
        $stmt->execute([$pedidoId]);
        $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Itens do pedido: " . print_r($itens, true)); // Log para depuração

        // Formatar os adicionais como array
        foreach ($itens as &$item) {
            $item['adicionais'] = $item['adicionais'] ? explode(',', $item['adicionais']) : [];
        }

        // Adicionar os itens ao array do pedido
        $pedido['itens'] = $itens;

        // Retornar os dados como JSON
        header('Content-Type: application/json');
        echo json_encode($pedido);
    } else {
        error_log("Pedido não encontrado."); // Log para depuração
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Pedido não encontrado.']);
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do pedido: " . $e->getMessage()); // Log para depuração
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar dados do pedido: ' . $e->getMessage()]);
}
?>
