<?php
require 'conexao.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $pedidoId = $data['id'];
    $observacao = $data['observacao'];
    $senha = $data['senha'];
    $desconto = $data['desconto'];
    $metodoPagamento = $data['metodo_pagamento'];
    $itens = $data['itens'];

    try {
        // Iniciar uma transação
        $pdo->beginTransaction();

        // Atualizar os dados gerais do pedido
        $stmt = $pdo->prepare("
            UPDATE pedidos
            SET observacao = ?, senha = ?, desconto = ?, metodo_pagamento = ?
            WHERE id = ?
        ");
        $stmt->execute([$observacao, $senha, $desconto, $metodoPagamento, $pedidoId]);

        // Excluir os adicionais dos itens antigos
        $stmt = $pdo->prepare("
            DELETE itens_pedido_adicionais
            FROM itens_pedido_adicionais
            JOIN itens_pedido ON itens_pedido_adicionais.item_pedido_id = itens_pedido.id
            WHERE itens_pedido.pedido_id = ?
        ");
        $stmt->execute([$pedidoId]);

        // Excluir os itens antigos do pedido
        $stmt = $pdo->prepare("DELETE FROM itens_pedido WHERE pedido_id = ?");
        $stmt->execute([$pedidoId]);

        // Inserir os novos itens do pedido
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

            // Inserir os adicionais do item
            foreach ($item['adicionais'] as $adicionalId) {
                $stmt = $pdo->prepare("
                    INSERT INTO itens_pedido_adicionais (item_pedido_id, adicional_id)
                    VALUES (?, ?)
                ");
                $stmt->execute([$itemPedidoId, $adicionalId]);
            }
        }

        // Commit da transação
        $pdo->commit();

        echo json_encode(['status' => 'success', 'message' => 'Pedido alterado com sucesso!']);
    } catch (Exception $e) {
        // Rollback em caso de erro
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao alterar o pedido: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados do pedido não recebidos.']);
}
?>