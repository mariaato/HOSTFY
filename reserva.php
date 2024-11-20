<?php
session_start();
require 'conexao.php';

// Verifica se o ID foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Converte o ID para inteiro

    // Busca os detalhes do imóvel no banco de dados
    $sql = "SELECT * FROM imovel WHERE ID_imovel = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $imovel = $result->fetch_assoc();
    } else {
        die("Imóvel não encontrado.");
    }
} else {
    die("ID inválido.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva do Imóvel</title>
</head>
<body>
    <h1>Reserva do Imóvel: <?= htmlspecialchars($imovel['Nome_imovel']); ?></h1>
    <p><strong>Cidade:</strong> <?= htmlspecialchars($imovel['Cidade']); ?></p>
    <p><strong>Descrição:</strong> <?= htmlspecialchars($imovel['Descrição']); ?></p>
    <p><strong>Valor:</strong> R$ <?= number_format($imovel['Valor'], 2, ',', '.'); ?></p>
    <a href="confirmar_reserva.php?id=<?= $imovel['ID_imovel']; ?>">Reservar agora</a>
</body>
</html>
