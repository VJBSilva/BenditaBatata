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
            width: 16px;
            height: 16px;
        }
        .adicional label {
            font-size: 14px;
        }
        .footer {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .footer .metodo-pagamento,
        .footer .desconto,
        .footer .total {
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .footer .metodo-pagamento label,
        .footer .desconto label {
            font-size: 1em;
        }
        .footer .metodo-pagamento input[type="radio"] {
            width: 16px;
            height: 16px;
        }
        .footer .desconto input {
            padding: 5px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 80px;
        }
        .footer button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        .footer button:hover {
            background-color: #218838;
        }
        .form-container {
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            width: 100%;
            height: 25px; /* Reduzido pela metade */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
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
                        // Carregar adicionais da categoria do produto
                        $categoria_id = $produto['categoria_id'];
                        if (isset($adicionaisPorCategoria[$categoria_id])) {
                            foreach ($adicionaisPorCategoria[$categoria_id] as $adicional): ?>
                                <div class="adicional">
                                    <input type="checkbox" id="adicional_<?= $adicional['id'] ?>_produto_<?= $produto['id'] ?>" name="adicionais[]" value="<?= $adicional['id'] ?>">
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

    <!-- Formulário para Observações e Senha -->
<div class="form-container">
    <div class="form-group" style="margin-right: 20px;"> <!-- Adicionado margin-right -->
        <label for="observacao">Observações:</label>
        <textarea id="observacao" name="observacao" placeholder="Digite observações (opcional)"></textarea>
    </div>
    <div class="form-group">
        <label for="senha">Senha do Pedido:</label>
        <input type="text" id="senha" name="senha" placeholder="Digite a senha" maxlength="5" style="width: 150px;"> <!-- Alterado tamanho e limite -->
    </div>
</div>

    <!-- Rodapé Fixo -->
    <div class="footer">
        <!-- Método de Pagamento -->
        <div class="metodo-pagamento">
            <span>Método de Pagamento:</span>
            <label><input type="radio" name="metodo_pagamento" value="cartao" checked> Cartão</label>
            <label><input type="radio" name="metodo_pagamento" value="pix"> PIX</label>
            <label><input type="radio" name="metodo_pagamento" value="dinheiro"> Dinheiro</label>
        </div>

        <!-- Desconto -->
        <div class="desconto">
            <label for="desconto">Desconto (R$):</label>
            <input type="text" id="desconto" name="desconto" value="0.00" oninput="calcularTotal()">
        </div>

        <!-- Total a Pagar -->
        <div class="total">
            Total a Pagar: R$ <span id="total">0.00</span>
        </div>

        <!-- Botão Finalizar Pedido -->
        <button onclick="finalizarPedido()">Finalizar Pedido</button>
    </div>

    <script>
        // Função para aumentar a quantidade
        function aumentarQuantidade(id) {
            const input = document.getElementById(id);
            let quantidade = parseInt(input.value) || 0;
            quantidade++;
            input.value = quantidade;
            calcularTotal();
        }

        // Função para diminuir a quantidade
        function diminuirQuantidade(id) {
            const input = document.getElementById(id);
            let quantidade = parseInt(input.value) || 0;
            if (quantidade > 0) {
                quantidade--;
                input.value = quantidade;
                calcularTotal();
            }
        }

        // Função para validar a quantidade digitada (aceitar apenas números)
        function validarQuantidade(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value === '') {
                input.value = 0;
            }
            calcularTotal();
        }

        // Função para calcular o total a pagar
        function calcularTotal() {
            const produtos = document.querySelectorAll('.produto');
            let total = 0;

            produtos.forEach(produto => {
                const quantidadeInput = produto.querySelector('input');
                const quantidade = parseInt(quantidadeInput.value) || 0;
                const preco = parseFloat(produto.getAttribute('data-preco'));
                total += quantidade * preco;
            });

            // Aplicar desconto
            const desconto = parseFloat(document.getElementById('desconto').value) || 0;
            total -= desconto;

            // Garantir que o total não seja negativo
            total = Math.max(total, 0);

            document.getElementById('total').textContent = total.toFixed(2);
        }

        // Função para finalizar o pedido
        function finalizarPedido() {
            const produtos = document.querySelectorAll('.produto');
            const observacao = document.getElementById('observacao').value;
            const senha = document.getElementById('senha').value;
            const metodoPagamento = document.querySelector('input[name="metodo_pagamento"]:checked').value;
            const desconto = parseFloat(document.getElementById('desconto').value) || 0;

            let pedido = {
                observacao: observacao,
                senha: senha,
                metodo_pagamento: metodoPagamento,
                desconto: desconto,
                itens: []
            };

            produtos.forEach(produto => {
                const produtoId = produto.getAttribute('data-id');
                const quantidadeInput = produto.querySelector('input');
                const quantidade = parseInt(quantidadeInput.value) || 0;
                if (quantidade > 0) {
                    const adicionais = produto.querySelectorAll('.adicionais input[type="checkbox"]:checked');
                    const adicionaisSelecionados = [];
                    adicionais.forEach(adicional => {
                        adicionaisSelecionados.push(adicional.value);
                    });

                    pedido.itens.push({
                        produto_id: produtoId,
                        quantidade: quantidade,
                        preco_unitario: parseFloat(produto.getAttribute('data-preco')),
                        adicionais: adicionaisSelecionados
                    });
                }
            });

            if (pedido.itens.length > 0) {
                fetch('salvar_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(pedido)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('Pedido finalizado com sucesso!');
                        console.log('Pedido:', pedido);
                        // Limpar o carrinho após finalizar o pedido
                        produtos.forEach(produto => {
                            const quantidadeInput = produto.querySelector('input');
                            quantidadeInput.value = 0;
                        });
                        document.getElementById('observacao').value = '';
                        document.getElementById('senha').value = '';
                        document.getElementById('desconto').value = '0.00';
                        calcularTotal(); // Atualizar o total para R$ 0.00
                    } else {
                        alert('Erro ao salvar o pedido: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao salvar pedido:', error);
                    alert('Erro ao salvar o pedido. Verifique o console para mais detalhes.');
                });
            } else {
                alert('Adicione itens ao pedido antes de finalizar.');
            }
        }
    </script>
</body>
</html>