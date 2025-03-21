<?php
require 'conexao.php';

// Verifica se o usuário está logado e é um administrador
if (!isset($_COOKIE['usuario_id']) || $_COOKIE['tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Lógica para salvar/editar despesa
if (isset($_POST['salvar'])) {
    $id = $_POST['id'];
    $tipo_despesa_id = $_POST['tipo_despesa_id'];
    $valor = $_POST['valor'];

    // Apenas substitui vírgula por ponto
    $valor = str_replace(',', '.', $valor);

    $data = $_POST['data'];
    $numero_documento = $_POST['numero_documento']; // Captura o número do documento

    // Validação do valor
    if (!is_numeric($valor) || $valor <= 0) {
        echo "<script>alert('O valor deve ser um número positivo válido e sem pontos.');</script>";
    } else {
        if ($id) {
            // Editar despesa existente
            $stmt = $pdo->prepare("UPDATE despesas SET tipo_despesa_id = ?, valor = ?, data = ?, numero_documento = ? WHERE id = ?");
            $stmt->execute([$tipo_despesa_id, $valor, $data, $numero_documento, $id]);
        } else {
            // Cadastrar nova despesa
            $stmt = $pdo->prepare("INSERT INTO despesas (tipo_despesa_id, valor, data, numero_documento) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tipo_despesa_id, $valor, $data, $numero_documento]);
            $id = $pdo->lastInsertId();
        }

        // Redirecionar para evitar reenvio do formulário
        header("Location: cadastro_despesa.php");
        exit();
    }
}

// Carregar tipos de despesa
$stmt = $pdo->query("SELECT * FROM tipo_despesa WHERE status = 'ativo'");
$tipos_despesa = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carregar despesas
$stmt = $pdo->query("SELECT d.id, d.valor, d.data, d.numero_documento, t.nome AS tipo_despesa FROM despesas d JOIN tipo_despesa t ON d.tipo_despesa_id = t.id WHERE d.status = 'ativo'");
$despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Cadastro de Despesas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .form-container {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-container input {
            width: 97%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .form-container button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        .table-container {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
        }
        .actions button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }
        .actions button:hover {
            background-color: #0056b3;
        }
        .actions button.delete {
            background-color: #dc3545;
        }
        .actions button.delete:hover {
            background-color: #c82333;
        }
        .search-container {
            position: relative;
        }
        .search-container .search-results {
            position: absolute;
            background-color: #fff;
            border: 1px solid #ddd;
            width: 100%;
            max-height: 150px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .search-container .search-results div {
            padding: 10px;
            cursor: pointer;
        }
        .search-container .search-results div:hover {
            background-color: #f1f1f1;
        }
        .lupa {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Admin - Cadastro de Despesas</h1>

    <!-- Formulário de Cadastro/Edição -->
    <div class="form-container">
        <h2>Cadastrar/Editar Despesa</h2>
        <form id="formDespesa" method="POST" action="">
            <input type="hidden" id="id" name="id">
            <div class="search-container">
                <input type="text" id="searchTipoDespesa" placeholder="Pesquisar tipo de despesa..." autocomplete="off">
                <i class="fas fa-search lupa" onclick="mostrarTodasDespesas()"></i>
                <input type="hidden" id="tipo_despesa_id" name="tipo_despesa_id">
                <div class="search-results" id="searchResults"></div>
            </div>
            <input type="text" id="valor" name="valor" placeholder="Valor" required>
            <input type="date" id="data" name="data" required>
            <input type="text" id="numero_documento" name="numero_documento" placeholder="Número do Documento (opcional)">
            <button type="submit" name="salvar">Salvar</button>
        </form>
    </div>

    <!-- Tabela de Despesas -->
    <div class="table-container">
        <h2>Lista de Despesas</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo de Despesa</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Número do Documento</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($despesas as $despesa): ?>
                    <tr>
                        <td><?= $despesa['id'] ?></td>
                        <td><?= $despesa['tipo_despesa'] ?></td>
                        <td>R$ <?= number_format($despesa['valor'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y', strtotime($despesa['data'])) ?></td>
                        <td><?= $despesa['numero_documento'] ?? 'N/A' ?></td>
                        <td class='actions'>
                            <a href='?editar=<?= $despesa['id'] ?>'><button>Editar</button></a>
                            <a href='?excluir=<?= $despesa['id'] ?>'><button class='delete'>Excluir</button></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Lógica para carregar dados no formulário ao editar
    if (isset($_GET['editar'])) {
        $id = $_GET['editar'];
        $stmt = $pdo->prepare("SELECT * FROM despesas WHERE id = ?");
        $stmt->execute([$id]);
        $despesa = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "
            <script>
                document.getElementById('id').value = '{$despesa['id']}';
                document.getElementById('tipo_despesa_id').value = '{$despesa['tipo_despesa_id']}';
                document.getElementById('searchTipoDespesa').value = '{$despesa['tipo_despesa']}';
                document.getElementById('valor').value = '" . number_format($despesa['valor'], 2, ',', '.') . "';
                document.getElementById('data').value = '{$despesa['data']}';
                document.getElementById('numero_documento').value = '{$despesa['numero_documento']}';
            </script>
        ";
    }
    ?>

    <script>
        // Dados dos tipos de despesa (carregados do PHP)
        const tiposDespesa = <?= json_encode($tipos_despesa) ?>;

        // Elementos do DOM
        const searchInput = document.getElementById('searchTipoDespesa');
        const searchResults = document.getElementById('searchResults');
        const tipoDespesaIdInput = document.getElementById('tipo_despesa_id');

        // Adiciona o evento de input ao campo de pesquisa
        searchInput.addEventListener('input', filtrarTipoDespesa);

        // Função para filtrar e exibir resultados
        function filtrarTipoDespesa() {
            const termo = searchInput.value.toLowerCase();
            const resultados = tiposDespesa.filter(tipo => 
                tipo.nome.toLowerCase().includes(termo)
            );

            // Limpar resultados anteriores
            searchResults.innerHTML = '';

            // Exibir novos resultados
            if (resultados.length > 0) {
                resultados.forEach(tipo => {
                    const div = document.createElement('div');
                    div.textContent = tipo.nome;
                    div.addEventListener('click', () => {
                        searchInput.value = tipo.nome; // Preenche o campo de pesquisa
                        tipoDespesaIdInput.value = tipo.id; // Armazena o ID
                        searchResults.style.display = 'none'; // Oculta os resultados
                    });
                    searchResults.appendChild(div);
                });
                searchResults.style.display = 'block';
            } else {
                searchResults.style.display = 'none';
            }
        }

        // Função para mostrar todas as despesas ao clicar na lupa
        function mostrarTodasDespesas() {
            // Limpar resultados anteriores
            searchResults.innerHTML = '';

            // Exibir todos os tipos de despesa, independentemente do campo de pesquisa
            tiposDespesa.forEach(tipo => {
                const div = document.createElement('div');
                div.textContent = tipo.nome;
                div.addEventListener('click', () => {
                    searchInput.value = tipo.nome; // Preenche o campo de pesquisa
                    tipoDespesaIdInput.value = tipo.id; // Armazena o ID
                    searchResults.style.display = 'none'; // Oculta os resultados
                });
                searchResults.appendChild(div);
            });

            // Exibir os resultados
            searchResults.style.display = 'block';
        }

        // Evento de clique na lupa
        document.querySelector('.lupa').addEventListener('click', mostrarTodasDespesas);

        // Ocultar resultados ao clicar fora
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Validação no frontend (ao enviar o formulário)
        document.getElementById('formDespesa').addEventListener('submit', function(event) {
            const valorInput = document.getElementById('valor');
            let valor = valorInput.value.replace(',', '.'); // Substitui vírgula por ponto

            if (isNaN(valor) || valor <= 0) {
                alert('O valor deve ser um número positivo válido.');
                event.preventDefault(); // Impede o envio do formulário
            } else {
                valorInput.value = valor; // Atualiza o valor no campo antes de enviar
            }
        });

        // Definir a data atual no fuso horário local
        const dataAtual = new Date();
        const offset = dataAtual.getTimezoneOffset(); // Obter o offset do fuso horário em minutos
        dataAtual.setMinutes(dataAtual.getMinutes() - offset); // Ajustar para o fuso horário local
        document.getElementById('data').value = dataAtual.toISOString().split('T')[0];
    </script>
</body>
</html>
