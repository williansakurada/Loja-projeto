<?php
$host = "192.168.56.101";
$usuario = "root";
$senha = "";
$banco = "loja_virtual";

$conn = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>