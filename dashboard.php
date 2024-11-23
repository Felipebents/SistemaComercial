<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redireciona para a página de login se não estiver logado
    exit();
}

// Conexão com o banco de dados
require 'db.php'; // Inclua seu arquivo de conexão ao banco de dados

// Recupera os dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Verifica o nível de acesso do usuário
$is_admin = $usuario['nivel_acesso'] >= 2; // Se o nível de acesso for maior ou igual a 2, o usuário é admin
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Comercial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: white;
            color: black;
        }

        .navbar {
            background-color: black;
            padding: 0.5rem;
            border-radius: 1px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
        }

        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            text-align: left;
            width: 50%;
            display: block;
        }

        .nav-link {
            color: white !important;
        }

        .container-dashboard {
            margin-top: 50px;
            justify-content: center;
            align-items: center;
        }

        .dashboard-item {
            text-decoration: none;
            margin-top: 20px;
            background-color: white;
            color: black;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 2px solid black;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
        }

        .dashboard-item:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .dashboard-item-header {
            font-size: 1.2rem;
            color: white;
            background-color: black;
            width: 100%;
            padding: 10px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .container-dashboard {
            flex: 1;
        }

        footer {
            background-color: black;
            color: white;
            padding: 10px 0;
            text-align: center;
            flex-shrink: 0;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .container-dashboard {
                margin-top: 30px;
            }
        }

        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1.2rem;
            }

            .dashboard-item {
                margin-bottom: 15px;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand mx-auto">Mini Box Maciel</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($is_admin) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastro.php">Cadastrar Usuário</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="sair.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container container-dashboard">
        <div class="row text-center">
            <div class="col-md-4 col-sm-12">
                <a href="pdv.php" class="dashboard-item">
                    <div class="dashboard-item-header">Venda (PDV)</div>
                </a>
            </div>
            <div class="col-md-4 col-sm-12">
                <a href="estoque.php" class="dashboard-item">
                    <div class="dashboard-item-header">Estoque</div>
                </a>
            </div>

            <?php if ($is_admin) : ?>
                <div class="col-md-4 col-sm-12">
                    <a href="financeiro.php" class="dashboard-item">
                        <div class="dashboard-item-header">Financeiro</div>
                    </a>
                </div>
            <?php else : ?>
                <div class="col-md-4 col-sm-12">
                    <div class="dashboard-item" onclick="exibirMensagem();">
                        <div class="dashboard-item-header">Financeiro</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-5 text">
        <p>&copy; 2024 Mini Box Maciel.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exibirMensagem() {
            const alerta = document.createElement('div');
            alerta.className = 'alert alert-danger alert-dismissible fade show';
            alerta.style.position = 'fixed';
            alerta.style.top = '20px';
            alerta.style.right = '20px';
            alerta.style.zIndex = '1050';
            alerta.innerHTML = `
                <strong>Acesso Negado!</strong> Somente o ADM pode acessar esta funcionalidade.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            document.body.appendChild(alerta);

            setTimeout(() => {
                alerta.classList.remove('show');
                setTimeout(() => alerta.remove(), 150);
            }, 3000);
        }
    </script>
</body>

</html>
