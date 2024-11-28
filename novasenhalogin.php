<?php
        include("conexao.php");


        if (isset($_POST['email']) && isset($_POST['nome']) && isset($_POST['cpf']) && isset($_POST['nova_senha'])) {

            if (strlen($_POST['email']) == 0) {
                $error_message = "Preencha seu e-mail";
            } else if (strlen($_POST['nome']) == 0) {
                $error_message = "Preencha seu nome";
            } else if (strlen($_POST['cpf']) == 0) {
                $error_message = "Preencha seu CPF";
            } else if (strlen($_POST['nova_senha']) == 0) {
                $error_message = "Preencha sua nova senha";
            } else {
                $email = $conexao->real_escape_string($_POST['email']);
                $nome = $conexao->real_escape_string($_POST['nome']);
                $cpf = $conexao->real_escape_string($_POST['cpf']);
                $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

                $sql_code = "SELECT * FROM usuario WHERE email = '$email' AND nome = '$nome' AND cpf = '$cpf'";
                $sql_query = $conexao->query($sql_code) or die("Falha na execução do código SQL: " . $conexao->error);

                if ($sql_query->num_rows == 1) {
                    $sql_update = "UPDATE usuario SET senha = '$nova_senha' WHERE email = '$email'";
                    if ($conexao->query($sql_update)) {
                        $final = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Senha alterada com sucesso! 
                        <a href='login.php' class='btn btn-primary btn-sm ml-2'>Faça seu login </a>
                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>";
                        // $error_message = "Senha alterada com sucesso!"; 
                        // header("Location: login.php");

                    } else {
                        $final = "<div class='alert alert-danger' role='alert'>
                                        Erro ao alterar a senha:  " . $conexao->error . "
                                    </div>";

                        // $error_message = "Erro ao alterar a senha: " . $conexao->error ;
                    }
                } else {
                    $error_message = "Dados incorretos! Verifique seu email, nome e CPF.";
                }
            }
        }
        ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocar Senha</title>
    <link rel="stylesheet" href="styles.css?">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">


    <style>
 
        a {
            text-decoration: none !important; 
            color: #fff !important;
        
        }
        * {
            margin: 0 !important;
            padding: 0;
            box-sizing: border-box;
        }

        .header {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        body {
            background-color: #FEF6EE !important;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .icon {
            background: none;
            border: none;
            font-size: 35px;
            cursor: pointer;
        }

        /* Centralizando o conteúdo principal */
        #main-content {
            display: flex !important;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            background-color: #FEF6EE;
        }

        /* Estilo do container do formulário */
        .container {
            background-color: #1a1e36;
            border-radius: 15px;
            padding: 30px;
            width: 350px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .container h1 {
            color: white ;
            /* margin-top: -10px !important; */
            margin-bottom: 15px !important;
            font-size: 24px;
        }

        .form-group {
            /* margin-bottom: 2px; */
            position: relative;
            font-size: 18px !important;

        }

        .form-group i {
            position: absolute !important;
            top: 70%;
            left: 10px;
            transform: translateY(-50%);
            color: #666;
            font-size: 20px;
        }

        .form-group input {
            width: 100% ;
            padding: 12px;
            padding-bottom: 8px;
            padding-left: 40px;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 14px !important;
        }

        .form-group input::placeholder {
            color: #999;
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

        .logo {
            width: 120px;
        }

        label {
            color: white;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
            font-size: 18px;
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
    </style>
</head>
<body>
<div class="header" id="header"> 
    <button class="icon" id="menu-toggle">
        <i class='bx bx-menu'></i>
    </button>
</div>

<p><?php if (isset($final)) {echo $final;} ?></p>


<div class="sidebar" id="sidebar">
    <a href="index.php">Área inicial </a>
    <a href="login.php">Login </a>
    <a href="quemsomos.php">Quem Somos</a>
    <a href="duvidas.php">Dúvidas</a>
</div>

<div class="overlay" id="overlay"></div>
<div id="main-content">


    <div class="container">
        <h1>Troque sua senha</h1>
        
        <form action="novasenhalogin.php" method="POST">

        <?php if (!empty($error_message)) : ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="form-group">
                <i class='bx bx-at'></i>
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email..." required>
            </div>
            <br>

            <div class="form-group">
                <i class='bx bx-user'></i>
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" placeholder="Digite seu nome..." required>
            </div>
            <br>

            <div class="form-group">
                <i class='bx bx-id-card'></i>
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" placeholder="Digite seu CPF..." required>
            </div>
            <br>

            <div class="form-group">
                <i class='bx bxs-lock'></i>
                <label for="nova_senha">Nova Senha</label>
                <input type="password" id="nova_senha" name="nova_senha" placeholder="Digite sua nova senha..." required>
            </div>
            <br>

            <button type="submit" class="submit-btn">Alterar Senha</button>
        </form>
        <a href="index.php">
            <img src="logoHostfy.png" alt="logo" class="logo" />
        </a>
        </div>
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
</body>
</html>
