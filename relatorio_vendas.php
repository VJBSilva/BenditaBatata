<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center; /* Centraliza o título */
            margin-bottom: 20px; /* Espaçamento abaixo do título */
        }
        .filtro {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h3 {
            margin-top: 20px;
            text-align: left; /* Alinha o total geral à esquerda */
        }
    </style>
</head>
<body>
    <h1>Relatório de Vendas</h1> <!-- Título centralizado -->
    <form method="GET" action="" class="filtro">
        <label for="data_inicio">Data Inicial:</label>
        <input type="date" id="data_inicio" name="data_inicio" value="<?php echo isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d'); ?>" required>

        <label for="data_fim">Data Final:</label>
        <input type="date" id="data_fim" name="data_fim" value="<?php echo isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d'); ?>" required>

        <button type="submit">Filtrar</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        require 'conexao.php'; // Arquivo de conexão com o banco de dados

        // Definir datas padrão (hoje)
        $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d');
        $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

        // Converter as datas para o formato timestamp (YYYY-MM-DD 00:00:00 e YYYY-MM-DD 23:59:59)
        $data_inicio_timestamp = $data_inicio . ' 00:00:00';
        $data_fim_timestamp = $data_fim . ' 23:59:59';

        // Buscar vendas no período
        $stmt = $pdo->prepare("
            SELECT p.id, p.dataPedido, p.metodo_pagamento, SUM(ip.quantidade * ip.valor_unitario) AS total
            FROM pedidos p
            JOIN itens_pedido ip ON p.id = ip.pedido_id
            WHERE p.dataPedido BETWEEN ? AND ?
            GROUP BY p.id
        ");
        $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp]);
        $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totalização por tipo de pagamento
        $stmt = $pdo->prepare("
            SELECT p.metodo_pagamento, SUM(ip.quantidade * ip.valor_unitario) AS total
            FROM pedidos p
            JOIN itens_pedido ip ON p.id = ip.pedido_id
            WHERE p.dataPedido BETWEEN ? AND ?
            GROUP BY p.metodo_pagamento
        ");
        $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp]);
        $total_por_pagamento = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totalização por categoria
        $stmt = $pdo->prepare("
            SELECT c.nome AS categoria, SUM(ip.quantidade * ip.valor_unitario) AS total
            FROM pedidos p
            JOIN itens_pedido ip ON p.id = ip.pedido_id
            JOIN produtos pr ON ip.produto_id = pr.id
            JOIN categorias c ON pr.categoria_id = c.id
            WHERE p.dataPedido BETWEEN ? AND ?
            GROUP BY c.nome
        ");
        $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp]);
        $total_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Exibir os resultados
        echo "<h2>Vendas no Período</h2>";
        if (count($vendas) > 0) {
            echo "<table>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Data</th>
                        <th>Método de Pagamento</th>
                        <th>Total</th>
                    </tr>";
            foreach ($vendas as $venda) {
                echo "<tr>
                        <td>{$venda['id']}</td>
                        <td>{$venda['dataPedido']}</td>
                        <td>{$venda['metodo_pagamento']}</td>
                        <td>R$ " . number_format($venda['total'], 2, ',', '.') . "</td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nenhuma venda encontrada no período selecionado.</p>";
        }

        echo "<h2>Totalização por Tipo de Pagamento</h2>";
        if (count($total_por_pagamento) > 0) {
            echo "<table>
                    <tr>
                        <th>Método de Pagamento</th>
                        <th>Total</th>
                    </tr>";
            foreach ($total_por_pagamento as $pagamento) {
                echo "<tr>
                        <td>{$pagamento['metodo_pagamento']}</td>
                        <td>R$ " . number_format($pagamento['total'], 2, ',', '.') . "</td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nenhum dado encontrado.</p>";
        }

        echo "<h2>Totalização por Categoria</h2>";
        if (count($total_por_categoria) > 0) {
            echo "<table>
                    <tr>
                        <th>Categoria</th>
                        <th>Total</th>
                    </tr>";
            foreach ($total_por_categoria as $categoria) {
                echo "<tr>
                        <td>{$categoria['categoria']}</td>
                        <td>R$ " . number_format($categoria['total'], 2, ',', '.') . "</td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nenhum dado encontrado.</p>";
        }

        // Calcular o total geral
        $total_geral = 0;
        foreach ($vendas as $venda) {
            $total_geral += $venda['total'];
        }

        // Exibir o total geral
        echo "<h3>Total Geral: R$ " . number_format($total_geral, 2, ',', '.') . "</h3>";
    }
    ?>
</body>
</html>