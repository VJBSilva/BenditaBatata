<?php
require 'conexao.php';
verificarLogin(); // Verifica se o usuário está logado

// Verifica o tipo de usuário
$tipo_usuario = $_COOKIE['tipo_usuario'] ?? 'user';
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

        <?php if ($tipo_usuario === 'admin'): ?>
            <button onclick="toggleSubMenu('subMenuCadastros')">Cadastros</button>
            <div class="sub-menu" id="subMenuCadastros">
                <button onclick="window.location.href='cadastro_despesa.php'">Cadastro de Despesa</button>
                <button onclick="window.location.href='cadastro_tipo_despesa.php'">Cadastro de Tipos de Despesa</button>
                <button onclick="window.location.href='cadastro_categoria.php'">Cadastro de Categoria</button>
                <button onclick="window.location.href='cadastro_produto.php'">Cadastro de Produto</button>
                <button onclick="window.location.href='cadastro_adicionais.php'">Cadastro de Opcional</button>
                <button onclick="window.location.href='cadastro_vincular_adicionais.php'">Vincular Opcional</button>
                <button onclick="window.location.href='cadastro_usuario.php'">Cadastro de Usuário</button>
            </div>

            <button onclick="toggleSubMenu('subMenuRelatorios')">Relatórios</button>
            <div class="sub-menu" id="subMenuRelatorios">
                <button onclick="window.location.href='relatorio_vendas.php'">Relatório de Venda</button>
                <button onclick="window.location.href='relatorio_despesas.php'">Relatório de Despesa</button>
            </div>
        <?php endif; ?>

        <button onclick="window.location.href='logout.php'">Sair</button>
    </div>

    <script>
        function toggleSubMenu(id) {
            const subMenu = document.getElementById(id);
            subMenu.style.display = subMenu.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
