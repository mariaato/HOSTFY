<?php
include("conexao.php");

$nome = $_POST['nome'];
$cpf = $_POST['cpf'];
$data_nascimento = $_POST['data_nascimento']; // Formato: YYYY-MM-DD
$endereco = $_POST['endereco'];
$cidade = $_POST['cidade'];
$estado = $_POST['estado'];
$telefone = $_POST['telefone'];
$email = $_POST['email'];
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

// Verificação de CPF banido
$sql_banido = "SELECT banido FROM usuario WHERE cpf = '$cpf'";
$result_banido = mysqli_query($conexao, $sql_banido);
$row_banido = mysqli_fetch_assoc($result_banido);

if ($row_banido && $row_banido['banido'] == 1) {
    echo "Erro: Este CPF está banido.";
} else {
    // Verificação de e-mail já cadastrado
    $sql_email = "SELECT email FROM usuario WHERE email = '$email'";
    $result_email = mysqli_query($conexao, $sql_email);

    if (mysqli_num_rows($result_email) > 0) {
        echo "Erro: Este e-mail já está cadastrado.";
    } else {
        // Validação de idade mínima (18 anos)
        $data_atual = new DateTime();
        $data_nascimento_obj = new DateTime($data_nascimento);
        $idade = $data_atual->diff($data_nascimento_obj)->y;

        if ($idade < 18) {
            echo "Erro: Você deve ter pelo menos 18 anos para se cadastrar.";
        } else {
            // Inserir novo usuário
            $sql = "INSERT INTO usuario(nome, cpf, data_nascimento, endereco, cidade, estado, telefone, email, senha)
                    VALUES ('$nome', '$cpf', '$data_nascimento', '$endereco', '$cidade', '$estado', '$telefone', '$email', '$senha')";

            if (mysqli_query($conexao, $sql)) {
                echo "Cadastro efetuado com sucesso.";
            } else {
                echo "ERRO: " . mysqli_error($conexao);
            }
        }
    }
}

mysqli_close($conexao);

// CODIGO MYSLI
//CREATE TABLE usuario (
   // id INT AUTO_INCREMENT PRIMARY KEY,
   // nome VARCHAR(100) NOT NULL,
   // cpf VARCHAR(11) NOT NULL,
    //data_nascimento DATE NOT NULL,
   // endereco VARCHAR(255) NOT NULL,
   // cidade VARCHAR(100) NOT NULL,
   // estado VARCHAR(2) NOT NULL,
   // telefone VARCHAR(15) NOT NULL,
   // email VARCHAR(100) NOT NULL,
   // senha VARCHAR(255) NOT NULL,
   // banido TINYINT(1) DEFAULT 0 // banimento true 1 e false 0 , em cima faz a verifcação, se for true nao deixa fazer novo cadastro naquele cpf
//);
?>