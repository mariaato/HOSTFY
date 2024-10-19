<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "cadastro";


$strcon = mysqli_connect($servername, $username, $password, $database);


if (!$strcon) {
    die("Falha na conexão: " . mysqli_connect_error());

}
echo "Sucesso na conexao";
?>