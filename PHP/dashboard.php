
<?php
require 'protege.php';
require 'conexao.php';
?>

<?php 
$page = $_GET['page'] ?? 'home'; 
$whitelist = ['home','produtos','categorias','expedicao','armazenagem','relatorios', 'fornecedor','movimentacoes','recebimento','expedicao'];


if(!in_array($page, $whitelist)) {
    $page = '404';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel com Sidebar</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/movimentacoes.css">
    <link rel="stylesheet" href="../css/produtos_cadastrar.css">
    <link rel="stylesheet" href="../css/categorias.css">
    <link rel="stylesheet" href="../css/home.css">



</head>
<body>

<nav class="navbar">
    <button id="btnMenu">☰</button>
    <h2>Painel Admin</h2>
    <a href="logout.php" class="logout-btn">Sair</a>
</nav>


<div id="sidebar" class="sidebar">
    <a href="dashboard.php?page=home">Home</a>
    <a href="dashboard.php?page=categorias">Categorias</a>
    <a href="dashboard.php?page=produtos">Produtos</a>
    <a href="dashboard.php?page=relatorios">Relatórios</a>
    <a href="dashboard.php?page=movimentacoes">Movimentações</a>
    <a href="dashboard.php?page=recebimento">Recebimento</a>
    <a href="dashboard.php?page=expedicao">Expedição</a>
    <a href="dashboard.php?page=fornecedor">Fornecedor</a>
    <a href="dashboard.php?page=armazenagem">armazenagem</a>
    <a href="dashboard.php?page=expedicao">Expedição</a>
    
</div>


<div class="content">
    <?php 
        if($page === '404'){
            echo "<h1>404 - Página não encontrada</h1>";
        } else {
            include "../php/$page.php";
        }
    ?>
</div>

<script src="../js/script.js"></script>
</body>
</html>
