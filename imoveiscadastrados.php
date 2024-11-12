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

// Buscar as informações do imovel no banco de dados
$sql = "SELECT cep, nome_imovel, rua, numero, bairro, cidade, uf, valor, descrição, id_categoria, numero_pessoas, id_checklist, imagens FROM imovel WHERE id = ?";
$resultado = mysqli_query($strcon,$sql);

while($registro = mysqli_fetch_array($resultado))
{ 
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
    echo "<tr>";
    echo "<td>".$nome_imovel . "</td>";
    echo "<td>".$endereco . "</td>";
    echo "<td>".$numero . "</td>";
    echo "<td>".$bairro . "</td>";
    echo "<td>".$cidade . "</td>";
    echo "<td>".$estado . "</td>";
    echo "<td>".$valor . "</td>";
    echo "<td>".$descricao . "</td>";
    echo "<td>".$categoria . "</td>";
    echo "<td>".$numero_pessoas . "</td>";
    echo "<td>".$caracteristicas . "</td>";
    echo "</tr>";
    
}


/*$stmt = $conexao->prepare($sql);
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
} else {
    echo "Usuário não encontrado.";
    exit();
}

    // Alterar Endereço, Cidade e Estado
    if (isset($_POST['acao']) && $_POST['acao'] === 'alterar_imovel') {
        $novo_endereco = $_POST['novo_endereco'];
        $nova_cidade = $_POST['nova_cidade'];
        $novo_estado = $_POST['novo_estado'];

        // Atualizar o endereço no banco de dados
        $sql = "UPDATE imovel SET endereco = ?, cidade = ?, estado = ? WHERE id = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param('sssi', $novo_endereco, $nova_cidade, $novo_estado, $_SESSION['id']);
        
        if ($stmt->execute()) {
            $endereco_alterado = true; // Define que o endereço foi alterado
        } else {
            $msg_erro = "Erro ao atualizar o endereço.";
        }
        $stmt->close();
    }

*/
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
            background-color: #FEF6EE;
            height: 100vh;
            display: flex;

        }
        .header {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .container {
            background-color: #1E2A38;
            border-radius: 15px;
            padding: 20px;
            width: 350px;
            border-radius: 8px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: white;
            margin-bottom: 20px;
            font-size: 24px;
        }

        p {
            margin: 10px 0;
            color: white;
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
            background-color:  #C56126;
            color: write;
            border: none;
        }

        button:hover { 
            background-color: #ff7043;
            
        }

        .alterar-senha, .alterar-telefone, .alterar-endereco {
            display: none; /* Inicialmente oculto */
            margin-top: 20px;
        }

        .mensagem-sucesso {
            color: #C56126 ;
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

    <table class="container">
        <h1>Imóveis cadastrados</h1>
        <tr>
            <th> NOME </th>
            <th> ENDEREÇO </th>
            <th> CATEGORIA </th>
        </tr>
            


        <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuario['nome']); ?></p>
    
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

        <button onclick="toggleForm('alterar-endereco-form')">Alterar Endereço</button>
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

                <input type="submit" value="Alterar Endereço">
            </form>
        </div>

        <div class="container">
        <h1>Imóveis Cadastrados</h1>
        <p><strong>Nome do imóvel:</strong> <?php echo htmlspecialchars($imovel['nome_imovel']); ?></p>
        



        <?php
        if ($senha_alterada) {
            echo "<p class='mensagem-sucesso'>Senha alterada com sucesso!</p>";
        }
        if ($telefone_alterado) {
            echo "<p class='mensagem-sucesso'>Telefone alterado com sucesso!</p>";
        }
        if ($endereco_alterado) {
            echo "<p class='mensagem-sucesso'>Endereço alterado com sucesso!</p>";
        }
        ?>
                <form action="index.php">
            <input type="submit" value="Voltar ao Menu Inicial">
        </form>
    </div>
    
</body>
</html>




