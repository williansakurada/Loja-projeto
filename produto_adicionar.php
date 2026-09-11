<?php
include 'conexao.php';
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function gerarSlug($texto) {
    $texto = mb_strtolower($texto, 'UTF-8');
    $mapa = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o','ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c'];
    $texto = strtr($texto, $mapa);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim($texto, '-');
}

$dados = json_decode(file_get_contents('php://input'), true);

$nome = trim($dados['nome'] ?? '');
$categoria_id = (int) ($dados['categoria_id'] ?? 0);
$preco = (float) ($dados['preco'] ?? 0);
$preco_orig = isset($dados['preco_orig']) && $dados['preco_orig'] !== '' ? (float) $dados['preco_orig'] : null;
$estoque = (int) ($dados['estoque'] ?? 0);
$img = trim($dados['img'] ?? '');

if ($nome === '' || $categoria_id <= 0 || $preco <= 0 || $img === '') {
    http_response_code(400);
    echo json_encode(['erro' => 'Preencha nome, categoria, preço e imagem.']);
    exit;
}

$slug_base = gerarSlug($nome);
$slug = $slug_base;
$sufixo = 2;

while (true) {
    $stmt = mysqli_prepare($conexao, "SELECT id FROM produtos WHERE slug = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $slug);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $existe = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if (!$existe) break;
    $slug = $slug_base . '-' . $sufixo;
    $sufixo++;
}

try {
    $catStmt = mysqli_prepare($conexao, "SELECT nome FROM categorias WHERE id = ?");
    mysqli_stmt_bind_param($catStmt, "i", $categoria_id);
    mysqli_stmt_execute($catStmt);
    $catResultado = mysqli_stmt_get_result($catStmt);
    $categoria = mysqli_fetch_assoc($catResultado);
    $categoria_nome = $categoria['nome'] ?? null;
    mysqli_stmt_close($catStmt);

    $stmt = mysqli_prepare($conexao, "INSERT INTO produtos (categoria_id, nome, slug, preco, preco_orig, estoque, img) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issddis", $categoria_id, $nome, $slug, $preco, $preco_orig, $estoque, $img);
    mysqli_stmt_execute($stmt);
    $novo_id = mysqli_insert_id($conexao);
    mysqli_stmt_close($stmt);

    echo json_encode([
        'sucesso' => true,
        'produto' => [
            'id' => $novo_id,
            'nome' => $nome,
            'categoria_nome' => $categoria_nome,
            'preco' => $preco,
            'estoque' => $estoque
        ]
    ]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao adicionar o produto.']);
}
?>