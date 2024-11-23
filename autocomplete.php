<?php
// Conectar ao banco de dados
include('db.php');

// Receber o valor de busca
$query = $_GET['query'] ?? '';

if ($query) {
    // Buscar produtos que contenham a query no nome
    $stmt = $pdo->prepare("SELECT nome FROM produtos WHERE nome LIKE ?");
    $stmt->execute([ '%' . $query . '%' ]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retornar em formato JSON
    echo json_encode($products);
}
?>