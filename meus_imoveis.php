<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include("conexao.php");

$pesquisa = $conexao->prepare("SELECT * FROM checklist");
$pesquisa->execute();
$checklists = $pesquisa->get_result();

$pesquisa = $conexao->prepare("SELECT * FROM categoria");
$pesquisa->execute();
$categorias = $pesquisa->get_result();

// checa se algum imóvel foi alterado e atualiza o banco
if (isset($_POST['id_imovel'])) {
    $ps = $conexao->prepare("UPDATE imovel SET nome_imovel=?, numero=?, rua=?, bairro=?, uf=?, cidade=?, cep=?, valor=?, descrição=?, numero_pessoas=?, id_categoria=?  WHERE id_imovel = ?");
    $ps->bind_param("sisssssdsiii", $_POST['nome_imovel'],  $_POST['numero'], $_POST['rua'], $_POST['bairro'], $_POST['uf'], $_POST['cidade'], $_POST['cep'], $_POST['valor'], $_POST['descricao'], $_POST['numero_pessoas'], $_POST['id_categoria'], $_POST['id_imovel']);
    $ps->execute();

    $deletar_antigas_caracteristicas = $conexao->prepare("DELETE FROM imovel_checklist WHERE id_imovel=?");
    $deletar_antigas_caracteristicas->bind_param("i", $_POST['id_imovel']);
    $deletar_antigas_caracteristicas->execute();
    foreach ($_POST['check'] as $c) { 
    $adicionar_novas_caracteristicas = $conexao->prepare("INSERT INTO imovel_checklist (id_imovel, id_checklist) VALUES (?,?)");
    $adicionar_novas_caracteristicas->bind_param('ii', $_POST['id_imovel'], $c);
    $adicionar_novas_caracteristicas->execute();
    }
}

// Seleciona os imóveis do usuário logado com a categoria
$sql = "
    SELECT 
        imovel.id_imovel, 
        imovel.nome_imovel, 
        imovel.numero, 
        imovel.rua, 
        imovel.bairro, 
        imovel.cidade, 
        imovel.uf, 
        imovel.cep, 
        imovel.valor, 
        imovel.descrição, 
        imovel.id_categoria, 
        imovel.numero_pessoas,
        imovel.imagens,
        categoria.nome_categoria
    FROM imovel
    JOIN Categoria AS categoria ON imovel.id_categoria = categoria.id_categoria
    WHERE imovel.id_proprietario = ?
";
$stmt = $conexao->prepare($sql);
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$imoveis = [];
while ($row = $result->fetch_assoc()) {
    $imoveis[] = $row;
}

if (isset($_GET['delete'])) {
    $id_proprietario = $_SESSION['id'];
    $id_imovel_delete = $_GET['delete'];

    $deletar_locacao = $conexao->prepare("DELETE FROM locação WHERE id_imovel=?;");
    $deletar_locacao->bind_param('i', $id_imovel_delete);
    $deletar_locacao->execute();

    $deletar_checklist = $conexao->prepare("DELETE FROM imovel_checklist WHERE id_imovel=?");
    $deletar_checklist->bind_param("i", $id_imovel_delete);
    $deletar_checklist->execute();

    $sql_delete = "DELETE FROM imovel WHERE id_imovel = ? AND id_proprietario = ?";
    $stmt_delete = mysqli_prepare($conexao, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "ii", $id_imovel_delete, $id_proprietario);
    if (mysqli_stmt_execute($stmt_delete)) {
        echo "<script>alert('Imóvel excluído com sucesso!');</script>";
        echo "<script>window.location.href = 'meus_imoveis.php';</script>"; // Redireciona após a exclusão
    } else {
        echo "<script>alert('Erro ao excluir o imóvel: " . mysqli_error($conexao) . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Imóveis</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="estilo.css"> 
    <style>
    
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #FEF6EE;
            box-sizing: border-box;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #1E2A38;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }
        h1 {
            font-size: 24px;
            font-weight: bold;
            color: black;
            margin: 0;
        }
        .imovel {
            background-color: #2E3B4E;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .imovel p {
            color: white;
            margin: 5px 0;
        }
        .editar-form {
            display: none;
            margin-top: 15px;
        }
        .mensagem-sucesso {
            color: #C56126;
            margin-top: 10px;
        }
        .botao{
            padding: 10px;
            border: none;
            background-color: #C56126;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
        }
        .botao:hover {
            background-color: #ff7043;
        }
        .imovel .editar-form {
            display: none;
            margin-top: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            color: white;
            margin-bottom: 5px;
            display: block;
        }
        input[type="text"], input[type="number"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            margin-bottom: 10px;
        }
        input[type="text"]:focus, input[type="number"]:focus {
            border-color: #0056b3;
            outline: none;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        #footer {
        position: sticky;
        bottom: 0;
        width: 100%;
        height: 2.5rem;            /* altura do rodapé */
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
         
    </style>
    <script>
        function toggleForm(id) {
            const form = document.getElementById(id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
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

        <h1>Meus Imóveis</h1>

        </header>
        

    <!-- Menu lateral (sidebar) -->
    <div class="sidebar" id="sidebar">
        <a href="index.php">Área inicial </a>
        <a href="perfilhtml.php">Perfil</a>
        <a href="imoveis.php">Cadastre seu imóvel</a>
        <a href="quemsomos.php">Quem Somos</a>
        <a href="duvidas.php">Dúvidas</a>
    </div>

    <!-- Overlay para quando o menu estiver aberto -->
    <div class="overlay" id="overlay"></div>

    <p><?php if (isset($final)) {echo $final;} ?></p>

    <?php foreach ($imoveis as $imovel): 
            $p_checklist = $conexao->prepare("SELECT * FROM checklist INNER JOIN imovel_checklist ON checklist.id_checklist=imovel_checklist.id_checklist WHERE imovel_checklist.id_imovel=?");
            $p_checklist->bind_param('i', $imovel['id_imovel']);
            $p_checklist->execute();
            $imovel_checklist = $p_checklist->get_result();
            $caracteristica = [];
            $id_caracteristica = [];
            while ($linha = $imovel_checklist->fetch_assoc()) {
                $caracteristica[] = $linha['nome_checklist'];
                $id_caracteristica[] = $linha['id_checklist'];
            }
            $caracteristicas = implode(', ', $caracteristica);    
    ?>
        <div class="container">
            <div class="imovel">
                <p><strong>ID do Imóvel:</strong> <?php echo htmlspecialchars($imovel['id_imovel']); ?></p>
                <p><strong>Nome do Imóvel:</strong> <?php echo htmlspecialchars($imovel['nome_imovel']); ?></p>
                <p><strong>Rua:</strong> <?php echo htmlspecialchars($imovel['rua']); ?></p>
                <p><strong>Número:</strong> <?php echo htmlspecialchars($imovel['numero']); ?></p>
                <p><strong>Bairro:</strong> <?php echo htmlspecialchars($imovel['bairro']); ?></p>
                <p><strong>Cidade:</strong> <?php echo htmlspecialchars($imovel['cidade']); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars($imovel['uf']); ?></p>
                <p><strong>CEP:</strong> <?php echo htmlspecialchars($imovel['cep']); ?></p>
                <p><strong>Valor:</strong> R$<?php echo htmlspecialchars($imovel['valor']); ?></p>
                <p><strong>Descrição:</strong> <?php echo htmlspecialchars($imovel['descrição']); ?></p>
                <p><strong>Número de Pessoas:</strong> <?php echo htmlspecialchars($imovel['numero_pessoas']); ?></p>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($imovel['nome_categoria']); ?></p>
                <p><strong>Características:</strong> <?php echo $caracteristicas . "."; ?></p>

                <button onclick="toggleForm('editar-<?php echo $imovel['id_imovel']; ?>')" class="botao">Editar Imóvel</button>
                <button onclick="if (confirm('Tem certeza que deseja deletar este imóvel?')) { window.location.href='?delete=<?php echo $imovel['id_imovel']; ?>'; }" class="botao">Deletar Imóvel</button>

                
                <div id="editar-<?php echo $imovel['id_imovel']; ?>" class="editar-form">
                    <form action="meus_imoveis.php" method="post">
                        <input type="hidden" name="id_imovel" value="<?php echo $imovel['id_imovel']; ?>">
                        
                        <div class="form-group">
                        <label>Nome do Imóvel:</label>
                        <input type="text" name="nome_imovel" value="<?php echo htmlspecialchars($imovel['nome_imovel']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label>CEP:</label>
                        <input type="text" name="cep" id="txtCep" value="<?php echo htmlspecialchars($imovel['cep']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label>Rua:</label>
                        <input type="text" name="rua" value="<?php echo htmlspecialchars($imovel['rua']); ?>" required> 
                        </div>
                        <div class="form-group">
                        <label>Número:</label>
                        <input type="text" name="numero" value="<?php echo htmlspecialchars($imovel['numero']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label>Bairro:</label>
                        <input type="text" name="bairro" value="<?php echo htmlspecialchars($imovel['bairro']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label>Cidade:</label>
                        <input type="text" name="cidade" value="<?php echo htmlspecialchars($imovel['cidade']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label>Estado:</label>
                        <input type="text" name="uf" value="<?php echo htmlspecialchars($imovel['uf']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label>Valor:</label>
                        <input type="text" name="valor" value="<?php echo htmlspecialchars($imovel['valor']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label>Descrição:</label>
                        <input type="text" name="descricao" value="<?php echo htmlspecialchars($imovel['descrição']); ?>" required>
                        </div>
                        <div class="form-group">
                        <label>Categoria do Imóvel:</label>

                        <?php foreach ($categorias as $categoria) { 
                            var_dump($categoria['id_categoria']); ?>
                                <div class="form-check">
                                <input type="radio" class="form-check-input" name="id_categoria" value="<?php echo $categoria['id_categoria']; ?>" 
                                    <?php echo ($imovel['id_categoria'] == $categoria['id_categoria']) ? 'checked' : ''; ?> required>
                                <label class="form-check-label"><?php echo $categoria['nome_categoria']; ?></label>
                            </div>
                        <?php } ?>

                        </div>
                        <div class="form-group">
                        <label>Número de Pessoas:</label>
                        <input type="text" name="numero_pessoas" value="<?php echo htmlspecialchars($imovel['numero_pessoas']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Características:</label>

                            <?php 
                                $ids_imovel = array_map('intval', $id_caracteristica);
                                foreach ($checklists as $checklist) { 
                            ?>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="check[]" value="<?php echo $checklist['id_checklist']; ?>"
                                    <?php echo in_array($checklist['id_checklist'], $ids_imovel) ? 'checked' : '';?> >
                                    <label class="form-check-label"><?php echo $checklist['nome_checklist']; ?></label>
                                </div>
                            <?php } ?>    

                            </div>
                        <input type="submit" value="Salvar Alterações" class="btn btn-primary btn-block">
                    </form>
                </div>
            </div>
        </div>
        <br>
        <?php endforeach; ?>
    
        <footer>
        <ul>
        <p class="rights"><span>&copy;&nbsp;<span id="copyright-year"></span> .Todos os direitos reservados. <span> por Byanca Campos Furlan, Igor Miguel Raimundo, Maria Antonia dos Santos e Rithiely Schmitt.</a></span>
        </ul>
    </footer>

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

    <?php $conexao->close(); ?>
     
</body>
</html>