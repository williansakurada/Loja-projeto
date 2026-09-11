<?php
include 'conexao.php';
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dados = json_decode(file_get_contents('php://input'), true);
$id = (int) ($dados['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'Produto inválido.']);
    exit;
}

try {
    $stmt = mysqli_prepare($conexao, "UPDATE produtos SET estoque = estoque + 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt2 = mysqli_prepare($conexao, "SELECT estoque FROM produtos WHERE id = ?");
    mysqli_stmt_bind_param($stmt2, "i", $id);
    mysqli_stmt_execute($stmt2);
    $resultado = mysqli_stmt_get_result($stmt2);
    $produto = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt2);

    if (!$produto) {
        http_response_code(404);
        echo json_encode(['erro' => 'Produto não encontrado.']);
        exit;
    }

    echo json_encode(['sucesso' => true, 'estoque' => $produto['estoque']]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao atualizar o estoque.']);
}
?>