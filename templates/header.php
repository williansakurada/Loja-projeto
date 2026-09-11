<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?? 'Gungnir Store' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            background-color: #0a0a0a;
            color: #ffffff;
            font-family: 'Arial Narrow', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }
        nav {
            background-color: #0a0a0a;
            border-bottom: 1px solid #fff;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 100;
        }
        .nav-logo {
            font-size: 1.4rem;
            font-weight: 900;
            letter-spacing: 6px;
            color: #fff;
            text-transform: uppercase;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.2s;
        }
        .nav-logo:hover { color: #aaa; }
        .nav-icons a {
            color: #fff;
            margin-left: 20px;
            font-size: 1.2rem;
            text-decoration: none;
            transition: color 0.2s;
            position: relative;
        }
        .nav-icons a:hover { color: #aaa; }
        .badge-carrinho {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #8b1a1a;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 900;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        main { flex: 1; }
        footer {
            background-color: #0a0a0a;
            border-top: 1px solid #fff;
            text-align: center;
            padding: 20px;
            font-size: 0.7rem;
            letter-spacing: 2px;
            color: #fff;
            text-transform: uppercase;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 200;
        }
        .overlay.ativo { display: block; }
        .mini-carrinho-sidebar {
            position: fixed;
            top: 0; right: -450px;
            width: 420px;
            height: 100%;
            background: #fff;
            z-index: 300;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .mini-carrinho-sidebar.ativo { right: 0; }
        .mini-carrinho-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #eee;
        }
        .mini-carrinho-header span {
            font-size: 0.85rem;
            font-weight: 900;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #000;
        }
        .fechar-mini-carrinho {
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #333;
            cursor: pointer;
        }
        .fechar-mini-carrinho:hover { color: #000; }
        .mini-carrinho-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
        }
        .mini-carrinho-vazio {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
        }
        .mini-carrinho-titulo {
            font-size: 1rem;
            font-weight: 900;
            letter-spacing: 2px;
            color: #000;
            margin-bottom: 16px;
            text-transform: uppercase;
        }
        .mini-carrinho-vazio p { font-size: 0.85rem; color: #555; }
        .mini-carrinho-vazio a { color: #000; text-decoration: underline; }
        .mini-carrinho-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            align-items: center;
        }
        .mini-carrinho-item img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            background: #f5f5f5;
        }
        .mini-carrinho-item-info { flex: 1; }
        .mini-carrinho-item-nome {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #000;
        }
        .mini-carrinho-item-tamanho {
            font-size: 0.75rem;
            color: #888;
            margin-top: 4px;
        }
        .mini-carrinho-item-preco {
            font-size: 0.85rem;
            font-weight: 700;
            color: #000;
            margin-top: 4px;
        }
        .mini-carrinho-item-remover {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1rem;
            padding: 4px 8px;
        }
        .mini-carrinho-item-remover:hover { color: #000; }
        .mini-carrinho-footer {
            padding: 20px 24px;
            border-top: 1px solid #eee;
        }
        .mini-carrinho-total {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 16px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .btn-ir-carrinho {
            background: #000;
            color: #fff;
            border: none;
            padding: 16px;
            width: 100%;
            font-size: 0.85rem;
            letter-spacing: 3px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-ir-carrinho:hover { opacity: 0.8; }
    </style>
</head>
<body>
<nav>
    <a class="nav-logo" href="index.php">Gungnir Store</a>
    <div class="nav-icons">
        <a href="<?= isset($_SESSION['cliente_id']) ? 'index.php' : 'login.php' ?>">
            <i class="bi bi-person"></i>
        </a>
        <a href="#" onclick="abrirMiniCarrinho(event)" style="position:relative">
            <i class="bi bi-bag"></i>
            <span class="badge-carrinho" id="badge-carrinho" style="display:none">0</span>
        </a>
    </div>
</nav>

<div class="overlay" id="mini-carrinho-overlay" onclick="fecharMiniCarrinho()"></div>

<div class="mini-carrinho-sidebar" id="mini-carrinho-sidebar">
    <div class="mini-carrinho-header">
        <span>Carrinho</span>
        <button class="fechar-mini-carrinho" onclick="fecharMiniCarrinho()">✕</button>
    </div>
    <div class="mini-carrinho-body" id="mini-carrinho-body">
        <div class="mini-carrinho-vazio">
            <h2 class="mini-carrinho-titulo">O Carrinho está vazio</h2>
            <p>Já tem conta? <a href="login.php">Faça login</a> para finalizar a compra mais rápido.</p>
        </div>
    </div>
    <div class="mini-carrinho-footer" id="mini-carrinho-footer" style="display:none">
        <div class="mini-carrinho-total">
            <span>Total</span>
            <span id="mini-carrinho-total-valor">R$ 0,00</span>
        </div>
        <button class="btn-ir-carrinho" onclick="window.location.href='checkout.php'">FINALIZAR COMPRA</button>
    </div>
</div>

<script>
function abrirMiniCarrinho(e) {
    e.preventDefault();
    renderizarMiniCarrinho();
    document.getElementById("mini-carrinho-sidebar").classList.add("ativo");
    document.getElementById("mini-carrinho-overlay").classList.add("ativo");
}

function fecharMiniCarrinho() {
    document.getElementById("mini-carrinho-sidebar").classList.remove("ativo");
    document.getElementById("mini-carrinho-overlay").classList.remove("ativo");
}

function removerItemMiniCarrinho(index) {
    var carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    carrinho.splice(index, 1);
    localStorage.setItem('carrinho', JSON.stringify(carrinho));
    renderizarMiniCarrinho();
    atualizarBadge();
}

function atualizarBadge() {
    var carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    var badge = document.getElementById('badge-carrinho');
    if (carrinho.length > 0) {
        badge.style.display = 'flex';
        badge.textContent = carrinho.length;
    } else {
        badge.style.display = 'none';
    }
}

function renderizarMiniCarrinho() {
    var carrinho = JSON.parse(localStorage.getItem('carrinho') || '[]');
    var body = document.getElementById('mini-carrinho-body');
    var footer = document.getElementById('mini-carrinho-footer');
    var badge = document.getElementById('badge-carrinho');

    if (carrinho.length === 0) {
        body.innerHTML = '<div class="mini-carrinho-vazio"><h2 class="mini-carrinho-titulo">O Carrinho está vazio</h2><p>Já tem conta? <a href="login.php">Faça login</a> para finalizar a compra mais rápido.</p></div>';
        footer.style.display = 'none';
        badge.style.display = 'none';
        return;
    }

    footer.style.display = 'block';
    badge.style.display = 'flex';
    badge.textContent = carrinho.length;

    var html = '';
    var total = 0;
    carrinho.forEach(function(item, index) {
        total += Number(item.preco);
        html += '<div class="mini-carrinho-item">';
        html += '<img src="' + item.img + '" alt="' + item.nome + '">';
        html += '<div class="mini-carrinho-item-info">';
        html += '<p class="mini-carrinho-item-nome">' + item.nome + '</p>';
        html += '<p class="mini-carrinho-item-tamanho">Tamanho: ' + item.tamanho + '</p>';
        html += '<p class="mini-carrinho-item-preco">R$ ' + Number(item.preco).toFixed(2).replace('.', ',') + '</p>';
        html += '</div>';
        html += '<button class="mini-carrinho-item-remover" onclick="removerItemMiniCarrinho(' + index + ')">✕</button>';
        html += '</div>';
    });

    body.innerHTML = html;
    document.getElementById('mini-carrinho-total-valor').textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
}

window.addEventListener('load', function() {
    atualizarBadge();
});
</script>

<main>