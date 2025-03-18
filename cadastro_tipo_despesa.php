<?php
require 'conexao.php';

// Verifica se o usuário está logado e é um administrador
if (!isset($_COOKIE['usuario_id']) || $_COOKIE['tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Lógica para salvar/editar tipo de despesa
if (isset($_POST['salvar'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $status = $_POST['status'];

    // Verifica se o tipo de despesa já existe (apenas para cadastro, não para edição)
    if (!$id) {
        $stmt = $pdo->prepare("SELECT id FROM tipo_despesa WHERE nome = ?");
        $stmt->execute([$nome]);
        $despesaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($despesaExistente) {
            echo "<script>alert('Tipo de despesa já cadastrado!');</script>";
            exit();
        }
    }

    if ($id) {
        // Editar tipo de despesa existente
        $stmt = $pdo->prepare("UPDATE tipo_despesa SET nome = ?, status = ? WHERE id = ?");
        $stmt->execute([$nome, $status, $id]);
    } else {
        // Cadastrar novo tipo de despesa
        $stmt = $pdo->prepare("INSERT INTO tipo_despesa (nome, status) VALUES (?, ?)");
        $stmt->execute([$nome, $status]);
        $id = $pdo->lastInsertId();
    }

    // Redirecionar para evitar reenvio do formulário
    header("Location: cadastro_tipo_despesa.php");
    exit();
}

// Lógica para excluir tipo de despesa
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM tipo_despesa WHERE id = ?");
    $stmt->execute([$id]);

    // Redirecionar para evitar reenvio do formulário
    header("Location: cadastro_tipo_despesa.php");
    exit();
}

// Carregar tipos de despesa
$stmt = $pdo->query("SELECT * FROM tipo_despesa");
$tiposDespesa = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Cadastro de Tipo de Despesa</title>
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
            animation
