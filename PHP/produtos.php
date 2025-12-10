<?php
require 'conexao.php';

// =====================
// Deletar produto
// =====================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    header("Location: dashboard.php?page=produtos");
    exit;
}

// =====================
// Editar / Adicionar produto
// =====================
$edit = false;
$erro = '';

if (isset($_GET['edit'])) {
    $edit = true;
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $produto = $stmt->fetch();
    if (!$produto) die("Produto não encontrado!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $categoria_id = intval($_POST['categoria_id']);
    $preco_custo = floatval($_POST['preco_custo']);
    $preco_venda = floatval($_POST['preco_venda']);
    $localizacao = trim($_POST['localizacao']);
    $codigo_barra = trim($_POST['codigo_barra']);

    if ($nome == '' || $categoria_id == 0) {
        $erro = "Nome e Categoria são obrigatórios!";
    } else {
        if ($edit) {
            $stmt = $pdo->prepare("
                UPDATE produtos 
                SET nome=:nome, categoria_id=:categoria_id, preco_custo=:preco_custo, preco_venda=:preco_venda, 
                    localizacao=:localizacao, codigo_barra=:codigo_barra 
                WHERE id=:id
            ");
            $stmt->execute([
                'nome'=>$nome,
                'categoria_id'=>$categoria_id,
                'preco_custo'=>$preco_custo,
                'preco_venda'=>$preco_venda,
                'localizacao'=>$localizacao,
                'codigo_barra'=>$codigo_barra,
                'id'=>$id
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO produtos (nome, categoria_id, preco_custo, preco_venda, localizacao, codigo_barra) 
                VALUES (:nome,:categoria_id,:preco_custo,:preco_venda,:localizacao,:codigo_barra)
            ");
            $stmt->execute([
                'nome'=>$nome,
                'categoria_id'=>$categoria_id,
                'preco_custo'=>$preco_custo,
                'preco_venda'=>$preco_venda,
                'localizacao'=>$localizacao,
                'codigo_barra'=>$codigo_barra
            ]);
        }
        header("Location: dashboard.php?page=produtos");
        exit;
    }
}

// =====================
// Listagem / Pesquisa
// =====================
$pesquisa = $_GET['pesquisa'] ?? '';
$stmt = $pdo->prepare("
    SELECT p.*, c.nome AS categoria_nome 
    FROM produtos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    WHERE p.nome LIKE :pesquisa OR p.codigo_barra LIKE :pesquisa
    ORDER BY p.nome ASC
");
$stmt->execute(['pesquisa'=>"%$pesquisa%"]);
$produtos = $stmt->fetchAll();

// Buscar categorias para select
$categoria_stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
$categorias = $categoria_stmt->fetchAll();
?>

<h1><?= $edit ? 'Editar Produto' : 'Produtos' ?></h1>

<!-- Formulário de Adicionar/Editar -->
<form method="POST" style="margin-bottom:20px;">
    <input type="text" name="nome" placeholder="Nome do produto" value="<?= $edit ? htmlspecialchars($produto['nome']) : '' ?>" required>
    
    <select name="categoria_id" required>
        <option value="">Selecione a categoria</option>
        <?php foreach ($categorias as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $edit && $produto['categoria_id']==$cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="number" step="0.01" name="preco_custo" placeholder="Preço de custo" value="<?= $edit ? $produto['preco_custo'] : '' ?>">
    <input type="number" step="0.01" name="preco_venda" placeholder="Preço de venda" value="<?= $edit ? $produto['preco_venda'] : '' ?>">
    <input type="text" name="localizacao" placeholder="Localização" value="<?= $edit ? htmlspecialchars($produto['localizacao']) : '' ?>">
    <input type="text" name="codigo_barra" placeholder="Código de barras" value="<?= $edit ? htmlspecialchars($produto['codigo_barra']) : '' ?>">

    <button type="submit"><?= $edit ? 'Salvar' : 'Adicionar' ?></button>
    <?php if ($edit): ?>
        <a href="dashboard.php?page=produtos">Cancelar</a>
    <?php endif; ?>
</form>

<?php if ($erro) echo "<p style='color:red;'>$erro</p>"; ?>

<!-- Pesquisa -->
<form method="GET" style="margin-bottom:20px;">
    <input type="hidden" name="page" value="produtos">
    <input type="text" name="pesquisa" placeholder="Pesquisar produto ou código de barras" value="<?= htmlspecialchars($pesquisa) ?>">
    <button type="submit">Pesquisar</button>
</form>

<!-- Lista de produtos -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Categoria</th>
        <th>Preço Custo</th>
        <th>Preço Venda</th>
        <th>Localização</th>
        <th>Código de barras</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($produtos as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['nome']) ?></td>
        <td><?= htmlspecialchars($p['categoria_nome']) ?></td>
        <td><?= number_format($p['preco_custo'],2,",",".") ?></td>
        <td><?= number_format($p['preco_venda'],2,",",".") ?></td>
        <td><?= htmlspecialchars($p['localizacao']) ?></td>
        <td><?= htmlspecialchars($p['codigo_barra']) ?></td>
        <td>
            <a href="dashboard.php?page=produtos&edit=<?= $p['id'] ?>">Editar</a> |
            <a href="dashboard.php?page=produtos&delete=<?= $p['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
