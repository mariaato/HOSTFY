<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOSTFY</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <header>
        <!-- Botão do ícone de menu -->
        <button class="menu-icon" id="menu-toggle">
            <i class='bx bx-menu'></i>
        </button>

        <img src="logoHostfy.png" alt="logo" class="logo" />

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
            <a href="logout.php" class="menu__link">Sair</a>
        </div>
    </header>

    <!-- Menu lateral (sidebar) -->
    <div class="sidebar" id="sidebar">
        <a href="quemsomos.php">Quem Somos</a>
        <a href="#">Dúvidas</a>
        <a href="imoveis.php" >Cadastre seu imóvel</a>
    </div>

    <!-- Overlay para quando o menu estiver aberto -->
    <div class="overlay" id="overlay"></div>

    <!-- Conteúdo principal -->
    <div class="main-content" id="main-content">
        <!-- Conteúdo da página -->
        <h1>Destaques</h1>
        <p>Aqui estão os imóveis em destaque para você.</p>
        <!-- Mais conteúdo pode ser adicionado aqui -->
        <?php
            include("funçaoAnuncio.php");

            $imagem = "casa.jpg";
            $titulo = "Apartamento Ingleses";
            $avaliacao = 4.25;
            $tags = ["Churrasqueira", "Ar condicionado", "Pet Friendly"];

            echo gerarAnuncio($imagem, $titulo, $avaliacao, $tags);
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
</html>
