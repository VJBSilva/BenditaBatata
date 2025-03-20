<?php
// Inclui o arquivo de conexão com o banco de dados
require 'conexao.php';

// Supondo que você tenha o ID do pedido
$pedido_id = 1; // Substitua pelo ID do pedido que você deseja imprimir

// Buscar os dados do pedido
$stmt = $pdo->prepare("
    SELECT p.*, ip.*, pr.nome AS produto_nome, pr.preco, c.nome AS categoria_nome, a.nome AS adicional_nome
    FROM pedidos p
    LEFT JOIN itens_pedido ip ON p.id = ip.pedido_id
    LEFT JOIN produtos pr ON ip.produto_id = pr.id
    LEFT JOIN categorias c ON pr.categoria_id = c.id
    LEFT JOIN itens_pedido_adicionais ipa ON ip.id = ipa.item_pedido_id
    LEFT JOIN adicionais a ON ipa.adicional_id = a.id
    WHERE p.id = :pedido_id
");
$stmt->execute(['pedido_id' => $pedido_id]);
$pedido = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verifica se o pedido foi encontrado
if (empty($pedido)) {
    die("Pedido não encontrado.");
}

// Organizar os dados do pedido
$dados_pedido = [
    'senha' => $pedido[0]['senha'],
    'observacao' => $pedido[0]['observacao'],
    'metodo_pagamento' => $pedido[0]['metodo_pagamento'],
    'desconto' => $pedido[0]['desconto'],
    'itens' => []
];

foreach ($pedido as $item) {
    if (!isset($dados_pedido['itens'][$item['produto_id']])) {
        $dados_pedido['itens'][$item['produto_id']] = [
            'nome' => $item['produto_nome'],
            'categoria' => $item['categoria_nome'],
            'quantidade' => $item['quantidade'],
            'preco' => $item['preco'],
            'adicionais' => []
        ];
    }
    if ($item['adicional_nome']) {
        $dados_pedido['itens'][$item['produto_id']]['adicionais'][] = $item['adicional_nome'];
    }
}

// Calcular o total do pedido
$total_pedido = array_reduce($dados_pedido['itens'], function($carry, $item) {
    return $carry + ($item['quantidade'] * $item['preco']);
}, 0) - $dados_pedido['desconto'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comanda de Pedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 57mm;
            margin: 0;
            padding: 0;
        }
        .comanda {
            width: 100%;
            text-align: center;
        }
        .senha {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .item {
            margin-bottom: 5px;
        }
        .adicionais {
            font-size: 10px;
            color: #555;
        }
        .observacao {
            margin-top: 10px;
            font-style: italic;
        }
        .total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="comanda">
        <div class="senha">Senha: <?php echo htmlspecialchars($dados_pedido['senha']); ?></div>
        <?php foreach ($dados_pedido['itens'] as $item): ?>
            <div class="item">
                <strong><?php echo htmlspecialchars($item['nome']); ?> - <?php echo $item['quantidade']; ?>x</strong>
                <div class="categoria">Categoria: <?php echo htmlspecialchars($item['categoria']); ?></div>
                <?php if (!empty($item['adicionais'])): ?>
                    <div class="adicionais">Adicionais: <?php echo htmlspecialchars(implode(', ', $item['adicionais'])); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="observacao">Observação: <?php echo htmlspecialchars($dados_pedido['observacao']); ?></div>
        <div class="total">Total: R$ <?php echo number_format($total_pedido, 2); ?></div>
    </div>
</body>
</html>
