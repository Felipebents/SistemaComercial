<?php
// Conexão com o banco de dados usando PDO
$dsn = "mysql:host=localhost;dbname=sistema_comercial"; // Nome do banco de dados
$username = "root"; // Usuário padrão do MySQL
$password = ""; // Senha padrão (deixe vazio se não houver)

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar se o ID do produto foi passado
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Consulta para obter os dados do produto
        $sql = "SELECT * FROM produtos WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se o produto não existir, redireciona para outra página
        if (!$produto) {
            echo "<script>alert('Produto não encontrado!');</script>";
            echo "<script>window.location.href = 'dashboard.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('ID do produto não fornecido!');</script>";
        echo "<script>window.location.href = 'dashboard.php';</script>";
        exit;
    }

    // Atualizar o produto se o formulário foi enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nome = $_POST['nome'];
        $preco = $_POST['preco'];
        $quantidade = $_POST['quantidade'];
        $descricao = $_POST['descricao'];

        $sql = "UPDATE produtos SET nome = :nome, preco = :preco, quantidade = :quantidade, descricao = :descricao WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':preco', $preco);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':descricao', $descricao);

        if ($stmt->execute()) {
            echo "<script>alert('Produto atualizado com sucesso!');</script>";
            echo "<script>window.location.href = 'dashboard.php';</script>";
        } else {
            echo "Erro ao atualizar o produto.";
        }
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Sistema Comercial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #003366;
            color: #FFD700;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
        }

        .btn-back {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 10px 15px;
            background-color: #FF8C00;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-back:hover {
            background-color: black;
        }

        .editar-container {
            background-color: #FF8C00;
            padding: 2rem;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
        }

        .editar-container h2 {
            color: #000;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        label {
            color: #000;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            padding: 0.8rem;
            border: 1px solid #000;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            padding: 1rem;
            background-color: #003366;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: #003365;
        }
    </style>
</head>
<body>
<a href="dashboard.php" class="btn-back">← Voltar</a>

<div class="editar-container">
    <h2>Editar Produto</h2>
    <form method="POST">
        <label for="nome">Nome do Produto:</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>

        <label for="quantidade">Quantidade:</label>
        <input type="number" id="quantidade" name="quantidade" value="<?php echo htmlspecialchars($produto['quantidade']); ?>" min="1" required>

        <label for="preco">Preço:</label>
        <input type="number" id="preco" name="preco" value="<?php echo htmlspecialchars($produto['preco']); ?>" min="0.01" step="0.01" required>

        <label for="descricao">Descrição (opcional):</label>
        <textarea id="descricao" name="descricao"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>

        <button type="submit">Atualizar Produto</button>
    </form>
</div>
</body>
</html>
