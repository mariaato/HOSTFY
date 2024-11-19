<?php

if(!isset($_SESSION)) {
    session_start();
}

$cookie_nome = $_SESSION['nome'];
$cookie_id = $_SESSION['id'];
setcookie('usuario', $cookie_nome, time() - 1800, '/');
setcookie('id', $cookie_id, time() - 1800, '/');

session_destroy();

header("Location: index.php");
