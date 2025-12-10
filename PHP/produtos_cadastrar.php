<?php
require 'conexao.php';

$edit = false;
$erro = '';

if (isset($_GET['edit'])) {
    $edit = true;
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute(['id'=>$id]);
    $produto = $stmt->fetch();
    if (!$produto) die("Produto não encontrado!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim($_POST['codigo']);
    $descricao = trim($_POST['descricao']);
    $categoria_id = intval($_POST['categoria_id']);
    $preco_custo = floatval($_POST['preco_custo']);
    $preco_venda = floatval($_POST['preco_venda']);
    $localizacao_padrao = trim($_POST['localizacao_padrao']);
    $sku = trim($_POST['sku']);

    if ($codigo == '' || $descricao == '' || $categoria_id == 0) {
        $erro = "Código, descrição e categoria são obrigatórios!";
    } else {
        if ($edit) {
            $stmt = $pdo->prepare("UPDATE produtos SET codigo=:codigo, descricao=:descricao, categoria_id=:categoria_id, preco_custo=:preco_custo, preco_venda=:preco_venda, localizacao_padrao=:localizacao_padrao, sku=:sku WHERE id=:id");
            $stmt->execute([
                'codigo'=>$codigo,
                'descricao'=>$descricao,
                'categoria_id'=>$categoria_id,
                'preco_custo'=>$preco_custo,
                'preco_venda'=>$preco_venda,
                'localizacao_padrao'=>$localizacao_padrao,
                'sku'=>$sku,
                'id'=>$id
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO produtos (codigo, descricao, categoria_id, preco_custo, preco_venda, localizacao_padrao, sku) VALUES (:codigo, :descricao, :categoria_id, :preco_custo, :preco_venda, :localizacao_padrao, :sku)");
            $stmt->execute([
                'codigo'=>$codigo,
                'descricao'=>$descricao,
                'categoria_id'=>$categoria_id,
                'preco_custo'=>$preco_custo,
                'preco_venda'=>$preco_venda,
                'localizacao_padrao'=>$localizacao_padrao,
                'sku'=>$sku
            ]);
        }
        header("Location: dashboard.php?page=produtos");
        exit;
    }
}

// Buscar categorias
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();
?>

<h1><?= $edit ? 'Editar Produto' : 'Cadastrar Produto' ?></h1>

<form method="POST">
    <input type="text" name="codigo" placeholder="Código do produto" value="<?= $edit ? htmlspecialchars($produto['codigo']) : '' ?>" required>
    <input type="text" name="descricao" placeholder="Descrição" value="<?= $edit ? htmlspecialchars($produto['descricao']) : '' ?>" required>
    <select name="categoria_id" required>
        <option value="">Selecione a categoria</option>
        <?php foreach($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $edit && $produto['categoria_id']==$cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="number" step="0.01" name="preco_custo" placeholder="Preço de custo" value="<?= $edit ? $produto['preco_custo'] : '' ?>">
    <input type="number" step="0.01" name="preco_venda" placeholder="Preço de venda" value="<?= $edit ? $produto['preco_venda'] : '' ?>">
    <input type="text" name="localizacao_padrao" placeholder="Localização padrão" value="<?= $edit ? htmlspecialchars($produto['localizacao_padrao']) : '' ?>">
    <input type="text" name="sku" placeholder="SKU" value="<?= $edit ? htmlspecialchars($produto['sku']) : '' ?>">

    <button type="submit"><?= $edit ? 'Salvar' : 'Cadastrar' ?></button>
</form>

<?php if ($erro) echo "<p style='color:red;'>$erro</p>"; ?>
