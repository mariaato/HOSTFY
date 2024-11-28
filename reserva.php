<?php
session_start();
require 'conexao.php';

// Verifica se o ID foi passado na URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']); // Converte o ID para inteiro

    // Busca os detalhes do imóvel no banco de dados
    $sql = "SELECT ID_imovel, CEP, Nome_imovel, Rua, Bairro, Cidade, UF, Valor, Descrição, id_proprietario, imagens, categoria.nome_categoria
            FROM imovel JOIN Categoria AS categoria ON imovel.id_categoria = categoria.id_categoria WHERE ID_imovel = ?";
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

// Resumo e cálculo da reserva
$resumo_reserva = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calcular_reserva'])) {
    $data_inicio = $_POST['data_inicio'] ?? null;
    $data_fim = $_POST['data_fim'] ?? null;

    if ($data_inicio && $data_fim) {
        $inicio = new DateTime($data_inicio);
        $fim = new DateTime($data_fim);
        $intervalo = $inicio->diff($fim)->days;

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
                $data_inicio_formatada = date("d-m-Y", strtotime($data_inicio));
                $data_fim_formatada = date("d-m-Y", strtotime($data_fim));

                // Exibindo as datas formatadas
              
                $resumo_reserva = "
                    <h2>Resumo da Reserva</h2>
                    <p><strong>Data de Início:</strong> " . htmlspecialchars($data_inicio_formatada) . "</p>
                    <p><strong>Data de Fim:</strong> " . htmlspecialchars($data_fim_formatada) . "</p>
                    <p><strong>Valor da Diária:</strong> R$ " . number_format($valor_diaria, 2, ',', '.') . "</p>
                    <p><strong>Quantidade de Dias:</strong> $intervalo</p>
                    <p><strong>Valor Total:</strong> R$ " . number_format($valor_total, 2, ',', '.') . "</p>
                    <form method='POST' class='calendario'>
                        <input type='hidden' name='data_inicio' value='$data_inicio'>
                        <input type='hidden' name='data_fim' value='$data_fim'>
                        <input type='hidden' name='valor_total' value='$valor_total'>
                        <button type='submit' name='confirmar_reserva' class='submit-btn'>Confirmar Reserva</button>
                    </form>
                ";
            }
        }
    }
}

// Inserção na tabela Locador
if (isset($_POST['confirmar_reserva'])) {
    if (!isset($_SESSION['id'])) {
        echo "<br>";
        echo "<style> *{ background-color:#FEF6EE;  color: #C56126; align-items: center; justify-content: center; } .logo {
        width: 120px; position: fixed; left: 50px; top: 35px;} a{text-decoration: none; color: #5b2c12</style><div style=' background-color: #fff3f3; border: 1px solid orange; padding: 15px; border-radius: 8px; text-align: center; '>  <a href='index.php'>
                <img src='logoHostfy.png' alt='logo' class='logo' />
            </a><h1>Você precisa estar logado para fazer uma reserva.</h1>Faça seu login  <a href='login.php'>aqui.</a></div>";
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
        $final = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        Reserva confirmada com sucesso! 
        
    </div>";    } else {
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.0/main.min.css">
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="estilo.css">
    
    <style>

       form {
        background-color: none;
        color: white;

       }
       

        .main-content {
            display: flex;
            /* /* align-items: flex-start;  */
            /* justify-content: space-between;   */
            flex-wrap: wrap; /* Permite que os itens se ajustem em telas menores */
            gap: 20px; /* Adiciona espaço entre os itens */
            margin-top: 10px;
            margin-left: 120px;
            margin-right: 120px; /* Margem à direita */
            
            

        }

        .imovel {
            flex: 1; /* Ocupará o espaço restante */
            margin-right: 100px; /* Espaço entre as seções */
            margin-bottom: 20px; /* Adiciona espaço abaixo da seção */
        
        }

        .imovel img {
            width: 750px; /* Faz a imagem ocupar toda a largura disponível */
            height: 550px; /* Mantém a proporção da imagem */
            height: auto; /* Mantém a proporção da imagem */
            max-width: 100%; /* Faz a imagem ocupar o espaço disponível */
            border-radius:15px; /* Cantos arredondados para a imagem */
            margin-top: 10px; /* Espaço entre o título e a imagem */
        }

        

        .reserva {
            
            flex: 0 0 35%; /* Define uma largura fixa para a div de reserva */
            max-width: 400px; /* Limita a largura máxima */
            margin-left: auto; /* Empurra a div para a direita */
            margin-top:50px;
            
        }

        .informação{
            display: flex;
            font-size: 25px;

        }

        .informação .valor {
            display: flex;
            margin-left: 350px;


        }
        .informação .diaria {
            display: flex;
            font-size: 16px; /* Diminui o tamanho do texto */
            color: #888; /* Deixa o texto mais claro */
            margin-left: 1px; /* Adiciona um pequeno espaço entre o valor e a palavra "diária" */
            font-weight: normal; /* Remove o negrito */
        }

        .calendario {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #1a1e36;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .calendario label {
            font-weight: bold;
            color: white;
        }

        .calendario input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .submit-btn {
            background-color: #D97C41;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #c96f36;
        }
        .calendar {
            margin: 20px auto;
            width: 100%;
            max-width: 800px;
        }


        .fc .fc-day-disabled {
            background-color: #f8d7da;
            color: #721c24;
            pointer-events: none;
        }

        .fc .fc-day:hover {
            cursor: pointer;
        }

        .alert {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            width: auto;
            min-width: 300px;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            animation: slide-down 0.4s ease-out;
        }

        @keyframes slide-down {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* From Uiverse.io by SelfMadeSystem */ 
        .rating {
        display: flex;
        flex-direction: row-reverse;
        gap: 0.3rem;
        margin-top:-40px;
        -left:500px;
        --stroke: #666;
        --fill: #ffc73a;
        }

        .rating input {
        appearance: unset;
        }

        .rating label {
        cursor: pointer;
        }

        .rating svg {
        width: 2rem;
        height: 2rem;
        overflow: visible;
        fill: transparent;
        stroke: var(--stroke);
        stroke-linejoin: bevel;
        stroke-dasharray: 12;
        animation: idle 4s linear infinite;
        transition: stroke 0.2s, fill 0.5s;
        }

        @keyframes idle {
        from {
            stroke-dashoffset: 24;
        }
        }

        .rating label:hover svg {
        stroke: var(--fill);
        }

        .rating input:checked ~ label svg {
        transition: 0s;
        animation: idle 4s linear infinite, yippee 0.75s backwards;
        fill: var(--fill);
        stroke: var(--fill);
        stroke-opacity: 0;
        stroke-dasharray: 0;
        stroke-linejoin: miter;
        stroke-width: 8px;
        }

        @keyframes yippee {
        0% {
            transform: scale(1);
            fill: var(--fill);
            fill-opacity: 0;
            stroke-opacity: 1;
            stroke: var(--stroke);
            stroke-dasharray: 10;
            stroke-width: 1px;
            stroke-linejoin: bevel;
        }

        30% {
            transform: scale(0);
            fill: var(--fill);
            fill-opacity: 0;
            stroke-opacity: 1;
            stroke: var(--stroke);
            stroke-dasharray: 10;
            stroke-width: 1px;
            stroke-linejoin: bevel;
        }

        30.1% {
            stroke: var(--fill);
            stroke-dasharray: 0;
            stroke-linejoin: miter;
            stroke-width: 8px;
        }

        60% {
            transform: scale(1.2);
            fill: var(--fill);
        }
        }


    </style>
</head>
<body>
    
<header>
    <!-- Botão do ícone de menu -->
    <button class="menu-icon" id="menu-toggle">
        <i class='bx bx-menu'></i>
    </button>

    <a href="index.php">
            <img src="logoHostfy.png" alt="logo" class="logo" />
        </a>
        <p><?php if (isset($final)) {echo $final;} ?></p>

    <!-- Campo de pesquisa -->
    <form method="post" action="pesquisar.php" class="search-form">
        <input type="text" name="pesquisar" placeholder="Encontre seu lugar ideal..." class="search-input">
        <span>
            <button type="submit" class="search-button">
                <i class='bx bx-search'></i>
            </button>
        </span>
    </form>
    <div id="deslogado">
        <a href="login.php" class="menu__link">Login</a>
        <a href="cadastro.php" class="menu__link">Cadastre-se</a>


    </div>
    <div id="logado">
    <?php if(isset($_SESSION['id'])) {echo '<a  href="perfilhtml.php" class="menu__link">Perfil</a>';}?>
        <a href="logout.php" class="menu__link">Sair</a>
    </div>
</header>

 <!-- Menu lateral (sidebar) -->
 <div class="sidebar" id="sidebar">
        <a href="index.php">Área inicial </a>
        <a href="imoveis.php" >Cadastre seu imóvel</a>
        <a href="meus_imoveis.php">Imóveis Cadastrados</a>
        <a href="quemsomos.php">Quem Somos</a>
        <a href="duvidas.php">Dúvidas</a>
        
    </div>

    <!-- Overlay para quando o menu estiver aberto -->
    <div class="overlay" id="overlay"></div>

<div class="main-content" id="main-content">
<div class="imovel">
    <h1><?= htmlspecialchars($imovel['Nome_imovel']); ?></h1>
        <div class="rating">
        <input type="radio" id="star-1" name="star-radio" value="star-1">
        <label for="star-1">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
        </label>
        <input type="radio" id="star-2" name="star-radio" value="star-1">
        <label for="star-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
        </label>
        <input type="radio" id="star-3" name="star-radio" value="star-1">
        <label for="star-3">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
        </label>
        <input type="radio" id="star-4" name="star-radio" value="star-1">
        <label for="star-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
        </label>
        <input type="radio" id="star-5" name="star-radio" value="star-1">
        <label for="star-5">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path pathLength="360" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"></path></svg>
        </label>
        </div>
<div id="carouselImovel" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <?php
        $imagens = explode(',', $imovel['imagens']); // Supondo que as imagens sejam armazenadas separadas por vírgulas
        foreach ($imagens as $index => $imagem) {
            $active = $index === 0 ? 'active' : ''; // A primeira imagem será ativa
            echo "
            <div class='carousel-item $active'>
                <img src='$imagem' class='d-block w-100' alt='Imagem do Imóvel'>
            </div>
            ";
        }
        ?>
    </div>
    <a class="carousel-control-prev" href="#carouselImovel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Anterior</span>
    </a>
    <a class="carousel-control-next" href="#carouselImovel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Próximo</span>
    </a>
</div>

    <div class="informação">
        <p><strong><?= htmlspecialchars($imovel['Cidade']); ?> - <?= htmlspecialchars($imovel['UF']); ?></strong></p>
        <span class="valor">R$ <?= number_format($imovel['Valor'], 2, ',', '.'); ?></span>
        <span class="diaria">diária</span>
    </div>
    <p><strong>Categoria:</strong> <?php echo htmlspecialchars($imovel['nome_categoria']); ?></p>
    <p><strong>Endereço:</strong> <?= htmlspecialchars($imovel['Rua']); ?>, <?= htmlspecialchars($imovel['Bairro']); ?>, <?= htmlspecialchars($imovel['Cidade']); ?> - <?= htmlspecialchars($imovel['UF']); ?></p>

    <p><strong>Descrição:</strong> <?= htmlspecialchars($imovel['Descrição']); ?></p>



</div>


    <div class="reserva">
    <h3>Selecione o período para a reserva</h3>
    <form method="POST" class="calendario">
        <label for="data_inicio">Data de Início:</label>
        <input type="date" id="data_inicio" name="data_inicio" required>
        <label for="data_fim">Data de Fim:</label>
        <input type="date" id="data_fim" name="data_fim" required>
        <button type="submit" name="calcular_reserva" class="submit-btn">Calcular Valor</button>

    </form>

    <?= $resumo_reserva; ?>

    <div id="calendar" class="calendar"></div>
    </div>


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

<script>
        // Função para alternar o menu lateral
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const overlay = document.getElementById('overlay');

        // Função de alternância para abrir/fechar o menu e o overlay
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-active');
            mainContent.classList.toggle('content-shift');
            overlay.classList.toggle('overlay-active');
        });

        // Função para fechar o menu se clicar fora (no overlay)
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('sidebar-active');
            mainContent.classList.remove('content-shift');
            overlay.classList.remove('overlay-active');
        });
    </script>

    <script>    
        //fução para a index do usuario logado
        function logado() {
            document.getElementById('logado').style.display='';
            document.getElementById('deslogado').style.display='none';
        }
        //função para o usuario deslogado
        function deslogado() {
            document.getElementById('logado').style.display='none';
            document.getElementById('deslogado').style.display='';
        }

           // Define o ano atual
    document.getElementById('copyright-year').textContent = new Date().getFullYear();

    </script>

    <?php
        //verifica o login e muda o index
        if (isset($_SESSION['id'])) {
            echo '<script> logado() </script>';
        } else {
            echo '<script> deslogado() </script>';
        }
    ?>
</body>
<footer>
    <ul>
        <p class="rights"><span>&copy;&nbsp;<span id="copyright-year"></span> .Todos os direitos reservados. <span> por Byanca Campos Furlan, Igor Miguel Raimundo, Maria Antonia dos Santos e Rithiely Schmitt.</a></span>
    </ul>
</footer>
</html>
