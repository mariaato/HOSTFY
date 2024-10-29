<?php
include("conexaoLogin.php");

session_start(); 

$max_attempts = 3;
$lockout_time = 180; // 3 minutos em segundos

$error_message = "";

// Verificar se o número de tentativas e o horário do bloqueio estão definidos na sessão
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
    $_SESSION['last_attempt_time'] = time();
}

// Verificar se o usuário está bloqueado
if ($_SESSION['attempts'] >= $max_attempts) {
    $time_since_last_attempt = time() - $_SESSION['last_attempt_time'];
    
    if ($time_since_last_attempt < $lockout_time) {
        $remaining_time = $lockout_time - $time_since_last_attempt;
        $error_message = "Conta bloqueada. Tente novamente em " . ceil($remaining_time / 60) . " minutos.";
    } else {
        // Resetar tentativas após o tempo de bloqueio expirar
        $_SESSION['attempts'] = 0;
    }
}

if ($_SESSION['attempts'] >= $max_attempts && !empty($error_message)) {
    echo "<div class='error-message'><h1>$error_message</h1></div>"; // mensagem de bloqueio
    exit;
}

if (isset($_POST['email']) || isset($_POST['senha'])) {

    if (strlen($_POST['email']) == 0) {
        $error_message = "Preencha seu e-mail";
    } else if (strlen($_POST['senha']) == 0) {
        $error_message = "Preencha sua senha";
    } else {
        $email = $mysqli->real_escape_string($_POST['email']);
        $senha = $_POST['senha']; 

        $sql_code = "SELECT * FROM usuario WHERE email = '$email'";
        $sql_query = $mysqli->query($sql_code) or die("Falha na execução do código SQL: " . $mysqli->error);

        if ($sql_query->num_rows == 1) {
            $usuario = $sql_query->fetch_assoc();

            // Verifique se a senha fornecida corresponde à senha criptografada
            if (password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido, resetar tentativas
                $_SESSION['attempts'] = 0;

                $_SESSION['id'] = $usuario['id'];
                $_SESSION['nome'] = $usuario['nome'];

                header("Location: inicio.html");
                exit;
            } else {
                // Senha incorreta, incrementar tentativas
                $_SESSION['attempts'] += 1;
                $_SESSION['last_attempt_time'] = time(); // Atualizar o tempo da última tentativa
                $error_message = "Falha ao logar! E-mail ou senha incorretos. Tentativas restantes: " . ($max_attempts - $_SESSION['attempts']);
                
                if ($_SESSION['attempts'] >= $max_attempts) {
                    $error_message .= "<br>Conta bloqueada por 3 minutos.";
                }
            }
        } else {
            $error_message = "Falha ao logar! E-mail ou senha incorretos <br><br> <a href='cadastro.html' class='cadastro'>Não tem cadastro? Crie seu cadastro</a><br><br>";
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
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <style>
        .error-message {
            color: red;
            margin-bottom: 10px;
            font-size: 14px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .header {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #FEF6EE;
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

        
        .cadastro{
            background-color: #D97C41;
            border-radius: 5px;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s;


        }
        .cadastro:hover{
            background-color: #c96f36;
        }

        /* Centralizando o conteúdo principal */
        #main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            background-color: #FEF6EE;
        }

        /* Estilo do container do formulário */
        .container {
            background-color: #1E2A38;
            border-radius: 15px;
            padding: 30px;
            width: 350px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .container h1 {
            color: white;
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* Estilo dos ícones dentro dos inputs */
        .form-group {
            margin-bottom: 2px;
            position: relative;
        }

        /* Posição dos ícones dentro do input */
        .form-group i {
            position: absolute;
            top: 65%;
            left: 10px;
            transform: translateY(-50%);
            color: #666;
            font-size: 20px;
        }

        /* Estilo dos inputs */
        .form-group input {
            width: 100%;
            padding: 12px;
            padding-left: 40px; /* Espaço para o ícone */
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .form-group input::placeholder {
            color: #999;
        }

        .forgot-password {
            color: #ccc;
            font-size: 14px;
            display: block;
            margin-bottom: 20px;
            text-align: left;
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
            color:white;

        }
    </style>
</head>
<body>
<div class="header" id="header"> 
    <button class="icon" id="menu-toggle">
        <i class='bx bx-menu'></i>
    </button>
</div>

<div class="sidebar" id="sidebar">
    <a href="index.php">Área inicial </a>
    <a href="#">Quem Somos</a>
    <a href="#">Dúvidas</a>
</div>

<div class="overlay" id="overlay"></div>
<div id="main-content">
    <div class="container">
        <h1>Acesse sua conta</h1>
        <form action="login.php" method="POST">
            
            <!-- Exibir mensagem de erro dentro da caixa de login -->
            <?php if (!empty($error_message)) : ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <i class='bx bx-at'></i>
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email...">
            </div>
            <br>

            <div class="form-group">
                <i class='bx bxs-lock'></i>
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha...">
            </div>
            <a href="novasenhalogin.php" class="forgot-password">Esqueceu sua senha?</a>
            <button type="submit" class="submit-btn">Entrar</button>
        </form>
        <img src="logoHostfy.png" alt="logo" class="logo">
    </div>
</div>

<script>
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const overlay = document.getElementById('overlay');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-active');
        mainContent.classList.toggle('content-shift');
        overlay.classList.toggle('overlay-active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('sidebar-active');
        mainContent.classList.remove('content-shift');
        overlay.classList.remove('overlay-active');
    });
</script>
</body>
</html>
