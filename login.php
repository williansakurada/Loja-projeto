<?php
session_start();
include 'conexao.php';

$erro = '';
$email_valor = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_valor = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email_valor === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $stmt = mysqli_prepare($conexao, "SELECT id, nome, senha_hash FROM clientes WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email_valor);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $cliente = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        if ($cliente && password_verify($senha, $cliente['senha_hash'])) {
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nome'] = $cliente['nome'];
            header('Location: index.php');
            exit;
        } else {
            $erro = 'E-mail ou senha incorretos.';
        }
    }
}

$tituloPagina = "Login - Gungnir Store";
include 'templates/header.php';
?>
<div class="login-container" id="tela-login">
    <h2 class="login-titulo">Entrar com email e senha</h2>

    <?php if ($erro !== ''): ?>
        <div class="msg-erro"><?= $erro ?></div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="login.php">
        <div class="campo">
            <label>E-mail</label>
            <input type="email" name="email" placeholder="Ex.: exemplo@mail.com" value="<?= htmlspecialchars($email_valor) ?>" required>
        </div>
        <div class="campo">
            <label>Senha</label>
            <div class="senha-wrapper">
                <input type="password" name="senha" id="senha" placeholder="Adicione sua senha" required>
                <i class="bi bi-eye-slash" id="toggle-senha" onclick="toggleSenha()"></i>
            </div>
        </div>
        <p class="esqueci"><a href="#" onclick="abrirEsqueci(event)">Esqueci minha senha</a></p>
        <button type="submit" class="btn-entrar">ENTRAR</button>
        <p class="cadastro">Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
    </form>
</div>

<div class="overlay-modal" id="overlay-modal" onclick="fecharEsqueci()"></div>
<div class="modal-esqueci" id="modal-esqueci">
    <h2 class="modal-titulo">Receber código de acesso por email</h2>
    <div class="campo">
        <label>E-mail</label>
        <input type="email" placeholder="Ex.: exemplo@mail.com">
    </div>
    <div class="modal-botoes">
        <a href="#" class="btn-voltar" onclick="fecharEsqueci(event)">← VOLTAR</a>
        <button class="btn-enviar">ENVIAR</button>
    </div>
</div>

<style>
main { display: flex; align-items: center; justify-content: center; }
.login-container { max-width: 600px; width: 100%; padding: 0 40px; }
.login-titulo {
    font-size: 1.8rem;
    font-weight: 700;
    text-align: center;
    color: #fff;
    letter-spacing: 1px;
    text-transform: none;
    margin-bottom: 40px;
}
.login-form { display: flex; flex-direction: column; gap: 20px; }
.campo { display: flex; flex-direction: column; gap: 8px; }
.campo label { font-size: 0.95rem; color: #ccc; letter-spacing: 1px; }
.campo input {
    background: #fff;
    border: 1px solid #ddd;
    padding: 16px;
    font-size: 1rem;
    color: #333;
    outline: none;
    border-radius: 2px;
    width: 100%;
}
.campo input:focus { border-color: #999; }
.senha-wrapper { position: relative; display: flex; align-items: center; }
.senha-wrapper input { padding-right: 48px; width: 100%; }
.senha-wrapper .bi {
    position: absolute;
    right: 14px;
    color: #999;
    cursor: pointer;
    font-size: 1.1rem;
    user-select: none;
}
.esqueci { text-align: right; margin: 0; }
.esqueci a { color: #8b1a1a; font-size: 0.9rem; text-decoration: none; }
.esqueci a:hover { text-decoration: underline; }
.btn-entrar {
    background: #8b1a1a;
    color: #fff;
    border: none;
    padding: 18px;
    font-size: 0.95rem;
    letter-spacing: 3px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
    transition: opacity 0.2s;
}
.btn-entrar:hover { opacity: 0.85; }
.cadastro { text-align: center; font-size: 0.9rem; color: #aaa; margin: 0; }
.cadastro a { color: #8b1a1a; text-decoration: none; }
.cadastro a:hover { text-decoration: underline; }
.msg-erro {
    background: #8b1a1a;
    color: #fff;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    border-radius: 2px;
    text-align: center;
}
.overlay-modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    z-index: 200;
}
.overlay-modal.ativo { display: block; }
.modal-esqueci {
    display: none;
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: #0a0a0a;
    border: 1px solid #fff;
    padding: 40px;
    width: 420px;
    z-index: 300;
    flex-direction: column;
    gap: 24px;
    border-radius: 2px;
}
.modal-esqueci.ativo { display: flex; }
.modal-titulo {
    font-size: 1.3rem;
    font-weight: 700;
    text-align: center;
    color: #fff;
    letter-spacing: 0.5px;
    text-transform: none;
    margin-bottom: 0;
}
.modal-esqueci .campo label { color: #ccc; }
.modal-esqueci .campo input { border: 1px solid #ddd; padding: 14px; }
.modal-botoes { display: flex; justify-content: space-between; align-items: center; }
.btn-voltar {
    color: #8b1a1a;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-decoration: none;
}
.btn-voltar:hover { text-decoration: underline; color: #8b1a1a; }
.btn-enviar {
    background: #8b1a1a;
    color: #fff;
    border: none;
    padding: 14px 28px;
    font-size: 0.85rem;
    letter-spacing: 2px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-enviar:hover { opacity: 0.85; }
</style>

<script>
function toggleSenha() {
    var input = document.getElementById("senha");
    var icon = document.getElementById("toggle-senha");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
}

function abrirEsqueci(e) {
    e.preventDefault();
    document.getElementById("modal-esqueci").classList.add("ativo");
    document.getElementById("overlay-modal").classList.add("ativo");
}

function fecharEsqueci(e) {
    if (e) e.preventDefault();
    document.getElementById("modal-esqueci").classList.remove("ativo");
    document.getElementById("overlay-modal").classList.remove("ativo");
}
</script>
<?php include 'templates/footer.php'; ?>