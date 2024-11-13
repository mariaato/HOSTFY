<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include("conexao.php");

// Verifica se o formulário foi enviado para atualizar o imóvel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_imovel'])) {
    // Prepara os dados para atualizar no banco
    $id_imovel = $_POST['id_imovel'];
    $nome_imovel = $_POST['nome_imovel'];
    $cep = $_POST['cep'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $uf = $_POST['uf'];
    $valor = $_POST['valor'];
    $descricao = $_POST['descrição'];
    $id_categoria = $_POST['id_categoria'];
    $numero_pessoas = $_POST['numero_pessoas'];
    $id_checklist = $_POST['id_checklist'];

    // Atualiza os dados no banco de dados
    $sql = "UPDATE imovel SET nome_imovel = ?, cep = ?, rua = ?, numero = ?, bairro = ?, cidade = ?, uf = ?, valor = ?, descrição = ?, id_categoria = ?, numero_pessoas = ?, id_checklist = ? WHERE id_imovel = ? AND id_proprietario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param('ssssssssssssi', $nome_imovel, $cep, $rua, $numero, $bairro, $cidade, $uf, $valor, $descricao, $id_categoria, $numero_pessoas, $id_checklist, $id_imovel, $_SESSION['id']);
    if ($stmt->execute()) {
        $mensagem_sucesso = "Imóvel atualizado com sucesso!";
    } else {
        $mensagem_erro = "Erro ao atualizar o imóvel!";
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
        imovel.descricao, 
        imovel.id_categoria, 
        imovel.numero_pessoas, 
        imovel.id_checklist, 
        categoria.nome_categoria
    FROM imovel
    JOIN Categoria AS categoria ON imovel.id_categoria = categoria.id_categoria
    JOIN Checklist AS checklist ON imovel.id_checklist = checklist.id_checklist
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

$conexao->close();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Imóveis</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #FEF6EE;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
            color: white;
            font-size: 24px;
            margin-bottom: 20px;
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
        button {
            width: 100%;
            padding: 10px;
            border: none;
            background-color: #C56126;
            color: white;
            cursor: pointer;
            border-radius: 4px;
            margin-top: 10px;
        }
        button:hover {
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
            color: #333;
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

    </style>
    <script>
        // Função para preencher automaticamente os campos ao digitar o CEP
        function preencherEndereco() {
            const cep = document.getElementById('txtCep').value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('rua').value = data.logradouro;
                            document.getElementById('bairro').value = data.bairro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('uf').value = data.uf;
                        } else {
                            alert('CEP não encontrado!');
                        }
                    });
            }
        }

        function toggleForm(id) {
            const form = document.getElementById(id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Meus Imóveis</h1>

        <?php if (isset($mensagem_sucesso)): ?>
            <div class="mensagem-sucesso"><?php echo $mensagem_sucesso; ?></div>
        <?php elseif (isset($mensagem_erro)): ?>
            <div class="mensagem-sucesso"><?php echo $mensagem_erro; ?></div>
        <?php endif; ?>
        
        <?php foreach ($imoveis as $imovel): ?>
            <div class="imovel">
                <p><strong>ID do Imóvel:</strong> <?php echo htmlspecialchars($imovel['id_imovel']); ?></p>
                <p><strong>Nome do Imóvel:</strong> <?php echo htmlspecialchars($imovel['nome_imovel']); ?></p>
                <p><strong>Rua:</strong> <?php echo htmlspecialchars($imovel['rua']); ?></p>
                <p><strong>Número:</strong> <?php echo htmlspecialchars($imovel['numero']); ?></p>
                <p><strong>Bairro:</strong> <?php echo htmlspecialchars($imovel['bairro']); ?></p>
                <p><strong>Cidade:</strong> <?php echo htmlspecialchars($imovel['cidade']); ?></p>
                <p><strong>Estado:</strong> <?php echo htmlspecialchars($imovel['uf']); ?></p>
                <p><strong>CEP:</strong> <?php echo htmlspecialchars($imovel['cep']); ?></p>
                <p><strong>Valor:</strong> R$ <?php echo number_format($imovel['valor'], 2, ',', '.'); ?></p>
                <p><strong>Descrição:</strong> <?php echo htmlspecialchars($imovel['descricao']); ?></p>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($imovel['nome_categoria']); ?></p>
                <button onclick="toggleForm('editar-form-<?php echo $imovel['id_imovel']; ?>')">Editar</button>

                <div id="editar-form-<?php echo $imovel['id_imovel']; ?>" class="editar-form">
                    <form method="POST" action="meus_imoveis.php">
                        <input type="hidden" name="id_imovel" value="<?php echo $imovel['id_imovel']; ?>">
                        <div class="form-group">
                            <label for="nome_imovel">Nome do Imóvel</label>
                            <input type="text" name="nome_imovel" value="<?php echo htmlspecialchars($imovel['nome_imovel']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="cep">CEP</label>
                            <input type="text" name="cep" id="txtCep" value="<?php echo htmlspecialchars($imovel['cep']); ?>" onblur="preencherEndereco()" required>
                        </div>
                        <div class="form-group">
                            <label for="rua">Rua</label>
                            <input type="text" name="rua" id="rua" value="<?php echo htmlspecialchars($imovel['rua']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="bairro">Bairro</label>
                            <input type="text" name="bairro" id="bairro" value="<?php echo htmlspecialchars($imovel['bairro']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" name="cidade" id="cidade" value="<?php echo htmlspecialchars($imovel['cidade']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="uf">Estado</label>
                            <input type="text" name="uf" id="uf" value="<?php echo htmlspecialchars($imovel['uf']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="valor">Valor</label>
                            <input type="number" name="valor" value="<?php echo $imovel['valor']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <input type="text" name="descricao" value="<?php echo htmlspecialchars($imovel['descrição']); ?>" required>
                        </div>
                        <div class="form-group">
                            <input type="submit" value="Salvar Alterações">
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
