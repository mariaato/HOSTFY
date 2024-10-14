<?php
$servidor = "localhost";
$usuario = "root";
$senha ="";
$dbname = "hostfy";

$conexao = mysqli_connect($servidor, $usuario, $senha, $dbname);


$pesquisar = $_POST['pesquisar'];
$resultado = "SELECT * FROM anuncios WHERE proprietario LIKE '%$pesquisar%' LIMIT 5";
$resultado_anuncio=mysqli_query($conexao, $resultado);

while($rows_anuncio=mysqli_fetch_array($resultado_anuncio)){
    echo "Proprietario: ".$rows_anuncio['proprietario']."<br>";
    echo "Valor: ".$rows_anuncio['valor']."<br>";
    echo "Cidade: ".$rows_anuncio['cidade']."<br>";
    echo "<br>";
}
?>