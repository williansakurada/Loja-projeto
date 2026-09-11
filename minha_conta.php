<?php
include 'conexao.php';

if (!isset($_SESSION['cliente_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = mysqli_prepare($conexao, "SELECT nome, email, telefone, criado_em FROM clientes WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['cliente_id']);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

$tituloPagina = "Minha Conta - Gungnir Store";
include 'templates/header.php';
?>
<div class="conta-container">
    <h2 class="conta-titulo">Minha Conta</h2>

    <div class="conta-info">
        <div class="conta-linha">
            <span class="conta-label">Nome</span>
            <span class="conta-valor"><?= htmlspecialchars($cliente['nome']) ?></span>
        </div>
        <div class="conta-linha">
            <span class="conta-label">E-mail</span>
            <span class="conta-valor"><?= htmlspecialchars($cliente['email']) ?></span>
        </div>
        <div class="conta-linha">
            <span class="conta-label">Telefone</span>
            <span class="conta-valor"><?= htmlspecialchars($cliente['telefone'] ?? 'Não informado') ?></span>
        </div>
        <div class="conta-linha">
            <span class="conta-label">Cliente desde</span>
            <span class="conta-valor"><?= date('d/m/Y', strtotime($cliente['criado_em'])) ?></span>
        </div>
    </div>

    <a href="logout.php" class="btn-sair">SAIR DA CONTA</a>
</div>

<style>
main { display: flex; align-items: center; justify-content: center; }
.conta-container { max-width: 560px; width: 100%; padding: 0 40px; }
.conta-titulo {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 1px;
    margin-bottom: 34px;
}
.conta-info { margin-bottom: 34px; }
.conta-linha {
    display: flex;
    justify-content: space-between;
    padding: 18px 0;
    border-bottom: 1px solid #222;
}
.conta-label { font-size: 1.05rem; color: #999; letter-spacing: 1px; }
.conta-valor { font-size: 1.15rem; color: #fff; font-weight: 700; }
.btn-sair {
    display: block;
    text-align: center;
    background: #8b1a1a;
    color: #fff;
    border: none;
    padding: 20px;
    font-size: 1.05rem;
    letter-spacing: 3px;
    font-weight: 700;
    text-decoration: none;
    transition: opacity 0.2s;
}
.btn-sair:hover { opacity: 0.85; color: #fff; }
</style>
<?php include 'templates/footer.php'; ?>