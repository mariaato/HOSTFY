<?php

session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
</head>
<body>

    <!-- Caso seja o admin -->
    <div id='permitido'>
        <p>admin logado</p>
        <a href="logout.php">sair</a>
    </div>
    
    <!-- Caso não seja o admin e acessem a pagina -->
    <div id='negado'>
        <p>Você não tem permissão para acessar essa página, volte ao <a href="index.php">início</a></p>
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