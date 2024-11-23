<?php
// Processamento do formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produto_nome = $_POST['produto-nome'];
    $quantidade_avariada = $_POST['quantidade-avariada'];
    $descricao_avaria = $_POST['descricao-avaria'];
    $data_avaria = date("Y-m-d"); // Data atual para registro

    // Conexão com o banco de dados usando PDO
    $dsn = "mysql:host=localhost;dbname=sistema_comercial";
    $username = "root";
    $password = "";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Transação para garantir consistência
        $pdo->beginTransaction();

        // Verificar se o produto existe e pegar o ID
        $sqlProduto = "SELECT id, quantidade FROM produtos WHERE nome = :produto_nome";
        $stmtProduto = $pdo->prepare($sqlProduto);
        $stmtProduto->bindParam(':produto_nome', $produto_nome);
        $stmtProduto->execute();
        $produto = $stmtProduto->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            $produto_id = $produto['id'];
            $quantidade_atual = $produto['quantidade'];

            if ($quantidade_atual < $quantidade_avariada) {
                throw new Exception("A quantidade avariada excede a quantidade disponível em estoque!");
            }

            // Inserir na tabela itens_avariados
            $sqlInsert = "INSERT INTO itens_avariados (produto_nome, quantidade_avariada, descricao_avaria, data_avaria) 
                          VALUES (:produto_nome, :quantidade_avariada, :descricao_avaria, :data_avaria)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->bindParam(':produto_nome', $produto_nome);
            $stmtInsert->bindParam(':quantidade_avariada', $quantidade_avariada);
            $stmtInsert->bindParam(':descricao_avaria', $descricao_avaria);
            $stmtInsert->bindParam(':data_avaria', $data_avaria);
            $stmtInsert->execute();

            // Atualizar a tabela produtos
            $nova_quantidade = $quantidade_atual - $quantidade_avariada;
            $sqlUpdate = "UPDATE produtos 
                          SET quantidade = :nova_quantidade, 
                              quantidade_avariada = quantidade_avariada + :quantidade_avariada 
                          WHERE id = :produto_id";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':nova_quantidade', $nova_quantidade);
            $stmtUpdate->bindParam(':quantidade_avariada', $quantidade_avariada);
            $stmtUpdate->bindParam(':produto_id', $produto_id);
            $stmtUpdate->execute();

            // Commit da transação
            $pdo->commit();
            echo "<script>alert('Avaria registrada e estoque atualizado para o produto \"$produto_nome\"!');</script>";
        } else {
            throw new Exception("Produto \"$produto_nome\" não encontrado no estoque!");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Erro: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itens Avariados - Sistema Comercial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: white; 
            color: black; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            padding: 2rem; 
        }

        header { 
            text-align: center; 
            margin-bottom: 2rem; 
        }

        .avariados-container { 
            background-color: white; 
            padding: 2rem; 
            border-radius: 10px; 
            width: 100%; 
            max-width: 600px; 
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
            margin-top: 50px;
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
            width: 100%;
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
            width: 100%;
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
    </style>
</head>
<body>
<a href="estoque.php" class="btn-back">← Voltar</a>
    <div class="avariados-container">
        <h2 align="center">Registrar Item Avariado</h2>
        <br>
        <form id="avariados-form" method="POST">
            <label for="produto-nome">Nome do Produto:</label>
            <input type="text" id="produto-nome" name="produto-nome" placeholder="Digite o nome do produto" onkeyup="autocompleteProduct()" autocomplete="off" required>
            <div class="list" id="suggestions"></div>

            <label for="quantidade-avariada">Quantidade Avariada:</label>
            <input type="number" name="quantidade-avariada" id="quantidade-avariada" min="1" required>

            <label for="descricao-avaria">Descrição da Avaria:</label>
            <textarea name="descricao-avaria" id="descricao-avaria" required></textarea>

            <label for="preco">Preço Total:</label>
            <input type="text" id="preco" name="preco" readonly>

            <button type="submit">Registrar Avaria</button>
        </form>
    </div>

    <script>
let precoOriginal = 0; // Variável para armazenar o preço original do produto

function autocompleteProduct() {
    const input = document.getElementById("produto-nome");
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
                        console.log("Produto selecionado:", product.nome); // Verifique se o nome está sendo passado corretamente
                        fetchPrice(product.nome); // Passando o nome do produto corretamente
                    };
                    suggestions.appendChild(div);
                });
            });
    } else {
        suggestions.innerHTML = "";
    }
}

function fetchPrice(productName) {
    fetch("get_price.php?nome=" + productName)
        .then(response => response.json())
        .then(data => {
            precoOriginal = parseFloat(data.preco); // Armazena o preço original
            const priceInput = document.getElementById("preco");
            priceInput.value = precoOriginal.toFixed(2); // Exibe o preço unitário inicialmente
        })
        .catch(error => {
            console.error("Erro ao buscar o preço:", error);
        });
}

document.getElementById("quantidade-avariada").addEventListener("input", function () {
    const quantidade = document.getElementById("quantidade-avariada").value;
    const priceInput = document.getElementById("preco");
    if (precoOriginal && quantidade) {
        priceInput.value = (precoOriginal * parseFloat(quantidade)).toFixed(2); // Atualiza o total
    } else {
        priceInput.value = precoOriginal.toFixed(2); // Caso não haja quantidade, mostra o preço unitário
    }
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
