<?php
$tituloPagina = "Gungnir Store";
include 'conexao.php';
include 'templates/header.php';

$sql = "SELECT * FROM vw_produtos_catalogo ORDER BY ordem_exibicao, nome";
$resultado = mysqli_query($conn, $sql);

$produtos_ordenados = [];
if ($resultado) {
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $produtos_ordenados[] = $linha;
    }
}
?>

<div class="row g-3 p-3" id="grid-produtos">
<?php foreach ($produtos_ordenados as $p):
    $sold_out = $p['status'] === 'esgotado';
    $is_sale = $p['status'] === 'promocao';
?>
    <div class="col-4">
        <div class="produto-card <?= $sold_out ? 'sold-out' : '' ?>" onclick="<?= !$sold_out ? 'window.location=\'produto.php?id='.$p['slug'].'\'' : '' ?>">
            <div class="produto-img">
                <?php if ($sold_out): ?>
                    <span class="tag-soldout">SOLD OUT</span>
                <?php endif; ?>
                <?php if ($is_sale): ?>
                    <span class="tag-sale">SALE</span>
                <?php endif; ?>
                <?php if (!empty($p['img'])): ?>
                    <img src="<?= $p['img'] ?>" alt="<?= $p['nome'] ?>">
                <?php else: ?>
                    <div class="img-placeholder"></div>
                <?php endif; ?>
            </div>
            <div class="produto-info">
                <p class="produto-nome"><?= $p['nome'] ?></p>
                <p class="produto-preco">
                    R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                    <?php if ($is_sale): ?>
                        <span class="preco-orig">R$ <?= number_format($p['preco_orig'], 2, ',', '.') ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<style>
.produto-card {
    position: relative;
    cursor: pointer;
    transition: opacity 0.2s;
    background: transparent;
}
.produto-card:hover { opacity: 0.85; }
.produto-img { position: relative; }
.produto-img img {
    width: 100%;
    height: auto;
    max-height: 400px;
    object-fit: contain;
    display: block;
    background-color: #fff;
}
.img-placeholder {
    width: 100%;
    height: 350px;
    background-color: #e0e0e0;
}
.sold-out .produto-img img,
.sold-out .img-placeholder { opacity: 0.4; }
.produto-info {
    padding: 10px 12px 14px;
    background: transparent;
}
.produto-nome {
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #fff;
    margin-bottom: 4px;
}
.produto-preco {
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
}
.preco-orig {
    text-decoration: line-through;
    color: #999;
    margin-left: 8px;
    font-size: 0.8rem;
}
.tag-soldout {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(0,0,0,0.7);
    color: #ccc;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 2px;
    padding: 5px 10px;
    z-index: 2;
}
.tag-sale {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #8b1a1a;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 2px;
    padding: 5px 10px;
    z-index: 2;
}
</style>

<?php include 'templates/footer.php'; ?>