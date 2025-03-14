<?php
// Caminho para o arquivo de pedidos
$caminhoArquivo = __DIR__ . '/pedidos.txt';

// Ler o corpo da requisição
$dados = json_decode(file_get_contents('php://input'), true);

// Verificar se os dados foram recebidos corretamente
if ($dados) {
    // Ler todos os pedidos existentes para gerar o próximo ID
    $pedidos = [];
    if (file_exists($caminhoArquivo)) {
        $linhas = file($caminhoArquivo);
        foreach ($linhas as $linha) {
            $pedidos[] = unserialize($linha);
        }
    }

    // Gerar um ID sequencial de 5 dígitos
    $ultimoId = 0;
    if (!empty($pedidos)) {
        // Encontrar o maior ID existente
        $ultimoId = max(array_column($pedidos, 'id'));
    }
    $novoId = str_pad($ultimoId + 1, 5, '0', STR_PAD_LEFT); // Garante 5 dígitos

    // Adicionar o ID ao pedido
    $dados['id'] = $novoId;

    // Adicionar o status do pedido (por padrão, "pendente")
    $dados['status'] = 'pendente';

    // Salvar o pedido no arquivo
    file_put_contents($caminhoArquivo, serialize($dados) . "\n", FILE_APPEND);

    echo "Pedido salvo com sucesso!";
} else {
    http_response_code(400); // Bad Request
    echo "Erro: Dados do pedido não recebidos.";
}
?>