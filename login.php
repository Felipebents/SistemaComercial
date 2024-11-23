<?php
session_start(); // Inicia a sessão

// Verifica se o usuário já está logado
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php"); // Redireciona para a tela inicial se já estiver logado
    exit();
}

// Conexão com o banco de dados
require 'db.php'; // Inclua seu arquivo de conexão ao banco de dados

$erro = ""; // Inicializa a variável de erro

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Verifica se o usuário existe no banco de dados
    $stmt = $pdo->prepare("SELECT id, nome, senha, nivel_acesso FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verifica a senha
    if ($usuario && password_verify($password, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id']; // ID do usuário
        $_SESSION['usuario_nome'] = $usuario['nome']; // Nome do usuário
        $_SESSION['nivel_acesso'] = $usuario['nivel_acesso']; // Nível de acesso

        // Redireciona com base no nível de acesso
        if ($usuario['nivel_acesso'] == 2) { // Administrador
            header("Location: dashboard.php");
        } else { // Usuário comum
            header("Location: pdv.php");
        }
        exit();
    } else {
        $erro = "Usuário ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Comercial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos globais */
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
            background: white;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 600px;
        }

        .login-box {
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
            text-align: center;
            width: 500px;
        }

        .login-box h1 {
            color: black;
            margin-bottom: 1.5rem;
        }

        .login-box label {
            display: block;
            font-weight: bold;
            color: black;
            margin-bottom: 0.5rem;
            text-align: left;
        }

        .login-box input[type="email"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.2rem;
            border: 1px solid #fff;
            border-radius: 5px;
            background-color: #FFF;
            color: #000;
        }

        .login-box button {
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

        .login-box button:hover {
            background-color: #DC3545;
            color: white;
        }

        .error {
            color: #DC3545;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2 class="name"><img src="mini_box-removebg-preview.png" alt="Logo" width="150"></h2>
            <form action="" method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email" required>

                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Digite sua senha" required>

                <button type="submit">Entrar</button>
                <?php if (!empty($erro)): ?>
                    <div class="error"><?php echo htmlspecialchars($erro); ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
