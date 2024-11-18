<?php
include("conexao.php");
include("funçaoAnuncio.php");

$pesquisar = $_POST['pesquisar'];
$resultado = "SELECT * FROM imovel WHERE 
    Nome_imovel LIKE '%$pesquisar%' OR 
    Valor LIKE '%$pesquisar%' OR 
    Cidade LIKE '%$pesquisar%' OR 
    Descrição LIKE '%$pesquisar%' OR
    Bairro LIKE '%$pesquisar%' OR 
    UF LIKE '%$pesquisar%' OR 
    Numero_pessoas LIKE '%$pesquisar%' 
    LIMIT 5";

$resultado_anuncio = mysqli_query($conexao, $resultado);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="estilo.css">

</head>
<body>
    <?php
if (mysqli_num_rows($resultado_anuncio) > 0) {
    while ($rows_anuncio = mysqli_fetch_array($resultado_anuncio)) {

        $imagem = $rows_anuncio['imagens'];
        $titulo = $rows_anuncio['Nome_imovel'] . " - " . $rows_anuncio['Cidade'];
        $avaliacao = "4.5"; // Pode ser um campo da tabela ou um valor padrão
        $tags = $rows_anuncio['Bairro'] . ", " . $rows_anuncio['UF'];

        // Chama a função gerarAnuncio com os dados
        echo gerarAnuncio($imagem, $titulo, $avaliacao, $tags);

    }
} else {
    echo "Nenhum resultado encontrado.";
}
?>
</body>
</html>