<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "hostfy";




$conexao = mysqli_connect($servername, $username, $password, $database);




if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());


}
?>
