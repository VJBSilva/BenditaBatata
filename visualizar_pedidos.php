<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Pedidos - Bendita Batata</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        #pedidos-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px; /* Espaço entre os pedidos */
        }
        .pedido {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            flex: 0 0 auto;
            width: auto;
            max-width: 100%;
            display: flex;
            flex-direction: column; /* Alinha o conteúdo verticalmente */
            height: auto; /* Altura automática */
        }
        .pedido h2 {
            margin-top: 0;
        }
        .pedido p {
            margin: 5px 0;
        }
        .pedido ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .pedido ul li {
            list-style-type: none;
        }
        .botoes-pedido {
            display: flex;
            gap: 5px; /* Espaço menor entre os botões */
            justify-content: flex-end; /* Alinha os botões à direita */
            width: 100%; /* Ocupa a largura total do contêiner */
            margin-top: auto; /* Empurra os botões para o final da caixa */
        }
        .botoes-pedido button {
            flex: 0 0 auto; /* Não permite que os botões cresçam */
            min-width: 56px; /* Reduzido em 20% (70px * 0.8) */
            max-width: 64px; /* Reduzido em 20% (80px * 0.8) */
            text-align: center;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 2.4px 4.8px; /* Reduzido em 20% (3px * 0.8 e 6px * 0.8) */
            border-radius: 3px;
            cursor: pointer;
            font-size: 9.6px; /* Reduzido em 20% (12px * 0.8) */
        }
        .botoes-pedido button.finalizado {
            background-color: #28a745;
        }
        .botoes-pedido button.finalizado:hover {
            background-color: #218838;
        }
        .botoes-pedido button.excluir {
            background-color: #dc3545;
        }
        .botoes-pedido button.excluir:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <h1>Visualizar Pedidos</h1>

    <!-- Contêiner para os pedidos -->
    <div id="pedidos-container">
        <!-- Os pedidos serão carregados aqui via JavaScript -->
    </div>

    <script>
// Função para carregar os pedidos pendentes
function carregarPedidos() {
    fetch('carregar_pedidos.php') // Endpoint para buscar os pedidos
        .then(response => response.text())
        .then(data => {
            document.getElementById('pedidos-container').innerHTML = data;
        })
        .catch(error => {
            console.error('Erro ao carregar pedidos:', error);
        });
}

// Função para marcar o pedido como finalizado
function marcarComoFinalizado(id) {
    console.log('Finalizando pedido:', id); // Depuração
    if (confirm(`Tem certeza que deseja marcar o Pedido ${id} como finalizado?`)) {
        fetch('marcar_finalizado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.text())
        .then(data => {
            console.log('Resposta do servidor:', data); // Depuração
            alert(data); // Exibir mensagem de sucesso
            carregarPedidos(); // Recarregar os pedidos após finalizar
        })
        .catch(error => {
            console.error('Erro ao marcar pedido como finalizado:', error);
        });
    }
}

// Função para excluir o pedido
function excluirPedido(id) {
    console.log('Excluindo pedido:', id); // Depuração
    if (confirm(`Tem certeza que deseja excluir o Pedido ${id}?`)) {
        fetch('excluir_pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}`
        })
        .then(response => response.text())
        .then(data => {
            console.log('Resposta do servidor:', data); // Depuração
            alert(data); // Exibir mensagem de sucesso
            carregarPedidos(); // Recarregar os pedidos após exclusão
        })
        .catch(error => {
            console.error('Erro ao excluir pedido:', error);
        });
    }
}

// Função para alterar o pedido
function alterarPedido(id) {
    console.log('Alterando pedido:', id); // Depuração
    if (confirm(`Tem certeza que deseja alterar o Pedido ${id}?`)) {
        // Implementar lógica para alterar o pedido
        alert(`Alterar pedido ${id}`);
    }
}

// Carregar os pedidos ao carregar a página
carregarPedidos();

// Configurar Server-Sent Events (SSE) para atualização em tempo real
const eventSource = new EventSource('sse_pedidos.php');
eventSource.onmessage = function(event) {
    // Quando uma nova mensagem é recebida, recarregar os pedidos
    carregarPedidos();
};
    </script>
</body>
</html>