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
    $stmt = mysqli_prepare($conexao, "DELETE FROM produtos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        http_response_code(404);
        echo json_encode(['erro' => 'Produto não encontrado.']);
    } else {
        echo json_encode(['sucesso' => true]);
    }
    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1451) {
        http_response_code(409);
        echo json_encode(['erro' => 'Esse produto já tem pedidos feitos e não pode ser excluído.']);
    } else {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao excluir o produto.']);
    }
}
?>