<?php
// Conexão com o banco de dados
require 'db.php';

// Inicializa a variável de erro
$erro = ""; 

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $nivel_acesso = $_POST['nivel_acesso']; // Captura o nível de acesso

    // Verifica se o usuário já existe no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuarioExistente = $stmt->fetch();

    if ($usuarioExistente) {
        $erro = "Este email já está cadastrado!";
    } else {
        // Insere o novo usuário no banco de dados
        $senhaHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$username, $email, $senhaHash, $nivel_acesso])) {
            header("Location: login.php"); // Redireciona para login
            exit();
        } else {
            $erro = "Erro ao cadastrar o usuário!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Sistema Comercial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: white;
        }
        .cadastro-container {
            width: 100%;
            max-width: 600px;
        }
        .cadastro-box {
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
            text-align: center;
            background-color: white;
        }
        .cadastro-box h2 img {
            width: 150px;
        }
        .cadastro-box label {
            display: block;
            font-weight: bold;
            color: black;
            margin-bottom: 0.5rem;
            text-align: left;
        }
        .cadastro-box input, .cadastro-box select {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.2rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #FFF;
            color: #000;
        }
        .cadastro-box button {
            width: 100%;
            padding: 0.8rem;
            background-color: black;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .cadastro-box button:hover {
            background-color: #DC3545;
        }
        .error {
            color: red;
            margin-top: 1rem;
        }
        .link-login {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="cadastro-container">
        <div class="cadastro-box">
            <h2><img src="mini_box-removebg-preview.png" alt="Logo"></h2>
            <form action="" method="POST">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" placeholder="Digite seu usuário" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email" required pattern="^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$">

                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Digite sua senha" required pattern=".{6,}">

                <label for="nivel_acesso">Nível de Acesso</label>
                <select id="nivel_acesso" name="nivel_acesso" required>
                    <option value="1">Usuário</option>
                    <option value="2">Admin</option>
                </select>

                <button type="submit">Cadastrar</button>

                <?php if (!empty($erro)): ?>
                    <div class="error"><?php echo htmlspecialchars($erro); ?></div>
                <?php endif; ?>
            </form>
            <div class="link-login">
                <p>Já tem uma conta? <a href="login.php" class="text-primary">Faça login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
