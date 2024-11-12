<?php
//gera um nome de 32 caracteres em hexadecimal
function nome_images($t = 16) {
    return bin2hex(random_bytes($t));
}

include("conexao.php");

// Inicia a sessão, se não tiver sido iniciada
session_start();

// Verifica se o usuário está logado
if (isset($_SESSION['id'])) {
    $id_proprietario = $_SESSION['id'];
} else {
    echo "<br>";
    echo "<style> *{ background-color:#FEF6EE;  color: #C56126; align-items: center; justify-content: center; } .logo {
    width: 120px; position: fixed; left: 50px; top: 35px;} a{text-decoration: none; color: #5b2c12</style><div style=' background-color: #fff3f3; border: 1px solid orange; padding: 15px; border-radius: 8px; text-align: center; '> <img src='logoHostfy.png' alt='logo' class='logo'/><h1>Você precisa estar logado para cadastrar um imóvel.</h1><p> Faça seu login <a href='login.php'>aqui</a>.</div>";
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtém os dados do formulário
    $cep = $_POST['cep'];
    $nome_imovel = $_POST['nome_imovel'];
    $endereco = $_POST['endereco'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $valor = $_POST['valor'];
    $descricao = $_POST['descricao'];
    $categoria = $_POST['categoria'];
    $numero_pessoas = $_POST['numero_pessoas'];
    $caracteristicas = isset($_POST['caracteristicas']) ? implode(", ", $_POST['caracteristicas']) : "";

    //envio das imagens
    //ALTER TABLE `imovel` ADD `imagens` VARCHAR(1500) NOT NULL AFTER `Numero_pessoas`;
    if (isset($_FILES['imagem'])) {    
        foreach ($_FILES['imagem']['name'] as $arquivo => $nome) {
            if ($_FILES['imagem']['type'][$arquivo] == 'image/png' || $_FILES['imagem']['type'][$arquivo] == 'image/jpeg') {   
                if ($_FILES['imagem']['type'][$arquivo] == 'image/png') {
                    $caminho = nome_images($t = 16) . '.png';
                } elseif ($_FILES['imagem']['type'][$arquivo] == 'image/jpeg') {
                    $caminho = nome_images($t = 16) . '.jpg';
                } 
                $temporario = $_FILES['imagem']['tmp_name'][$arquivo];
                $caminho = './uploads/' . $caminho;
                $destinos[] = $caminho;

                move_uploaded_file($temporario, $caminho);
                $ver = 1;
            } else {
                $erro = ' - tipo de arquivo invalido.';
                $erro_tipo = 'img';
                $ver = 0;
            }
        }

        if ($ver == 1) {

            $destinos_bd = implode(", ", $destinos);

            // Insere os dados no banco de dados com o id_proprietario definido automaticamente
            $sql = "INSERT INTO imovel (cep, nome_imovel, rua, numero, bairro, cidade, uf, id_proprietario, valor, descrição, id_categoria, numero_pessoas, id_checklist, imagens)
            VALUES ('$cep', '$nome_imovel', '$endereco', '$numero', '$bairro', '$cidade', '$estado', '$id_proprietario', '$valor', '$descricao', '$categoria', '$numero_pessoas', '$caracteristicas', '$destinos_bd')";

            if ($conexao->query($sql) === TRUE) {
                    $final = "Imóvel cadastrado com sucesso!</p>";
                } else {
                    $final = "Erro ao cadastrar imóvel: " . $conexao->error;
            }

            // Fecha a conexão com o banco de dados
            $conexao->close();
        }
    } else {
        $erro = ' - falha ao enviar as imagens';
        $erro_tipo = 'img';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Novo Imóvel</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="estilo.css"> 
    <style>
          h1 {
            font-size: 24px;
            font-weight: bold;
            color: #black;
            margin: 0;
        }

        #main-content {
        
            padding-top: 500px;

        }
    </style>
    
<body>
<header>
        <!-- Botão do ícone de menu -->
        <button class="menu-icon" id="menu-toggle">
            <i class='bx bx-menu'></i>
        </button>
        <img src="logoHostfy.png" alt="logo" class="logo" />
        <p><?php if (isset($final)) {echo $final;} ?></p>

        <h1>Cadastro de Imóvel</h1>
    </header>

    <!-- Menu lateral (sidebar) -->
    <div class="sidebar" id="sidebar">
        <a href="index.php">Área inicial </a>
        <a href="quemsomos.html">Quem Somos</a>
        <a href="#">Dúvidas</a>
    </div>

    <!-- Overlay para quando o menu estiver aberto -->
    <div class="overlay" id="overlay"></div>

    <div class="main-content" id="main-content">
        <form id="registerForm" action="imoveis.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome_imovel">Nome do Imóvel</label>
                <input type="text" name="nome_imovel" class="form-control" placeholder="Digite o nome do imóvel" maxlength="50"oninput="changeColor(this)" required>
            </div>
            <div class="form-group">
                <label for="txtCep">CEP</label>
                <input id="txtCep"  type="text" name="cep" class="form-control" placeholder="Digite seu CEP" maxlength="8" oninput="changeColor(this)" required>
            </div>
                <div class="form-group">
                    <label for="endereco">Rua</label>
                    <input id = "endereco" type="text" name="endereco"  class="form-control" placeholder="Digite a rua" maxlength="50" oninput="changeColor(this)" required>
                </div>
                <div class="form-group">
                    <label for="numero">Número</label>
                    <input type="number" name="numero" class="form-control" placeholder="Digite o número" required>
                </div>
                <div class="form-group">
                    <label for="bairro">Bairro</label>
                    <input id="bairro" type="text" name="bairro"  class="form-control" placeholder="Digite o bairro" maxlength="50"oninput="changeColor(this)" required>
                </div>
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input id="cidade" type="text" name="cidade"  class="form-control" placeholder="Digite a cidade" maxlength="50"oninput="changeColor(this)" required>
                </div>
                <div class="form-group">
                    <label for="estado">UF</label>
                    <input type="text" name="estado" id="estado" class="form-control" placeholder="Digite a UF" oninput="changeColor(this)" required>
                </div>
                <div class="form-group">
                    <label for="valor">Valor Diária</label>
                    <input type="number" name="valor" class="form-control" placeholder="Digite o valor do imóvel" step="0.01"oninput="changeColor(this)" required>
                </div>
                <div class="form-group">
                    <label for="numero_pessoas">Número de Pessoas</label>
                    <input type="number" name="numero_pessoas" class="form-control" placeholder="Digite o número de pessoas" oninput="changeColor(this)" required>
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" class="form-control" placeholder="Digite uma descrição" maxlength="500" oninput="changeColor(this)" required></textarea>
                </div>

                <div class="form-group">
                    <label>Categoria do Imóvel </label>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="categoria" value="1" required>
                        <label class="form-check-label" for="casa">Casa</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="categoria" value="apartamento">
                        <label class="form-check-label" for="apartamento">Apartamento</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="categoria" value="sitio">
                        <label class="form-check-label" for="sitio">Sítio</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Características do Imóvel</label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="1">
                        <label class="form-check-label" for="garagem">Garagem</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="bicicleta">
                        <label class="form-check-label" for="bicicleta">Bicicleta</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="pet_friendly">
                        <label class="form-check-label" for="pet_friendly">Pet Friendly</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="churrasqueira">
                        <label class="form-check-label" for="churrasqueira">Churrasqueira</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="piscina">
                        <label class="form-check-label" for="piscina">Piscina</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="sauna">
                        <label class="form-check-label" for="sauna">Sauna</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="quadra_poliesportiva">
                        <label class="form-check-label" for="quadra_poliesportiva">Quadra Poliesportiva</label>
                    </div>
                    <div class="form-group">
                        <!-- o label está chamando o input, qualquer estilização feita deve ser aplicada ao label -->
                        <label for="imagem">fotos do arquivo<?php if(isset($erro) && $erro_tipo = 'img') {echo $erro;} ?></label>
                        <input style="display: none;" id="imagem"  type="file" name="imagem[]" required multiple>
                    </div>

        </div>
            <button type="submit" class="btn btn-primary btn-block">Cadastrar Imóvel</button>
            <a href="index.php" class="btn btn-primary btn-block">Página inicial</a>
        
        </form>
        
    </div>
    <script>
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

    function changeColor(input) {
            if (input.value.trim() !== "") {
                input.classList.add('filled'); // Adiciona a classe 'filled'
            } else {
                input.classList.remove('filled'); // Remove a classe 'filled'
            }
        }

    //limita o número de arquivos a 20
    document.getElementById('imagem').addEventListener('change', function(e) {
    if (e.target.files.length > 20) {
        alert("Você só pode enviar até 20 arquivos.");
        e.target.value = "";
        }
    });

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