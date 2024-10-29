<?php
include("conexao.php");


if (isset($_POST['email']) || isset($_POST['senha']) || isset($_POST['nome']) || isset($_POST['cpf']) || isset($_POST['data_nascimento']) || isset($_POST['endereco']) || isset($_POST['cidade']) || isset($_POST['estado']) || isset($_POST['telefone'])) {
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
    }else
        // Verificação de CPF já cadastrado
        $sql_cpf = "SELECT cpf FROM usuario WHERE cpf = '$cpf'";
        $result_cpf = mysqli_query($conexao, $sql_cpf);

        if (mysqli_num_rows($result_cpf) > 0) {
            echo "Erro: Este CPF já está cadastrado.";
            echo "<br>";
            echo "Faça seu login";
            echo "<br>";
            echo "<a href='login.php' class='btn btn-primary btn-block'>Login</a>";
        }  else {
        // Verificação de e-mail já cadastrado
        $sql_email = "SELECT email FROM usuario WHERE email = '$email'";
        $result_email = mysqli_query($conexao, $sql_email);

        if (mysqli_num_rows($result_email) > 0) {
            echo "Erro: Este e-mail já está cadastrado.";
            echo "<br>";
            echo "Faça seu login";
            echo "<br>";
            echo "<a href='login.php' class='btn btn-primary btn-block'>Login</a>";
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
                    echo "Faça seu login";
                    echo "<br>";
                    echo "<a href='login.php' class='btn btn-primary btn-block'>Login</a>";

                } else {
                    echo "ERRO: " . mysqli_error($conexao);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Novo Usuário</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <header>
        <img src="logoHostfy.png" alt="logo" class="logo" />
    </header>

    <div class="sidebar" id="sidebar">
        <a href="quemsomos.html">Quem Somos</a>
        <a href="#">Seus Aluguéis</a>
        <a href="#">Perfil</a>
        <a href="#">Configurações</a>
    </div>

    <div class="overlay" id="overlay"></div>

    <div class="main-content" id="main-content">
        <form id="registerForm" action="cadastro.php" method="POST">
            <div class="container">
                <div class="card card-register mx-auto col-8 px-0">
                    <div class="card-header">Cadastro de Usuário</div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="form-row">
                                <div class="col-12">
                                    <label for="nome">Nome completo</label>
                                    <input type="text" name="nome" class="form-control" placeholder="Digite seu nome completo" required>
                                </div>
                                <div class="col-12">
                                    <label for="cpf">CPF</label>
                                    <input type="text" name="cpf" class="form-control" placeholder="Digite seu CPF" required>
                                </div>
                                <div class="col-12">
                                    <label for="data_nascimento">Data de Nascimento</label>
                                    <input type="date" name="data_nascimento" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label for="endereco">Endereço</label>
                                    <input type="text" name="endereco" class="form-control" placeholder="Digite seu endereço" required>
                                </div>
                                <div class="col-6">
                                    <label for="cidade">Cidade</label>
                                    <input type="text" name="cidade" class="form-control" placeholder="Digite sua cidade" required>
                                </div>
                                <div class="col-6">
                                    <label for="estado">Estado</label>
                                    <select name="estado" class="form-control" required>
                                        <option value="">Selecione um estado</option>
                                        <option value="SC">Santa Catarina</option>
                                        <option value="PR">Paraná</option>
                                        <option value="SP">São Paulo</option>
                                        <option value="RJ">Rio de Janeiro</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="telefone">Telefone</label>
                                    <input type="text" name="telefone" class="form-control" placeholder="Digite seu telefone" required>
                                </div>
                                <div class="col-12">
                                    <label for="email">E-mail</label>
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Digite seu E-mail" required> 
                                </div>
                                <div class="col-6">
                                    <label for="senha">Senha:</label><br>
                                    <input type="password" id="senha" name="senha" class="form-control" required minlength="8" placeholder="Mínimo de 8 caracteres"><br>
                                </div>
                                <div class="col-12">
                                    <label for="confirmar_senha">Confirme a Senha:</label><br>
                                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="Confirme sua senha" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
                        <br>
                        <div class="text-center">
                            <a href="index.php" class="btn btn-primary btn-block">Página inicial</a>
                        </div>
                    </div>
                </div>
            </div>       
        </form>
    </div>

    <script>
        // Função para validar senhas ao tentar enviar o formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;

            // Limpa qualquer mensagem de erro anterior
            document.getElementById('confirmar_senha').setCustomValidity('');

            // Verifica se as senhas são diferentes
            if (senha !== confirmarSenha) {
                // Define a mensagem de erro no campo "confirmar senha"
                document.getElementById('confirmar_senha').setCustomValidity('As senhas não coincidem. Tente novamente.');
                e.preventDefault(); // Impede o envio do formulário
                document.getElementById('confirmar_senha').reportValidity(); // Exibe o tooltip de erro
            }
        });

        // Função para reiniciar a validação sempre que o campo de confirmação de senha for alterado
        document.getElementById('confirmar_senha').addEventListener('input', function() {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;

            if (senha === confirmarSenha) {
                document.getElementById('confirmar_senha').setCustomValidity(''); // Limpa o erro quando as senhas coincidem
            }
        });
    </script>
</body>
</html>