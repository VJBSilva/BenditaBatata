<?php
require 'conexao.php';
verificarLogin(); // Verifica se o usuário está logado

// Lógica para salvar/editar produto
if (isset($_POST['salvar'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $categoria_id = $_POST['categoria_id'];
    $preco = $_POST['preco'];

    if ($id) {
        // Editar produto existente
        $stmt = $pdo->prepare("UPDATE produtos SET nome = ?, categoria_id = ?, preco = ? WHERE id = ?");
        $stmt->execute([$nome, $categoria_id, $preco, $id]);
    } else {
        // Cadastrar novo produto
        $stmt = $pdo->prepare("INSERT INTO produtos (nome, categoria_id, preco) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $categoria_id, $preco]);
        $id = $pdo->lastInsertId();
    }
}

// Lógica para excluir produto
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
}

// Carregar produtos
$stmt = $pdo->query("SELECT * FROM produtos");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carregar categorias
$stmt = $pdo->query("SELECT * FROM categorias");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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
            <select id="categoria_id" name="categoria_id" required>
                <option value="">Selecione a Categoria</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>"><?= $categoria['nome'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="nome" name="nome" placeholder="Nome do Produto" required>
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
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= $produto['id'] ?></td>
                        <td><?= $produto['nome'] ?></td>
                        <td><?= $categorias[array_search($produto['categoria_id'], array_column($categorias, 'id'))]['nome'] ?></td>
                        <td>R$ <?= number_format($produto['preco'], 2, ',', '.') ?></td>
                        <td class='actions'>
                            <a href='?editar=<?= $produto['id'] ?>'><button>Editar</button></a>
                            <a href='?excluir=<?= $produto['id'] ?>'><button class='delete'>Excluir</button></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Lógica para carregar dados no formulário ao editar
    if (isset($_GET['editar'])) {
        $id = $_GET['editar'];
        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "
            <script>
                document.getElementById('id').value = '{$produto['id']}';
                document.getElementById('nome').value = '{$produto['nome']}';
                document.getElementById('categoria_id').value = '{$produto['categoria_id']}';
                document.getElementById('preco').value = '{$produto['preco']}';
            </script>
        ";
    }
    ?>
</body>
</html>
