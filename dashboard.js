"use strict";
async function buscarCategorias() {
    try {
        const resposta = await fetch('api/categorias.php');
        if (!resposta.ok) {
            throw new Error('Falha ao buscar categorias');
        }
        const dados = await resposta.json();
        return dados.categorias;
    }
    catch (erro) {
        console.error(erro);
        return [];
    }
}
async function buscarProdutos() {
    try {
        const resposta = await fetch('api/produtos.php');
        if (!resposta.ok) {
            throw new Error('Falha ao buscar produtos');
        }
        const dados = await resposta.json();
        return dados;
    }
    catch (erro) {
        console.error(erro);
        return [];
    }
}
async function buscarVendas() {
    try {
        const resposta = await fetch('api/vendas.php');
        if (!resposta.ok) {
            throw new Error('Falha ao buscar vendas');
        }
        const dados = await resposta.json();
        return dados;
    }
    catch (erro) {
        console.error(erro);
        return [];
    }
}
function filtrarProdutos(produtos, categoriaId, apenasPromocao) {
    return produtos.filter(function (p) {
        const passaCategoria = categoriaId === null || p.categoria_id === categoriaId;
        const passaPromocao = !apenasPromocao || p.status === 'promocao';
        return passaCategoria && passaPromocao;
    });
}
function calcularFaturamentoTotal(vendas) {
    return vendas.reduce(function (total, item) {
        return total + item.subtotal;
    }, 0);
}
function calcularRanking(vendas) {
    const mapa = vendas.reduce(function (acc, item) {
        if (!acc[item.produto_id]) {
            acc[item.produto_id] = {
                produto_id: item.produto_id,
                produto_nome: item.produto_nome,
                quantidade_total: 0,
                faturamento_total: 0
            };
        }
        acc[item.produto_id].quantidade_total += item.quantidade;
        acc[item.produto_id].faturamento_total += item.subtotal;
        return acc;
    }, {});
    return Object.values(mapa).sort(function (a, b) {
        return b.quantidade_total - a.quantidade_total;
    });
}
function formatarMoeda(valor) {
    return 'R$ ' + valor.toFixed(2).replace('.', ',');
}
function renderizarProdutos(produtos) {
    const container = document.getElementById('lista-filtrados');
    if (!container)
        return;
    if (produtos.length === 0) {
        container.innerHTML = '<p class="vazio">Nenhum produto encontrado com esse filtro.</p>';
        return;
    }
    container.innerHTML = produtos.map(function (p) {
        return '<div class="card-produto">' +
            '<p class="nome">' + p.nome + '</p>' +
            '<p class="categoria">' + (p.categoria_nome || 'Sem categoria') + '</p>' +
            '<p class="preco">' + formatarMoeda(p.preco) + '</p>' +
            '</div>';
    }).join('');
}
function renderizarRanking(ranking) {
    const container = document.getElementById('lista-ranking');
    if (!container)
        return;
    if (ranking.length === 0) {
        container.innerHTML = '<p class="vazio">Ainda não há vendas registradas.</p>';
        return;
    }
    container.innerHTML = ranking.map(function (item, indice) {
        return '<div class="linha-ranking">' +
            '<span class="posicao">#' + (indice + 1) + '</span>' +
            '<span class="nome">' + item.produto_nome + '</span>' +
            '<span class="qtd">' + item.quantidade_total + ' un.</span>' +
            '<span class="valor">' + formatarMoeda(item.faturamento_total) + '</span>' +
            '</div>';
    }).join('');
}
async function iniciar() {
    const [produtos, vendas, categorias] = await Promise.all([buscarProdutos(), buscarVendas(), buscarCategorias()]);
    renderizarProdutos(produtos);
    renderizarRanking(calcularRanking(vendas));
    const faturamentoEl = document.getElementById('faturamento-total');
    if (faturamentoEl) {
        faturamentoEl.textContent = formatarMoeda(calcularFaturamentoTotal(vendas));
    }
    const seletorCategoria = document.getElementById('filtro-categoria');
    const checkboxPromocao = document.getElementById('filtro-promocao');
    if (seletorCategoria) {
        seletorCategoria.innerHTML = '<option value="">Todas as categorias</option>' +
            categorias.map(function (c) {
                return '<option value="' + c.id + '">' + c.nome + '</option>';
            }).join('');
    }
    function aplicarFiltro() {
        const categoriaId = seletorCategoria && seletorCategoria.value !== '' ? Number(seletorCategoria.value) : null;
        const apenasPromocao = checkboxPromocao ? checkboxPromocao.checked : false;
        renderizarProdutos(filtrarProdutos(produtos, categoriaId, apenasPromocao));
    }
    if (seletorCategoria)
        seletorCategoria.addEventListener('change', aplicarFiltro);
    if (checkboxPromocao)
        checkboxPromocao.addEventListener('change', aplicarFiltro);
}
iniciar();
