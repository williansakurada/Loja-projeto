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
?>
<div class="admin-container">
    <h2 class="admin-titulo">GERENCIAR PRODUTOS</h2>
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
                <td><?= $p['estoque'] ?></td>
                <td>
                    <button class="btn-excluir" onclick="excluirProduto(<?= $p['id'] ?>, '<?= addslashes($p['nome']) ?>')">
                        Excluir
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.admin-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
.admin-titulo {
    font-size: 1.4rem;
    font-weight: 900;
    letter-spacing: 2px;
    color: #fff;
    margin-bottom: 24px;
}
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
    font-size: 0.9rem;
    border-radius: 2px;
}
.admin-msg.erro { background: #8b1a1a; color: #fff; }
.admin-msg.sucesso { background: #1a4d1a; color: #fff; }
.d-none { display: none; }
</style>

<script>
function mostrarMensagem(texto, tipo) {
    const msg = document.getElementById('admin-msg');
    msg.textContent = texto;
    msg.className = 'admin-msg ' + tipo;
    msg.classList.remove('d-none');
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