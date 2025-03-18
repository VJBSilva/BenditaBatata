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
        <input type="time" id="hora_inicio" name="hora_inicio" value="<?php echo isset($_GET['hora_inicio']) ? $_GET['hora_inicio'] : '00:00'; ?>" required>

        <label for="data_fim">Data Final:</label>
        <input type="date" id="data_fim" name="data_fim" value="<?php echo isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d'); ?>" required>
        <input type="time" id="hora_fim" name="hora_fim" value="<?php echo isset($_GET['hora_fim']) ? $_GET['hora_fim'] : '23:59'; ?>" required>

        <button type="submit">Filtrar</button>
    </form>

    <?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require 'conexao.php'; // Arquivo de conexão com o banco de dados

    // Verifica se o usuário está logado e é um administrador
if (!isset($_COOKIE['usuario_id']) || $_COOKIE['tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}
    
    // Definir datas e horas padrão (hoje)
    $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d');
    $hora_inicio = isset($_GET['hora_inicio']) ? $_GET['hora_inicio'] : '00:00';
    $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
    $hora_fim = isset($_GET['hora_fim']) ? $_GET['hora_fim'] : '23:59';

    // Combinar data e hora para o formato timestamp
    $data_inicio_timestamp = $data_inicio . ' ' . $hora_inicio;
    $data_fim_timestamp = $data_fim . ' ' . $hora_fim;

    // Validar datas
    if ($data_inicio_timestamp > $data_fim_timestamp) {
        echo "<p class='mensagem-erro'>A data/hora inicial não pode ser maior que a data/hora final.</p>";
    } else {
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
                  AND p.status != 'excluido' -- Ignorar pedidos excluídos
                GROUP BY p.id, p.dataPedido, p.metodo_pagamento, p.desconto
            ");
            $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp]);
            $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Totalização por tipo de pagamento
            $stmt = $pdo->prepare("
                SELECT p.metodo_pagamento, 
                       SUM(ip.quantidade * ip.valor_unitario) AS total_bruto, 
                       (SELECT SUM(p2.desconto) 
                        FROM pedidos p2 
                        WHERE p2.metodo_pagamento = p.metodo_pagamento 
                          AND p2.dataPedido BETWEEN ? AND ?
                          AND p2.status != 'excluido') AS total_desconto, 
                       SUM(ip.quantidade * ip.valor_unitario) - (SELECT SUM(p2.desconto) 
                                                                 FROM pedidos p2 
                                                                 WHERE p2.metodo_pagamento = p.metodo_pagamento 
                                                                   AND p2.dataPedido BETWEEN ? AND ?
                                                                   AND p2.status != 'excluido') AS total_liquido
                FROM pedidos p
                JOIN itens_pedido ip ON p.id = ip.pedido_id
                WHERE p.dataPedido BETWEEN ? AND ?
                  AND p.status != 'excluido' -- Ignorar pedidos excluídos
                GROUP BY p.metodo_pagamento
            ");
            $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp, $data_inicio_timestamp, $data_fim_timestamp, $data_inicio_timestamp, $data_fim_timestamp]);
            $total_por_pagamento = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Totalização por categoria
            $stmt = $pdo->prepare("
                WITH TotalDesconto AS (
                    SELECT SUM(p.desconto) AS total_desconto
                    FROM pedidos p
                    WHERE p.dataPedido BETWEEN ? AND ?
                      AND p.status != 'excluido' -- Ignorar pedidos excluídos
                ),
                TotalBruto AS (
                    SELECT 
                        c.nome AS categoria, 
                        SUM(ip.quantidade * ip.valor_unitario) AS total_bruto
                    FROM pedidos p
                    JOIN itens_pedido ip ON p.id = ip.pedido_id
                    JOIN produtos pr ON ip.produto_id = pr.id
                    JOIN categorias c ON pr.categoria_id = c.id
                    WHERE p.dataPedido BETWEEN ? AND ?
                      AND p.status != 'excluido' -- Ignorar pedidos excluídos
                    GROUP BY c.nome
                )
                SELECT 
                    tb.categoria, 
                    tb.total_bruto, 
                    CASE 
                        WHEN ROW_NUMBER() OVER (ORDER BY tb.categoria) = 1 THEN (SELECT total_desconto FROM TotalDesconto)
                        ELSE NULL 
                    END AS total_desconto,
                    CASE 
                        WHEN ROW_NUMBER() OVER (ORDER BY tb.categoria) = 1 THEN (SELECT SUM(total_bruto) FROM TotalBruto) - (SELECT total_desconto FROM TotalDesconto)
                        ELSE NULL 
                    END AS total_liquido
                FROM TotalBruto tb
            ");
            $stmt->execute([$data_inicio_timestamp, $data_fim_timestamp, $data_inicio_timestamp, $data_fim_timestamp]);
            $total_por_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Função para formatar valores em reais
            function formatarReais($valor) {
                return 'R$ ' . number_format($valor, 2, ',', '.');
            }

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
                        foreach ($linha as $chave => $valor) {
                            if (in_array($chave, ['total_bruto', 'desconto', 'total_liquido', 'total_desconto'])) {
                                echo "<td>" . ($valor !== null ? formatarReais($valor) : '') . "</td>";
                            } else {
                                echo "<td>$valor</td>";
                            }
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
            echo "<h3>Total Líquido Geral: " . formatarReais($total_liquido_geral) . "</h3>";
        } catch (PDOException $e) {
            echo "<p class='mensagem-erro'>Erro ao buscar dados: " . $e->getMessage() . "</p>";
        }
    }
}
?>
</body>
</html>
