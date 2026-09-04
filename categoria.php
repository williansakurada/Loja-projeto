<?php
header('Content-Type: application/json; charset=utf-8');

include 'conexao.php';

$sql = "SELECT * FROM categorias";
$resultado = mysqli_query($conexao, $sql);

$categorias = [];

if ($resultado) {
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $categorias[] = $linha;
    }
    echo json_encode(['categorias' => $categorias]);
} else {
    http_response_code(500);
    echo json_encode(['erro' => mysqli_error($conexao)]);
}