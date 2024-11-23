<?php
session_start();
include('db.php'); // Conectando ao banco de dados

// Inicializar variáveis
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Inicializar carrinho se não existir
}
$totalPrice = 0.00;

// Adicionando produtos ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $productName = $_POST['product_name'];
        $productQuantity = $_POST['product_quantity'];

        // Obter informações do produto pelo nome
        $stmt = $pdo->prepare("SELECT id, nome, preco, quantidade FROM produtos WHERE nome = ?");
        $stmt->execute([$productName]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Verificar se há estoque suficiente
            if ($product['quantidade'] >= $productQuantity) {
                $totalProductPrice = $product['preco'] * $productQuantity;
                $_SESSION['cart'][] = [
                    'id' => $product['id'],
                    'nome' => $product['nome'],
                    'quantidade' => $productQuantity,
                    'preco' => $product['preco'],
                    'total' => $totalProductPrice
                ];
            } else {
                echo "<script>alert('Estoque insuficiente para o produto {$product['nome']}!');</script>";
            }
        } else {
            echo "<script>alert('Produto não encontrado!');</script>";
        }
    } elseif ($_POST['action'] === 'choose_payment') {
        $paymentMethod = $_POST['payment_method'];
        $_SESSION['payment_method'] = $paymentMethod;

        // Calcular o total do carrinho
        foreach ($_SESSION['cart'] as $item) {
            $totalPrice += $item['total'];
        }

        // Inserir a venda no banco de dados
        $stmt = $pdo->prepare("INSERT INTO vendas (total, forma_pagamento, usuario_id) VALUES (?, ?, ?)");
        $stmt->execute([$totalPrice, $paymentMethod, $_SESSION['usuario_id']]);

        // Obter o ID da venda recém-criada
        $saleId = $pdo->lastInsertId();

        // Atualizar o estoque após a venda
        foreach ($_SESSION['cart'] as $item) {
            // Reduzir a quantidade de cada produto vendido
            $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
            $stmt->execute([$item['quantidade'], $item['id']]);
        }

        // Limpar o carrinho após a venda
        unset($_SESSION['cart']);
        $totalPrice = 0.00;
        unset($_SESSION['payment_method']);

        header('Location: pdv.php');
        exit();
    } elseif ($_POST['action'] === 'clear') {
        unset($_SESSION['cart']);
        $totalPrice = 0.00;
        header('Location: pdv.php');
        exit();
    } elseif ($_POST['action'] === 'edit') {
        // Editar a quantidade do produto no carrinho
        $productId = $_POST['product_id'];
        $newQuantity = $_POST['new_quantity'];

        // Atualizar a quantidade do produto no carrinho
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantidade'] = $newQuantity;
                $item['total'] = $item['preco'] * $newQuantity;
            }
        }

        header('Location: pdv.php');
        exit();
    } elseif ($_POST['action'] === 'delete') {
        // Excluir o produto do carrinho
        $productId = $_POST['product_id'];

        // Remover o produto do carrinho
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $productId) {
                unset($_SESSION['cart'][$key]);
            }
        }

        header('Location: pdv.php');
        exit();
    }
}

// Calcular o total do carrinho para exibição
$totalPrice = 0.00;
foreach ($_SESSION['cart'] as $item) {
    $totalPrice += $item['total'];
}

if (isset($_POST['action']) && $_POST['action'] === 'choose_payment') {
    $valorPago = (float)$_POST['valor_pago'];
    $troco = $valorPago - $totalPrice;
    // Exemplo de uso:
    echo "<script>alert('Troco: R$ " . number_format($troco, 2, ',', '.') . "');</script>";
}

?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venda (PDV) - Sistema Comercial</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: white;
            padding: 2rem;
            font-family: Arial, sans-serif;
        }

        header {
            text-align: center;
            margin-bottom: 2rem;
        }

        h1 {
            font-size: 2.5rem;
        }

        .section {
            background-color: black;
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .section h2 {
            color: white;
            margin-bottom: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.8rem;
            border-bottom: 1px solid white;
            text-align: center;
        }

        th {
            background-color: #fff;
            color: black;
        }

        button, select {
            width: 100%;
            padding: 0.8rem;
            background-color: white;
            color: black;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover, select:hover {
            background-color: white;
            color: black;
        }

        input[class="text"], input[class="text"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #000;
            border-radius: 5px;
            margin-bottom: 1rem;
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
        .btn1, .btn2{
            width: 20%;
        }
        .qtd{
            width: 20%;
          
        
        }

        /* Responsividade para a tabela */
.table-responsive {
    overflow-x: auto;
}

section {
    padding: 1.5rem;
    margin-bottom: 1rem;
    border-radius: 10px;
}

/* Responsividade para os campos e botões */
.qtd, .btn1, .btn2 {
    width: 100%; /* Garantir que botões e inputs ocupem toda a largura disponível */
}

@media (max-width: 768px) {
    .section input, .section select, .btn {
        width: 100%; /* Garantir que os inputs e botões se ajustem na tela */
        margin-bottom: 0.5rem;
    }

    .table-responsive {
        max-width: 100%;
    }

    /* Ajustar a tabela para caber em telas pequenas */
    table {
        width: 100%;
    }

    th, td {
        font-size: 0.875rem; /* Ajuste da fonte para uma tela menor */
    }
}

/* Ajuste de largura dos inputs */
input[type="number"], input[type="text"], select {
    width: 100%; /* Forçar o campo a ocupar toda a largura da div */
    padding: 0.8rem;
    border: 1px solid #000;
    border-radius: 5px;
    margin-bottom: 1rem;
}

/* Ajuste de largura para botões */
button {
    width: 100%;
    padding: 1rem;
    background-color: #28a745;
    color: white;
    font-weight: bold;
    border-radius: 5px;
    border: none;
    cursor: pointer;
}

button:hover {
    background-color: #218838;
}

/* Responsividade para pequenos dispositivos */
@media (max-width: 576px) {
    .btn1, .btn2, .qtd {
        width: 100%;
    }
}


    </style>

</head>
<body>

<a href="dashboard.php" class="btn-back">← Voltar</a> 
<br><br>
<h1 align="center">Ponto de Venda</h1>
<br>
<div class="container">
    <div class="row">
        <!-- Seção para Produtos Selecionados -->
        <div class="col-lg-8">
        <div class="section">
    <h2>Produtos Selecionados</h2>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço</th>
                    <th>Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody id="product-list">
                <?php if (isset($_SESSION['cart'])): ?>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nome']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantidade']); ?></td>
                            <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($item['total'], 2, ',', '.'); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input class="qtd" type="number" name="new_quantity" value="<?php echo $item['quantidade']; ?>" min="1" required>
                                    <button type="submit" class="btn btn-warning btn1">Editar</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn2">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="clear">
        <button type="submit" class="btn btn-danger">Limpar Carrinho</button>
    </form>
</div>

        </div>

        <!-- Seção para Adicionar Produto e Finalizar Venda -->
        <div class="col-lg-4">
            <div class="section">
                <h2>Adicionar Produto</h2>
                <form id="product-form" method="POST">
                    <input type="hidden" name="action" value="add">
                    <label for="product-name">Nome do Produto</label>
                    <input class="text" type="text" id="product-name" name="product_name" placeholder="Digite o nome do produto" onkeyup="autocompleteProduct()" autocomplete="off">
                    <div class="list" id="suggestions"></div>
                    <label for="product-quantity">Quantidade</label>
                    <input class="text" type="number" id="product-quantity" name="product_quantity" placeholder="Quantidade" min="1" required>
                    <button type="submit" class="btn btn-success">Adicionar ao Carrinho</button>
                </form>
                <br>

                <h2>Finalizar Venda</h2>
                <br>
                <h4>Total: R$ <?php echo number_format($totalPrice, 2, ',', '.'); ?></h4>
<label for="valor-pago">Valor Pago:</label>
<input class="text" type="number" id="valor-pago" name="valor_pago" placeholder="Digite o valor pago" step="0.01" min="<?php echo $totalPrice; ?>" required>
<h4>Troco: R$ <span id="troco">0,00</span></h4>

                <form method="POST">
                    <input type="hidden" name="action" value="choose_payment">
                    <label for="payment-method">Escolha a forma de pagamento:</label>
                    <select id="payment-method" name="payment_method" required>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="debito">Cartão de Debito</option>
                        <option value="credito">Cartão de Crédito</option>
                        <option value="pix">PIX</option>
                    </select>
                    <br> <br>
                    <button type="submit" class="btn btn-primary">Finalizar Venda</button>
                </form>
            </div>
        </div>
    </div>
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

<script>
    document.getElementById('valor-pago').addEventListener('input', function() {
    const total = <?php echo $totalPrice; ?>;
    const valorPago = parseFloat(this.value);
    const troco = valorPago > total ? (valorPago - total).toFixed(2) : '0.00';
    document.getElementById('troco').innerText = troco.replace('.', ',');
});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>