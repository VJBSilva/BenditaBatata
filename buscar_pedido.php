<?php
// Simula um pedido fictício
$pedidoId = $_GET['id'];

// Dados fictícios do pedido
$pedidoFicticio = [
    "id" => $pedidoId,
    "observacao" => "Pedido de teste",
    "senha" => "123",
    "desconto" => "0.00",
    "metodo_pagamento" => "cartao",
    "status" => "pendente",
    "itens" => [
        [
            "id" => 1,
            "pedido_id" => $pedidoId,
            "produto_id" => 1,
            "quantidade" => 2,
            "valor_unitario" => "38.00",
            "produto_nome" => "Frango",
            "adicionais" => [1, 2] // IDs dos adicionais
        ],
        [
            "id" => 2,
            "pedido_id" => $pedidoId,
            "produto_id" => 2,
            "quantidade" => 1,
            "valor_unitario" => "40.00",
            "produto_nome" => "Bacon",
            "adicionais" => [3] // IDs dos adicionais
        ]
    ]
];

// Retorna os dados fictícios como JSON
header('Content-Type: application/json');
echo json_encode($pedidoFicticio);
?>
