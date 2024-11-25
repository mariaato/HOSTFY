<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    // Redireciona para a página de login se não estiver logado
    header("Location: login.php");
    exit();
}

// Conectar ao banco de dados
include("conexao.php");

// Buscar as informações do usuário no banco de dados
$sql = "SELECT nome, telefone, email, endereco, cidade, estado, senha FROM usuario WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
} else {
    echo "Usuário não encontrado.";
    exit();
}

// Processamento da alteração de dados
$senha_alterada = false; // Flag para verificar se a senha foi alterada
$telefone_alterado = false; // Flag para verificar se o telefone foi alterado
$endereco_alterado = false; // Flag para verificar se o endereço foi alterado

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Alterar Senha
    if (isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];

        // Verificar se a senha atual está correta
        if (password_verify($senha_atual, $usuario['senha'])) {
            if ($nova_senha === $confirmar_senha && strlen($nova_senha) >= 8) {
                // Atualizar a senha no banco de dados com hash seguro
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuario SET senha = ? WHERE id = ?";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param('si', $nova_senha_hash, $_SESSION['id']);
                
                if ($stmt->execute()) {
                    $senha_alterada = true; // Define que a senha foi alterada
                } else {
                    $msg_erro = "Erro ao atualizar a senha.";
                }
                $stmt->close();
            } else {
                $msg_erro = "As senhas não coincidem ou a nova senha é muito curta.";
            }
        } else {
            $msg_erro = "Senha atual incorreta.";
        }
    }

    // Alterar Telefone
    if (isset($_POST['acao']) && $_POST['acao'] === 'alterar_telefone') {
        $novo_telefone = $_POST['novo_telefone'];

        // Atualizar o telefone no banco de dados
        $sql = "UPDATE usuario SET telefone = ? WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param('si', $novo_telefone, $_SESSION['id']);
        
        if ($stmt->execute()) {
            $telefone_alterado = true; // Define que o telefone foi alterado
        } else {
            $msg_erro = "Erro ao atualizar o telefone.";
        }
        $stmt->close();
    }

    // Alterar Endereço, Cidade e Estado
    if (isset($_POST['acao']) && $_POST['acao'] === 'alterar_endereco') {
        $novo_endereco = $_POST['novo_endereco'];
        $nova_cidade = $_POST['nova_cidade'];
        $novo_estado = $_POST['novo_estado'];

        // Atualizar o endereço no banco de dados
        $sql = "UPDATE usuario SET endereco = ?, cidade = ?, estado = ? WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param('sssi', $novo_endereco, $nova_cidade, $novo_estado, $_SESSION['id']);
        
        if ($stmt->execute()) {
            $endereco_alterado = true; // Define que o endereço foi alterado
        } else {
            $msg_erro = "Erro ao atualizar o endereço.";
        }
        $stmt->close();
    }
}

// Fechar a conexão
$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="estilo.css">
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <style>

#main-content {
    justify-content: center;
    align-items: center;
    background-color: #FEF6EE;

}
            header {
                display: flex;
                justify-content: center; /* Distribui os itens entre os extremos */
                align-items: center;
                text-align: center;
                padding: 0px 500px;
                background-color: #FEF6EE; 
            }

            header img {
                margin-right: 0px;
            }

            header h1 {
                margin: 0;
                font-size: 1.5em;
            }

            header a {
                margin-left: auto;
                text-decoration: none;
                color: #000; /* Ajuste de cor */
                padding: 5px 10px;
            }

            .container {
                width: 100%;
                max-width: 800px; /* Largura máxima para o conteúdo */
                margin: 0 auto; /* Centraliza horizontalmente */
                padding: 20px;
                text-align: center;
            }

        
        h1 {
            font-size: 24px;
            font-weight: bold;
            color: #black;
            margin: 50px;
            padding: 5px;
        }

        p {
            margin: 10px 0;
            color: white;
        }

        .profile-data {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Adaptável */
            gap: 20px; /* Espaço entre os cards */
            margin-top: 20px;
        }

        .card {
            padding: 20px;
            background-color: #1a1e36;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card strong {
            color: #D97C41; /* Destaque para os rótulos */
            display: block;
            margin-bottom: 8px;
        }
        input[type="password"],
        input[type="text"],
        input[type="submit"],
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            
        }

       
        .alterar-senha, .alterar-telefone, .alterar-endereco {
            display: none; /* Inicialmente oculto */
            margin-top: 20px;
            width: 100%;
            padding: 30px;
            text-align: center;

            
        }

        .mensagem-sucesso {
            color: #C56126 ;
            margin-top: 20px;
        }

        .mensagem-erro {
            color: red;
            margin-top: 20px;
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
            <button class="menu-icon" id="menu-toggle">
                <i class='bx bx-menu'></i>
            </button>
            <a href="index.php">
            <img src="logoHostfy.png" alt="logo" class="logo" />
        </a>
            <h1>Seu </h1>
            <h1> Perfil</h1>
            <a href="logout.php" class="menu__link">Sair</a>

    </header>

    <div class="sidebar" id="sidebar">
        <a href="index.php">Área inicial </a>
        <a href="imoveis.php" >Cadastre seu imóvel</a>
        <a href="meus_imoveis.php">Imóveis Cadastrados</a>
        <a href="quemsomos.php">Quem Somos</a>
        <a href="duvidas.php">Dúvidas</a>

        
    </div>

    <div class="overlay" id="overlay"></div>
    <div class="main-content" id="main-content">

<div class="container">
<div class="profile-data">
        <div class="card">
            <strong>Nome</strong>
            <p><?php echo htmlspecialchars($usuario['nome']); ?></p>
        </div>
        <div class="card">
            <strong>Telefone</strong>
            <p><?php echo htmlspecialchars($usuario['telefone']); ?></p>
        </div>
        <div class="card">
            <strong>Email</strong>
            <p><?php echo htmlspecialchars($usuario['email']); ?></p>
        </div>
        <div class="card">
            <strong>Endereço</strong>
            <p><?php echo htmlspecialchars($usuario['endereco']); ?></p>
        </div>
        <div class="card">
            <strong>Cidade</strong>
            <p><?php echo htmlspecialchars($usuario['cidade']); ?></p>
        </div>
        <div class="card">
            <strong>Estado</strong>
            <p><?php echo htmlspecialchars($usuario['estado']); ?></p>
        </div>
    </div>  
        <button onclick="toggleForm('alterar-senha-form')" class="submit-btn">Alterar Senha</button>
        <div id="alterar-senha-form" class="alterar-senha">
            <h2>Alterar Senha</h2>
            <form action="" method="post">
                <input type="hidden" name="acao" value="alterar_senha">
                <label for="senha_atual">Senha Atual:</label>
                <input type="password" name="senha_atual" id="senha_atual" required><br>

                <label for="nova_senha">Nova Senha:</label>
                <input type="password" name="nova_senha" id="nova_senha" required><br>

                <label for="confirmar_senha">Confirmar Nova Senha:</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" required><br>

                <input type="submit" value="Alterar Senha" class="submit-btn">
            </form>
            <?php
            if (isset($msg_erro)) {
                echo "<p class='mensagem-erro'>$msg_erro</p>";
            }
            ?>
    
     </div>
        <button onclick="toggleForm('alterar-telefone-form')" class="submit-btn">Alterar Telefone</button>
        <div id="alterar-telefone-form" class="alterar-telefone">
            <h2>Alterar Telefone</h2>
            <form action="" method="post">
                <input type="hidden" name="acao" value="alterar_telefone">
                <label for="novo_telefone">Novo Telefone:</label>
                <input type="text" name="novo_telefone" id="novo_telefone" required><br>

                <input type="submit" value="Alterar Telefone" class="submit-btn">
            </form>
        </div>

        <button onclick="toggleForm('alterar-endereco-form')" class="submit-btn">Alterar Endereço</button>
        <div id="alterar-endereco-form" class="alterar-endereco">
            <h2>Alterar Endereço</h2>
            <form action="" method="post">
                <input type="hidden" name="acao" value="alterar_endereco">
                <label for="novo_endereco">Novo Endereço:</label>
                <input type="text" name="novo_endereco" id="novo_endereco" required><br>

                <label for="nova_cidade">Nova Cidade:</label>
                <input type="text" name="nova_cidade" id="nova_cidade" required><br>

                <label for="novo_estado">Novo Estado:</label>
                <input type="text" name="novo_estado" id="novo_estado" required><br>

                <input type="submit" value="Alterar Endereço" class="submit-btn">
            </form>
        </div>

            <?php
        if ($senha_alterada) {
            echo "<p class='mensagem-sucesso'>Senha alterada com sucesso!</p>";
            echo "<script>window.location.href = 'perfilhtml.php';</script>"; // Redireciona após a exclusão

        }
        if ($telefone_alterado) {
            echo "<p class='mensagem-sucesso'>Telefone alterado com sucesso!</p>";
            echo "<script>window.location.href = 'perfilhtml.php';</script>"; // Redireciona após a exclusão

        }
        if ($endereco_alterado) {
            echo "<p class='mensagem-sucesso'>Endereço alterado com sucesso!</p>";
            echo "<script>window.location.href = 'perfilhtml.php';</script>"; // Redireciona após a exclusão

        }
        ?>
                
    <button onclick="window.location.href='meus_imoveis.php'" class="submit-btn">Meus Imóveis</button>
    </div>

    <footer>
    <ul>
        <p class="rights"><span>&copy;&nbsp;<span id="copyright-year"></span> .Todos os direitos reservados. <span> por Byanca Campos Furlan, Igor Miguel Raimundo, Maria Antonia dos Santos e Rithiely Schmitt.</a></span>
    </ul>
</footer>
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

        function toggleForm(id) {
            const form = document.getElementById(id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
         // Define o ano atual
    document.getElementById('copyright-year').textContent = new Date().getFullYear();

    </script>
</body>


</div>
</html>




