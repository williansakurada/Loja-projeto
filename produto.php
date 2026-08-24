<?php
include 'conexao.php';

$slug = $_GET['id'] ?? '';

$p = null;
if ($slug !== '') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM vw_produtos_catalogo WHERE slug = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $slug);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $p = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
}

$tituloPagina = $p ? strtoupper($p['nome']) . " - Gungnir Store" : "Gungnir Store";
include 'templates/header.php';

if (!$p) {
    echo '<script>window.location.href="index.php";</script>';
    include 'templates/footer.php';
    exit;
}
?>

<div class="produto-page">
    <div class="produto-imagem">
        <img src="<?= $p['img'] ?>" alt="<?= $p['nome'] ?>">
    </div>
    <div class="produto-detalhes">
        <h1 class="produto-titulo"><?= strtoupper($p['nome']) ?></h1>
        <div class="produto-preco">
            R$ <?= number_format($p['preco'], 2, ',', '.') ?>
            <?php if ($p['preco_orig']): ?>
                <span class="preco-orig">R$ <?= number_format($p['preco_orig'], 2, ',', '.') ?></span>
            <?php endif; ?>
        </div>

        <div class="tamanhos">
            <p class="tamanho-label">TAMANHO</p>
            <div class="tamanho-botoes">
                <?php foreach (['P', 'M', 'G', 'GG', 'XG'] as $t): ?>
                    <button class="btn-tamanho" onclick="selecionarTamanho(this)"><?= $t ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="msg-erro" class="msg-erro d-none"></div>

        <button class="btn-carrinho" onclick="adicionarCarrinho()">
            <i class="bi bi-bag"></i> ADICIONAR AO CARRINHO
        </button>
    </div>
</div>

<style>
.produto-page {
    display: flex;
    gap: 60px;
    padding: 40px;
    min-height: calc(100vh - 120px);
    align-items: flex-start;
}
.produto-imagem {
    flex: 1;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}
.produto-imagem img {
    width: 100%;
    max-height: 600px;
    object-fit: contain;
}
.produto-detalhes {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding-top: 20px;
}
.produto-titulo {
    font-size: 2rem;
    font-weight: 900;
    letter-spacing: 3px;
    color: #fff;
    line-height: 1.2;
    text-transform: uppercase;
}
.produto-preco {
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
}
.preco-orig {
    text-decoration: line-through;
    color: #666;
    margin-left: 12px;
    font-size: 1rem;
}
.tamanho-label {
    font-size: 0.75rem;
    letter-spacing: 2px;
    color: #aaa;
    margin-bottom: 12px;
}
.tamanho-botoes { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-tamanho {
    width: 50px;
    height: 50px;
    background: transparent;
    border: 1px solid #555;
    color: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-tamanho:hover { border-color: #fff; }
.btn-tamanho.selecionado {
    background: #fff;
    color: #000;
    border-color: #fff;
}
.btn-carrinho {
    background: #fff;
    color: #000;
    border: none;
    padding: 18px;
    font-size: 0.9rem;
    letter-spacing: 3px;
    font-weight: 900;
    cursor: pointer;
    width: 100%;
    transition: opacity 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.btn-carrinho:hover { opacity: 0.85; }
.msg-erro {
    padding: 12px;
    font-size: 0.8rem;
    letter-spacing: 1px;
    text-align: center;
}
.msg-erro.erro { background: #8b1a1a; color: #fff; }
.msg-erro.sucesso { background: #1a4d1a; color: #fff; }
</style>

<script>
var tamanhoSelecionado = null;

function selecionarTamanho(btn) {
    document.querySelectorAll('.btn-tamanho').forEach(function(b) {
        b.classList.remove('selecionado');
    });
    btn.classList.add('selecionado');
    tamanhoSelecionado = btn.textContent;
    document.getElementById('msg-erro').classList.add('d-none');
}

function adicionarCarrinho() {
    if (!tamanhoSelecionado) {
        var msg = document.getElementById('msg-erro');
        msg.textContent = "Selecione um tamanho antes de adicionar!";
        msg.className = "msg-erro erro";
        msg.classList.remove('d-none');
        return;
    }

    var carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    var item = {
        nome: "<?= addslashes($p['nome']) ?>",
        preco: <?= $p['preco'] ?>,
        img: "<?= $p['img'] ?>",
        tamanho: tamanhoSelecionado
    };

    carrinho.push(item);
    localStorage.setItem('carrinho', JSON.stringify(carrinho));

    var msg = document.getElementById('msg-erro');
    msg.textContent = "Adicionado ao carrinho!";
    msg.className = "msg-erro sucesso";
    msg.classList.remove('d-none');

    setTimeout(function() {
        window.location.href = 'index.php';
    }, 1000);
}
</script>

<?php include 'templates/footer.php'; ?>