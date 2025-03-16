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
            text-align: center;
            margin-bottom: 20px;
        }
        .filtro {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .filtro label {
            font-weight: bold;
        }
        .filtro input[type="date"] {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .filtro button {
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filtro button:hover {
            background-color: #0056b3;
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
            text-align: left;
        }
        .mensagem-erro {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Relatório de Vendas</h1>
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

    // Validar datas
    if ($data_inicio > $data_fim) {
        echo "<p class='mensagem-erro'>A data inicial não pode ser maior que a data final.</p>";
    } else {
        // Converter as datas para o formato timestamp (YYYY-MM-DD 00:00:00 e YYYY-MM-DD 23:59:59)
        $data_inicio_timestamp = $data_inicio . ' 00:00:00';
        $data_fim_timestamp = $data_fim . ' 23:59:59';

        try {
            // Buscar vendas no período
            $stmt = $pdo->prepare("
                SELECT p.id, p.dataPedido, p.metodo_pagamento, 
                       SUM(ip.quantidade * ip.valor_unitario) AS total_bruto, 
                       p.desconto, 
                       SUM(ip.quantidade * ip.valor_unitario) - p.desconto AS total_liquido
                FROM pedidos p
                JOIN itens_pedido ip ON p.id = ip.pedido_id
                WHERE p.dataPedido BETWEEN ? AND ?
                GROUP BY p.id, p.dataPedido, p.metodo_pagamento, p.desconto
            ");
            $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp]);
            $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Totalização por tipo de pagamento
            $stmt = $pdo->prepare("
                SELECT p.metodo_pagamento, 
                       SUM(ip.quantidade * ip.valor_unitario) AS total_bruto, 
                       SUM(p.desconto) AS total_desconto, 
                       SUM(ip.quantidade * ip.valor_unitario) - SUM(p.desconto) AS total_liquido
                FROM pedidos p
                JOIN itens_pedido ip ON p.id = ip.pedido_id
                WHERE p.dataPedido BETWEEN ? AND ?
                GROUP BY p.metodo_pagamento
            ");
            $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp]);
            $total_por_pagamento = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Totalização por categoria
            $stmt = $pdo->prepare("
                SELECT c.nome AS categoria, 
                       SUM(ip.quantidade * ip.valor_unitario) AS total_bruto, 
                       SUM(p.desconto) AS total_desconto, 
                       SUM(ip.quantidade * ip.valor_unitario) - SUM(p.desconto) AS total_liquido
                FROM pedidos p
                JOIN itens_pedido ip ON p.id = ip.pedido_id
                JOIN produtos pr ON ip.produto_id = pr.id
                JOIN categorias c ON pr.categoria_id = c.id
                WHERE p.dataPedido BETWEEN ? AND ?
                GROUP BY c.nome
            ");
            $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp]);
            $total_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Função para exibir tabelas
            function exibirTabela($dados, $colunas, $titulo) {
                if (count($dados) > 0) {
                    echo "<h2>$titulo</h2>";
                    echo "<table>
                            <tr>";
                    foreach ($colunas as $coluna) {
                        echo "<th>$coluna</th>";
                    }
                    echo "</tr>";
                    foreach ($dados as $linha) {
                        echo "<tr>";
                        foreach ($linha as $valor) {
                            echo "<td>$valor</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>Nenhum dado encontrado.</p>";
                }
            }

            // Exibir os resultados
            exibirTabela($vendas, ['ID Pedido', 'Data', 'Método de Pagamento', 'Total Bruto', 'Desconto', 'Total Líquido'], 'Vendas no Período');
            exibirTabela($total_por_pagamento, ['Método de Pagamento', 'Total Bruto', 'Desconto', 'Total Líquido'], 'Totalização por Tipo de Pagamento');
            exibirTabela($total_por_categoria, ['Categoria', 'Total Bruto', 'Desconto', 'Total Líquido'], 'Totalização por Categoria');

            // Calcular o total líquido geral
            $total_liquido_geral = 0;
            foreach ($vendas as $venda) {
                $total_liquido_geral += $venda['total_liquido'];
            }

            // Exibir o total líquido geral
            echo "<h3>Total Líquido Geral: R$ " . number_format($total_liquido_geral, 2, ',', '.') . "</h3>";
        } catch (PDOException $e) {
            echo "<p class='mensagem-erro'>Erro ao buscar dados: " . $e->getMessage() . "</p>";
        }
    }
}
?>
</body>
</html>
