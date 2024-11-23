<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Redireciona para login se não estiver logado
    exit();
}

// Conexão com o banco de dados
$host = "localhost";
$user = "root";
$password = "";
$dbname = "sistema_comercial";

$conn = new mysqli($host, $user, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Paginação
$produtosPorPagina = 10; // Quantidade de produtos por página
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $produtosPorPagina;

// Filtra produtos
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$sqlFiltro = $filtro ? "WHERE nome LIKE '%$filtro%'" : '';

// Seleciona os produtos com base no filtro e na paginação
$sql = "SELECT id, nome, preco, quantidade FROM produtos $sqlFiltro LIMIT $produtosPorPagina OFFSET $offset";
$result = $conn->query($sql);

// Conta o total de produtos para a paginação
$sqlTotal = "SELECT COUNT(*) as total FROM produtos $sqlFiltro";
$totalResult = $conn->query($sqlTotal);
$totalProdutos = $totalResult->fetch_assoc()['total'];
$totalPaginas = ceil($totalProdutos / $produtosPorPagina);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Estoque - Sistema Comercial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: white; 
            color: black;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .estoque-container {
            background-color: white; 
            padding: 2rem;
            border-radius: 10px;
            max-width: 1000px;
            width: 100%;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.8);
            text-align: center;
            margin-top: 50px;
        }
        .card-button {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            justify-content: center;
        }
        .card {
            text-decoration: none;
            padding: 1.5rem;
            flex: 1;
            text-align: center;
            font-weight: bold;
            color: #FFF;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .registrar {
            background-color: black;
        }
        .registrar:hover {
            background-color: #333;
        }
        .avariados {
            background-color: black;
        }
        .avariados:hover {
            background-color: #333;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
        .pagination a {
            padding: 8px 12px;
            margin: 0 4px;
            border-radius: 5px;
            text-decoration: none;
            color: black;
            background-color: white;
        }
        .pagination a.active {
            background-color: black;
            color: white;
        }
        .table-container {
            overflow-x: auto;
        }
        .table {
            background-color: white;
        }
        .btn-primary {
            background-color: black;
            border: none;
        }
        .btn-primary:hover {
            background-color: #333;
        }
        .btn-back {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 10px 15px;
            background-color: black;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-back:hover {
            background-color: #333;
        }
    </style>
</head>
<body>
<a href="dashboard.php" class="btn-back">← Voltar</a>
<div class="estoque-container">
    <h2>Gerenciamento de Estoque</h2>

    <!-- Botões de Atalhos -->
    <div class="card-button">
        <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] == 2): ?>
            <a href="recebimento.php" class="card registrar">Atualizar Produtos</a>
            <a href="itensavariados.php" class="card avariados">Itens Avariados</a>
        <?php else: ?>
            <div class="card registrar" onclick="exibirMensagem();">Atualizar Produtos</div>
            <div class="card avariados" onclick="exibirMensagem();">Itens Avariados</div>
        <?php endif; ?>
    </div>

    <!-- Campo de Busca -->
    <form method="GET" action="" class="d-flex justify-content-center mb-3">
        <input type="text" name="filtro" value="<?= htmlspecialchars($filtro) ?>" class="form-control me-2" placeholder="Buscar produto" style="max-width: 300px;">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    <!-- Tabela de Estoque -->
    <div class="table-container">
        <table class="table table-hover text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Quantidade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($product = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td><?= $product['nome'] ?></td>
                        <td>R$ <?= number_format($product['preco'], 2, ',', '.') ?></td>
                        <td><?= $product['quantidade'] ?></td>
                        <td class="<?= $product['quantidade'] < 5 ? 'text-danger' : 'text-success' ?>">
                            <?= $product['quantidade'] < 5 ? 'Estoque Baixo' : 'Estoque Normal' ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <a href="?pagina=<?= $i ?>&filtro=<?= urlencode($filtro) ?>" class="<?= $i == $paginaAtual ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</div>
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
