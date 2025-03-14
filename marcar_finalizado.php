<?php
// Caminho para o arquivo de pedidos
$caminhoArquivo = __DIR__ . '/pedidos.txt';

// Verificar se o ID do pedido foi recebido
if (isset($_POST['id'])) {
    $idPedido = $_POST['id'];

    // Ler todos os pedidos
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
                // Se for o pedido que deve ser finalizado, atualize o status
                if ($pedido['id'] == $idPedido) {
                    $pedido['status'] = 'finalizado';
                }
                $pedidos[] = $pedido; // Adicionar o pedido ao array (atualizado ou não)
            } else {
                // Log de erro para depuração
                error_log("Erro ao desserializar a linha: $linha");
            }
        }
    }

    // Salvar os pedidos atualizados no arquivo
    $conteudo = '';
    foreach ($pedidos as $pedido) {
        $conteudo .= serialize($pedido) . "\n"; // Serializar e adicionar quebra de linha
    }
    file_put_contents($caminhoArquivo, $conteudo);

    echo "Pedido finalizado com sucesso!";
} else {
    http_response_code(400); // Bad Request
    echo "Erro: ID do pedido não recebido.";
}
?>