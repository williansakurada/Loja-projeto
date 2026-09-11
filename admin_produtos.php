<?php
include 'conexao.php';
$tituloPagina = "Administrar Produtos - Gungnir Store";
include 'templates/header.php';

$sql = "SELECT * FROM vw_produtos_catalogo ORDER BY nome";
$resultado = mysqli_query($conexao, $sql);
$produtos = [];
if ($resultado) {
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $produtos[] = $linha;
    }
}

$categorias = [];
$resultadoCategorias = mysqli_query($conexao, "SELECT id, nome FROM categorias ORDER BY nome");
while ($linha = mysqli_fetch_assoc($resultadoCategorias)) {
    $categorias[] = $linha;
}
?>
<div class="admin-container">
    <div class="admin-cabecalho">
        <h2 class="admin-titulo">GERENCIAR PRODUTOS</h2>
        <button class="btn-abrir-modal" onclick="abrirModal()">+ Adicionar Produto</button>
    </div>
    <div id="admin-msg" class="admin-msg d-none"></div>

    <table class="admin-tabela">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="admin-tbody">
            <?php foreach ($produtos as $p): ?>
            <tr id="linha-produto-<?= $p['id'] ?>">
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['categoria_nome'] ?? '—') ?></td>
                <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                <td id="estoque-produto-<?= $p['id'] ?>"><?= $p['estoque'] ?></td>
                <td class="acoes">
                    <button class="btn-adicionar-estoque" onclick="adicionarEstoque(<?= $p['id'] ?>)" title="Adicionar 1 unidade ao estoque">+</button>
                    <button class="btn-excluir" onclick="excluirProduto(<?= $p['id'] ?>, '<?= addslashes($p['nome']) ?>')">
                        Excluir
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="overlay-modal" id="overlay-modal" onclick="fecharModal()"></div>
<div class="modal-produto" id="modal-produto">
    <h3 class="modal-titulo">Adicionar produto</h3>
    <div id="modal-msg" class="admin-msg d-none"></div>

    <div class="campo">
        <label>Nome</label>
        <input type="text" id="novo-nome" placeholder="Ex.: Camiseta Oversized">
    </div>
    <div class="form-linha">
        <div class="campo campo-flex">
            <label>Categoria</label>
            <select id="novo-categoria">
                <option value="">Selecione</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo campo-flex">
            <label>Estoque inicial</label>
            <input type="number" id="novo-estoque" min="0" step="1" placeholder="0">
        </div>
    </div>
    <div class="form-linha">
        <div class="campo campo-flex">
            <label>Preço (R$)</label>
            <input type="number" id="novo-preco" step="0.01" min="0" placeholder="0,00">
        </div>
        <div class="campo campo-flex">
            <label>Preço antigo (opcional)</label>
            <input type="number" id="novo-preco-orig" step="0.01" min="0" placeholder="0,00">
        </div>
    </div>
    <div class="campo">
        <label>Caminho da imagem</label>
        <input type="text" id="novo-img" placeholder="Ex.: imgs/camiseta1.png">
    </div>

    <div class="modal-botoes">
        <a href="#" class="btn-cancelar" onclick="fecharModal(event)">CANCELAR</a>
        <button class="btn-confirmar-adicionar" id="btn-confirmar-adicionar" onclick="adicionarProduto()">ADICIONAR</button>
    </div>
</div>

<style>
.admin-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
.admin-cabecalho {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.admin-titulo {
    font-size: 1.4rem;
    font-weight: 900;
    letter-spacing: 2px;
    color: #fff;
}
.btn-abrir-modal {
    background: #1a4d1a;
    color: #fff;
    border: none;
    padding: 10px 18px;
    font-size: 0.8rem;
    letter-spacing: 1px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-abrir-modal:hover { opacity: 0.85; }
.admin-tabela {
    width: 100%;
    border-collapse: collapse;
    background: #111;
}
.admin-tabela th {
    text-align: left;
    padding: 12px 14px;
    font-size: 0.75rem;
    letter-spacing: 1px;
    color: #aaa;
    border-bottom: 1px solid #333;
    text-transform: uppercase;
}
.admin-tabela td {
    padding: 12px 14px;
    font-size: 0.9rem;
    color: #fff;
    border-bottom: 1px solid #222;
}
.acoes { display: flex; gap: 8px; align-items: center; }
.btn-adicionar-estoque {
    background: transparent;
    border: 1px solid #1a4d1a;
    color: #4caf50;
    width: 28px;
    height: 28px;
    font-size: 1rem;
    font-weight: 700;
    line-height: 1;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-adicionar-estoque:hover { background: #1a4d1a; color: #fff; }
.btn-adicionar-estoque:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-excluir {
    background: transparent;
    border: 1px solid #8b1a1a;
    color: #8b1a1a;
    padding: 6px 14px;
    font-size: 0.75rem;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-excluir:hover { background: #8b1a1a; color: #fff; }
.btn-excluir:disabled { opacity: 0.5; cursor: not-allowed; }
.admin-msg {
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 0.85rem;
    border-radius: 2px;
}
.admin-msg.erro { background: #8b1a1a; color: #fff; }
.admin-msg.sucesso { background: #1a4d1a; color: #fff; }
.d-none { display: none; }
.overlay-modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 200;
}
.overlay-modal.ativo { display: block; }
.modal-produto {
    display: none;
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: #0a0a0a;
    border: 1px solid #fff;
    padding: 30px;
    width: 460px;
    max-width: 90vw;
    z-index: 300;
    flex-direction: column;
    gap: 14px;
}
.modal-produto.ativo { display: flex; }
.modal-titulo {
    font-size: 1.1rem;
    font-weight: 900;
    letter-spacing: 1px;
    color: #fff;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.campo { display: flex; flex-direction: column; gap: 6px; }
.campo label { font-size: 0.75rem; color: #ccc; letter-spacing: 1px; }
.campo input, .campo select {
    background: #fff;
    border: 1px solid #ddd;
    padding: 10px 12px;
    font-size: 0.9rem;
    color: #333;
    outline: none;
    border-radius: 2px;
    width: 100%;
}
.form-linha { display: flex; gap: 12px; }
.campo-flex { flex: 1; }
.modal-botoes { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; }
.btn-cancelar {
    color: #aaa;
    font-size: 0.8rem;
    letter-spacing: 1px;
    text-decoration: none;
}
.btn-cancelar:hover { color: #fff; }
.btn-confirmar-adicionar {
    background: #1a4d1a;
    color: #fff;
    border: none;
    padding: 12px 24px;
    font-size: 0.8rem;
    letter-spacing: 2px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-confirmar-adicionar:hover { opacity: 0.85; }
.btn-confirmar-adicionar:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<script>
function mostrarMensagem(texto, tipo) {
    const msg = document.getElementById('admin-msg');
    msg.textContent = texto;
    msg.className = 'admin-msg ' + tipo;
    msg.classList.remove('d-none');
}

function abrirModal() {
    document.getElementById('modal-produto').classList.add('ativo');
    document.getElementById('overlay-modal').classList.add('ativo');
}

function fecharModal(e) {
    if (e) e.preventDefault();
    document.getElementById('modal-produto').classList.remove('ativo');
    document.getElementById('overlay-modal').classList.remove('ativo');
    document.getElementById('modal-msg').classList.add('d-none');
}

async function adicionarEstoque(id) {
    const botao = document.querySelector('#linha-produto-' + id + ' .btn-adicionar-estoque');
    botao.disabled = true;

    try {
        const resposta = await fetch('produto_estoque.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        const dados = await resposta.json();

        if (!resposta.ok) {
            throw new Error(dados.erro || 'Não foi possível atualizar o estoque.');
        }

        document.getElementById('estoque-produto-' + id).textContent = dados.estoque;
    } catch (erro) {
        mostrarMensagem(erro.message, 'erro');
    } finally {
        botao.disabled = false;
    }
}

async function adicionarProduto() {
    const nome = document.getElementById('novo-nome').value.trim();
    const categoria_id = document.getElementById('novo-categoria').value;
    const preco = document.getElementById('novo-preco').value;
    const preco_orig = document.getElementById('novo-preco-orig').value;
    const estoque = document.getElementById('novo-estoque').value;
    const img = document.getElementById('novo-img').value.trim();
    const modalMsg = document.getElementById('modal-msg');

    if (!nome || !categoria_id || !preco || !img) {
        modalMsg.textContent = 'Preencha nome, categoria, preço e imagem.';
        modalMsg.className = 'admin-msg erro';
        modalMsg.classList.remove('d-none');
        return;
    }

    const botao = document.getElementById('btn-confirmar-adicionar');
    botao.disabled = true;
    botao.textContent = 'ADICIONANDO...';

    try {
        const resposta = await fetch('produto_adicionar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nome: nome,
                categoria_id: categoria_id,
                preco: preco,
                preco_orig: preco_orig,
                estoque: estoque || 0,
                img: img
            })
        });

        const dados = await resposta.json();

        if (!resposta.ok) {
            throw new Error(dados.erro || 'Não foi possível adicionar o produto.');
        }

        const p = dados.produto;
        const tbody = document.getElementById('admin-tbody');
        const linha = document.createElement('tr');
        linha.id = 'linha-produto-' + p.id;
        linha.innerHTML =
            '<td>' + p.nome + '</td>' +
            '<td>' + (p.categoria_nome || '—') + '</td>' +
            '<td>R$ ' + Number(p.preco).toFixed(2).replace('.', ',') + '</td>' +
            '<td id="estoque-produto-' + p.id + '">' + p.estoque + '</td>' +
            '<td class="acoes">' +
                '<button class="btn-adicionar-estoque" onclick="adicionarEstoque(' + p.id + ')" title="Adicionar 1 unidade ao estoque">+</button>' +
                '<button class="btn-excluir" onclick="excluirProduto(' + p.id + ', \'' + p.nome.replace(/'/g, "\\'") + '\')">Excluir</button>' +
            '</td>';
        tbody.appendChild(linha);

        document.getElementById('novo-nome').value = '';
        document.getElementById('novo-categoria').value = '';
        document.getElementById('novo-preco').value = '';
        document.getElementById('novo-preco-orig').value = '';
        document.getElementById('novo-estoque').value = '';
        document.getElementById('novo-img').value = '';

        fecharModal();
        mostrarMensagem('"' + p.nome + '" foi adicionado com sucesso.', 'sucesso');
    } catch (erro) {
        modalMsg.textContent = erro.message;
        modalMsg.className = 'admin-msg erro';
        modalMsg.classList.remove('d-none');
    } finally {
        botao.disabled = false;
        botao.textContent = 'ADICIONAR';
    }
}

async function excluirProduto(id, nome) {
    const confirmou = window.confirm('Tem certeza que deseja excluir "' + nome + '"? Essa ação não pode ser desfeita.');
    if (!confirmou) return;

    const linha = document.getElementById('linha-produto-' + id);
    const botao = linha.querySelector('.btn-excluir');
    botao.disabled = true;
    botao.textContent = 'Excluindo...';

    try {
        const resposta = await fetch('produto_excluir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        const dados = await resposta.json();

        if (!resposta.ok) {
            throw new Error(dados.erro || 'Não foi possível excluir o produto.');
        }

        linha.remove();
        mostrarMensagem('"' + nome + '" foi excluído com sucesso.', 'sucesso');
    } catch (erro) {
        botao.disabled = false;
        botao.textContent = 'Excluir';
        mostrarMensagem(erro.message, 'erro');
    }
}
</script>
<?php include 'templates/footer.php'; ?>