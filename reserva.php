<?php
session_start();
require 'conexao.php';

// Verifica se o ID foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Converte o ID para inteiro

    // Busca os detalhes do imóvel no banco de dados, incluindo o id_proprietario
    $sql = "SELECT ID_imovel, Nome_imovel, Valor, id_proprietario, imagens FROM imovel WHERE ID_imovel = ?";
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

// Resumo e inserção na tabela Locação
$resumo_reserva = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados do formulário
    $data_inicio = $_POST['data_inicio'] ?? null;
    $data_fim = $_POST['data_fim'] ?? null;

    if ($data_inicio && $data_fim) {
        $inicio = new DateTime($data_inicio);
        $fim = new DateTime($data_fim);
        $intervalo = $inicio->diff($fim)->days + 1;

        if ($intervalo < 1) {
            $resumo_reserva = "<p style='color:red;'>Período inválido. A data de fim deve ser igual ou maior que a data de início.</p>";
        } else {
            $valor_diaria = floatval($imovel['Valor']);
            $valor_total = $intervalo * $valor_diaria;

            // Exibe o resumo da reserva
            $resumo_reserva = "
                <h2>Resumo da Reserva</h2>
                <p><strong>Data de Início:</strong> " . htmlspecialchars($data_inicio) . "</p>
                <p><strong>Data de Fim:</strong> " . htmlspecialchars($data_fim) . "</p>
                <p><strong>Valor da Diária:</strong> R$ " . number_format($valor_diaria, 2, ',', '.') . "</p>
                <p><strong>Quantidade de Dias:</strong> $intervalo</p>
                <p><strong>Valor Total:</strong> R$ " . number_format($valor_total, 2, ',', '.') . "</p>
                <form method='POST'>
                    <input type='hidden' name='data_inicio' value='$data_inicio'>
                    <input type='hidden' name='data_fim' value='$data_fim'>
                    <input type='hidden' name='valor_total' value='$valor_total'>
                    <button type='submit' name='confirmar_reserva'>Confirmar Reserva</button>
                </form>
            ";
        }
    }

    // Inserção na tabela Locação
    if (isset($_POST['confirmar_reserva'])) {
        if (!isset($_SESSION['id'])) {
            die("Você precisa estar logado para fazer uma reserva.");
        }

        $id_locador = $_SESSION['id'];
        $id_proprietario = $imovel['id_proprietario'];
        $valor_total = $_POST['valor_total'];

        $sql_insert = "
            INSERT INTO Locação (id_proprietario, id_locador, ID_imovel, Data_inicial, Data_Final, Valor_total)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt_insert = $conexao->prepare($sql_insert);
        $stmt_insert->bind_param("iiissd", $id_proprietario, $id_locador, $id, $data_inicio, $data_fim, $valor_total);

        if ($stmt_insert->execute()) {
            $resumo_reserva = "<p style='color:green;'>Reserva confirmada com sucesso!</p>";
        } else {
            $resumo_reserva = "<p style='color:red;'>Erro ao confirmar a reserva: " . $stmt_insert->error . "</p>";
        }
    }
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
    <h1>Imóvel: <?= htmlspecialchars($imovel['Nome_imovel']); ?></h1>
    <img src="<?= htmlspecialchars($imovel['imagens']); ?>" alt="Imagem do imóvel" style="width:300px;height:200px;">
    <p><strong>Valor da diária:</strong> R$ <?= number_format($imovel['Valor'], 2, ',', '.'); ?></p>

    <h2>Selecione o período de reserva</h2>
    <form method="POST">
        <label for="data_inicio">Data de Início:</label>
        <input type="date" id="data_inicio" name="data_inicio" required>
        <br>
        <label for="data_fim">Data de Fim:</label>
        <input type="date" id="data_fim" name="data_fim" required>
        <br>
        <button type="submit" name="calcular_reserva">Calcular Valor</button>
    </form>

    <?= $resumo_reserva; ?>
</body>
</html>
