<?php
// Função para ler pedidos do arquivo
function lerPedidos() {
    $caminhoArquivo = __DIR__ . '/pedidos.txt';
    $pedidos = [];

    if (file_exists($caminhoArquivo)) {
        $linhas = file($caminhoArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($linhas as $linha) {
            // Verificar se a linha não está vazia
            if (empty(trim($linha))) {
                continue;
            }

            // Tentar desserializar a linha
            $pedido = unserialize($linha);

            // Verificar se a desserialização foi bem-sucedida
            if ($pedido !== false && is_array($pedido)) {
                $pedidos[] = $pedido;
            } else {
                // Log de erro para depuração
                error_log("Erro ao desserializar a linha: $linha");
            }
        }
    }

    return $pedidos;
}

// Ler pedidos do arquivo
$pedidos = lerPedidos();

// Filtrar pedidos pendentes
$pedidosPendentes = array_filter($pedidos, function($pedido) {
    return isset($pedido['status']) && $pedido['status'] == 'pendente';
});

// Exibir pedidos pendentes
foreach ($pedidosPendentes as $pedido) {
    echo "
        <div class='pedido'>
            <h2>Pedido {$pedido['id']}</h2>
            <p><strong>Cliente:</strong> {$pedido['nomeCliente']}</p>
            <p><strong>Itens:</strong></p>
            <ul>
    ";

    // Agrupar itens por categoria
    $itensPorCategoria = [];
    foreach ($pedido['itens'] as $item) {
        $categoria = $item['categoria']; // Usar a categoria salva no pedido
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

// Exibir mensagem se não houver pedidos pendentes
if (empty($pedidosPendentes)) {
    echo "<p>Não há pedidos pendentes.</p>";
}
?>