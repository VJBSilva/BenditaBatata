<?php
require 'conexao.php';
verificarLogin(); // Verifica se o usuário está logado
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .menu-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .menu-container h2 {
            margin-bottom: 20px;
        }
        .menu-container button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .menu-container button:hover {
            background-color: #0056b3;
        }
        .sub-menu {
            display: none;
            margin-top: 10px;
        }
        .sub-menu button {
            background-color: #28a745;
        }
        .sub-menu button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="menu-container">
        <h2>Menu Principal</h2>
        <button onclick="window.location.href='cadastro_pedido.php'">Cadastro de Pedido</button>
        <button onclick="window.location.href='visualizar_pedidos.php'">Visualizar Pedidos</button>
        <button onclick="toggleSubMenu()">Cadastros</button>
        <div class="sub-menu" id="subMenu">
            <button onclick="window.location.href='cadastro_categoria.php'">Cadastro de Categoria</button>
            <button onclick="window.location.href='cadastro_produto.php'">Cadastro de Produto</button>
            <button onclick="window.location.href='cadastro_opcional.php'">Cadastro de Opcional</button>
        </div>
        <button onclick="window.location.href='logout.php'">Sair</button>
    </div>

    <script>
        function toggleSubMenu() {
            const subMenu = document.getElementById('subMenu');
            subMenu.style.display = subMenu.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
