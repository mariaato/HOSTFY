<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include("conexao.php");

// checa se algum imóvel foi alterado e atualiza o banco
if (isset($_POST['id_imovel'])) {
    $ps = $conexao->prepare("UPDATE imovel SET nome_imovel=?, numero=?, rua=?, bairro=?, uf=?, cidade=?, cep=?, valor=?, descrição=?, id_categoria=?, numero_pessoas=?, id_checklist=?  WHERE id_imovel = ?");
    $ps->bind_param("sisssssdsiiii", $_POST['nome_imovel'],  $_POST['numero'], $_POST['rua'], $_POST['bairro'], $_POST['uf'], $_POST['cidade'], $_POST['cep'], $_POST['valor'], $_POST['descricao'], $_POST['id_categoria'], $_POST['numero_pessoas'], $_POST['id_checklist'], $_POST['id_imovel']);
    $ps->execute();
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
        function toggleForm(id) {
            const form = document.getElementById(id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Meus Imóveis</h1>
        
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
                <p><strong>Valor:</strong> R$<?php echo htmlspecialchars($imovel['valor']); ?></p>
                <p><strong>Descrição:</strong> <?php echo htmlspecialchars($imovel['descrição']); ?></p>
                <p><strong>Número de Pessoas:</strong> <?php echo htmlspecialchars($imovel['numero_pessoas']); ?></p>
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($imovel['nome_categoria']); ?></p>
                <p><strong>Características:</strong> <?php echo htmlspecialchars($imovel['id_checklist']); ?></p>

                <button onclick="toggleForm('editar-<?php echo $imovel['id_imovel']; ?>')">Editar Imóvel</button>
                
                <div id="editar-<?php echo $imovel['id_imovel']; ?>" class="editar-form">
                    <form action="meus_imoveis.php" method="post">
                        <input type="hidden" name="id_imovel" value="39">
                        
                        <div class="form-group">
                        <label>Nome do Imóvel:</label>
                        <input type="text" name="nome_imovel" value="<?php echo htmlspecialchars($imovel['nome_imovel']); ?>" required>

                        <div class="form-group">
                        <label>CEP:</label>
                        <input type="text" name="cep" id="txtCep" value="<?php echo htmlspecialchars($imovel['cep']); ?>" required>

                        <div class="form-group">
                        <label>Rua:</label>
                        <input type="text" name="rua" value="<?php echo htmlspecialchars($imovel['rua']); ?>" required> 

                        <div class="form-group">
                        <label>Número:</label>
                        <input type="text" name="numero" value="<?php echo htmlspecialchars($imovel['numero']); ?>" required>

                        <div class="form-group">
                        <label>Bairro:</label>
                        <input type="text" name="bairro" value="<?php echo htmlspecialchars($imovel['bairro']); ?>" required>

                        <div class="form-group">
                        <label>Cidade:</label>
                        <input type="text" name="cidade" value="<?php echo htmlspecialchars($imovel['cidade']); ?>" required>

                        <div class="form-group">
                        <label>Estado:</label>
                        <input type="text" name="uf" value="<?php echo htmlspecialchars($imovel['uf']); ?>" required>

                        <div class="form-group">
                        <label>Valor:</label>
                        <input type="text" name="valor" value="<?php echo htmlspecialchars($imovel['valor']); ?>" required>

                        <div class="form-group">
                        <label>Descrição:</label>
                        <input type="text" name="descricao" value="<?php echo htmlspecialchars($imovel['descrição']); ?>" required>

                        <div class="form-group">
                        <label>Categoria:</label>
                        <input type="text" name="id_categoria" value="<?php echo htmlspecialchars($imovel['id_categoria']); ?>" required>

                        <div class="form-group">
                        <label>Número de Pessoas:</label>
                        <input type="text" name="numero_pessoas" value="<?php echo htmlspecialchars($imovel['numero_pessoas']); ?>" required>

                        <div class="form-group">
                        <label>Características:</label>
                        <input type="text" name="id_checklist" value="<?php echo htmlspecialchars($imovel['id_checklist']); ?>" required>

                        <input type="submit" value="Salvar Alterações">
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
</body>
</html>