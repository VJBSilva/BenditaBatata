<?php
session_start();
require 'conexao.php'; // Arquivo de conexão com o banco de dados

// Verifica se o usuário está logado e é um administrador


$mensagem = ''; // Variável para exibir mensagens de sucesso ou erro

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    $tipo_usuario = $_POST['tipo_usuario'];

    // Validação dos campos
    if (empty($usuario) || empty($senha)) {
        $mensagem = "Por favor, preencha todos os campos.";
    } else {
        // Gera o hash da senha
        $hash_senha = password_hash($senha, PASSWORD_BCRYPT);

        try {
            // Insere o usuário no banco de dados
            $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, senha, tipo_usuario) VALUES (?, ?, ?)");
            $stmt->execute([$usuario, $hash_senha, $tipo_usuario]);

            $mensagem = "Usuário cadastrado com sucesso!";
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { // Código de erro para duplicação de usuário
                $mensagem = "Erro: O nome de usuário já está em uso.";
            } else {
                $mensagem = "Erro ao cadastrar usuário: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .cadastro-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .cadastro-container h2 {
            margin-bottom: 20px;
        }
        .cadastro-container input, .cadastro-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .cadastro-container button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .cadastro-container button:hover {
            background-color: #0056b3;
        }
        .mensagem {
            margin-top: 10px;
            color: green;
        }
        .mensagem-erro {
            margin-top: 10px;
            color: red;
        }
    </style>
</head>
<body>
    <div class="cadastro-container">
        <h2>Cadastro de Usuário</h2>
        <?php if ($mensagem): ?>
            <p class="<?php echo strpos($mensagem, 'Erro') !== false ? 'mensagem-erro' : 'mensagem'; ?>">
                <?php echo htmlspecialchars($mensagem); ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="usuario" placeholder="Nome de usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <select name="tipo_usuario" required>
                <option value="admin">Administrador</option>
                <option value="user">Usuário Comum</option>
            </select>
            <button type="submit">Cadastrar</button>
        </form>
        <p><a href="menu.php">Voltar ao Menu</a></p>
    </div>
</body>
</html>
