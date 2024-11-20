<?php
session_start();
require 'conexao.php';

// Verifica se o ID foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Converte o ID para inteiro

    // Busca os detalhes do imóvel no banco de dados
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

// Busca os dados do usuário logado
$usuario = [];
if (isset($_SESSION['id'])) {
    $id_locador = $_SESSION['id'];
    $sql_usuario = "SELECT CPF, telefone FROM usuario WHERE id = ?";
    $stmt_usuario = $conexao->prepare($sql_usuario);
    $stmt_usuario->bind_param("i", $id_locador);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();

    if ($result_usuario->num_rows > 0) {
        $usuario = $result_usuario->fetch_assoc();
    } else {
        die("Usuário não encontrado.");
    }
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
            echo "<p>Você precisa estar logado para fazer uma reserva. <a href='login.php'>Faça seu login aqui.</a></p>";
            exit;
        }
    
        $id_locador = $_SESSION['id']; // ID do locador (usuário logado)
        $cpf = $usuario['CPF'] ?? null;
        $telefone = $usuario['telefone'] ?? null;
    
        if (!$cpf || !$telefone) {
            die("<p style='color:red;'>Erro: CPF ou telefone do locador não encontrado.</p>");
        }
    
        // Verifica se o locador já existe
        $verificar_locador_sql = "SELECT COUNT(*) FROM locador WHERE id_locador = ?";
        $stmt_verificar = $conexao->prepare($verificar_locador_sql);
        $stmt_verificar->bind_param("i", $id_locador);
        $stmt_verificar->execute();
        $stmt_verificar->bind_result($locador_existe);
        $stmt_verificar->fetch();
        $stmt_verificar->close(); // Fecha o statement para liberar a conexão
    
        if ($locador_existe == 0) {
            // Insere o locador apenas se não existir
            $locador_sql = "INSERT INTO locador (id_locador, CPF, telefone) VALUES (?, ?, ?)";
            $stmt_locador = $conexao->prepare($locador_sql);
            $stmt_locador->bind_param("iss", $id_locador, $cpf, $telefone);
    
            if (!$stmt_locador->execute()) {
                echo "<p style='color:red;'>Erro ao salvar os dados do locador: " . $stmt_locador->error . "</p>";
                exit;
            }
            $stmt_locador->close(); // Fecha o statement para evitar problemas
        }
    
        // Insere os dados na tabela locação
        $id_proprietario = $imovel['id_proprietario'];
        $locacao_sql = "INSERT INTO `locação` (id_proprietario, id_locador, id_imovel, data_inicial, data_final, valor_total)
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_locacao = $conexao->prepare($locacao_sql);
        $stmt_locacao->bind_param("iiissd", $id_proprietario, $id_locador, $id, $data_inicio, $data_fim, $valor_total);
    
        if ($stmt_locacao->execute()) {
            echo "<p style='color:green;'>Reserva confirmada com sucesso!</p>";
        } else {
            echo "<p style='color:red;'>Erro ao confirmar a reserva: " . $stmt_locacao->error . "</p>";
        }
        $stmt_locacao->close(); // Fecha o statement para evitar problemas
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
