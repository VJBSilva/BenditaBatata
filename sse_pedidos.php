<?php
// Configurar o cabeçalho para Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Função para enviar uma mensagem SSE
function enviarMensagem($dados) {
    echo "data: " . json_encode($dados) . "\n\n";
    ob_flush();
    flush();
}

// Verificar se há novos pedidos
$ultimoPedido = null;
while (true) {
    // Ler o arquivo de pedidos
    $caminhoArquivo = __DIR__ . '/pedidos.txt';
    if (file_exists($caminhoArquivo)) {
        $linhas = file($caminhoArquivo);
        $pedidos = [];
        foreach ($linhas as $linha) {
            $pedidos[] = unserialize($linha);
        }

        // Verificar se há novos pedidos
        $ultimoPedidoAtual = end($pedidos);
        if ($ultimoPedidoAtual !== $ultimoPedido) {
            $ultimoPedido = $ultimoPedidoAtual;
            enviarMensagem(['novoPedido' => true]);
        }
    }

    // Aguardar 1 segundo antes de verificar novamente
    sleep(1);
}
?>