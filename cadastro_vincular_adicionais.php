<?php
require 'conexao.php';

// Verifica se o usuário está logado e é um administrador
if (!isset($_COOKIE['usuario_id']) || $_COOKIE['tipo_usuario'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Lógica para salvar/editar vínculo de adicionais à categoria
if (isset($_POST['salvar'])) {
    $categoria_id = $_POST['categoria_id'];
    $adicionais = $_POST['adicionais'] ?? [];

    // Remover vínculos existentes
    $stmt = $pdo->prepare("DELETE FROM categoria_adicionais WHERE categoria_id = ?");
    $stmt->execute([$categoria_id]);

    // Adicionar novos vínculos
    foreach ($adicionais as $adicional_id) {
        $stmt = $pdo->prepare("INSERT INTO categoria_adicionais (categoria_id, adicional_id) VALUES (?, ?)");
        $stmt->execute([$categoria_id, $adicional_id]);
    }
}

// Carregar categorias
$stmt = $pdo->query("SELECT * FROM categorias");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carregar adicionais
$stmt = $pdo->query("SELECT * FROM adicionais");
$adicionais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carregar adicionais vinculados à categoria selecionada
$adicionaisVinculados = [];
if (isset($_GET['categoria_id'])) {
    $categoria_id = $_GET['categoria_id'];
    $stmt = $pdo->prepare("SELECT adicional_id FROM categoria_adicionais WHERE categoria_id = ?");
    $stmt->execute([$categoria_id]);
    $adicionaisVinculados = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Adicionais às Categorias - Bendita Batata</title>
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
            max-width: 600px;
            margin: 0 auto;
        }
        .form-container input, .form-container select {
            width: 100%;
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
            width: 100%;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        .adicionais {
            margin-top: 20px;
        }
        .adicional-container {
            border: 1px solid #ddd; /* Borda semelhante à da categoria */
            border-radius: 5px; /* Bordas arredondadas */
            padding: 5px 5px; /* Reduz o padding vertical (5px) e mantém o horizontal (10px) */
            margin-bottom: 5px; /* Reduz o espaçamento entre os contêineres */
            background-color: #fff; /* Fundo branco */
            display: flex; /* Alinha o label e o checkbox horizontalmente */
            align-items: center; /* Centraliza verticalmente */
        }
        .adicional-container input[type="checkbox"] {
            margin-left: 10px; /* Espaço entre o label e o checkbox */
        }
        .adicional-container label {
            margin: 0; /* Remove margens padrão do label */
            white-space: nowrap; /* Impede que o texto quebre em várias linhas */
        }
    </style>
    <script>
        function confirmarSalvar() {
            // Exibe um alerta de confirmação
            const confirmacao = confirm("Deseja realmente salvar as alterações?");
            
            // Retorna true para enviar o formulário ou false para cancelar
            return confirmacao;
        }
    </script>
</head>
<body>
    <h1>Vincular Adicionais às Categorias</h1>

    <!-- Formulário de Vincular Adicionais -->
    <div class="form-container">
        <h2>Vincular Adicionais à Categoria</h2>
        <form method="GET" action="">
            <select id="categoria_id" name="categoria_id" required onchange="this.form.submit()">
                <option value="">Selecione a Categoria</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>" 
                        <?php echo (isset($_GET['categoria_id']) && $_GET['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                        <?php echo $categoria['nome']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (isset($_GET['categoria_id'])): ?>
            <form method="POST" action="" onsubmit="return confirmarSalvar()">
                <input type="hidden" name="categoria_id" value="<?php echo $_GET['categoria_id']; ?>">
                <div class="adicionais">
                    <h3>Adicionais:</h3>
                    <?php foreach ($adicionais as $adicional): ?>
                        <div class="adicional-container"> <!-- Contêiner retangular para cada adicional -->
                            <label for="adicional_<?php echo $adicional['id']; ?>"><?php echo $adicional['nome']; ?></label>
                            <input type="checkbox" id="adicional_<?php echo $adicional['id']; ?>" name="adicionais[]" value="<?php echo $adicional['id']; ?>"
                                <?php echo in_array($adicional['id'], $adicionaisVinculados) ? 'checked' : ''; ?>>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="salvar">Salvar</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
