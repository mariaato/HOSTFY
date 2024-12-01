<?php
session_start();
include("conexao.php");
include("funçaoAnuncio.php");

// Recuperar a pesquisa inicial
$pesquisar = $_POST['pesquisar'] ?? '';

// Recuperar os valores dos filtros adicionais
$valor_min = $_GET['valor_min'] ?? null;
$valor_max = $_GET['valor_max'] ?? null;
$numero_pessoas = $_GET['numero_pessoas'] ?? null;

$query = "
SELECT DISTINCT imovel.*
FROM imovel
LEFT JOIN imovel_checklist ON imovel.id_imovel = imovel_checklist.id_imovel
LEFT JOIN checklist ON imovel_checklist.id_checklist = checklist.id_checklist
LEFT JOIN categoria ON imovel.id_categoria = categoria.id_categoria
WHERE 
    (imovel.Nome_imovel LIKE ? OR 
    imovel.Valor LIKE ? OR 
    imovel.Cidade LIKE ? OR 
    imovel.Descrição LIKE ? OR
    imovel.Rua LIKE ? OR
    imovel.Bairro LIKE ? OR 
    imovel.UF LIKE ? OR 
    imovel.Numero_pessoas LIKE ? OR
    checklist.nome_checklist LIKE ? OR
    categoria.nome_categoria LIKE ?)";
// Parâmetros e tipos

$params = ["%$pesquisar%","%$pesquisar%", "%$pesquisar%", "%$pesquisar%", "%$pesquisar%", "%$pesquisar%", "%$pesquisar%", "%$pesquisar%", "%$pesquisar%", "%$pesquisar%"];
$types = "ssssssssss";
// Adicionar filtros adicionais, se presentes
if (!empty($valor_min)) {
    $query .= " AND Valor >= ?";
    $params[] = $valor_min;
    $types .= "d";
}
if (!empty($valor_max)) {
    $query .= " AND Valor <= ?";
    $params[] = $valor_max;
    $types .= "d";
}
if (!empty($numero_pessoas)) {
    $query .= " AND Numero_pessoas = ?";
    $params[] = $numero_pessoas;
    $types .= "i";
}

// Preparar a consulta
$stmt = $conexao->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado_anuncio = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados Encontrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="estilo.css?">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <style>
        #main-content {

            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            background-color: #FEF6EE;

        }
        .linha{
            display: flex;
            align-items: center;


        }

    
        form {
            margin-top:10px;
            margin-left:300px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            box-shadow: none;
            background: none; /* Remove qualquer imagem ou cor de fundo */
            background-color: none; /* Deixa o fundo transparente */
            border: none; /* Remove borda se necessário */
            padding: 0; /* Ajusta o espaçamento interno */
        }

        form input[type="range"],form input[type="number"] {
            flex: 1;
            max-width: 150px; /* Limita o tamanho máximo dos inputs */
}
        form label{
            color:#1a1e36;
            font-size: 13px;


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
<div class="linha">
        <h1>Resultados Encontrados</h1>
        <!-- FILTROS -->
        <form method="GET" action="pesquisar.php" class="filtro">
            <!-- Campo de pesquisa (oculto) -->
            <input type="hidden" name="pesquisar" value="<?= htmlspecialchars($pesquisar) ?>">

            <!-- Input para Valor Mínimo -->
            <label for="valor_min">Valor Mínimo:</label>
            <input 
                type="range" 
                name="valor_min" 
                id="valor_min" 
                step="1.00" 
                min="0" 
                max="2000" 
                value="<?= htmlspecialchars($_GET['valor_min'] ?? '0') ?>" 
                oninput="updateRangeValue('valor_min', this.value)"
            >
            <span id="valor_min_display"><?= htmlspecialchars($_GET['valor_min'] ?? '0') ?></span>

            <!-- Input para Valor Máximo -->
            <label for="valor_max">Valor Máximo:</label>
            <input 
                type="range" 
                name="valor_max" 
                id="valor_max" 
                step="1.00" 
                min="0" 
                max="10000" 
                value="<?= htmlspecialchars($_GET['valor_max'] ?? '1000') ?>" 
                oninput="updateRangeValue('valor_max', this.value)"
            >
            <span id="valor_max_display"><?= htmlspecialchars($_GET['valor_max'] ?? '1000') ?></span>

            <!-- Input para Número de Pessoas -->
            <label for="numero_pessoas">Hóspedes:</label>
            <input 
                type="number" 
                name="numero_pessoas" 
                id="numero_pessoas" 
                min="1" 
                value="<?= htmlspecialchars($_GET['numero_pessoas'] ?? '') ?>"
            >

            <!-- Botão de Submissão -->
            <button type="submit" class="submit-btn">Filtrar</button>
        </form>
</div>

            <div class="anuncio-container">
    <?php
        if (mysqli_num_rows($resultado_anuncio) > 0) {
            while ($rows_anuncio = mysqli_fetch_array($resultado_anuncio)) {
                $id = $rows_anuncio['ID_imovel'];
                $imagem = $rows_anuncio['imagens'];
                $titulo = $rows_anuncio['Nome_imovel'] . " - " . $rows_anuncio['Cidade'];
                $valor = $rows_anuncio ['Valor']; 


                $p_checklist = $conexao->prepare("SELECT * FROM checklist INNER JOIN imovel_checklist ON checklist.id_checklist=imovel_checklist.id_checklist WHERE imovel_checklist.id_imovel=?");
                    $p_checklist->bind_param('i', $id);
                    $p_checklist->execute();
            
                    // Obtém os resultados
                    $imovel_checklist = $p_checklist->get_result();
            
                    $caracteristica = [];
                    $id_caracteristica = [];
                    if ($imovel_checklist->num_rows > 0) {
                        while ($linha = $imovel_checklist->fetch_assoc()) {
                            $caracteristica[] = $linha['nome_checklist'];
                            $id_caracteristica[] = $linha['id_checklist'];
                        }
                    }
            
                    $tags = $caracteristica;
                // Chama a função gerarAnuncio com os dados
                echo gerarAnuncio($id, $imagem, $titulo, $valor, $tags);

            }
        } else {
            echo "Nenhum resultado encontrado.";
        }
    ?>
    </div>
    <footer>
    <ul>
        <p class="rights"><span>&copy;&nbsp;<span id="copyright-year"></span> .Todos os direitos reservados. <span> por Byanca Campos Furlan, Igor Miguel Raimundo, Maria Antonia dos Santos e Rithiely Schmitt.</a></span>
    </ul>
</footer>
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

<script>
    function updateRangeValue(id, value) {
        document.getElementById(`${id}_display`).textContent = value;
    }
</script>
</body>
</html>
