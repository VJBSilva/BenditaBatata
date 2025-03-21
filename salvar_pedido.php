<?php
require 'conexao.php';

// Receber os dados do pedido
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $observacao = $data['observacao'];
    $senha = $data['senha'];
    $metodoPagamento = $data['metodo_pagamento'];
    $desconto = $data['desconto'];
    $itens = $data['itens'];

    try {
        // Iniciar uma transação
        $pdo->beginTransaction();

        // Inserir o pedido na tabela `pedidos`
        $stmt = $pdo->prepare("INSERT INTO pedidos (observacao, senha, metodo_pagamento, desconto) VALUES (?, ?, ?, ?)");
        $stmt->execute([$observacao, $senha, $metodoPagamento, $desconto]);
        $pedidoId = $pdo->lastInsertId();

        // Inserir os itens do pedido na tabela `itens_pedido`
        foreach ($itens as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, valor_unitario)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $pedidoId,
                $item['produto_id'],
                $item['quantidade'],
                $item['preco_unitario']
            ]);
            $itemPedidoId = $pdo->lastInsertId();

            // Inserir os adicionais do item na tabela `itens_pedido_adicionais`
            foreach ($item['adicionais'] as $adicionalId) {
                $stmt = $pdo->prepare("INSERT INTO itens_pedido_adicionais (item_pedido_id, adicional_id) VALUES (?, ?)");
                $stmt->execute([$itemPedidoId, $adicionalId]);
            }
        }

        // Commit da transação
        $pdo->commit();

        // Retornar uma resposta de sucesso
        echo json_encode(['status' => 'success', 'message' => 'Pedido salvo com sucesso!']);
    } catch (Exception $e) {
        // Rollback em caso de erro
        $pdo->rollBack();
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Erro ao salvar o pedido: ' . $e->getMessage()]);
    }
} else {
    // Retornar uma resposta de erro
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Dados do pedido não recebidos.']);
}
?>
