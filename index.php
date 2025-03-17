<?php
require 'conexao.php';

// Carregar produtos com categorias
$stmt = $pdo->query("
    SELECT produtos.*, categorias.nome AS categoria_nome
    FROM produtos
    JOIN categorias ON produtos.categoria_id = categorias.id
");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar produtos por categoria
$produtosPorCategoria = [];
foreach ($produtos as $produto) {
    $categoria = $produto['categoria_nome'];
    if (!isset($produtosPorCategoria[$categoria])) {
        $produtosPorCategoria[$categoria] = [];
    }
    $produtosPorCategoria[$categoria][] = $produto;
}

// Carregar adicionais por categoria
$adicionaisPorCategoria = [];
$stmt = $pdo->query("
    SELECT categoria_adicionais.categoria_id, adicionais.*
    FROM categoria_adicionais
    JOIN adicionais ON categoria_adicionais.adicional_id = adicionais.id
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $categoria_id = $row['categoria_id'];
    if (!isset($adicionaisPorCategoria[$categoria_id])) {
        $adicionaisPorCategoria[$categoria_id] = [];
    }
    $adicionaisPorCategoria[$categoria_id][] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Bendita Batata</title>
    <style>
        /* Estilos gerais */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        h1 {
            text-align: center;
            color: #333;
            background-color: #fff;
            padding: 20px;
            margin: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #555;
            margin-top: 10px;
            padding: 0 20px;
        }
        .container {
            flex: 1;
            overflow-y: auto;
            padding: 0 20px;
        }
        .produto {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .linha-superior {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .controle-quantidade {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .produto button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .produto button:hover {
            background-color: #0056b3;
        }
        .produto input {
            width: 20px;
            height: 20px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px;
            font-size: 14px;
        }
        .adicionais {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .adicional {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .adicional input[type="checkbox"] {
            appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid #28a745;
            border-radius: 3px;
            cursor: pointer;
            position: relative;
        }
        .adicional input[type="checkbox"]:checked {
            background-color: #28a745;
        }
        .adicional input[type="checkbox"]:checked::after {
            content: '✔';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
        }
        .adicional input[type="checkbox"]:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .footer {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 20px;
            position: sticky;
            bottom: 0;
        }
        .form-container {
            display: flex;
            gap: 20px;
            padding: 0 20px;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            width: 100%;
            height: 25px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        .form-group input[type="text"] {
            width: 150px; /* Tamanho fixo para o campo de senha */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        .metodo-pagamento {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .opcoes-pagamento {
            display: flex;
            gap: 10px;
        }
        .desconto-total {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .desconto label,
        .total {
            font-size: 1em;
        }
        .desconto input {
            padding: 5px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 80px;
        }
        .finalizar-pedido {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        .finalizar-pedido:hover {
            background-color: #218838;
        }
        @media (max-width: 768px) {
            .footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .form-container {
                flex-direction: column;
                gap: 10px;
            }
            .form-group textarea {
                width: 100%;
            }
            .form-group input[type="text"] {
                width: 100%; /* Campo de senha ocupa toda a largura em mobile */
            }
            .metodo-pagamento {
                width: 100%;
            }
            .desconto-total {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .finalizar-pedido {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <h1>Bendita Batata</h1>

    <!-- Área Rolável para Sabores -->
    <div class="container">
        <?php foreach ($produtosPorCategoria as $categoria => $produtos): ?>
            <h2><?= $categoria ?></h2>
            <?php foreach ($produtos as $produto): ?>
                <div class="produto" data-id="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>">
                    <div class="linha-superior">
                        <span><?= $produto['nome'] ?></span>
                        <div class="controle-quantidade">
                            <button onclick="diminuirQuantidade('produto_<?= $produto['id'] ?>')">-</button>
                            <input type="text" id="produto_<?= $produto['id'] ?>" value="0" size="2" oninput="validarQuantidade(this)">
                            <button onclick="aumentarQuantidade('produto_<?= $produto['id'] ?>')">+</button>
                        </div>
                    </div>
                    <div class="adicionais">
                        <?php
                        $categoria_id = $produto['categoria_id'];
                        if (isset($adicionaisPorCategoria[$categoria_id])) {
                            foreach ($adicionaisPorCategoria[$categoria_id] as $adicional): ?>
                                <div class="adicional">
                                    <input type="checkbox" id="adicional_<?= $adicional['id'] ?>_produto_<?= $produto['id'] ?>" name="adicionais[]" value="<?= $adicional['id'] ?>" disabled>
                                    <label for="adicional_<?= $adicional['id'] ?>_produto_<?= $produto['id'] ?>"><?= $adicional['nome'] ?></label>
                                </div>
                            <?php endforeach;
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- Rodapé Fixo -->
    <div class="footer">
        <!-- Formulário para Observações e Senha -->
        <div class="form-container">
            <div class="form-group">
                <label for="observacao">Observações:</label>
                <textarea id="observacao" name="observacao" placeholder="Digite observações (opcional)"></textarea>
            </div>
            <div class="form-group">
                <label for="senha">Senha do Pedido:</label>
                <input type="text" id="senha" name="senha" placeholder="Digite a senha" maxlength="5">
            </div>
        </div>

        <!-- Método de Pagamento -->
        <div class="metodo-pagamento">
            <span>Método de Pagamento:</span>
            <div class="opcoes-pagamento">
                <label><input type="radio" name="metodo_pagamento" value="cartao" checked> Cartão</label>
                <label><input type="radio" name="metodo_pagamento" value="pix"> PIX</label>
                <label><input type="radio" name="metodo_pagamento" value="dinheiro"> Dinheiro</label>
            </div>
        </div>

        <!-- Desconto e Total -->
        <div class="desconto-total">
            <div class="desconto">
                <label for="desconto">Desconto (R$):</label>
                <input type="text" id="desconto" name="desconto" value="0.00" oninput="validarDesconto(this)">
            </div>
            <div class="total">
                Total a Pagar: R$ <span id="total">0.00</span>
            </div>
        </div>

        <!-- Botão Finalizar Pedido -->
        <button class="finalizar-pedido" onclick="finalizarPedido()">Finalizar Pedido</button>
    </div>

    <script>
        // Funções JavaScript (mantidas iguais ao código anterior)
        // ...
    </script>
</body>
</html>
