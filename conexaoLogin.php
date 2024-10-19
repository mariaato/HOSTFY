<?php
// Configuração de conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$database = "hostfy";

// Criar a conexão
$mysqli = new mysqli($servername, $username, $password, $database);

// Verifique se a conexão foi bem-sucedida
if ($mysqli->connect_error) {
    die("Falha na conexão: " . $mysqli->connect_error);
}

?>