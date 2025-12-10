<?php
require 'conexao.php';

// =====================
// Ação de deletar
// =====================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
    $stmt->execute(['id' => $id]);
    header("Location: dashboard.php?page=categorias");
    exit;
}

// =====================
// Ação de adicionar/editar
// =====================
$edit = false;
$erro = '';

if (isset($_GET['edit'])) {
    $edit = true;
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $categoria = $stmt->fetch();
    if (!$categoria) {
        die("Categoria não encontrada!");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    
    if ($nome == '') {
        $erro = "O nome da categoria não pode ficar vazio!";
    } else {
        if ($edit) {
            $stmt = $pdo->prepare("UPDATE categorias SET nome = :nome WHERE id = :id");
            $stmt->execute(['nome' => $nome, 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (:nome)");
            $stmt->execute(['nome' => $nome]);
        }
        header("Location: dashboard.php?page=categorias");
        exit;
    }
}

// =====================
// Listagem e pesquisa
// =====================
$pesquisa = $_GET['pesquisa'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE nome LIKE :pesquisa ORDER BY nome ASC");
$stmt->execute(['pesquisa' => "%$pesquisa%"]);
$categorias = $stmt->fetchAll();
?>

<h1><?= $edit ? 'Editar Categoria' : 'Categorias' ?></h1>

<!-- Formulário de adicionar/editar -->
<form method="POST" style="margin-bottom:20px;">
    <input type="text" name="nome" placeholder="Nome da categoria" value="<?= $edit ? htmlspecialchars($categoria['nome']) : '' ?>" required>
    <button type="submit"><?= $edit ? 'Salvar' : 'Adicionar' ?></button>
    <?php if ($edit): ?>
        <a href="dashboard.php?page=categorias">Cancelar</a>
    <?php endif; ?>
</form>

<?php if ($erro) echo "<p style='color:red;'>$erro</p>"; ?>

<!-- Pesquisa -->
<form method="GET" style="margin-bottom:20px;">
    <input type="hidden" name="page" value="categorias">
    <input type="text" name="pesquisa" placeholder="Pesquisar categoria" value="<?= htmlspecialchars($pesquisa) ?>">
    <button type="submit">Pesquisar</button>
</form>

<!-- Lista de categorias -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($categorias as $cat): ?>
    <tr>
        <td><?= $cat['id'] ?></td>
        <td><?= htmlspecialchars($cat['nome']) ?></td>
        <td>
            <a href="dashboard.php?page=categorias&edit=<?= $cat['id'] ?>">Editar</a> | 
            <a href="dashboard.php?page=categorias&delete=<?= $cat['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
