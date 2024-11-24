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
    
    //checa se o cpf tem o tamanho adequado
    $num_cpf = str_split($cpf);
    if (count($num_cpf) == 11) {

        //calcúlo para verificar que o cpf é válido
        $i = 0;
        for ($i == 0; $i <= 10; $i++) {
            $num_cpf[$i] = intval($num_cpf[$i]);
        }
        $digit_j = ($num_cpf[0] * 10 + $num_cpf[1] * 9 + $num_cpf[2] * 8 + $num_cpf[3] * 7 + $num_cpf[4] * 6 + $num_cpf[5] * 5 + $num_cpf[6] * 4 + $num_cpf[7] * 3 + $num_cpf[8] * 2)%11;
        $digit_k = ($num_cpf[0] * 11 + $num_cpf[1] * 10 + $num_cpf[2] * 9 + $num_cpf[3] * 8 + $num_cpf[4] * 7 + $num_cpf[5] * 6 + $num_cpf[6] * 5 + $num_cpf[7] * 4 + $num_cpf[8] * 3 + $num_cpf[9] * 2)%11;
        if (((($digit_j < 2 && $num_cpf[9] == 0)) || ($digit_j > 1 && ($num_cpf[9] == 11 - $digit_j))) && (($digit_k < 2 && ($num_cpf[10] == 0)) || ($digit_k > 1 && ($num_cpf[10] == 11 - $digit_k)))) {

            // Verificação de CPF banido
            $sql_banido = "SELECT banido FROM usuario WHERE cpf = '$cpf'";
            $result_banido = mysqli_query($conexao, $sql_banido);
            $row_banido = mysqli_fetch_assoc($result_banido);

            if ($row_banido && $row_banido['banido'] == 1) {
                $type_error = 'cpf';
                $erro = " - Este CPF está banido.";
            }else
                // Verificação de CPF já cadastrado
                $sql_cpf = "SELECT cpf FROM usuario WHERE cpf = '$cpf'";
                $result_cpf = mysqli_query($conexao, $sql_cpf);

                if (mysqli_num_rows($result_cpf) > 0) {
                    $type_error = 'cpf';
                    $erro = " - Este CPF já está cadastrado. Faça seu <a href='login.php'>login aqui!</a>";
                }  else {
                // Verificação de e-mail já cadastrado
                $sql_email = "SELECT email FROM usuario WHERE email = '$email'";
                $result_email = mysqli_query($conexao, $sql_email);

                if (mysqli_num_rows($result_email) > 0) {
                    $type_error = 'email';
                    $erro = " - Este e-mail já está cadastrado. Faça seu <a href='login.php'>login aqui!</a>";
                } else {
                    // Validação de idade mínima (18 anos)
                    $data_atual = new DateTime();
                    $data_nascimento_obj = new DateTime($data_nascimento);
                    $idade = $data_atual->diff($data_nascimento_obj)->y;

                    if ($idade < 18) {
                        $type_error = 'idade';
                        $erro = " - Você deve ter pelo menos 18 anos para se cadastrar.";
                    } else {
                        // Inserir novo usuário
                        $sql = "INSERT INTO usuario(nome, cpf, data_nascimento, endereco, cidade, estado, telefone, email, senha)
                                VALUES ('$nome', '$cpf', '$data_nascimento', '$endereco', '$cidade', '$estado', '$telefone', '$email', '$senha')";


                            if (mysqli_query($conexao, $sql)) {
                                $final = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                        Cadastro efetuado com sucesso. 
                                        <a href='login.php' class='btn btn-primary btn-sm ml-2'>Faça seu login</a>
                                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                    </div>";
                            } else {
                                $final = "<div class='alert alert-danger' role='alert'>
                                        ERRO: " . mysqli_error($conexao) . "
                                    </div>";
                            }

                    }
                }
            }
        } else {
            $type_error = 'cpf';
            $erro = ' - CPF inválido.';
        }
    } else {
        $type_error = 'cpf';
        $erro = " - CPF contem mais ou menos que 11 caracteres. Remova '.' e '-' se houver.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Novo Usuário</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="estilo.css"> 
    <style>
        a {
            text-decoration: none !important;
        }

        header .menu__link {
            color: #000000 !important;
            line-height: 2;
            position: relative;
            margin-left: 90px;
        }

        header .menu__link::before {
            content: '';
            width: 0;
            height: 2px;
            border-radius: 2px;
            background-color: #000000;
            position: absolute;
            bottom: -.25rem;
            right: 0;
            transition: right .4s, width .4s, left .4s;
        }

        header .menu__link:hover::before {
            width: 100%;
            left: 0;
        }


        h1 {
            font-size: 24px;
            font-weight: bold;
            color: #black;
            margin: 0;
        }
        #main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            background-color: #FEF6EE;
            margin-bottom: 60px;
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
                <h1>Bem-vindo ao HOSTFY</h1>

        <a href="login.php" class="menu__link">Login</a>
        <p><?php if (isset($final)) {echo $final;} ?></p>

    </header>

    <!-- Menu lateral (sidebar) -->
    <div class="sidebar" id="sidebar">
        <a href="index.php">Área inicial </a>
        <a href="quemsomos.php">Quem Somos</a>
        <a href="duvidas.php">Dúvidas</a>
    </div>

    <!-- Overlay para quando o menu estiver aberto -->
    <div class="overlay" id="overlay"></div>
    

    <div class="main-content" id="main-content">
        <form id="registerForm" action="cadastro.php" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome completo</label>
                    <input type="text" name="nome" class="form-control" placeholder="Digite seu nome completo" required>
                </div>
                <div class="form-group">
                    <label for="cpf">CPF<span style="color: red;"><?php if (isset($erro) && $type_error == 'cpf') {echo $erro;} ?></span></label>
                    <input type="text" name="cpf" class="form-control" placeholder="Digite seu CPF" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="data_nascimento">Data de Nascimento<span style="color: red;"><?php if (isset($erro) && $type_error == 'idade') {echo $erro;} ?></span></label>
                    <input type="date" name="data_nascimento" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="txtCep">CEP</label>
                    <input id = "txtCep" type="text" name="cep" class="form-control" placeholder="Digite seu CEP" required>
                </div>
            </div>
            <div class="form-row">
               
                <div class="form-group">
                    <label for="endereco">Endereço</label>
                    <input id = "endereco" type="text" name="endereco" class="form-control" placeholder="Digite seu endereço" required>
                </div>
                <div class="form-group">
                    <label for="bairro">Bairro</label>
                    <input id = "bairro" type="text" name="bairro" class="form-control" placeholder="Digite sua cidade" required>
                </div> 
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input id = "cidade" type="text" name="cidade" class="form-control" placeholder="Digite sua cidade" required>
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <input id = "estado" type="text" name="estado" class="form-control" placeholder="Digite sua cidade" required>
                </div>
            </div>
                                <!--     
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
                                -->
            <div class="form-row">
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <input type="text" name="telefone" class="form-control" placeholder="Digite seu telefone" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail<span style="color: red;"><?php if (isset($erro) && $type_error == 'email') {echo $erro;} ?></span></label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Digite seu E-mail" required> 
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" class="form-control" required minlength="8" placeholder="Mínimo de 8 caracteres"><br>
                </div>
                <div class="form-group">
                    <label for="confirmar_senha">Confirme a Senha:</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" placeholder="Confirme sua senha" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
            <br>
            <div class="text-center">
                <a href="index.php" class="btn btn-primary btn-block">Página inicial</a>
            </div>
        </form>
    </div>

    <footer>
    <ul>
        <p class="rights"><span>&copy;&nbsp;<span id="copyright-year"></span> .Todos os direitos reservados. <span> por Byanca Campos Furlan, Igor Miguel Raimundo, Maria Antonia dos Santos e Rithiely Schmitt.</a></span>
    </ul>
</footer>
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
        // Função buscaCEP
        function buscaCep(){
            let cep = document.getElementById('txtCep').value;
            if (cep!==""){
                let url = "https://brasilapi.com.br/api/cep/v1/" + cep;
                let req = new XMLHttpRequest();
                req.open("GET", url);
                req.send();

                //tratar a resposta da requisição
                req.onload = function(){
                    if(req.status === 200){
                        let endereco = JSON.parse(req.response);
                        document.getElementById("endereco").value = endereco.street;
                        document.getElementById("bairro").value = endereco.neighborhood;
                        document.getElementById("cidade").value = endereco.city;
                        document.getElementById("estado").value = endereco.state;
                    }
                    else if (req.status ===400){
                        alert("CEP inválido");
                    }
                    else{
                        alert("Erro ao fazer a requisição");
                    }
                }
            }
            else{
                alert("Digite um CEP válido!");
            }
        }
window.onload = function(){
    let txtCep = document.getElementById('txtCep');
    txtCep.addEventListener("blur", buscaCep);
        }

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

</body>

</html>