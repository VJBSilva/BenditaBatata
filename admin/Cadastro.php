<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bendita Batata</title>
    <style>
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
        .form-container {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .form-container button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        .table-container {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
        }
        .actions button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }
        .actions button:hover {
            background-color: #0056b3;
        }
        .actions button.delete {
            background-color: #dc3545;
        }
        .actions button.delete:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <h1>Admin - Bendita Batata</h1>

    <!-- Formulário de Cadastro/Edição -->
    <div class="form-container">
        <h2>Cadastrar/Editar Produto</h2>
        <form method="POST" action="">
            <input type="hidden" id="id" name="id">
            
            <!-- Categoria (agora antes do nome) -->
            <select id="categoria" name="categoria" required>
                <option value="">Selecione a Categoria</option>
                <option value="Batata Recheada">Batata Recheada</option>
                <option value="Caldos">Caldos</option>
            </select>

            <!-- Nome do Produto -->
            <input type="text" id="nome" name="nome" placeholder="Nome do Produto" required>

            <!-- Preço -->
            <input type="number" id="preco" name="preco" placeholder="Preço" step="0.01" required>

            <button type="submit" name="salvar">Salvar</button>
        </form>
    </div>

    <!-- Tabela de Produtos -->
    <div class="table-container">
        <h2>Lista de Produtos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Função para ler produtos do arquivo
                function lerProdutos() {
                    $caminhoArquivo = __DIR__ . '/produtos.txt';
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

                // Função para salvar produtos no arquivo
                function salvarProdutos($produtos) {
                    $caminhoArquivo = __DIR__ . '/produtos.txt';
                    $conteudo = '';
                    foreach ($produtos as $produto) {
                        $conteudo .= serialize($produto) . "\n";
                    }
                    file_put_contents($caminhoArquivo, $conteudo);
                }

                // Lógica para salvar/editar produto
                if (isset($_POST['salvar'])) {
                    $id = $_POST['id'];
                    $nome = $_POST['nome'];
                    $categoria = $_POST['categoria'];
                    $preco = $_POST['preco'];

                    $produtos = lerProdutos();

                    if ($id) {
                        // Editar produto existente
                        foreach ($produtos as &$produto) {
                            if ($produto['id'] == $id) {
                                $produto['nome'] = $nome;
                                $produto['categoria'] = $categoria;
                                $produto['preco'] = $preco;
                                break;
                            }
                        }
                    } else {
                        // Cadastrar novo produto
                        $novoId = count($produtos) + 1;
                        $produtos[] = [
                            'id' => $novoId,
                            'nome' => $nome,
                            'categoria' => $categoria,
                            'preco' => $preco
                        ];
                    }

                    salvarProdutos($produtos);
                }

                // Lógica para excluir produto
                if (isset($_GET['excluir'])) {
                    $id = $_GET['excluir'];
                    $produtos = lerProdutos();
                    $produtos = array_filter($produtos, function($produto) use ($id) {
                        return $produto['id'] != $id;
                    });
                    salvarProdutos($produtos);
                }

                // Exibir produtos na tabela
                $produtos = lerProdutos();
                foreach ($produtos as $produto) {
                    echo "
                        <tr>
                            <td>{$produto['id']}</td>
                            <td>{$produto['nome']}</td>
                            <td>{$produto['categoria']}</td>
                            <td>R$ {$produto['preco']}</td>
                            <td class='actions'>
                                <a href='?editar={$produto['id']}'><button>Editar</button></a>
                                <a href='?excluir={$produto['id']}'><button class='delete'>Excluir</button></a>
                            </td>
                        </tr>
                    ";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php
    // Lógica para carregar dados no formulário ao editar
    if (isset($_GET['editar'])) {
        $id = $_GET['editar'];
        $produtos = lerProdutos();
        foreach ($produtos as $produto) {
            if ($produto['id'] == $id) {
                echo "
                    <script>
                        document.getElementById('id').value = '{$produto['id']}';
                        document.getElementById('nome').value = '{$produto['nome']}';
                        document.getElementById('categoria').value = '{$produto['categoria']}';
                        document.getElementById('preco').value = '{$produto['preco']}';
                    </script>
                ";
                break;
            }
        }
    }
    ?>
</body>
</html>