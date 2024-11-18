<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados
include("conexao.php");

// Verifica se a conexão foi bem-sucedida
if (!$conexao) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

// ID do proprietário (deve vir da sessão ou ser definido)
$id_proprietario = $_SESSION['id'];

// Verifica se o formulário de edição foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados do formulário
    $ID_imovel = $_POST['ID_imovel'];
    $nome_imovel = $_POST['nome_imovel'];
    $cep = $_POST['txtCep'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $uf = $_POST['uf'];
    $valor = $_POST['valor'];
    $descricao = $_POST['descricao'];
    $id_categoria = $_POST['id_categoria'];
    $numero_pessoas = $_POST['numero_pessoas'];
    $id_checklist= $_POST['caracteristicas'];

    // Atualiza as informações do imóvel no banco de dados
    $sql_update = " UPDATE imovel SET 
            CEP = ?, 
            Nome_imovel = ?, 
            Rua = ?,
            Numero = ?, 
            Bairro = ?, 
            Cidade = ?, 
            UF = ?, 
            Valor = ?, 
            Descrição = ?, 
            id_categoria = ?, 
            Numero_pessoas = ?, 
            id_checklist = ?
        WHERE ID_imovel = ? AND ID_proprietario = ? ";
    $stmt_update = mysqli_prepare($conexao, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssssssssssssii", $cep, $nome_imovel, $rua, $numero, $bairro, $cidade, $uf, $valor, $descricao, $id_categoria, $numero_pessoas, $caracteristicas, $ID_imovel, $ID_proprietario);
    if (mysqli_stmt_execute($stmt_update)) {
        $message = "Imóvel atualizado com sucesso!";
    } else {
        $message = "Erro ao atualizar o imóvel: " . mysqli_error($conexao);
    }
}


// Buscar os imóveis cadastrados do proprietário
$sql = "
    SELECT 
        imovel.ID_imovel, 
        imovel.CEP, 
        imovel.Nome_imovel,
        imovel.Rua,
        imovel.Numero, 
        imovel.Bairro, 
        imovel.Cidade, 
        imovel.UF, 
        imovel.Valor, 
        imovel.Descrição, 
        categoria.id_categoria AS categoria_nome,
        imovel.Numero_pessoas, 
        imovel.id_checklist
    FROM imovel
    JOIN Categoria AS categoria ON imovel.id_categoria = categoria.id_categoria
    WHERE imovel.ID_proprietario = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_proprietario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// Verifica se há um imóvel a ser editado (caso o ID seja passado via GET)
$edit_imovel = null;
if (isset($_GET['edit'])) {
    $id_imovel_edit = $_GET['edit'];
    $sql_edit = "SELECT * FROM imovel WHERE ID_imovel = ? AND ID_proprietario = ?";
    $stmt_edit = mysqli_prepare($conexao, $sql_edit);
    mysqli_stmt_bind_param($stmt_edit, "ii", $id_imovel_edit, $id_proprietario);
    mysqli_stmt_execute($stmt_edit);
    $result_edit = mysqli_stmt_get_result($stmt_edit);
    if ($edit_imovel = mysqli_fetch_assoc($result_edit)) {
        // Dados do imóvel para edição
    }
}
if (isset($_GET['delete'])) {
    $id_imovel_delete = $_GET['delete'];
    $sql_delete = "DELETE FROM imovel WHERE ID_imovel = ? AND ID_proprietario = ?";
    $stmt_delete = mysqli_prepare($conexao, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "ii", $id_imovel_delete, $id_proprietario);
    if (mysqli_stmt_execute($stmt_delete)) {
        $message = "Imóvel excluído com sucesso!";
    } else {
        $message = "Erro ao excluir o imóvel: " . mysqli_error($conexao);
    }
}


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imóveis Cadastrados</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

    <style>
 body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #FEF6EE;
        }
        input[type="submit"],

        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            background-color: #1E2A38;
            border-radius: 4px;
            font-size: 16px;
            background-color: #C56126;

        }
    .form-container {
            background-color: #1E2A38;
            border-radius: 15px;
            padding: 20px;
            max-width: 1800px;
            margin: auto;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: white;
            margin-bottom: 20px;
        }
        .menu-icon {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 30px;
            color: #333;
         }
     
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #FEF6EE;
            padding: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}
.img {
    border-radius: 15px;
}
        table {
            width: 100%;
            color: white;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #C56126;
        }

        td {
            background-color: #333;
        }
        .edit-form input[type="text"],
        .edit-form input[type="number"],
        .edit-form textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
       
        .alert {
            color: #C56126;
            margin-top: 20px;
        }
                .a {
            text-decoration: none;  
        }
        .section1 {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: wrap;
            width: 100vw;
            padding: 10px;
            box-sizing: border-box;
        }

        .rights {
            padding: 10px 0;
            text-align: center;
            align-items: center;
            font-size: 14px;
            font-weight: 500;
            color: #9b9b9b;
           
            overflow: auto;
}
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
                
        #footer {
        position: sticky;
        bottom: 0;
        width: 100%;
        height: 2.5rem;            /* altura do rodapé */
        }
    </style>
</head>
<body>
<header>
            <!-- Botão do ícone de menu -->
             <div class="section1">
                <button class="menu-icon" id="menu-toggle"aria-label="Abrir Menu">
                    <i class='bx bx-menu'></i>
                </button>
                <img src="logoHostfy.png" alt="logo" class="logo" />
                </div>
                <div id="deslogado">
                    <a href="login.php" class="menu__link">Login</a>
                    <a href="cadastro.php" class="menu__link">Cadastre-se</a>
        
        
                </div>
                <div id="logado">
                    <a href="logout.php" class="menu__link">Sair</a>
                </div>
               
         
    </header>
<!-- Menu lateral (sidebar) -->
        <div class="sidebar" id="sidebar">
            <a href="index.php">Área inicial </a>
            <a href="quemsomos.php">Quem Somos</a>
            <a href="duvidas.php">Dúvidas</a>
        </div>
   
   
        <!-- Overlay para quando o menu estiver aberto -->
        <div class="overlay" id="overlay"></div>
        <!-- Conteúdo principal -->
      
<div class="form-container">
    <h1>Imóveis Cadastrados</h1>
    <?php if (isset($message)) { echo "<div class='alert'>$message</div>"; } ?>
    <table>
        <thead>
            <tr>
                <th>Nome do Imóvel</th>
                <th>CEP</th>
                <th>Endereço</th>
                <th>Número</th>
                <th>Bairro</th>
                <th>Cidade</th>
                <th>Estado</th>
                <th>Valor</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Número de Pessoas</th>
                <th>Características</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($registro = mysqli_fetch_assoc($resultado)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($registro['Nome_imovel']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['CEP']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Rua']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Numero']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Bairro']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Cidade']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['UF']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Valor']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Descrição']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['categoria_nome']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['Numero_pessoas']) . "</td>";
                echo "<td>" . htmlspecialchars($registro['id_checklist']) . "</td>";
                echo "<td><a href='?edit=" . $registro['ID_imovel'] . "'>Editar</a></td>";
                echo "<td><a href='?delete=" . $registro['ID_imovel'] . "'>Excluir</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Formulário de edição -->
    <?php if ($edit_imovel): ?>
        <div class="edit-form">
            <h2>Editar Imóvel</h2>
            <form id="registerForm" action="imoveiscadastrados.php" method="POST">
            <!-- Campo oculto com o ID do imóvel -->
                <input type="hidden" name="ID_imovel" value="<?= $edit_imovel['ID_imovel'] ?>">
                <label for="nome_imovel">Nome do Imóvel</label>
                <input type="text" name="nome_imovel" value="<?=  htmlspecialchars($edit_imovel['Nome_imovel']) ?>" required>
                <label for="txtCep">CEP</label>
                <input type="text" id = "txtCep" name="txtCep" value="<?=  htmlspecialchars($edit_imovel['CEP']) ?>" required>
                <label for="rua">Rua</label>
                <input type="text" id= "rua" name="rua" value="<?=  htmlspecialchars($edit_imovel['Rua']) ?>" required>
                <label for="numero">Número</label>
                <input type="number" name="numero" value="<?=  htmlspecialchars($edit_imovel['Numero'])?>" required>
                <label for="bairro">Bairro</label>
                <input type="text" id= "bairro" name="bairro" value="<?= htmlspecialchars($edit_imovel['Bairro']) ?>" required>
                <label for="cidade">Cidade</label>
                <input type="text" id = "cidade" name="cidade" value="<?= htmlspecialchars($edit_imovel['Cidade']) ?>" required>
                <label for="uf">Estado</label>
                <input type="text" id = "uf" name="uf" value="<?= htmlspecialchars($edit_imovel['UF']) ?>" required>
                <label for="valor">Valor</label>
                <input type="number" name="valor" value="<?= htmlspecialchars($edit_imovel['Valor']) ?>" required>
                <label for="descricao">Descrição</label>
                <textarea name="descricao" required><?= htmlspecialchars($edit_imovel['Descrição']) ?></textarea>
                <label for="id_categoria">Categoria</label>
                <input type="number" name="id_categoria" value="<?= htmlspecialchars($edit_imovel['id_categoria']) ?>" required>
                <label for="numero_pessoas">Número de Pessoas</label>
                <input type="number" name="numero_pessoas" value="<?= htmlspecialchars($edit_imovel['Numero_pessoas']) ?>" required>
                <label for="caracteristicas">Características</label>
                <textarea name="caracteristicas" required><?=  htmlspecialchars($edit_imovel['caracteristicas']) ?></textarea>
                <input type="submit" value="Salvar Alterações">
                
            </form>
            <?php endif; ?>
        </div>
    
</div>

<?php
// Liberando recursos e fechando a conexão com o banco
mysqli_free_result($resultado);

mysqli_close($conexao);
?>
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
                    document.getElementById("rua").value = endereco.street;
                    document.getElementById("bairro").value = endereco.neighborhood;
                    document.getElementById("cidade").value = endereco.city;
                    document.getElementById("uf").value = endereco.state;
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
 
    document.getElementById('copyright-year').textContent = new Date().getFullYear();

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
        
        
     
</script>
<script>    
            //fução para a index do usuario logado
            function logado() {
                document.getElementById('logado').style.display='';
                document.getElementById('deslogado').style.display='none';
            }
            //função para o usuario deslogado
            function deslogado() {
                document.getElementById('logado').style.display='none';
                document.getElementById('deslogado').style.display='';
            }
        </script>

        <?php
            //verifica o login e muda o index
            if (isset($_SESSION['id'])) {
                echo '<script> logado() </script>';
            } else {
                echo '<script> deslogado() </script>';
            }
        ?> 
</body>
</html>