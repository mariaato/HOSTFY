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
$sql = "SELECT nome, telefone, email, senha FROM usuario WHERE id = ?";
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

// Processamento da alteração de senha
$senha_alterada = false; // Flag para verificar se a senha foi alterada
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
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        p {
            margin: 10px 0;
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

        button {
            background-color: #5cb85c;
            color: white;
            border: none;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .alterar-senha, .alterar-telefone {
            display: none; /* Inicialmente oculto */
            margin-top: 20px;
        }

        .mensagem-sucesso {
            color: green;
            margin-top: 20px;
        }

        .mensagem-erro {
            color: red;
            margin-top: 20px;
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
    <div class="container">
        <h1>Perfil do Usuário</h1>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
        <p><strong>Telefone:</strong> <?php echo htmlspecialchars($usuario['telefone']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>

        <button onclick="toggleForm('alterar-senha-form')">Alterar Senha</button>
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

                <input type="submit" value="Alterar Senha">
            </form>
            <?php
            if (isset($msg_erro)) {
                echo "<p class='mensagem-erro'>$msg_erro</p>";
            }
            ?>
        </div>

        <button onclick="toggleForm('alterar-telefone-form')">Alterar Telefone</button>
        <div id="alterar-telefone-form" class="alterar-telefone">
            <h2>Alterar Telefone</h2>
            <form action="" method="post">
                <input type="hidden" name="acao" value="alterar_telefone">
                <label for="novo_telefone">Novo Telefone:</label>
                <input type="text" name="novo_telefone" id="novo_telefone" required><br>

                <input type="submit" value="Alterar Telefone">
            </form>
        </div>

        <?php if (isset($telefone_alterado) && $telefone_alterado): ?>
            <p class="mensagem-sucesso">Telefone alterado com sucesso!</p>
        <?php endif; ?>

        <?php if ($senha_alterada): ?>
            <p class="mensagem-sucesso">Senha alterada com sucesso!</p>
        <?php endif; ?>

        <form action="inicio.html">
            <input type="submit" value="Voltar ao Menu Inicial">
        </form>
    </div>
</body>
</html>