
<?php
include("conexao.php");

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

if (mysqli_num_rows($resultado_anuncio) > 0) {
    while ($rows_anuncio = mysqli_fetch_array($resultado_anuncio)) {
        echo "Valor: " . $rows_anuncio['Valor'] . "<br>";
        echo "Cidade: " . $rows_anuncio['Cidade'] . "<br>";
        echo "<br>";
    }
} else {
    echo "Nenhum resultado encontrado.";
}
?>
