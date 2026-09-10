<?php
include 'conexao.php';

$erro = '';
$sucesso = '';
$nome_valor = '';
$email_valor = '';
$telefone_valor = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_valor = trim($_POST['nome'] ?? '');
    $email_valor = trim($_POST['email'] ?? '');
    $telefone_valor = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if ($nome_valor === '' || $email_valor === '' || $senha === '') {
        $erro = 'Preencha nome, e-mail e senha.';
    } elseif (!filter_var($email_valor, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha precisa ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $stmt = mysqli_prepare($conexao, "SELECT id FROM clientes WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email_valor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $erro = 'Já existe uma conta com esse e-mail.';
        }
        mysqli_stmt_close($stmt);

        if ($erro === '') {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt2 = mysqli_prepare($conexao, "INSERT INTO clientes (nome, email, senha_hash, telefone) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt2, "ssss", $nome_valor, $email_valor, $senha_hash, $telefone_valor);
            if (mysqli_stmt_execute($stmt2)) {
                $sucesso = 'Conta criada com sucesso! Você já pode fazer login.';
                $nome_valor = '';
                $email_valor = '';
                $telefone_valor = '';
            } else {
                $erro = 'Erro ao criar conta. Tente novamente.';
            }
            mysqli_stmt_close($stmt2);
        }
    }
}

$tituloPagina = "Cadastro - Gungnir Store";
include 'templates/header.php';
?>
<div class="cadastro-container">
    <h2 class="cadastro-titulo">Criar conta</h2>

    <?php if ($erro !== ''): ?>
        <div class="msg-erro">Erro no cadastro: <?= $erro ?></div>
    <?php endif; ?>
    <?php if ($sucesso !== ''): ?>
        <div class="msg-sucesso"><?= $sucesso ?> <a href="login.php">Ir para o login</a></div>
    <?php endif; ?>

    <form class="cadastro-form" method="POST" action="cadastro.php">
        <div class="campo">
            <label>Nome completo</label>
            <input type="text" name="nome" id="nome" placeholder="Ex.: João da Silva" oninput="apenasLetras(this)" value="<?= htmlspecialchars($nome_valor) ?>" required>
        </div>
        <div class="campo">
            <label>E-mail</label>
            <input type="email" name="email" placeholder="Ex.: exemplo@mail.com" value="<?= htmlspecialchars($email_valor) ?>" required>
        </div>
        <div class="campo">
            <label>Telefone</label>
            <input type="tel" name="telefone" id="telefone" placeholder="Ex.: (44) 99999-9999" inputmode="numeric" maxlength="15" oninput="mascararTelefone(this)" value="<?= htmlspecialchars($telefone_valor) ?>">
        </div>
        <div class="campo">
            <label>Senha</label>
            <div class="senha-wrapper">
                <input type="password" name="senha" id="senha" placeholder="Crie uma senha" required minlength="6">
                <i class="bi bi-eye-slash" id="toggle-senha" onclick="toggleSenha('senha', 'toggle-senha')"></i>
            </div>
        </div>
        <div class="campo">
            <label>Confirmar senha</label>
            <div class="senha-wrapper">
                <input type="password" name="confirmar_senha" id="confirmar_senha" placeholder="Digite a senha novamente" required minlength="6">
                <i class="bi bi-eye-slash" id="toggle-confirmar-senha" onclick="toggleSenha('confirmar_senha', 'toggle-confirmar-senha')"></i>
            </div>
            <p id="msg-senha-diferente" class="msg-senha-diferente d-none">As senhas não coincidem.</p>
        </div>
        <div class="campo-check">
            <input type="checkbox" id="novidades" name="novidades">
            <label for="novidades">Enviar novidades e ofertas para mim por e-mail</label>
        </div>
        <button type="submit" class="btn-cadastrar">CRIAR CONTA</button>
        <p class="ja-tem-conta">Já tem uma conta? <a href="login.php">Faça login</a></p>
    </form>
</div>

<style>
main {
    display: flex;
    align-items: center;
    justify-content: center;
}
.cadastro-container {
    max-width: 560px;
    width: 100%;
    padding: 24px 40px;
}
.cadastro-titulo {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    color: #fff;
    letter-spacing: 1px;
    text-transform: none;
    margin-bottom: 20px;
}
.cadastro-form { display: flex; flex-direction: column; gap: 12px; }
.campo { display: flex; flex-direction: column; gap: 4px; }
.campo label { font-size: 0.9rem; color: #ccc; letter-spacing: 1px; }
.campo input {
    background: #fff;
    border: 1px solid #ddd;
    padding: 14px;
    font-size: 1.05rem;
    color: #333;
    outline: none;
    border-radius: 2px;
    width: 100%;
}
.campo input:focus { border-color: #999; }
.senha-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.senha-wrapper input {
    padding-right: 48px;
    width: 100%;
}
.senha-wrapper .bi {
    position: absolute;
    right: 14px;
    color: #999;
    cursor: pointer;
    font-size: 1.1rem;
    user-select: none;
}
.campo-check {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 4px;
}
.campo-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #8b1a1a;
    flex-shrink: 0;
}
.campo-check label {
    font-size: 0.85rem;
    color: #ccc;
    cursor: pointer;
    letter-spacing: 0.5px;
}
.btn-cadastrar {
    background: #8b1a1a;
    color: #fff;
    border: none;
    padding: 13px;
    font-size: 0.9rem;
    letter-spacing: 3px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
    transition: opacity 0.2s;
    margin-top: 4px;
}
.btn-cadastrar:hover { opacity: 0.85; }
.ja-tem-conta { text-align: center; font-size: 0.9rem; color: #aaa; margin: 0; }
.ja-tem-conta a { color: #8b1a1a; text-decoration: none; }
.ja-tem-conta a:hover { text-decoration: underline; }
.msg-erro {
    background: #8b1a1a;
    color: #fff;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    border-radius: 2px;
}
.msg-sucesso {
    background: #1a4d1a;
    color: #fff;
    padding: 12px 16px;
    margin-bottom: 20px;
    font-size: 0.9rem;
    border-radius: 2px;
}
.msg-sucesso a { color: #fff; text-decoration: underline; }
.msg-senha-diferente {
    color: #e05252;
    font-size: 0.8rem;
    margin: 4px 0 0;
}
</style>

<script>
function apenasLetras(input) {
    input.value = input.value.replace(/[^a-zA-ZÀ-ÿ\s]/g, '');
}

function mascararTelefone(input) {
    let v = input.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 10) {
        v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (v.length > 5) {
        v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    } else if (v.length > 2) {
        v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
    } else if (v.length > 0) {
        v = v.replace(/(\d*)/, '($1');
    }
    input.value = v;
}

function toggleSenha(idInput, idIcone) {
    var input = document.getElementById(idInput);
    var icon = document.getElementById(idIcone);
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

document.querySelector('.cadastro-form').addEventListener('submit', function (e) {
    var senha = document.getElementById('senha').value;
    var confirmar = document.getElementById('confirmar_senha').value;
    var msg = document.getElementById('msg-senha-diferente');

    if (senha !== confirmar) {
        e.preventDefault();
        msg.classList.remove('d-none');
    } else {
        msg.classList.add('d-none');
    }
});
</script>
<?php include 'templates/footer.php'; ?>