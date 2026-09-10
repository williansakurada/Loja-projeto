<?php
include 'conexao.php';
$tituloPagina = "Carrinho - Gungnir Store";
include 'templates/header.php';
$logado = isset($_SESSION['cliente_id']) ? 'true' : 'false';
?>
<div class="carrinho-container">
    <div class="carrinho-box">
        <button class="fechar" onclick="window.history.back()">✕</button>

        <div class="carrinho-vazio" id="carrinho-vazio" style="display:none;">
            <h2 class="carrinho-titulo">O CARRINHO ESTÁ VAZIO</h2>
            <p>Já tem conta? <a href="login.php">Faça login</a> para finalizar a compra mais rápido.</p>
        </div>

        <div class="carrinho-cheio" id="carrinho-cheio" style="display:none;">
            <h2 class="carrinho-titulo">MEU CARRINHO</h2>
            <div id="lista-itens"></div>
            <div class="carrinho-total">
                <span>Total</span>
                <span id="carrinho-total-valor">R$ 0,00</span>
            </div>
            <div id="carrinho-msg" class="carrinho-msg d-none"></div>
            <button class="btn-finalizar" id="btn-finalizar" onclick="finalizarCompra()">FINALIZAR COMPRA</button>
        </div>
    </div>
</div>

<style>
main { display: flex; align-items: center; justify-content: center; }
.carrinho-container {
    width: 100%;
    max-width: 460px;
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
    justify-content: center;
}
.carrinho-box {
    background: #fff;
    width: 100%;
    min-height: calc(100vh - 120px);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 30px;
}
.fechar {
    position: absolute;
    top: 16px;
    right: 16px;
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #333;
    cursor: pointer;
}
.fechar:hover { color: #000; }
.carrinho-vazio { text-align: center; }
.carrinho-titulo {
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: 2px;
    color: #000;
    margin-bottom: 16px;
}
.carrinho-vazio p { font-size: 0.85rem; color: #555; }
.carrinho-vazio a { color: #000; text-decoration: underline; }
.carrinho-cheio { width: 100%; }
.item-carrinho {
    display: flex;
    gap: 14px;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid #eee;
}
.item-carrinho img {
    width: 60px;
    height: 60px;
    object-fit: contain;
    background: #f2f2f2;
}
.item-info { flex: 1; }
.item-nome {
    font-size: 0.85rem;
    font-weight: 700;
    color: #000;
    margin: 0 0 4px;
}
.item-detalhe {
    font-size: 0.75rem;
    color: #777;
    margin: 0;
}
.item-preco {
    font-size: 0.85rem;
    font-weight: 700;
    color: #000;
}
.btn-remover {
    background: none;
    border: none;
    color: #999;
    font-size: 0.9rem;
    cursor: pointer;
    margin-left: 8px;
}
.btn-remover:hover { color: #8b1a1a; }
.carrinho-total {
    display: flex;
    justify-content: space-between;
    font-size: 0.95rem;
    font-weight: 700;
    color: #000;
    padding: 18px 0;
}
.btn-finalizar {
    background: #8b1a1a;
    color: #fff;
    border: none;
    padding: 16px;
    font-size: 0.85rem;
    letter-spacing: 2px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
}
.btn-finalizar:hover { opacity: 0.85; }
.btn-finalizar:disabled { opacity: 0.5; cursor: not-allowed; }
.carrinho-msg {
    padding: 10px;
    font-size: 0.8rem;
    text-align: center;
    margin-bottom: 14px;
}
.carrinho-msg.erro { background: #8b1a1a; color: #fff; }
.carrinho-msg.sucesso { background: #1a4d1a; color: #fff; }
.d-none { display: none; }
</style>

<script>
const logado = <?= $logado ?>;

function pegarCarrinho() {
    return JSON.parse(localStorage.getItem('carrinho') || '[]');
}

function salvarCarrinho(itens) {
    localStorage.setItem('carrinho', JSON.stringify(itens));
}

function renderizarCarrinho() {
    const itens = pegarCarrinho();
    const vazio = document.getElementById('carrinho-vazio');
    const cheio = document.getElementById('carrinho-cheio');

    if (itens.length === 0) {
        vazio.style.display = 'block';
        cheio.style.display = 'none';
        return;
    }

    vazio.style.display = 'none';
    cheio.style.display = 'block';

    const lista = document.getElementById('lista-itens');
    lista.innerHTML = '';
    let total = 0;

    itens.forEach(function(item, indice) {
        total += Number(item.preco);
        const div = document.createElement('div');
        div.className = 'item-carrinho';
        div.innerHTML = `
            <img src="${item.img}" alt="${item.nome}">
            <div class="item-info">
                <p class="item-nome">${item.nome}</p>
                <p class="item-detalhe">Tamanho: ${item.tamanho}</p>
            </div>
            <span class="item-preco">R$ ${Number(item.preco).toFixed(2).replace('.', ',')}</span>
            <button class="btn-remover" onclick="removerItem(${indice})">✕</button>
        `;
        lista.appendChild(div);
    });

    document.getElementById('carrinho-total-valor').textContent =
        'R$ ' + total.toFixed(2).replace('.', ',');
}

function removerItem(indice) {
    const itens = pegarCarrinho();
    itens.splice(indice, 1);
    salvarCarrinho(itens);
    renderizarCarrinho();
}

function mostrarMensagem(texto, tipo) {
    const msg = document.getElementById('carrinho-msg');
    msg.textContent = texto;
    msg.className = 'carrinho-msg ' + tipo;
    msg.classList.remove('d-none');
}

async function finalizarCompra() {
    if (!logado) {
        window.location.href = 'login.php';
        return;
    }

    const itens = pegarCarrinho();
    if (itens.length === 0) return;

    const botao = document.getElementById('btn-finalizar');
    botao.disabled = true;
    botao.textContent = 'PROCESSANDO...';

    try {
        const resposta = await fetch('carrinho_finalizar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ itens: itens })
        });

        const dados = await resposta.json();

        if (!resposta.ok) {
            throw new Error(dados.erro || 'Não foi possível finalizar a compra.');
        }

        localStorage.removeItem('carrinho');
        mostrarMensagem('Pedido #' + dados.pedido_id + ' realizado com sucesso!', 'sucesso');
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 1500);
    } catch (erro) {
        mostrarMensagem(erro.message, 'erro');
        botao.disabled = false;
        botao.textContent = 'FINALIZAR COMPRA';
    }
}

renderizarCarrinho();
</script>
<?php include 'templates/footer.php'; ?>