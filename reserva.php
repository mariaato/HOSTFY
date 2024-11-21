<?php
session_start();
require 'conexao.php';

// Verifica se o ID foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Converte o ID para inteiro

    // Busca os detalhes do imóvel no banco de dados
    $sql = "SELECT ID_imovel, CEP, Nome_imovel, Rua, Bairro, Cidade, UF, Valor, Descrição, id_proprietario, imagens 
            FROM imovel WHERE ID_imovel = ?";
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

// Verifica as datas indisponíveis
$datas_indisponiveis = [];
$sql_datas = "SELECT data_inicial, data_final FROM `locação` WHERE id_imovel = ?";
$stmt_datas = $conexao->prepare($sql_datas);
$stmt_datas->bind_param("i", $id);
$stmt_datas->execute();
$result_datas = $stmt_datas->get_result();

while ($row = $result_datas->fetch_assoc()) {
    $inicio = new DateTime($row['data_inicial']);
    $fim = new DateTime($row['data_final']);
    while ($inicio <= $fim) {
        $datas_indisponiveis[] = $inicio->format('Y-m-d');
        $inicio->modify('+1 day');
    }
}

// Envia as datas indisponíveis para o JavaScript
$datas_indisponiveis_json = json_encode($datas_indisponiveis);

// Resumo e cálculo da reserva
$resumo_reserva = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calcular_reserva'])) {
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

            // Verifica se as datas estão dentro de um intervalo de locação já existente
            $datas_conflito = false;
            for ($i = 0; $i < $intervalo; $i++) {
                $data_atual = $inicio->format('Y-m-d');
                if (in_array($data_atual, $datas_indisponiveis)) {
                    $datas_conflito = true;
                    break;
                }
                $inicio->modify('+1 day');
            }

            if ($datas_conflito) {
                $resumo_reserva = "<p style='color:red;'>Uma ou mais datas selecionadas estão indisponíveis. Tente outro período.</p>";
            } else {
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
    }
}

// Confirma a reserva e insere os dados na tabela Locação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_reserva'])) {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $valor_total = $_POST['valor_total'];

    // Obtém os IDs do locador (usuário logado) e proprietário
    $id_locador = $_SESSION['id']; // Assume que o ID do usuário logado está na sessão
    $id_proprietario = $imovel['id_proprietario'];

    // Insere os dados na tabela Locação
    $sql_reserva = "INSERT INTO locação (id_proprietario, id_locador, ID_imovel, Data_inicial, Data_Final, Valor_total) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_reserva = $conexao->prepare($sql_reserva);
    $stmt_reserva->bind_param("iiissd", $id_proprietario, $id_locador, $id, $data_inicio, $data_fim, $valor_total);

    if ($stmt_reserva->execute()) {
        echo "<p>Reserva confirmada com sucesso!</p>";
    } else {
        echo "<p style='color:red;'>Erro ao confirmar a reserva.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva do Imóvel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.0/main.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .calendar {
            margin-top: 20px;
        }

        .fc .fc-day-disabled {
            background-color: #f8d7da;
            color: #721c24;
            pointer-events: none;
        }

        .fc .fc-day:hover {
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Reserva do Imóvel: <?= htmlspecialchars($imovel['Nome_imovel']); ?></h1>
    <img src="<?= htmlspecialchars($imovel['imagens']); ?>" width="100%" height="auto" alt="Imagem do Imóvel">
    <p><strong>Cidade:</strong> <?= htmlspecialchars($imovel['Cidade']); ?></p>
    <p><strong>Descrição:</strong> <?= htmlspecialchars($imovel['Descrição']); ?></p>
    <p><strong>Valor:</strong> R$ <?= number_format($imovel['Valor'], 2, ',', '.'); ?></p>

    <h2>Selecione o período para a reserva</h2>
    <form method="POST">
        <label for="data_inicio">Data de Início:</label>
        <input type="date" id="data_inicio" name="data_inicio" required>
        <br><br>
        <label for="data_fim">Data de Fim:</label>
        <input type="date" id="data_fim" name="data_fim" required>
        <br><br>
        <button type="submit" name="calcular_reserva">Calcular Valor</button>
    </form>

    <?= $resumo_reserva; ?>

    <div id="calendar" class="calendar"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.0/main.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var datasIndisponiveis = <?= $datas_indisponiveis_json; ?>;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'pt-br',
            dateClick: function (info) {
                if (datasIndisponiveis.includes(info.dateStr)) {
                    alert('Esta data está indisponível.');
                } else {
                    alert('Data disponível: ' + info.dateStr);
                }
            },
            validRange: {
                start: new Date().toISOString().split('T')[0]
            },
            events: datasIndisponiveis.map(function (data) {
                return {
                    title: 'Indisponível',
                    start: data,
                    end: data
                };
            })
        });

        calendar.render();
    });
</script>
</body>
</html>
