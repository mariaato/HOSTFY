<?php
    session_start();
    include("conexao.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOSTFY</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="styles.css?">
    <style>
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
        margin-bottom:90px;
        }
        .section1 {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: wrap;
            width: 100vw;
            padding: 10px;
            box-sizing: border-box;
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
        width: 220px;
        height: 250px;
        border-radius: 15px;
        }
        .menu-icon {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 30px;
            color: #333;
         }
        .search-form {
            display: flex;
            align-items: center;
            background-color: #C56126; /* Cor de fundo semelhante à imagem */
            border-radius: 25px; /* Bordas arredondadas */
            padding: 10px 20px; /* Espaçamento interno */
            width: 100%; /* Ajuste conforme necessário */
            max-width: 600px; /* Largura máxima */
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
            font-size: 20px;
            color: #2b3a4e; /* Cor do menu lateral */
            justify-content: center;
            width: 100%;
            height: auto;
            text-align: center;
            padding: 20px;  
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
             <div class="section1">
                <button class="menu-icon" id="menu-toggle"aria-label="Abrir Menu">
                    <i class='bx bx-menu'></i>
                </button>
                <a href="index.php">
            <img src="logoHostfy.png" alt="logo" class="logo" />
        </a>                <!-- Campo de pesquisa -->
                <form method="post" action="pesquisar.php" class="search-form">
                    <input type="text" name="pesquisar" placeholder="Encontre seu lugar ideal..." class="search-input">
                    <span>
                        <button type="submit" class="search-button"aria-label="Abrir Menu">
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
               
                <h2 class="equipe">Projeto Integrador</h2></div>
                <br><div class="texto1">
                <a>Somos uma plataforma dedicada a conectar locatários e proprietários de maneira rápida, direta e prática.</a><br><br>
                <a>Com o objetivo de oferecer uma experiência de viagem que combina conforto e custo-benefício.</a><br><br>
                <a> Um sistema intuitivo que facilita o processo de reserva, criando um ambiente acolhedor e eficiente para quem busca uma estadia temporária com o aconchego e a sensação de estar em casa, mesmo longe. </a><br><br>
                <a>Nossa missão é simplificar cada etapa da locação, garantindo que cada viagem seja uma experiência única, memorável e agradável.</a>  <br><br>
            <br>
        </div>
            <h2 class="equipe" >  Apresentação do time:</h2><br>
           <!-- <img id="profile-image"  src="byanca.png" onmouseover="this.src='byanca.png'" onmouseout="this.src='byancagray.png'" alt="Foto de Perfil" style="width:300px; height:300px;border-radius: 15px;"  >-->
           <div class="section">  
                <div class="equipe-box">
                    <div class="equipe">
                        <h2 class="texto">Product Owner</h2>
                        <img src="byanca.png" alt="Foto de Perfil" class="img-perfil" > 
                        <h2>Byanca Campos Furlan</h2>
                        <br/>
                        <a href="mailto:byancafurlanarq@gmail.com"target="_blank">E-mail</a><br/>
                        <a href="https://br.linkedin.com/in/byanca-furlan"target="_blank"rel="noopener">Linkedin</a><br/>
                    </div> 
                        <div class="equipe">
                            <h2 class="texto">Scrum Master</h2>
                            <img src="maria.jpeg" alt="Foto de Perfil" class="img-perfil" >
                            <h2>Maria Antônia dos Santos</h2>
                            <a href="mailto: mariaaatonha@gmail.com"target="_blank">E-mail</a><br/>
                            <a href="https://www.linkedin.com/in/maria-antônia-dos-santos"target="_blank"rel="noopener">Linkedin</a>
                    </div>
                    <div class="equipe">
                        <h2 class="texto">Desenvolvedor</h2>
                        <img src="igor.jpeg" alt="Foto de Perfil" class="img-perfil" >
                        <h2>Igor Miguel Raimundo</h2>
                        <a href="mailto: igor.raimundo@aluno.fmpsc.edu.br"target="_blank">E-mail</a><br/>
                        <a href=" https://www.linkedin.com/in/igor-miguel-raimundo-27b964286/"target="_blank"rel="noopener">Linkedin</a>
                    </div>
                    <div class="equipe">
                        <h2 class="texto">Desenvolvedora</h2>
                        <img src="rithiely.jpeg" alt="Foto de Perfil" class="img-perfil" >
                        <h2>Rithiely Schmitt</h2>
                        <a href="mailto: rithiely_ph@hotmail.com"target="_blank">E-mail</a><br/>
                        <a href="https://br.linkedin.com/in/rithiély-schmitt-100b92326/"target="_blank"rel="noopener">Linkedin</a>
                    </div>
         
             </div>
             </div>
           </div>
           <br>           
</br>
</br>  
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

        </body>
      
        </html>
        

        