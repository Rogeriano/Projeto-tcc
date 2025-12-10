<?php
require 'conexao.php';

// Inicializa variáveis
$erro = '';
$usuario_id = $_SESSION['usuario_id'] ?? 0;

// =====================
// Registrar movimentação
// =====================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produto_id = intval($_POST['produto_id']);
    $tipo = $_POST['tipo']; // 'entrada' ou 'saida'
    $quantidade = intval($_POST['quantidade']);
    $motivo = trim($_POST['motivo']);
    
    if ($produto_id == 0 || $quantidade <= 0 || !in_array($tipo, ['entrada','saida'])) {
        $erro = "Preencha todos os campos corretamente!";
    } else {
        // Inserir movimentação
        $stmt = $pdo->prepare("INSERT INTO estoque_movimentacoes (produto_id, tipo, quantidade, usuario_id, motivo) VALUES (:produto_id,:tipo,:quantidade,:usuario_id,:motivo)");
        $stmt->execute([
            'produto_id'=>$produto_id,
            'tipo'=>$tipo,
            'quantidade'=>$quantidade,
            'usuario_id'=>$usuario_id,
            'motivo'=>$motivo
        ]);

        // Atualizar estoque na tabela produtos
        if ($tipo == 'entrada') {
            $pdo->prepare("UPDATE produtos SET quantidade = quantidade + :q WHERE id = :id")
                ->execute(['q'=>$quantidade,'id'=>$produto_id]);
        } else {
            $pdo->prepare("UPDATE produtos SET quantidade = quantidade - :q WHERE id = :id")
                ->execute(['q'=>$quantidade,'id'=>$produto_id]);
        }

        header("Location: dashboard.php?page=movimentacoes");
        exit;
    }
}

// =====================
// Listagem e pesquisa
// =====================
$pesquisa = $_GET['pesquisa'] ?? '';
$stmt = $pdo->prepare("SELECT em.*, p.nome AS produto_nome, u.nome AS usuario_nome 
                       FROM estoque_movimentacoes em
                       JOIN produtos p ON em.produto_id = p.id
                       JOIN usuarios u ON em.usuario_id = u.id
                       WHERE p.nome LIKE :pesquisa OR em.tipo LIKE :pesquisa OR em.motivo LIKE :pesquisa
                       ORDER BY em.data_movimentacao DESC");
$stmt->execute(['pesquisa'=>"%$pesquisa%"]);
$movimentacoes = $stmt->fetchAll();

// Buscar produtos para o select
$produtos_stmt = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome ASC");
$produtos = $produtos_stmt->fetchAll();
?>

<h1>Movimentações de Estoque</h1>

<!-- Formulário de registro -->
<form method="POST" style="margin-bottom:20px;">
    <select name="produto_id" required>
        <option value="">Selecione o produto</option>
        <?php foreach($produtos as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="tipo" required>
        <option value="">Tipo</option>
        <option value="entrada">Entrada</option>
        <option value="saida">Saída</option>
    </select>

    <input type="number" name="quantidade" placeholder="Quantidade" min="1" required>
    <input type="text" name="motivo" placeholder="Motivo (Ex: Venda, Reposição, Perda)" required>

    <button type="submit">Registrar</button>
</form>

<?php if($erro) echo "<p style='color:red;'>$erro</p>"; ?>

<!-- Pesquisa -->
<form method="GET" style="margin-bottom:20px;">
    <input type="hidden" name="page" value="movimentacoes">
    <input type="text" name="pesquisa" placeholder="Pesquisar por produto, tipo ou motivo" value="<?= htmlspecialchars($pesquisa) ?>">
    <button type="submit">Pesquisar</button>
</form>

<!-- Lista de movimentações -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Produto</th>
        <th>Tipo</th>
        <th>Quantidade</th>
        <th>Motivo</th>
        <th>Usuário</th>
        <th>Data</th>
    </tr>
    <?php foreach($movimentacoes as $m): ?>
    <tr>
        <td><?= $m['id'] ?></td>
        <td><?= htmlspecialchars($m['produto_nome']) ?></td>
        <td><?= ucfirst($m['tipo']) ?></td>
        <td><?= $m['quantidade'] ?></td>
        <td><?= htmlspecialchars($m['motivo']) ?></td>
        <td><?= htmlspecialchars($m['usuario_nome']) ?></td>
        <td><?= $m['data_movimentacao'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
