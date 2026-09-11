<?php
include 'conexao.php';
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['cliente_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Você precisa estar logado para finalizar a compra.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$itens = $dados['itens'] ?? [];
$endereco = trim($dados['endereco'] ?? '');
$forma_pagamento = trim($dados['forma_pagamento'] ?? '');

if (empty($itens)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Carrinho vazio.']);
    exit;
}

if ($endereco === '' || $forma_pagamento === '') {
    http_response_code(400);
    echo json_encode(['erro' => 'Preencha o endereço e a forma de pagamento.']);
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

try {
    mysqli_begin_transaction($conexao);

    $stmt = mysqli_prepare($conexao, "INSERT INTO pedidos (cliente_id, status, endereco, forma_pagamento) VALUES (?, 'pendente', ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $cliente_id, $endereco, $forma_pagamento);
    mysqli_stmt_execute($stmt);
    $pedido_id = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmt);

    $stmtItem = mysqli_prepare($conexao, "INSERT INTO itens_pedido (pedido_id, produto_id, tamanho, quantidade, preco_unitario) VALUES (?, ?, ?, ?, ?)");

    foreach ($itens as $item) {
        $produto_id = (int) $item['produto_id'];
        $tamanho = (string) $item['tamanho'];
        $quantidade = 1;
        $preco = (float) $item['preco'];
        mysqli_stmt_bind_param($stmtItem, "iisid", $pedido_id, $produto_id, $tamanho, $quantidade, $preco);
        mysqli_stmt_execute($stmtItem);
    }
    mysqli_stmt_close($stmtItem);

    mysqli_commit($conexao);
    echo json_encode(['sucesso' => true, 'pedido_id' => $pedido_id]);
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conexao);
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao gravar o pedido no banco.']);
}
?>