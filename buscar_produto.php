<?php
session_start();
include('db.php'); // Conexão com o banco de dados

if (isset($_GET['nome'])) {
    $nome = $_GET['nome'];
    
    // Buscar produtos cujo nome contenha o texto digitado
    $stmt = $pdo->prepare("SELECT nome FROM produtos WHERE nome LIKE ? LIMIT 5");
    $stmt->execute(["%$nome%"]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Exibir cada sugestão como uma opção clicável
    foreach ($produtos as $produto) {
        echo "<div onclick=\"selectProduct('{$produto['nome']}')\">{$produto['nome']}</div>";
    }
}
?>
