<?php
if (isset($_GET['nome'])) {
    $produto_nome = $_GET['nome'];

    // Conexão com o banco de dados usando PDO
    $dsn = "mysql:host=localhost;dbname=sistema_comercial";
    $username = "root";
    $password = "";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Comando SQL para buscar o preço
        $sql = "SELECT preco FROM produtos WHERE nome = :nome";
        $stmt = $pdo->prepare($sql);

        // Liga o parâmetro corretamente
        $stmt->bindParam(':nome', $produto_nome, PDO::PARAM_STR);
        $stmt->execute();

        // Obter o resultado da consulta
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se o produto for encontrado, retorna o preço
        if ($result) {
            echo json_encode(['preco' => $result['preco'], 'found' => true]);
        } else {
            // Se o produto não for encontrado, retorna uma resposta adequada
            echo json_encode(['preco' => null, 'found' => false, 'message' => 'Produto não encontrado']);
        }
    } catch (PDOException $e) {
        // Caso ocorra um erro na execução, retorna um erro de banco de dados
        echo json_encode(['preco' => null, 'found' => false, 'error' => 'Erro ao buscar preço: ' . $e->getMessage()]);
    }
}
?>