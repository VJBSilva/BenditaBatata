<?php
require 'conexao.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

function enviarMensagem($dados) {
    echo "data: " . json_encode($dados) . "\n\n";
    ob_flush();
    flush();
}

$ultimoPedido = null;
while (true) {
    $stmt = $pdo->query("SELECT * FROM pedidos ORDER BY id DESC LIMIT 1");
    $ultimoPedidoAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ultimoPedidoAtual !== $ultimoPedido) {
        $ultimoPedido = $ultimoPedidoAtual;
        enviarMensagem(['novoPedido' => true]);
    }

    sleep(1);
}
?>