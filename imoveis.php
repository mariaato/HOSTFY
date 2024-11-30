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
    width: 120px; position: fixed; left: 50px; top: 35px;} a{text-decoration: none; color: #5b2c12</style><div style=' background-color: #fff3f3; border: 1px solid orange; padding: 15px; border-radius: 8px; text-align: center; '>  <a href='index.php'>
            <img src='logoHostfy.png' alt='logo' class='logo' />
        </a><h1>Você precisa estar logado para cadastrar um imóvel.</h1><p> Faça seu login <a href='login.php'>aqui</a>.</div>";
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
    $id_checklist = $_POST['caracteristicas'];

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
            $sql = "INSERT INTO imovel (cep, nome_imovel, rua, numero, bairro, cidade, uf, id_proprietario, valor, descrição, id_categoria, numero_pessoas, imagens)
            VALUES ('$cep', '$nome_imovel', '$endereco', '$numero', '$bairro', '$cidade', '$estado', '$id_proprietario', '$valor', '$descricao', '$categoria', '$numero_pessoas', '$destinos_bd')";

            if ($conexao->query($sql) === TRUE) {

                    $id_imovel = $conexao->insert_id;

                    foreach ($id_checklist as $ids) {
                        $checklist = $conexao->prepare("INSERT INTO imovel_checklist (id_imovel, id_checklist) VALUES (?,?)");
                        $checklist->bind_param('ii', $id_imovel, $ids);
                        $checklist->execute();
                    }
                    $final = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                                        Imóvel cadastrado com sucesso! 
                                        <a href='meus_imoveis.php' class='btn btn-primary btn-sm ml-2'>Veja seus imóveis</a>
                                        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                            <span aria-hidden='true'>&times;</span>
                                        </button>
                                    </div>";
                } else {
                    $final = "<div class='alert alert-danger' role='alert'>
                                        Erro ao cadastrar imóvel:  " . mysqli_error($conexao) . "
                                    </div>";
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
            color: black;
            margin: 0;
        }

        #main-content {   
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #FEF6EE;
            margin-top: -55px;
            
        }
        .custum-file-upload {
            margin-top: 20px;
            height: 200px;
            width: 300px;
            display: flex;
            flex-direction: column;
            align-items: space-between;
            gap: 20px;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            border: 2px dashed #e8e8e8;
            padding: 1.5rem;
            border-radius: 10px;
        }

            .custum-file-upload .icon {
            display: flex;
            align-items: center;
            justify-content: center;
            }

            .custum-file-upload .icon svg {
            height: 80px;
            fill: #e8e8e8;
            }

            .custum-file-upload .text {
            display: flex;
            align-items: center;
            justify-content: center;
            }

            .custum-file-upload .text span {
            font-weight: 400;
            color: #e8e8e8;
            }

    </style>
    
<body>
<header>
        <!-- Botão do ícone de menu -->
        <button class="menu-icon" id="menu-toggle">
            <i class='bx bx-menu'></i>
        </button>
        <a href="index.php">
            <img src="logoHostfy.png" alt="logo" class="logo" />
        </a>
        <p><?php if (isset($final)) {echo $final;} ?></p>

        <h1>Cadastro de Imóvel</h1>
    </header>

    <!-- Menu lateral (sidebar) -->
    <div class="sidebar" id="sidebar">
        <a href="index.php">Área inicial </a>
        <a href="perfilhtml.php">Perfil</a>
        <a href="meus_imoveis.php">Imóveis Cadastrados</a>
        <a href="quemsomos.php">Quem Somos</a>
        <a href="duvidas.php">Dúvidas</a>
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
                    <input type="number" name="valor" class="form-control" placeholder="Digite o valor do imóvel"   min="100.00" step="1.00"oninput="changeColor(this)" required>
                </div>
                <div class="form-group">
                    <label for="numero_pessoas">Número de Pessoas</label>
                    <input type="number" name="numero_pessoas" class="form-control" placeholder="Digite o número de pessoas"  min="0" oninput="changeColor(this)" required>
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
                        <input type="radio" class="form-check-input" name="categoria" value="2">
                        <label class="form-check-label" for="apartamento">Apartamento</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="categoria" value="3">
                        <label class="form-check-label" for="sitio">Sítio</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="categoria" value="4">
                        <label class="form-check-label" for="sitio">Hotel</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="categoria" value="5">
                        <label class="form-check-label" for="sitio">Cabana</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="categoria" value="6">
                        <label class="form-check-label" for="sitio">Resort</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Características do Imóvel</label>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="1">
                        <label class="form-check-label" for="garagem">Garagem</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="2">
                        <label class="form-check-label" for="bicicleta">Bicicleta</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="3">
                        <label class="form-check-label" for="pet_friendly">Pet Friendly</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="4">
                        <label class="form-check-label" for="churrasqueira">Churrasqueira</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="5">
                        <label class="form-check-label" for="piscina">Piscina</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="6">
                        <label class="form-check-label" for="sauna">Sauna</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="caracteristicas[]" value="7">
                        <label class="form-check-label" for="quadra_poliesportiva">Quadra Poliesportiva</label>
                    </div>
                    <div class="form-group">
                        <!-- o label está chamando o input, qualquer estilização feita deve ser aplicada ao label -->
                        <label for="imagem" class="custum-file-upload">
                            <div class="icon">
                                <svg viewBox="0 0 24 24" fill="" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 1C9.73478 1 9.48043 1.10536 9.29289 1.29289L3.29289 7.29289C3.10536 7.48043 3 7.73478 3 8V20C3 21.6569 4.34315 23 6 23H7C7.55228 23 8 22.5523 8 22C8 21.4477 7.55228 21 7 21H6C5.44772 21 5 20.5523 5 20V9H10C10.5523 9 11 8.55228 11 8V3H18C18.5523 3 19 3.44772 19 4V9C19 9.55228 19.4477 10 20 10C20.5523 10 21 9.55228 21 9V4C21 2.34315 19.6569 1 18 1H10ZM9 7H6.41421L9 4.41421V7ZM14 15.5C14 14.1193 15.1193 13 16.5 13C17.8807 13 19 14.1193 19 15.5V16V17H20C21.1046 17 22 17.8954 22 19C22 20.1046 21.1046 21 20 21H13C11.8954 21 11 20.1046 11 19C11 17.8954 11.8954 17 13 17H14V16V15.5ZM16.5 11C14.142 11 12.2076 12.8136 12.0156 15.122C10.2825 15.5606 9 17.1305 9 19C9 21.2091 10.7909 23 13 23H20C22.2091 23 24 21.2091 24 19C24 17.1305 22.7175 15.5606 20.9844 15.122C20.7924 12.8136 18.858 11 16.5 11Z" fill=""></path> </g></svg>
                            </div>
                        <div class="text">Fotos do imóvel</div>
                        <?php if(isset($erro) && $erro_tipo = 'img') {echo $erro;} ?> </label>
                        <input style="display: none;" id="imagem"  type="file" name="imagem[]" required multiple>
                        <!-- Div para exibir o nome dos arquivos -->
                        <div id="file-list" style="margin-top: 10px; color: #555;"></div>
</div>


        </div>
            <button type="submit" class="btn btn-primary btn-block">Cadastrar Imóvel</button>
            <a href="index.php" class="btn btn-primary btn-block">Página inicial</a>
        
        </form>
        
    </div>
    <footer><ul>
            <p class="rights"><span>&copy;&nbsp;<span id="copyright-year"></span> .Todos os direitos reservados. <span> por Byanca Campos Furlan, Igor Miguel Raimundo, Maria Antonia dos Santos e Rithiely Schmitt.</a></span>
    </ul>
        </footer>
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

    // Seleciona o input e o elemento para exibir os arquivos
    const fileInput = document.getElementById('imagem');
    const fileList = document.getElementById('file-list');

    // Adiciona um evento para quando arquivos forem selecionados
    fileInput.addEventListener('change', function () {
        // Limpa o conteúdo anterior
        fileList.innerHTML = '';

        // Verifica se algum arquivo foi selecionado
        if (fileInput.files.length > 0) {
            const fileNames = Array.from(fileInput.files).map(file => `<p>${file.name}</p>`); // Gera um array de nomes de arquivos
            fileList.innerHTML = fileNames.join(''); // Adiciona os nomes ao conteúdo da div
        } else {
            fileList.innerHTML = '<p>Nenhum arquivo selecionado.</p>';
        }
    });
      // Define o ano atual
      document.getElementById('copyright-year').textContent = new Date().getFullYear();

        
</script>
</body>

</html>