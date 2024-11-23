<?php
// Processamento do formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação dos dados enviados pelo formulário
    if (isset($_POST['nome'], $_POST['preco'], $_POST['quantidade']) && 
        !empty($_POST['nome']) && !empty($_POST['preco']) && !empty($_POST['quantidade'])) {
        
        $nome = $_POST['nome'];
        $preco = $_POST['preco'];
        $quantidade = $_POST['quantidade'];

        // Conexão com o banco de dados usando PDO
        $dsn = "mysql:host=localhost;dbname=sistema_comercial";
        $username = "root";
        $password = "";

        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verifica se o produto já existe
            $sql = "SELECT * FROM produtos WHERE nome = :nome";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->execute();
            $produtoExistente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($produtoExistente) {
                // Se o produto já existir, atualiza a quantidade
                $novaQuantidade = $produtoExistente['quantidade'] + $quantidade;
                $sql = "UPDATE produtos SET preco = :preco, quantidade = :quantidade WHERE nome = :nome";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':preco', $preco);
                $stmt->bindParam(':quantidade', $novaQuantidade);
                
                if ($stmt->execute()) {
                    echo "<script>alert('Estoque atualizado para o produto \"$nome\" com nova quantidade de $novaQuantidade!');</script>";
                } else {
                    echo "Erro ao atualizar o produto.";
                }
            } else {
                // Se o produto não existir, insere um novo produto
                $sql = "INSERT INTO produtos (nome, preco, quantidade) 
                        VALUES (:nome, :preco, :quantidade)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':preco', $preco);
                $stmt->bindParam(':quantidade', $quantidade);
                
                if ($stmt->execute()) {
                    echo "<script>alert('Produto \"$nome\" adicionado com sucesso!');</script>";
                } else {
                    echo "Erro ao adicionar produto.";
                }
            }
        } catch (PDOException $e) {
            echo "Erro: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Todos os campos são obrigatórios!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recebimento de Produtos - Sistema Comercial</title>
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
            background-color: white; 
            color: #FFD700; 
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
        .recebimento-container {
            background-color: white; 
            padding: 2rem;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
            margin-top: 50px;
        }
        .recebimento-container h2 {
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
            background-color: black;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
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
        .list {
            color: black;
        }
        .list:hover {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <br>
    <a href="estoque.php" class="btn-back">← Voltar</a>
    <div class="recebimento-container">
        <h2>Atualizar Produto</h2>
        <form id="recebimento-form" method="POST">
            <label for="nome">Nome do Produto:</label>
            <input type="text" id="product-name" name="nome" placeholder="Digite o nome do produto" onkeyup="autocompleteProduct()" autocomplete="off">
            <div class="list" id="suggestions"></div>

            <label for="quantidade">Quantidade:</label>
            <input type="number" id="quantidade" name="quantidade" min="1" required>

            <label for="preco">Preço</label>
            <input type="number" id="preco" name="preco" min="1" step="0.01" required>

            <button type="submit">Registrar Produto</button>
        </form>
    </div>
    <script>
    function autocompleteProduct() {
        const input = document.getElementById("product-name");
        const suggestions = document.getElementById("suggestions");

        if (input.value.length > 1) {
            const query = input.value;
            fetch("autocomplete.php?query=" + query)
                .then(response => response.json())
                .then(data => {
                    suggestions.innerHTML = "";
                    data.forEach(product => {
                        const div = document.createElement("div");
                        div.textContent = product.nome;
                        div.onclick = function() {
                            input.value = product.nome;
                            suggestions.innerHTML = "";
                        };
                        suggestions.appendChild(div);
                    });
                });
        } else {
            suggestions.innerHTML = "";
        }
    }
    </script>
</body>
</html>
