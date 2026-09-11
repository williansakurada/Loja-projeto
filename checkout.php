<?php
include 'conexao.php';

if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

$tituloPagina = "Finalizar Compra - Gungnir Store";
include 'templates/header.php';
?>
<div class="checkout-container">
    <div class="checkout-resumo">
        <h2 class="checkout-titulo">Resumo do pedido</h2>
        <div id="checkout-itens"></div>
        <div class="checkout-total">
            <span>Total</span>
            <span id="checkout-total-valor">R$ 0,00</span>
        </div>
    </div>

    <div class="checkout-form-box">
        <h2 class="checkout-titulo">Entrega e pagamento</h2>
        <div id="checkout-msg" class="checkout-msg d-none"></div>

        <div class="campo">
            <label>CEP</label>
            <input type="text" id="cep" placeholder="00000-000" inputmode="numeric" maxlength="9" oninput="mascararCep(this)" onblur="buscarCep()">
        </div>
        <div class="campo-linha">
            <div class="campo campo-flex">
                <label>Rua</label>
                <input type="text" id="rua" placeholder="Rua/Avenida">
            </div>
            <div class="campo campo-pequeno">
                <label>Número</label>
                <input type="text" id="numero" placeholder="Nº">
            </div>
        </div>
        <div class="campo-linha">
            <div class="campo campo-flex">
                <label>Bairro</label>
                <input type="text" id="bairro" placeholder="Bairro">
            </div>
        </div>
        <div class="campo-linha">
            <div class="campo campo-flex">
                <label>Cidade</label>
                <input type="text" id="cidade" placeholder="Cidade">
            </div>
            <div class="campo campo-pequeno">
                <label>UF</label>
                <input type="text" id="uf" placeholder="UF" maxlength="2">
            </div>
        </div>

        <div class="campo">
            <label>Forma de pagamento</label>
            <div class="opcoes-pagamento">
                <label class="opcao-pagamento">
                    <input type="radio" name="pagamento" value="cartao" checked>
                    Cartão de crédito
                </label>
                <label class="opcao-pagamento">
                    <input type="radio" name="pagamento" value="pix">
                    Pix
                </label>
                <label class="opcao-pagamento">
                    <input type="radio" name="pagamento" value="boleto">
                    Boleto
                </label>
            </div>
        </div>

        <button class="btn-confirmar" id="btn-confirmar" onclick="confirmarPedido()">CONFIRMAR PEDIDO</button>
    </div>
</div>

<style>
main { display: flex; align-items: flex-start; justify-content: center; }
.checkout-container {
    max-width: 1000px;
    width: 100%;
    display: flex;
    gap: 40px;
    padding: 40px 20px;
}
.checkout-resumo {
    flex: 1;
    background: #111;
    padding: 24px;
    height: fit-content;
}
.checkout-form-box {
    flex: 1.3;
}
.checkout-titulo {
    font-size: 1rem;
    font-weight: 900;
    letter-spacing: 2px;
    color: #fff;
    text-transform: uppercase;
    margin-bottom: 18px;
}
.item-resumo {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #222;
    font-size: 0.85rem;
    color: #ccc;
}
.item-resumo .nome { color: #fff; font-weight: 700; }
.checkout-total {
    display: flex;
    justify-content: space-between;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    padding-top: 16px;
    margin-top: 8px;
}
.campo { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
.campo label { font-size: 0.8rem; color: #ccc; letter-spacing: 1px; }
.campo input {
    background: #fff;
    border: 1px solid #ddd;
    padding: 12px 14px;
    font-size: 0.95rem;
    color: #333;
    outline: none;
    border-radius: 2px;
    width: 100%;
}
.campo-linha { display: flex; gap: 14px; }
.campo-flex { flex: 1; }
.campo-pequeno { width: 100px; }
.opcoes-pagamento { display: flex; flex-direction: column; gap: 10px; }
.opcao-pagamento {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #ccc;
    font-size: 0.9rem;
    cursor: pointer;
}
.opcao-pagamento input { width: 16px; height: 16px; accent-color: #8b1a1a; cursor: pointer; }
.btn-confirmar {
    background: #8b1a1a;
    color: #fff;
    border: none;
    padding: 16px;
    font-size: 0.9rem;
    letter-spacing: 3px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
    margin-top: 10px;
    transition: opacity 0.2s;
}
.btn-confirmar:hover { opacity: 0.85; }
.btn-confirmar:disabled { opacity: 0.5; cursor: not-allowed; }
.checkout-msg { padding: 12px; font-size: 0.85rem; margin-bottom: 14px; }
.checkout-msg.erro { background: #8b1a1a; color: #fff; }
.checkout-msg.sucesso { background: #1a4d1a; color: #fff; }
.d-none { display: none; }
</style>

<script>
function pegarCarrinho() {
    return JSON.parse(localStorage.getItem('carrinho') || '[]');
}

function mostrarMensagem(texto, tipo) {
    const msg = document.getElementById('checkout-msg');
    msg.textContent = texto;
    msg.className = 'checkout-msg ' + tipo;
    msg.classList.remove('d-none');
}

function renderizarResumo() {
    const itens = pegarCarrinho();
    const container = document.getElementById('checkout-itens');

    if (itens.length === 0) {
        window.location.href = 'carrinho.php';
        return;
    }

    let total = 0;
    container.innerHTML = itens.map(function (item) {
        total += Number(item.preco);
        return '<div class="item-resumo">' +
            '<span class="nome">' + item.nome + ' <small>(' + item.tamanho + ')</small></span>' +
            '<span>R$ ' + Number(item.preco).toFixed(2).replace('.', ',') + '</span>' +
            '</div>';
    }).join('');

    document.getElementById('checkout-total-valor').textContent =
        'R$ ' + total.toFixed(2).replace('.', ',');
}

function mascararCep(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 8);
    if (v.length > 5) {
        v = v.replace(/(\d{5})(\d{0,3})/, '$1-$2');
    }
    input.value = v;
}

async function buscarCep() {
    const cepInput = document.getElementById('cep');
    const cep = cepInput.value.replace(/\D/g, '');
    if (cep.length !== 8) return;

    try {
        const resposta = await fetch('https://viacep.com.br/ws/' + cep + '/json/');
        if (!resposta.ok) {
            throw new Error('Não foi possível buscar o CEP.');
        }
        const dados = await resposta.json();
        if (dados.erro) {
            throw new Error('CEP não encontrado.');
        }
        document.getElementById('rua').value = dados.logradouro || '';
        document.getElementById('bairro').value = dados.bairro || '';
        document.getElementById('cidade').value = dados.localidade || '';
        document.getElementById('uf').value = dados.uf || '';
    } catch (erro) {
        mostrarMensagem(erro.message, 'erro');
    }
}

async function confirmarPedido() {
    const itens = pegarCarrinho();
    if (itens.length === 0) return;

    const rua = document.getElementById('rua').value.trim();
    const numero = document.getElementById('numero').value.trim();
    const bairro = document.getElementById('bairro').value.trim();
    const cidade = document.getElementById('cidade').value.trim();
    const uf = document.getElementById('uf').value.trim();
    const pagamentoEl = document.querySelector('input[name="pagamento"]:checked');

    if (!rua || !numero || !cidade || !uf) {
        mostrarMensagem('Preencha o endereço completo.', 'erro');
        return;
    }
    if (!pagamentoEl) {
        mostrarMensagem('Escolha uma forma de pagamento.', 'erro');
        return;
    }

    const endereco = rua + ', ' + numero + ' - ' + bairro + ', ' + cidade + '/' + uf;
    const botao = document.getElementById('btn-confirmar');
    botao.disabled = true;
    botao.textContent = 'PROCESSANDO...';

    try {
        const resposta = await fetch('carrinho_finalizar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                itens: itens,
                endereco: endereco,
                forma_pagamento: pagamentoEl.value
            })
        });

        const dados = await resposta.json();

        if (!resposta.ok) {
            throw new Error(dados.erro || 'Não foi possível finalizar a compra.');
        }

        localStorage.removeItem('carrinho');
        mostrarMensagem('Pedido #' + dados.pedido_id + ' confirmado com sucesso!', 'sucesso');
        setTimeout(function () {
            window.location.href = 'index.php';
        }, 1800);
    } catch (erro) {
        mostrarMensagem(erro.message, 'erro');
        botao.disabled = false;
        botao.textContent = 'CONFIRMAR PEDIDO';
    }
}

renderizarResumo();
</script>
<?php include 'templates/footer.php'; ?>