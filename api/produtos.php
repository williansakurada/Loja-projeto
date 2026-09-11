<?php
include '../conexao.php';
header('Content-Type: application/json');

$sql = "SELECT id, categoria_id, categoria_nome, nome, slug, preco, preco_orig, estoque, img, status, percentual_desconto FROM vw_produtos_catalogo ORDER BY nome";
$resultado = mysqli_query($conexao, $sql);
$produtos = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $linha['id'] = (int) $linha['id'];
    $linha['categoria_id'] = $linha['categoria_id'] !== null ? (int) $linha['categoria_id'] : null;
    $linha['preco'] = (float) $linha['preco'];
    $linha['preco_orig'] = $linha['preco_orig'] !== null ? (float) $linha['preco_orig'] : null;
    $linha['estoque'] = (int) $linha['estoque'];
    $linha['percentual_desconto'] = $linha['percentual_desconto'] !== null ? (float) $linha['percentual_desconto'] : null;
    $produtos[] = $linha;
}
echo json_encode($produtos);
?>