<?php
require 'conexao.php';

// Verifica se o usuário está logado e é um administrador
if (!isset($_COOKIE['usuario_id']) || $_COOKIE['tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Lógica para salvar/editar adicional
if (isset($_POST['salvar'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $status = $_POST['status'];

    // Verifica se o adicional já existe (apenas para cadastro, não para edição)
    if (!$id) {
        $stmt = $pdo->prepare("SELECT id FROM adicionais WHERE nome = ?");
        $stmt->execute([$nome]);
        $adicionalExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($adicionalExistente) {
            echo "<script>alert('Adicional já cadastrado!');</script>";
            exit();
        }
    }

    if ($id) {
        // Editar adicional existente
        $stmt = $pdo->prepare("UPDATE adicionais SET nome = ?, status = ? WHERE id = ?");
        $stmt->execute([$nome, $status, $id]);
    } else {
        // Cadastrar novo adicional
        $stmt = $pdo->prepare("INSERT INTO adicionais (nome, status) VALUES (?, ?)");
        $stmt->execute([$nome, $status]);
        $id = $pdo->lastInsertId();
    }

    // Redirecionar para evitar reenvio do formulário
    header("Location: cadastro_adicionais.php");
    exit();
}

// Lógica para excluir adicional
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM adicionais WHERE id = ?");
    $stmt->execute([$id]);

    // Redirecionar para evitar reenvio do formulário
    header("Location: cadastro_adicionais.php");
    exit();
}

// Carregar adicionais
$stmt = $pdo->query("SELECT * FROM adicionais");
$adicionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Cadastro de Adicionais</title>
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

        /* Estilo do overlay de loading */
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        #loadingOverlay div {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <h1>Admin - Cadastro de Adicionais</h1>

    <!-- Overlay de Loading -->
    <div id="loadingOverlay">
        <div>
            <p>Salvando...</p>
            <div class="loader"></div>
        </div>
    </div>

    <!-- Formulário de Cadastro/Edição -->
    <div class="form-container">
        <h2>Cadastrar/Editar Adicional</h2>
        <form id="formAdicional" method="POST" action="">
            <input type="hidden" id="id" name="id">
            <input type="text" id="nome" name="nome" placeholder="Nome do Adicional" required>
            <select id="status" name="status" required>
                <option value="ativo">Ativo</option>
                <option value="inativo">Inativo</option>
            </select>
            <button type="submit" name="salvar">Salvar</button>
        </form>
    </div>

    <!-- Tabela de Adicionais -->
    <div class="table-container">
        <h2>Lista de Adicionais</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adicionais as $adicional): ?>
                    <tr>
                        <td><?= $adicional['id'] ?></td>
                        <td><?= $adicional['nome'] ?></td>
                        <td><?= $adicional['status'] ?></td>
                        <td class='actions'>
                            <a href='?editar=<?= $adicional['id'] ?>'><button>Editar</button></a>
                            <a href='?excluir=<?= $adicional['id'] ?>'><button class='delete'>Excluir</button></a>
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
        $stmt = $pdo->prepare("SELECT * FROM adicionais WHERE id = ?");
        $stmt->execute([$id]);
        $adicional = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "
            <script>
                document.getElementById('id').value = '{$adicional['id']}';
                document.getElementById('nome').value = '{$adicional['nome']}';
                document.getElementById('status').value = '{$adicional['status']}';
            </script>
        ";
    }
    ?>

    <script>
        // Exibe o loading ao enviar o formulário
        document.getElementById('formAdicional').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').style.display = 'flex'; // Exibe o loading
        });

        // Oculta o loading após o envio do formulário
        window.addEventListener('load', function() {
            document.getElementById('loadingOverlay').style.display = 'none'; // Oculta o loading
        });
    </script>
</body>
</html>
