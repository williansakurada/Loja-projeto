<?php
include 'conexao.php';
$tituloPagina = "Dashboard - Gungnir Store";
include 'templates/header.php';
?>
<div class="dashboard-container">
    <h2 class="dashboard-titulo">DASHBOARD</h2>

    <div class="dashboard-resumo">
        <span>Faturamento total</span>
        <strong id="faturamento-total">R$ 0,00</strong>
    </div>

    <div class="dashboard-secao">
        <h3>Produtos mais vendidos</h3>
        <div id="lista-ranking" class="lista-ranking"></div>
    </div>

    <div class="dashboard-secao">
        <div class="filtros">
            <select id="filtro-categoria">
                <option value="">Todas as categorias</option>
            </select>
            <label class="filtro-check">
                <input type="checkbox" id="filtro-promocao">
                Apenas promoções
            </label>
        </div>
        <div id="lista-filtrados" class="grid-filtrados"></div>
    </div>
</div>

<style>
.dashboard-container { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
.dashboard-titulo {
    font-size: 1.4rem;
    font-weight: 900;
    letter-spacing: 2px;
    color: #fff;
    margin-bottom: 24px;
}
.dashboard-resumo {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #111;
    padding: 20px;
    margin-bottom: 30px;
    color: #fff;
}
.dashboard-resumo strong { font-size: 1.4rem; }
.dashboard-secao { margin-bottom: 40px; }
.dashboard-secao h3 {
    font-size: 1rem;
    letter-spacing: 1px;
    color: #fff;
    margin-bottom: 16px;
    text-transform: uppercase;
}
.lista-ranking { display: flex; flex-direction: column; gap: 8px; }
.linha-ranking {
    display: grid;
    grid-template-columns: 40px 1fr 80px 100px;
    align-items: center;
    background: #111;
    padding: 12px 16px;
    color: #fff;
    font-size: 0.85rem;
}
.linha-ranking .posicao { color: #8b1a1a; font-weight: 700; }
.vazio { color: #999; font-size: 0.85rem; }
.filtros { display: flex; gap: 20px; align-items: center; margin-bottom: 20px; }
.filtros select {
    padding: 10px 14px;
    background: #fff;
    border: none;
    font-size: 0.85rem;
}
.filtro-check { color: #ccc; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; cursor: pointer; }
.grid-filtrados {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
}
.card-produto { background: #111; padding: 14px; }
.card-produto .nome { color: #fff; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px; }
.card-produto .categoria { color: #999; font-size: 0.75rem; margin-bottom: 8px; }
.card-produto .preco { color: #fff; font-size: 0.9rem; font-weight: 700; }
</style>

<script src="dashboard.js"></script>
<?php include 'templates/footer.php'; ?>