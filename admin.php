<?php

session_start();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <style> 
        .body_error{ 
            background-color:#FEF6EE;  
            color: #C56126;
            align-items: center; 
            justify-content: center; 
            }

        .logo_error {
            width: 120px; 
            position: fixed; 
            left: 50px; 
            top: 35px;} 
            
        .a_error {
            text-decoration: none; 
            color: #5b2c12
            }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #FEF6EE;
            padding: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        </style>
</head>
<body>

    <!-- caso seja o admin -->
    <div id='permitido'>
        <header>
            <!-- Botão do ícone de menu -->
            <button class="menu-icon" id="menu-toggle">
                <i class='bx bx-menu'></i>
            </button>

            <img src="logoHostfy.png" alt="logo" class="logo" />

            <!-- Campo de pesquisa -->
            <form method="post" action="pesquisar.php" class="search-form">
                <input type="text" name="pesquisar" class="search-input">
                <span>
                    <button type="submit" class="search-button">
                        <i class='bx bx-search'></i>
                    </button>
                </span>
            </form>
            <a href="logout.php" class="menu__link">Sair</a>
        </header>

        
        <a href="logout.php">sair</a>
    </div>
    
    <!-- caso não seja o admin e acessem a pagina -->
    <div id='negado'>
    <br>
        <div class="body_error" style=" background-color: #fff3f3; border: 1px solid orange; padding: 15px; border-radius: 8px; text-align: center; "> 
            <img src="logoHostfy.png" alt="logo" class="logo_error">
            <h1>Você não tem permissão para acessar essa página!</h1>
            <p>Volte ao <a class="a_error" href="index.php">início.</a></p>
        </div>   
    </div>
    
    <script>

        function permitido() {
            document.getElementById('permitido').style.display='';
            document.getElementById('negado').style.display='none';
        }

        function negado() {
            document.getElementById('permitido').style.display='none';
            document.getElementById('negado').style.display='';
        }

    </script>

    <?php

        //verificar a permissão
        if (isset($_SESSION['id']) && $_SESSION['id'] == 0) {
            echo '<script>permitido()</script>';
        } else {
            echo '<script>negado()</script>';
        }
    ?>
</body>
</html>