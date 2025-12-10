<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: php/dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Sistema de Estoque</title>
<style>
body { font-family: Arial; background:#f4f6f8; padding:40px;}
.box{
    max-width:340px; margin:0 auto; background:#fff; padding:25px;
    border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1);
}
.input{width:100%; padding:10px; margin-bottom:10px; border:1px solid #ccc; border-radius:6px}
.btn{width:100%; padding:10px; background:#1e90ff; color:#fff; border:0; border-radius:6px; cursor:pointer}
.error{background:#ffe6e6; padding:10px; border:1px solid #ffb3b3; margin-bottom:10px; border-radius:6px}
</style>
</head>
<body>

<div class="box">
    <h2>Login</h2>

    <?php if(isset($_GET['erro'])): ?>
        <div class="error">Usuário ou senha incorretos</div>
    <?php endif; ?>

    <form action="php/login_process.php" method="post">
        <input class="input" type="email" name="email" placeholder="Email" required>
        <input class="input" type="password" name="senha" placeholder="Senha" required>
        <button class="btn" type="submit">Entrar</button>
    </form>
</div>

</body>
</html>
