<?php
// Conexão com o banco de dados
$dsn = "mysql:host=localhost;dbname=sistema_comercial";
$username = "root";
$password = "";

try {
    // Definir o fuso horário para o horário de Brasília
    date_default_timezone_set('America/Sao_Paulo');

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Função para validar e formatar as datas
    function validarData($data) {
        $date = DateTime::createFromFormat('Y-m-d', $data);
        return $date && $date->format('Y-m-d') === $data;
    }

    // Filtro de datas para o relatório
    $periodo = filter_input(INPUT_POST, 'periodo', FILTER_SANITIZE_STRING) ?? 'dia';
    $data_inicio = filter_input(INPUT_POST, 'data_inicio', FILTER_SANITIZE_STRING);
    $data_fim = filter_input(INPUT_POST, 'data_fim', FILTER_SANITIZE_STRING);

    // Ajusta a data de início e fim com base no período selecionado
    if ($periodo === 'dia') {
        $data_inicio = $data_fim = date('Y-m-d');
    } elseif ($periodo === 'semana') {
        $data_inicio = date('Y-m-d', strtotime('-7 days'));
        $data_fim = date('Y-m-d');
    } elseif ($periodo === 'mes') {
        $data_inicio = date('Y-m-d', strtotime('-1 month'));
        $data_fim = date('Y-m-d');
    }

    // Verificar se as datas fornecidas são válidas
    if ($data_inicio && !validarData($data_inicio)) {
        throw new Exception('Data de início inválida');
    }
    if ($data_fim && !validarData($data_fim)) {
        throw new Exception('Data de fim inválida');
    }

    // Inicializa a variável $where_clause com base nos filtros de data
    $where_clause = "";
    if ($data_inicio && $data_fim) {
        $where_clause = "WHERE DATE(data_venda) BETWEEN :data_inicio AND :data_fim";
    }

    // Consulta para obter o resumo diário de vendas
    $stmt_vendas = $pdo->prepare("SELECT SUM(total) AS total_vendas, COUNT(id) AS quantidade_vendas FROM vendas WHERE DATE(data_venda) = CURDATE()");
    $stmt_vendas->execute();
    $resumo_vendas = $stmt_vendas->fetch(PDO::FETCH_ASSOC);

    // Consulta para obter vendas no intervalo especificado
    $stmt = $pdo->prepare("SELECT SUM(total) AS total_filtro, COUNT(id) AS quantidade_filtro FROM vendas $where_clause");
    if ($data_inicio && $data_fim) {
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
    }
    $stmt->execute();
    $resumo_filtro = $stmt->fetch(PDO::FETCH_ASSOC);

    // Consulta para obter o resumo por forma de pagamento
    $stmt_pagamentos = $pdo->prepare("SELECT forma_pagamento, COUNT(id) AS quantidade_pagamento, SUM(total) AS total_pagamento FROM vendas $where_clause GROUP BY forma_pagamento");
    if ($data_inicio && $data_fim) {
        $stmt_pagamentos->bindParam(':data_inicio', $data_inicio);
        $stmt_pagamentos->bindParam(':data_fim', $data_fim);
    }
    $stmt_pagamentos->execute();
    $vendas_por_pagamento = $stmt_pagamentos->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obter a quantidade de vendas por forma de pagamento
    $stmt_quantidade_pagamentos = $pdo->prepare("SELECT forma_pagamento, COUNT(id) AS quantidade_pagamento FROM vendas $where_clause GROUP BY forma_pagamento");
    if ($data_inicio && $data_fim) {
        $stmt_quantidade_pagamentos->bindParam(':data_inicio', $data_inicio);
        $stmt_quantidade_pagamentos->bindParam(':data_fim', $data_fim);
    }
    $stmt_quantidade_pagamentos->execute();
    $vendas_quantidade_por_pagamento = $stmt_quantidade_pagamentos->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obter o total de avarias (preço das avarias) e a quantidade de avarias (total de itens avariados)
$stmt_avarias = $pdo->prepare("
SELECT 
    SUM(i.quantidade_avariada * p.preco) AS total_avarias, 
    SUM(i.quantidade_avariada) AS quantidade_avarias 
FROM 
    itens_avariados i
JOIN 
    produtos p ON i.produto_nome = p.nome
");
$stmt_avarias->execute();
$resumo_avarias = $stmt_avarias->fetch(PDO::FETCH_ASSOC);




} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit;
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financeiro - Sistema Supermercado</title>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: white;
    color: #333;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
}

header {
    text-align: center;
    margin-bottom: 2rem;
    color: black;
}

header h1 {
    font-size: 2.2rem;
}

.content {
    display: flex;
    justify-content: space-evenly;
    gap: 2rem;
    width: 100%;
    max-width: 1200px;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.card {
    background-color: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
    width: 100%;
    max-width: 350px;
    text-align: center;
    margin-bottom: 2rem; /* Espaçamento entre os cards */
}

.card h2 {
    color: black;
    font-size: 1.4rem;
    margin-bottom: 1rem;
}

.saldo-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.saldo-item:last-child {
    border-bottom: none;
}

.filter-container {
    margin: 2rem 0;
    display: flex;
    gap: 1rem;
}

.filter-container input, .filter-container select {
    padding: 0.5rem;
    font-size: 1rem;
    border-radius: 5px;
    border: 1px solid #333;
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

/* Estilo para responsividade */
@media (max-width: 768px) {
    .content {
        flex-direction: column;
        align-items: center;
    }

    .card {
        max-width: 100%;
    }

    .filter-container {
        flex-direction: column;
        align-items: center;
    }

    .filter-container input,
    .filter-container select {
        width: 100%;
        margin-bottom: 1rem;
    }
}
    </style>
</head>
<body>
    <a href="dashboard.php" class="btn-back">← Voltar</a>
        <br><br>
    <header>
        <h1>Financeiro - Sistema Supermercado</h1>
    </header>

    <!-- Filtro de data -->
    <div class="filter-container">
        <form action="" method="POST">
            <select name="periodo" onchange="ajustarDatas(this.value)">
                <option value="dia" <?= ($periodo == 'dia') ? 'selected' : '' ?>>Diário</option>
                <option value="semana" <?= ($periodo == 'semana') ? 'selected' : '' ?>>Semanal</option>
                <option value="mes" <?= ($periodo == 'mes') ? 'selected' : '' ?>>Mensal</option>
                <option value="personalizado" <?= ($periodo == 'personalizado') ? 'selected' : '' ?>>Personalizado</option>
            </select>
            <input type="date" name="data_inicio" value="<?= $data_inicio ?>" required>
            <input type="date" name="data_fim" value="<?= $data_fim ?>" required>
            <button type="submit" name="filtrar">Filtrar</button>
        </form>
    </div>

    <!-- Cards Lado a Lado -->
    <div class="content">
        <div class="card">
            <h2>Resumo Filtrado</h2>
            <div class="saldo-item">
                <span>Total de Vendas:</span>
                <span><strong>R$ <?= number_format($resumo_filtro['total_filtro'] ?? 0, 2, ',', '.'); ?></strong></span>
            </div>
            <div class="saldo-item">
                <span>Quantidade de Vendas:</span>
                <span><strong><?= $resumo_filtro['quantidade_filtro'] ?? 0; ?></strong></span>
            </div>
        </div>

        <div class="card">
            <h2>Vendas por Forma de Pagamento</h2>
            <?php foreach ($vendas_por_pagamento as $venda) : ?>
                <div class="saldo-item">
                    <span><?= $venda['forma_pagamento']; ?>:</span>
                    <span><strong>R$ <?= number_format($venda['total_pagamento'], 2, ',', '.'); ?></strong></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2>Quantidade de Vendas por Forma de Pagamento</h2>
            <?php foreach ($vendas_quantidade_por_pagamento as $venda) : ?>
                <div class="saldo-item">
                    <span><?= $venda['forma_pagamento']; ?>:</span>
                    <span><strong><?= $venda['quantidade_pagamento']; ?> vendas</strong></span>
                </div>
            <?php endforeach; ?>
        </div>
        </div>

        <div class="card">
    <h2>Itens Avariados</h2>
    <div class="saldo-item">
        <span>Total de Avarias (R$):</span>
        <span><strong>R$ <?= number_format($resumo_avarias['total_avarias'], 2, ',', '.'); ?></strong></span>
    </div>
    <div class="saldo-item">
        <span>Quantidade de Avarias:</span>
        <span><strong><?= $resumo_avarias['quantidade_avarias']; ?> itens</strong></span>
    </div>
</div>
    </div>
</body>
</html>