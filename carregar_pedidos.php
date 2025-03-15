<?php
require 'conexao.php';

// Consulta para carregar os pedidos pendentes
$stmt = $pdo->query("SELECT * FROM pedidos WHERE status = 'pendente'");
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pedidos as $pedido) {
    echo "
        <div class='pedido'>
            <h2>Pedido {$pedido['id']}</h2>
            <p><strong>Senha:</strong> {$pedido['senha']}</p>
            <p><strong>Itens:</strong></p>
            <ul>
    ";

    // Consulta para carregar os itens do pedido com o nome da categoria
    $stmt = $pdo->prepare("
        SELECT itens_pedido.*, produtos.nome, categorias.nome AS categoria_nome
        FROM itens_pedido
        JOIN produtos ON itens_pedido.produto_id = produtos.id
        JOIN categorias ON produtos.categoria_id = categorias.id
        WHERE itens_pedido.pedido_id = ?
    ");
    $stmt->execute([$pedido['id']]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar itens por categoria
    $itensPorCategoria = [];
    foreach ($itens as $item) {
        $categoria = $item['categoria_nome'];
        if (!isset($itensPorCategoria[$categoria])) {
            $itensPorCategoria[$categoria] = [];
        }
        $itensPorCategoria[$categoria][] = $item;
    }

    // Exibir itens agrupados por categoria
    foreach ($itensPorCategoria as $categoria => $itens) {
        echo "<li><strong>{$categoria}:</strong></li>";
        foreach ($itens as $item) {
            echo "<li>{$item['quantidade']}x {$item['nome']}</li>";

            // Consulta para carregar os adicionais do item do pedido
            $stmtAdicionais = $pdo->prepare("
                SELECT adicionais.nome
                FROM itens_pedido_adicionais
                JOIN adicionais ON itens_pedido_adicionais.adicional_id = adicionais.id
                WHERE itens_pedido_adicionais.item_pedido_id = ?
            ");
            $stmtAdicionais->execute([$item['id']]);
            $adicionais = $stmtAdicionais->fetchAll(PDO::FETCH_COLUMN);

            // Exibir adicionais do item
            if (!empty($adicionais)) {
                echo "<li>Adicionais: " . implode(", ", $adicionais) . "</li>";
            }
        }
    }

    echo "
            </ul>
            <p><strong>Observações:</strong> {$pedido['observacao']}</p>
            <div class='botoes-pedido'>
                <button onclick='alterarPedido(\"{$pedido['id']}\")'>Alterar</button>
                <button class='finalizado' onclick='marcarComoFinalizado(\"{$pedido['id']}\")'>Finalizar</button>
                <button class='excluir' onclick='excluirPedido(\"{$pedido['id']}\")'>Excluir</button>
            </div>
        </div>
    ";
}