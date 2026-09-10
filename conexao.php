<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "loja_virtual";

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro na conexão: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, "utf8mb4");
?>