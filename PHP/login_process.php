<?php
session_start();

require 'conexao.php'; // como está dentro da mesma pasta, é isso mesmo

$email = trim($_POST['email']);
$senha = trim($_POST['senha']);

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($senha, $user['senha'])) {
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_nome'] = $user['nome'];
    header('Location: dashboard.php');
    exit;
}

header('Location: ../login.php?erro=1');
exit;
