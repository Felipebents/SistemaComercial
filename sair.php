<?php
session_start();

// Lógica para realizar logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmar'])) {
    session_unset(); // Limpa todas as variáveis de sessão
    session_destroy(); // Destroi a sessão

    // Redirecionar para a página de login
    header('Location: login.php'); // Substitua 'login.html' pela URL da sua página de login
    exit(); // Sempre use exit após redirecionamentos
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Mini Box Maciel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
* {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: white; 
            color: black;
            width: 100%;
            height: 100vh; 
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout {
            position: relative;
            top: 0; 
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
        }


        header {
            text-align: center;
            margin-bottom: 2rem;
        }

        header h1 {
            font-size: 2.5rem;
        }

        /* Container de logout */
        .logout-container {
            background-color:white; 
            padding: 2rem;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
        }

        .logout-container h2 {
            color: #000;
            margin-bottom: 1rem;
        }

        .logout-container p {
            color: #000;
            margin-bottom: 1.5rem;
        }

        button {
            padding: 1rem;
            background-color: black;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin: 0.5rem 0;
        }

        button:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
    

    <div class="logout-container">
        <h2>Você está prestes a sair do sistema.</h2>
        <p>Tem certeza que deseja sair?</p>
        <form method="POST">
            <button type="submit" name="confirmar">Confirmar </button>
            <button type="button" onclick="cancelarLogout()">Cancelar</button>
        </form>
    </div>

    <script>
    function cancelarLogout() {
        alert("Logout cancelado! Você ainda está logado.");
        window.location.href = 'login.php'; 
    }
    </script>
</body>
</html>
