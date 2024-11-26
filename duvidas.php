<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOSTFY</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
         body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: ##fef6ee;
        }

        .texto1 h2 {
            font-size: 25px;
            cursor: pointer;
            margin: 0;
            padding: 10px;
            border-radius: 8px;
            position: relative;
            justify-content: center;

        }

        .texto1 h2 i {
            position: center;
            right: 15px;
            transition: transform 0.3s ease;
        }

        .texto1 h2 i.rotate {
            transform: rotate(180deg);
        }

        .texto1 p {
            font-size: 20px;
            color: #333;
            display: none;
            padding: 10px;
            margin: 0;
            border-left: 4px solid #C56126;
        }

        .texto1.active p {
            display: block;
        }
.a {
            text-decoration: none;  
        }
        .section {
        display: flex;
        align-items: top;
        justify-content: center;
        flex-flow: wrap;
        width: 100vw;
        height: 50vh;
        padding: 20px;
        box-sizing: border-box;

        }
        .section1 {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: wrap;
            width: 100vw;
            padding: 10px;
            box-sizing: border-box;
            background-color: #fef6ee;

        }
        .equipe-box { 
            display: flex;
            justify-content: center;
            flex-direction: row;
            gap: 20px;    
        }
        .equipe {
            width: 100%;
            height: auto;
            border: 1px solid #FFF5E6;
            border-radius: 10px;  
            background-color: #C56126;
            text-align: center;
            padding: 20px;  
            color: #2b3a4e;
            box-shadow: 10px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .img-perfil {
        width: 300px;
        height: 300px;
        border-radius: 15px;
        }
    
        .texto {
            font-size: 20px;
            color: #2b3a4e; /* Cor do menu lateral */
            justify-content: center;
            width: 100%;
            height: auto;
            text-align: center;
            padding: 20px;  
        }
        .texto1 {
            font-size: 15px;
            color: #2b3a4e; /* Cor do menu lateral */
            justify-content: center;
            width: 100%;
            height: auto;
            text-align: center;
            padding: 20px;  
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
            <a href="quemsomos.php">Quem Somos</a>
            <a href="duvidas.php">Dúvidas</a>
        </div>
   
   
        <!-- Overlay para quando o menu estiver aberto -->
        <div class="overlay" id="overlay"></div>
        <!-- Conteúdo principal -->
        <br/> 
            <div class="section1">
               
                <h2 class="equipe">Dúvidas Frequentes</h2></div>

                <div class="texto1">
                <h2>Como faço para cancelar minha reserva? <i class="bx bx-chevron-down"></i></h2>
                <p>
                Para cancelar sua reserva, acesse "Minhas Reservas" no site ou aplicativo, selecione a reserva e clique em "Cancelar".
                Lembre-se de verificar as políticas de cancelamento da sua acomodação.
            </p>
        </div>

        <div class="texto1">
        <h2>Como altero as datas da minha reserva? <i class="bx bx-chevron-down"></i></h2>
        <p>
                Para alterar as datas, acesse "Minhas Reservas" e escolha a opção "Alterar Datas". 
                Note que alterações dependem da disponibilidade da acomodação e das políticas de alteração.
            </p>
        </div>

        <div class="texto1">
        <h2>Quais métodos de pagamento são aceitos? <i class="bx bx-chevron-down"></i></h2>
        <p>
                Aceitamos cartões de crédito e débito das principais bandeiras, além de transferências bancárias e pagamentos via PIX.
                Algumas propriedades também oferecem a opção de pagamento na chegada.
            </p>
        </div>

        <div class="texto1">
        <h2>Como posso entrar em contato com a propriedade? <i class="bx bx-chevron-down"></i></h2>
        <p>
                Após realizar sua reserva, você encontrará as informações de contato da propriedade na seção "Minhas Reservas".
                Utilize essas informações para tirar dúvidas ou fazer solicitações específicas.
            </p>
        </div>

        <div class="texto1">
        <h2>Qual é a política de reembolso? <i class="bx bx-chevron-down"></i></h2>
        <p>
                O reembolso depende das condições da reserva. Verifique a política de cancelamento da sua acomodação na confirmação de reserva.
                Em caso de dúvidas, entre em contato com o suporte.
            </p>
        </div>
        </div>



        <footer><ul>
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
            
    // Define o ano atual
    
          document.getElementById('copyright-year').textContent = new Date().getFullYear();

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

<script>
        // Alternar visualização de FAQ
        document.querySelectorAll('.texto1 h2').forEach(item => {
            item.addEventListener('click', () => {
                const parent = item.parentElement;
                const icon = item.querySelector('i');

                parent.classList.toggle('active');
                icon.classList.toggle('rotate');
            });
        });

        // Atualizar ano no rodapé
        document.getElementById('copyright-year').textContent = new Date().getFullYear();

        // Função para exibir/desexibir botões de login/logou

        <?php
        // Verifica o login e ajusta a exibição
        if (isset($_SESSION['id'])) {
            echo 'logado();';
        } else {
            echo 'deslogado();';
        }
        ?>
    </script>
</body>
</html>

        