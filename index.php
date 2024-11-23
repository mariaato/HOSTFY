<?php
    session_start();
    include("conexao.php");
    include("funçaoAnuncio.php");

    //cookies
    if(isset($_SESSION['id']) && !isset($_COOKIE['usuario'])) {
        $cookie_nome = $_SESSION['nome'];
        $cookie_id = $_SESSION['id'];
        setcookie('usuario', $cookie_nome, time() + 1800, '/');
        setcookie('id', $cookie_id, time() + 1800, '/');
        $_COOKIE['usuario'] = $_SESSION['nome'];
        $_COOKIE['id'] = $_SESSION['id'];
    } elseif (isset($_COOKIE['usuario']) && !isset($_SESSION['id'])) {
        $_SESSION['nome'] = $_COOKIE['usuario'];
        $_SESSION['id'] = $_COOKIE['id'];
    }

    //anuncios
    $anuncio = "SELECT * FROM imovel LIMIT 12" ;

    $resultado_anuncio = mysqli_query($conexao, $anuncio);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOSTFY</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="estilo.css">
    <style>
        #main-content {
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            background-color: #FEF6EE;

        }

        .anuncio-container {
        display: flex;
        flex-wrap: wrap; /* Permite que os anúncios sejam quebrados em novas linhas */
        gap: 20px; /* Espaçamento entre os anúncios */
        justify-content: space-evenly; /* Espaçamento uniforme entre os anúncios */
}
 
        .anuncio-imagem {
            width: 100%;
            /* height: auto; */
            border-radius: 10px;
            height: 200px; /* Altura padrão definida */
            object-fit: cover; /* Garante que a imagem preencha o espaço sem distorção */
        }
        .rights {
        padding: 10px 0;
        text-align: center;
        align-items: center;
        font-size: 14px;
        font-weight: 500;
        color: #9b9b9b;
       
        overflow: auto;
}
    ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    #footer {
    position: sticky;
    bottom: 0;
    width: 100%;
    height: 2.5rem;            /* altura do rodapé */
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
        <a href="imoveis.php" >Cadastre seu imóvel</a>
        <a href="meus_imoveis.php">Imóveis Cadastrados</a>
        <a href="quemsomos.php">Quem Somos</a>
        <a href="duvidas.php">Dúvidas</a>
        
    </div>

    <!-- Overlay para quando o menu estiver aberto -->
    <div class="overlay" id="overlay"></div>

    <!-- Conteúdo principal -->
        <!-- Conteúdo da página -->
<div class="main-content" id="main-content">
        <h1>Destaques</h1>
        <p>Aqui estão os imóveis em destaque para você.</p>
        <!-- Mais conteúdo pode ser adicionado aqui -->


        <h1>Nossos imóveis</h1>

        <div class="anuncio-container">

        <?php

            if (mysqli_num_rows($resultado_anuncio) > 0) {
                while ($rows_anuncio = mysqli_fetch_array($resultado_anuncio)) {
                    $id = $rows_anuncio['ID_imovel'];
                    $imagem = $rows_anuncio['imagens'];
                    $titulo = $rows_anuncio['Nome_imovel'] . " - " . $rows_anuncio['Cidade'];
                    $valor = $rows_anuncio ['Valor']; 
                    $tags = [$rows_anuncio['Bairro'], $rows_anuncio['UF']];
    
                    // Chama a função gerarAnuncio com os dados
                    echo gerarAnuncio($id, $imagem, $titulo, $valor, $tags);
    
                }
            } else {
                echo "Nenhum resultado encontrado.";
            }
        ?>
    </div>

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
