<?php
include '../conexao.php';
header('Content-Type: application/json');

$sql = "SELECT id, categoria_id, categoria_nome, nome, slug, preco, preco_orig, estoque, img, status, percentual_desconto FROM vw_produtos_catalogo ORDER BY nome";
$resultado = mysqli_query($conexao, $sql);
$produtos = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $produtos[] = $linha;
}
echo json_encode($produtos);
?>