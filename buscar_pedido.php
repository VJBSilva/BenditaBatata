<?php
require 'conexao.php';

$pedidoId = $_GET['id'];

// Buscar os dados do pedido
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$pedidoId]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if ($pedido) {
    // Buscar os itens do pedido
    $stmt = $pdo->prepare("
        SELECT 
            itens_pedido.id, 
            itens_pedido.pedido_id, 
            itens_pedido.produto_id, 
            itens_pedido.quantidade, 
            produtos.nome AS produto_nome, 
            STRING_AGG(itens_pedido_adicionais.adicional_id, ',') AS adicionais
        FROM 
            itens_pedido
        JOIN 
            produtos ON itens_pedido.produto_id = produtos.id
        LEFT JOIN 
            itens_pedido_adicionais ON itens_pedido.id = itens_pedido_adicionais.item_pedido_id
        WHERE 
            itens_pedido.pedido_id = ?
        GROUP BY 
            itens_pedido.id, 
            itens_pedido.pedido_id, 
            itens_pedido.produto_id, 
            itens_pedido.quantidade, 
            produtos.nome
    ");
    $stmt->execute([$pedidoId]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar os adicionais como array
    foreach ($itens as &$item) {
        $item['adicionais'] = $item['adicionais'] ? explode(',', $item['adicionais']) : [];
    }

    // Adicionar os itens ao array do pedido
    $pedido['itens'] = $itens;

    // Retornar o pedido com os itens em formato JSON
    header('Content-Type: application/json');
    echo json_encode($pedido);
} else {
    // Pedido não encontrado
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Pedido não encontrado.']);
}
?>
