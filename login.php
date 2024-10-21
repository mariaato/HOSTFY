<?php
include("conexaoLogin.php");

if (isset($_POST['email']) || isset($_POST['senha'])) {

    if (strlen($_POST['email']) == 0) {
        echo "Preencha seu e-mail";
    } else if (strlen($_POST['senha']) == 0) {
        echo "Preencha sua senha";
    } else {

        $email = $mysqli->real_escape_string($_POST['email']);
        $senha = $_POST['senha']; 

        $sql_code = "SELECT * FROM usuario WHERE email = '$email'";
        $sql_query = $mysqli->query($sql_code) or die("Falha na execução do código SQL: " . $mysqli->error);

        if ($sql_query->num_rows == 1) {
            $usuario = $sql_query->fetch_assoc();

            // Verifique se a senha fornecida corresponde à senha criptografada
            if (password_verify($senha, $usuario['senha'])) {
                if (!isset($_SESSION)) {
                    session_start();
                }

                $_SESSION['id'] = $usuario['id'];
                $_SESSION['nome'] = $usuario['nome'];

                header("Location: inicio.html");
                exit(); 
            } else {
                echo "Falha ao logar! E-mail ou senha incorretos";
            }
        } else {
            echo "Falha ao logar! E-mail ou senha incorretos";
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">


</head>
<body>
    <div class="container">
        <h1>Acesse sua conta</h1>
        <form action="" method="POST">
            <div class="form-group">
            <i class='bx bx-at'></i>
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite seu email...">
            </div>
            <div class="form-group">
            <i class='bx bxs-lock'></i>
                <label for="senha">Senha</label>
                
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha...">
            </div>
            <a href="#" class="forgot-password">Esqueceu sua senha?</a>
            <button type="submit" class="submit-btn">Entrar</button>
        </form>
        <div class="logo">
            <img src="logoHostfy.png" alt="logo" class="logo">
        </div>
    </div>

</body>
</html>