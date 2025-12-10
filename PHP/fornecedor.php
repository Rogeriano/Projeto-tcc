<?php
require 'protege.php';
require 'conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $cnpj = $_POST['cnpj'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $email = $_POST['email'] ?? '';
    $endereco = $_POST['endereco'] ?? '';

    if ($nome) {
        $stmt = $pdo->prepare("INSERT INTO fornecedores (nome, cnpj, telefone, email, endereco) 
                               VALUES (:nome, :cnpj, :telefone, :email, :endereco)");
        $stmt->execute([
            ':nome' => $nome,
            ':cnpj' => $cnpj,
            ':telefone' => $telefone,
            ':email' => $email,
            ':endereco' => $endereco
        ]);
        $mensagem = "Fornecedor cadastrado com sucesso!";
    } else {
        $mensagem = "Preencha o nome do fornecedor.";
    }
}
?>

<h1>Cadastro de Fornecedores</h1>
<?php if ($mensagem) echo "<p>$mensagem</p>"; ?>
<form method="post">
    <label>Nome:</label><br>
    <input type="text" name="nome" required><br><br>

    <label>CNPJ:</label><br>
    <input type="text" name="cnpj"><br><br>

    <label>Telefone:</label><br>
    <input type="text" name="telefone"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email"><br><br>

    <label>Endereço:</label><br>
    <input type="text" name="endereco"><br><br>

    <button type="submit">Cadastrar Fornecedor</button>
</form>
