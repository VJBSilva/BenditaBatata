<?php
require 'conexao.php';

// Função para carregar os pedidos pendentes
function carregarPedidos($pdo) {
    $stmt = $pdo->query("SELECT * FROM pedidos WHERE status = 'pendente'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para carregar os itens de um pedido
function carregarItensPedido($pdo, $pedidoId) {
    $stmt = $pdo->prepare("
        SELECT itens_pedido.*, produtos.nome, categorias.nome AS categoria_nome
        FROM itens_pedido
        JOIN produtos ON itens_pedido.produto_id = produtos.id
        JOIN categorias ON produtos.categoria_id = categorias.id
        WHERE itens_pedido.pedido_id = ?
    ");
    $stmt->execute([$pedidoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para carregar os adicionais de um item do pedido
function carregarAdicionaisItemPedido($pdo, $itemPedidoId) {
    $stmt = $pdo->prepare("
        SELECT adicionais.nome
        FROM itens_pedido_adicionais
        JOIN adicionais ON itens_pedido_adicionais.adicional_id = adicionais.id
        WHERE itens_pedido_adicionais.item_pedido_id = ?
    ");
    $stmt->execute([$itemPedidoId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Carregar produtos e adicionais (para o pop-up de alteração)
$stmt = $pdo->query("
    SELECT produtos.*, categorias.nome AS categoria_nome
    FROM produtos
    JOIN categorias ON produtos.categoria_id = categorias.id
");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$produtosPorCategoria = [];
foreach ($produtos as $produto) {
    $categoria = $produto['categoria_nome'];
    if (!isset($produtosPorCategoria[$categoria])) {
        $produtosPorCategoria[$categoria] = [];
    }
    $produtosPorCategoria[$categoria][] = $produto;
}

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

// Carregar pedidos pendentes
$pedidos = carregarPedidos($pdo);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Pedidos - Bendita Batata</title>
    <style>
        /* Estilos da página de visualização de pedidos */
        /* Estilos gerais */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        #pedidos-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .pedido {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            flex: 0 0 auto;
            width: auto;
            max-width: 100%;
            display: flex;
            flex-direction: column;
            height: auto;
        }

        .pedido h2 {
            margin-top: 0;
        }

        .pedido p {
            margin: 5px 0;
        }

        .pedido ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .pedido ul li {
            list-style-type: none;
        }

        .botoes-pedido {
            display: flex;
            gap: 5px;
            justify-content: flex-start;
            width: 100%;
            margin-top: auto;
        }

        .botoes-pedido button {
            flex: 0 0 auto;
            min-width: 56px;
            max-width: 70px;
            text-align: center;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }

        .botoes-pedido button:hover {
            background-color: #0056b3;
        }

        .botoes-pedido button.finalizado {
            background-color: #28a745;
        }

        .botoes-pedido button.finalizado:hover {
            background-color: #218838;
        }

        .botoes-pedido button.excluir {
            background-color: #dc3545;
        }

        .botoes-pedido button.excluir:hover {
            background-color: #c82333;
        }

        /* Estilos do pop-up de alteração */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 95%;
            max-width: 1200px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .popup-header {
            text-align: center;
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 20px;
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

        /* Estilos do footer (rodapé do pop-up) */
        .footer {
            background-color: #fff;
            padding: 0px;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            position: sticky;
            bottom: 0;
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

        /* Estilos para mobile */
        @media (max-width: 768px) {
            .footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 0px;
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

        /* Estilos do formulário */
        .form-container {
            padding: 5px;
            background-color: #fff;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 30px;
        }

        .form-group {
            margin-bottom: 0px;
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-group textarea {
            width: 80%;
            height: 25px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }

        .form-group input[type="text"] {
            width: 30%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }

        /* Estilo do botão de fechar pop-up */
        .close-popup {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: red;
            cursor: pointer;
        }

        .close-popup:hover {
            color: darkred;
        }
    </style>
</head>
<body>
    <h1>Visualizar Pedidos</h1>

    <!-- Contêiner para os pedidos -->
    <div id="pedidos-container">
        <?php foreach ($pedidos as $pedido): ?>
            <div class="pedido">
                <h2>Pedido <?= $pedido['id'] ?></h2>
                <p><strong>Senha:</strong> <?= $pedido['senha'] ?? 'Nenhuma' ?></p>
                <p><strong>Itens:</strong></p>
                <ul>
                    <?php
                    $itens = carregarItensPedido($pdo, $pedido['id']);
                    $itensPorCategoria = [];
                    foreach ($itens as $item) {
                        $categoria = $item['categoria_nome'];
                        if (!isset($itensPorCategoria[$categoria])) {
                            $itensPorCategoria[$categoria] = [];
                        }
                        $itensPorCategoria[$categoria][] = $item;
                    }

                    foreach ($itensPorCategoria as $categoria => $itens) {
                        echo "<li><strong>{$categoria}:</strong></li>";
                        foreach ($itens as $item) {
                            echo "<li>{$item['quantidade']}x {$item['nome']}</li>";
                            $adicionais = carregarAdicionaisItemPedido($pdo, $item['id']);
                            if (!empty($adicionais)) {
                                echo "<li>Adicionais: " . implode(", ", $adicionais) . "</li>";
                            }
                        }
                    }
                    ?>
                </ul>
                <p><strong>Observações:</strong> <?= $pedido['observacao'] ?? 'Nenhuma' ?></p>
                <div class="botoes-pedido">
                    <button onclick="abrirPopupAlterarPedido(<?= $pedido['id'] ?>)">Alterar</button>
                    <button class="finalizado" onclick="marcarComoFinalizado(<?= $pedido['id'] ?>)">Finalizar</button>
                    <button class="excluir" onclick="excluirPedido(<?= $pedido['id'] ?>)">Excluir</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pop-up para alterar pedido -->
    <div id="popup-alterar-pedido" class="popup">
        <div class="popup-content">
            <span class="close-popup" onclick="fecharPopup()">&times;</span>
            <div class="popup-header">Alterar Pedido <span id="pedido-id"></span></div>
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
            <div class="form-container">
                <div class="form-group">
                    <label for="observacao-alterar">Observações:</label>
                    <textarea id="observacao-alterar" name="observacao"></textarea>
                </div>
                <div class="form-group">
                    <label for="senha-alterar">Senha do Pedido:</label>
                    <input type="text" id="senha-alterar" name="senha" maxlength="5">
                </div>
            </div>
            <div class="footer">
                <!-- Método de Pagamento -->
                <div class="metodo-pagamento">
                    <span>Método de Pagamento:</span>
                    <div class="opcoes-pagamento">
                        <label><input type="radio" name="metodo_pagamento_alterar" value="cartao" checked> Cartão</label>
                        <label><input type="radio" name="metodo_pagamento_alterar" value="pix"> PIX</label>
                        <label><input type="radio" name="metodo_pagamento_alterar" value="dinheiro"> Dinheiro</label>
                    </div>
                </div>

                <!-- Desconto e Total -->
                <div class="desconto-total">
                    <div class="desconto">
                        <label for="desconto-alterar">Desconto (R$):</label>
                        <input type="text" id="desconto-alterar" name="desconto" value="0.00" oninput="atualizarTotal()">
                    </div>
                    <div class="total">
                        Total a Pagar: R$ <span id="total-alterar">0.00</span>
                    </div>
                </div>

                <!-- Botão Salvar Alterações -->
                <button class="finalizar-pedido" onclick="salvarAlteracoes()">Salvar Alterações</button>
            </div>
        </div>
    </div>

    <script>
        // Funções JavaScript para manipulação do pop-up e alteração do pedido
        function abrirPopupAlterarPedido(pedidoId) {
            console.log('Abrindo pop-up para o pedido:', pedidoId);

            // Limpar os dados do pop-up antes de carregar um novo pedido
            limparPopup();

            document.getElementById('pedido-id').textContent = pedidoId;
            document.getElementById('popup-alterar-pedido').style.display = 'flex';

            // Buscar os dados do pedido
            fetch(`buscar_pedido.php?id=${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        // Preencher os campos do pop-up com os dados do pedido
                        document.getElementById('observacao-alterar').value = data.observacao || '';
                        document.getElementById('senha-alterar').value = data.senha || '';
                        document.getElementById('desconto-alterar').value = data.desconto || '0.00';
                        document.querySelector(`input[name="metodo_pagamento_alterar"][value="${data.metodo_pagamento}"]`).checked = true;

                        // Preencher as quantidades e adicionais dos itens
                        if (data.itens && data.itens.length > 0) {
                            data.itens.forEach(item => {
                                const inputQuantidade = document.getElementById(`produto_${item.produto_id}`);
                                if (inputQuantidade) {
                                    inputQuantidade.value = item.quantidade;
                                }

                                // Marcar os adicionais selecionados
                                if (item.adicionais && item.adicionais.length > 0) {
                                    item.adicionais.forEach(adicionalId => {
                                        const checkbox = document.getElementById(`adicional_${adicionalId}_produto_${item.produto_id}`);
                                        if (checkbox) {
                                            checkbox.checked = true;
                                        }
                                    });
                                }
                            });

                            // Atualizar o total a pagar
                            atualizarTotal();
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar dados do pedido:', error);
                });
        }

        function limparPopup() {
            // Limpar as quantidades dos produtos
            const produtos = document.querySelectorAll('.produto input[type="text"]');
            produtos.forEach(input => {
                input.value = '0';
            });

            // Limpar os adicionais selecionados
            const adicionais = document.querySelectorAll('.adicionais input[type="checkbox"]');
            adicionais.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Limpar as observações, senha, método de pagamento e desconto
            document.getElementById('observacao-alterar').value = '';
            document.getElementById('senha-alterar').value = '';
            document.getElementById('desconto-alterar').value = '0.00';
            document.querySelector('input[name="metodo_pagamento_alterar"][value="cartao"]').checked = true;

            // Atualizar o total a pagar
            atualizarTotal();
        }

        function fecharPopup() {
            document.getElementById('popup-alterar-pedido').style.display = 'none';
        }

        function salvarAlteracoes() {
            const pedidoId = document.getElementById('pedido-id').textContent;
            const observacao = document.getElementById('observacao-alterar').value;
            const senha = document.getElementById('senha-alterar').value;
            const metodoPagamento = document.querySelector('input[name="metodo_pagamento_alterar"]:checked').value;
            const desconto = parseFloat(document.getElementById('desconto-alterar').value) || 0;

            const produtos = document.querySelectorAll('.produto');
            const itens = [];

            produtos.forEach(produto => {
                const quantidadeInput = produto.querySelector('input');
                const quantidade = parseInt(quantidadeInput.value) || 0;
                if (quantidade > 0) {
                    const adicionais = produto.querySelectorAll('.adicionais input[type="checkbox"]:checked');
                    const adicionaisSelecionados = [];
                    adicionais.forEach(adicional => {
                        adicionaisSelecionados.push(adicional.value);
                    });

                    itens.push({
                        produto_id: produto.getAttribute('data-id'),
                        quantidade: quantidade,
                        preco_unitario: parseFloat(produto.getAttribute('data-preco')),
                        adicionais: adicionaisSelecionados
                    });
                }
            });

            const dados = {
                id: pedidoId,
                observacao: observacao,
                senha: senha,
                metodo_pagamento: metodoPagamento,
                desconto: desconto,
                itens: itens
            };

            fetch('alterar_pedido.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Pedido alterado com sucesso!');
                    fecharPopup();
                    location.reload(); // Recarregar a página para atualizar a lista de pedidos
                } else {
                    alert('Erro ao alterar o pedido: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao salvar alterações:', error);
            });
        }

        function atualizarTotal() {
            const produtos = document.querySelectorAll('.produto');
            let total = 0;

            // Soma o valor total dos produtos
            produtos.forEach(produto => {
                const quantidade = parseInt(produto.querySelector('input').value) || 0;
                const preco = parseFloat(produto.getAttribute('data-preco')) || 0;
                total += quantidade * preco;
            });

            // Aplica o desconto
            const desconto = parseFloat(document.getElementById('desconto-alterar').value) || 0;
            total -= desconto;

            // Garante que o total não seja negativo
            total = Math.max(total, 0);

            // Atualiza o valor exibido
            document.getElementById('total-alterar').textContent = total.toFixed(2);
        }

        function diminuirQuantidade(id) {
            const input = document.getElementById(id);
            let valor = parseInt(input.value) || 0;
            if (valor > 0) {
                valor--;
                input.value = valor;
                atualizarTotal();
                const produtoId = id.split('_')[1];
                atualizarCheckboxes(produtoId);
            }
        }

        function aumentarQuantidade(id) {
            const input = document.getElementById(id);
            let valor = parseInt(input.value) || 0;
            valor++;
            input.value = valor;
            atualizarTotal();
            const produtoId = id.split('_')[1];
            atualizarCheckboxes(produtoId);
        }

        function validarQuantidade(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value === '') {
                input.value = 0;
            }
            atualizarTotal();
            const produtoId = input.id.split('_')[1];
            atualizarCheckboxes(produtoId);
        }

        function atualizarCheckboxes(produtoId) {
            const quantidadeInput = document.getElementById(`produto_${produtoId}`);
            const quantidade = parseInt(quantidadeInput.value) || 0;
            const checkboxes = document.querySelectorAll(`.produto[data-id="${produtoId}"] .adicionais input[type="checkbox"]`);

            checkboxes.forEach(checkbox => {
                checkbox.disabled = quantidade === 0;
                if (quantidade === 0) {
                    checkbox.checked = false; // Desmarca o checkbox se a quantidade for zero
                }
            });
        }

        function marcarComoFinalizado(pedidoId) {
            if (confirm("Tem certeza de que deseja marcar este pedido como finalizado?")) {
                const botaoFinalizar = document.querySelector(`button.finalizado[onclick="marcarComoFinalizado(${pedidoId})"]`);
                botaoFinalizar.disabled = true;
                botaoFinalizar.textContent = "Finalizando...";

                fetch('finalizar_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: pedidoId })
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                })
                .catch(error => {
                    console.error('Erro ao finalizar o pedido:', error);
                    alert('Erro ao finalizar o pedido. Verifique o console para mais detalhes.');
                })
                .finally(() => {
                    botaoFinalizar.disabled = false;
                    botaoFinalizar.textContent = "Finalizar";
                });
            }
        }

        function excluirPedido(pedidoId) {
            if (confirm("Tem certeza de que deseja excluir este pedido?")) {
                const botaoExcluir = document.querySelector(`button.excluir[onclick="excluirPedido(${pedidoId})"]`);
                botaoExcluir.disabled = true;
                botaoExcluir.textContent = "Excluindo...";

                fetch('excluir_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: pedidoId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        location.reload(); // Recarregar a página para atualizar a lista de pedidos
                    } else {
                        alert('Erro ao excluir o pedido: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao excluir o pedido:', error);
                    alert('Erro ao excluir o pedido. Verifique o console para mais detalhes.');
                })
                .finally(() => {
                    botaoExcluir.disabled = false;
                    botaoExcluir.textContent = "Excluir";
                });
            }
        }
    </script>
</body>
</html>
