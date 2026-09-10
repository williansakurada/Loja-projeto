<?php
include '../conexao.php';
header('Content-Type: application/json');

$sql = "SELECT produto_id, produto_nome, quantidade, preco_unitario, subtotal FROM vw_pedidos_detalhado";
$resultado = mysqli_query($conexao, $sql);
$vendas = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $vendas[] = $linha;
}
echo json_encode($vendas);
?>