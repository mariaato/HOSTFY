<?php
include("conexaoLogin.php");
include("conexao.php");

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
    echo "<br>";
    echo " <style> *{ background-color:#FEF6EE;  color: #C56126; align-items: center; justify-content: center; } .logo {width: 120px; position: fixed; left: 50px; top: 35px;} a{text-decoration: none; color: #5b2c12;</style><div style='color: #C56126; background-color: #fff3f3; border: 1px solid red; padding: 15px; border-radius: 8px; text-align: center;'><img src='logoHostfy.png' alt='logo' class='logo'/><h1>$error_message</h1><p>Por favor, aguarde até que o tempo de bloqueio termine. Se você esqueceu sua senha, pode redefini-la <a href='novasenhalogin.php'>aqui</a>.</div>";
    exit;
}

if (isset($_POST['email']) || isset($_POST['senha'])) {

    if (strlen($_POST['email']) == 0) {
        $error_message = "Preencha seu e-mail";
    } else if (strlen($_POST['senha']) == 0) {
        $error_message = "Preencha sua senha";
    }else {
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

                //checa pelo admin
                if ($usuario['id'] == 0) {
                    $_SESSION['id'] = $usuario['id'];
                    $_SESSION['nome'] = $usuario['nome'];
                    $cookie_nome = $_SESSION['nome'];
                    $cookie_id = $_SESSION['id'];
                    setcookie('usuario', $cookie_nome, time() + 1800, '/');
                    setcookie('id', $cookie_id, time() + 1800, '/');
                    $_COOKIE['usuario'] = $_SESSION['nome'];
                    $_COOKIE['id'] = $_SESSION['id'];
                    header("Location: admin.php");
                    exit;
                } elseif ($usuario['banido'] == 1) {
                    $error_message = "<br>Usuário banido.";
                } else {
                    $_SESSION['id'] = $usuario['id'];
                    $_SESSION['nome'] = $usuario['nome'];
                    $cookie_nome = $_SESSION['nome'];
                    $cookie_id = $_SESSION['id'];
                    setcookie('usuario', $cookie_nome, time() + 1800, '/');
                    setcookie('id', $cookie_id, time() + 1800, '/');
                    $_COOKIE['usuario'] = $_SESSION['nome'];
                    $_COOKIE['id'] = $_SESSION['id'];
                    header("Location: index.php");
                    exit;
                }
            } else {
                // Senha incorreta, incrementar tentativas
                $_SESSION['attempts'] += 1;
                $_SESSION['last_attempt_time'] = time(); // Atualizar o tempo da última tentativa
                $error_message = "Falha ao logar! Senha incorreta. Tentativas restantes: " . ($max_attempts - $_SESSION['attempts']);
                
                if ($_SESSION['attempts'] >= $max_attempts) {
                    $error_message = "<br>Conta bloqueada por 3 minutos.";
                }
            }
        } else {
            $error_message = "Falha ao logar! E-mail incorreto <br><br> <a href='cadastro.php' class='cadastro'>Não tem cadastro? Crie seu cadastro</a><br><br>";
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
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="styles.css?">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

   <style>
        .error-message {
            color: red;
            margin-bottom: 10px;
            font-size: 18px;
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
            margin: 0;
            padding: 0;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;

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
    <a href="quemsomos.php">Quem Somos</a>
    <a href="duvidas.php">Dúvidas</a>
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
        <a href="index.php">
            <img src="logoHostfy.png" alt="logo" class="logo" />
        </a>    </div>
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
    // Define o ano atual
    document.getElementById('copyright-year').textContent = new Date().getFullYear();

</script>
</body>
<footer>
        <ul>
        <p class="rights"><span>&copy;&nbsp;<span id="copyright-year"></span> .Todos os direitos reservados. <span> por Byanca Campos Furlan, Igor Miguel Raimundo, Maria Antonia dos Santos e Rithiely Schmitt.</a></span>
        </ul>
    </footer>   
</html>
