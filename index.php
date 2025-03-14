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
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .produto .controle-quantidade {
            display: flex;
            align-items: center;
        }
        .produto button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            margin: 0 5px;
        }
        .produto button:hover {
            background-color: #0056b3;
        }
        .produto input {
            width: 40px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px;
        }
        .footer {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
            text-align: right;
        }
        .footer .total {
            font-size: 1.5em;
            margin-bottom: 10px;
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>Bendita Batata</h1>

    <!-- Área Rolável para Sabores -->
    <div class="container">
        <?php
        // Função para ler produtos do arquivo
        function lerProdutos() {
            $caminhoArquivo = __DIR__ . '/admin/produtos.txt';
            if (file_exists($caminhoArquivo)) {
                $linhas = file($caminhoArquivo);
                $produtos = [];
                foreach ($linhas as $linha) {
                    $produtos[] = unserialize($linha);
                }
                return $produtos;
            }
            return [];
        }

        // Ler produtos do arquivo
        $produtos = lerProdutos();

        // Agrupar produtos por categoria
        $categorias = [];
        foreach ($produtos as $produto) {
            $categoria = $produto['categoria'];
            if (!isset($categorias[$categoria])) {
                $categorias[$categoria] = [];
            }
            $categorias[$categoria][] = $produto;
        }

        // Exibir produtos por categoria
        foreach ($categorias as $categoria => $produtosCategoria) {
            echo "<h2>{$categoria}</h2>";
            foreach ($produtosCategoria as $produto) {
                // Criar um ID único para o campo de quantidade, incluindo a categoria
                $idQuantidade = strtolower(str_replace(' ', '_', $categoria . '_' . $produto['nome']));
                echo "
                    <div class='produto' data-nome='{$produto['nome']}' data-preco='{$produto['preco']}' data-categoria='{$categoria}'>
                        <span>{$produto['nome']}</span>
                        <div class='controle-quantidade'>
                            <button onclick='diminuirQuantidade(\"{$idQuantidade}\")'>-</button>
                            <input type='text' id='{$idQuantidade}' value='0' size='2' oninput='validarQuantidade(this)'>
                            <button onclick='aumentarQuantidade(\"{$idQuantidade}\")'>+</button>
                        </div>
                    </div>
                ";
            }
        }
        ?>
    </div>

    <!-- Formulário para Nome do Cliente e Observações -->
    <div class="form-container">
        <div class="form-group">
            <label for="nomeCliente">Nome do Cliente:</label>
            <input type="text" id="nomeCliente" name="nomeCliente" placeholder="Digite o nome do cliente" required>
        </div>
        <div class="form-group">
            <label for="observacao">Observações:</label>
            <textarea id="observacao" name="observacao" placeholder="Digite observações (opcional)"></textarea>
        </div>
    </div>

    <!-- Rodapé Fixo -->
    <div class="footer">
        <div class="total">
            Total a Pagar: R$ <span id="total">0.00</span>
        </div>
        <button onclick="finalizarPedido()">Finalizar Pedido</button>
    </div>

    <script>
        // Função para aumentar a quantidade
        function aumentarQuantidade(id) {
            const input = document.getElementById(id);
            let quantidade = parseInt(input.value) || 0; // Garante que o valor seja um número
            quantidade++;
            input.value = quantidade;
            calcularTotal();
        }

        // Função para diminuir a quantidade
        function diminuirQuantidade(id) {
            const input = document.getElementById(id);
            let quantidade = parseInt(input.value) || 0; // Garante que o valor seja um número
            if (quantidade > 0) {
                quantidade--;
                input.value = quantidade;
                calcularTotal();
            }
        }

        // Função para validar a quantidade digitada (aceitar apenas números)
        function validarQuantidade(input) {
            input.value = input.value.replace(/[^0-9]/g, ''); // Remove tudo que não for número
            if (input.value === '') {
                input.value = 0; // Define como 0 se o campo estiver vazio
            }
            calcularTotal();
        }

        // Função para calcular o total a pagar
        function calcularTotal() {
            const produtos = document.querySelectorAll('.produto');
            let total = 0;

            produtos.forEach(produto => {
                const quantidadeInput = produto.querySelector('input');
                const quantidade = parseInt(quantidadeInput.value) || 0; // Garante que o valor seja um número
                const preco = parseFloat(produto.getAttribute('data-preco'));
                total += quantidade * preco;
            });

            document.getElementById('total').textContent = total.toFixed(2);
        }

        // Função para finalizar o pedido
        function finalizarPedido() {
            const produtos = document.querySelectorAll('.produto');
            const nomeCliente = document.getElementById('nomeCliente').value;
            const observacao = document.getElementById('observacao').value;

            let pedido = {
                nomeCliente: nomeCliente,
                observacao: observacao,
                itens: []
            };

            produtos.forEach(produto => {
                const nome = produto.getAttribute('data-nome');
                const quantidadeInput = produto.querySelector('input');
                const quantidade = parseInt(quantidadeInput.value) || 0; // Garante que o valor seja um número
                const categoria = produto.getAttribute('data-categoria');
                if (quantidade > 0) {
                    pedido.itens.push({ nome, quantidade, categoria });
                }
            });

            if (pedido.itens.length > 0) {
                // Salvar pedido no arquivo
                fetch('salvar_pedido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(pedido)
                })
                .then(response => response.text())
                .then(data => {
                    alert('Pedido finalizado com sucesso!');
                    console.log('Pedido:', pedido);
                })
                .catch(error => {
                    console.error('Erro ao salvar pedido:', error);
                });
            } else {
                alert('Adicione itens ao pedido antes de finalizar.');
            }
        }
    </script>
</body>
</html>