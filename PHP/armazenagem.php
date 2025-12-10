<?php
require 'conexao.php';

$erro = '';
$sucesso = '';

// =====================
// Recebimento de mercadoria
// =====================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['receber'])) {
    $produto_id = intval($_POST['produto_id']);
    $quantidade = intval($_POST['quantidade']);
    $lote = trim($_POST['lote']);
    $codigo_barra = trim($_POST['codigo_barra']);
    $rua = trim($_POST['rua']);
    $prateleira = trim($_POST['prateleira']);
    $nivel = trim($_POST['nivel']);
    $localizacao = "$rua - Prateleira $prateleira - Nível $nivel";

    if ($produto_id == 0 || $quantidade <= 0) {
        $erro = "Produto e quantidade são obrigatórios!";
    } else {
        // Inserir no estoque
        $stmt = $pdo->prepare("INSERT INTO estoque (produto_id, quantidade, lote, codigo_barra, localizacao_atual) 
                               VALUES (:produto_id, :quantidade, :lote, :codigo_barra, :localizacao)");
        $stmt->execute([
            'produto_id'=>$produto_id,
            'quantidade'=>$quantidade,
            'lote'=>$lote,
            'codigo_barra'=>$codigo_barra,
            'localizacao'=>$localizacao
        ]);

        // Inserir histórico
        $stmt2 = $pdo->prepare("INSERT INTO historico_entrada (produto_id, quantidade, lote, codigo_barra, localizacao) 
                                VALUES (:produto_id, :quantidade, :lote, :codigo_barra, :localizacao)");
        $stmt2->execute([
            'produto_id'=>$produto_id,
            'quantidade'=>$quantidade,
            'lote'=>$lote,
            'codigo_barra'=>$codigo_barra,
            'localizacao'=>$localizacao
        ]);

        $sucesso = "Mercadoria recebida com sucesso!";

        // Excluir automaticamente se quantidade = 0
        $stmtExcluir = $pdo->prepare("DELETE FROM estoque WHERE produto_id = :produto_id AND lote = :lote AND quantidade <= 0");
        $stmtExcluir->execute([
            'produto_id' => $produto_id,
            'lote' => $lote
        ]);
    }
}

// =====================
// Transferência de estoque
// =====================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transferir'])) {
    $estoque_id = intval($_POST['estoque_id']);
    $rua = trim($_POST['rua']);
    $prateleira = trim($_POST['prateleira']);
    $nivel = trim($_POST['nivel']);
    $nova_localizacao = "$rua - Prateleira $prateleira - Nível $nivel";

    if ($estoque_id > 0 && $nova_localizacao != '') {
        $stmt = $pdo->prepare("UPDATE estoque SET localizacao_atual = :nova_localizacao WHERE id = :id");
        $stmt->execute([
            'nova_localizacao' => $nova_localizacao,
            'id' => $estoque_id
        ]);

        // Excluir automaticamente se quantidade = 0
        $stmtExcluir = $pdo->prepare("DELETE FROM estoque WHERE id = :id AND quantidade <= 0");
        $stmtExcluir->execute(['id' => $estoque_id]);

        $sucesso = "Produto transferido com sucesso!";
    } else {
        $erro = "Selecione um produto e informe a nova localização.";
    }
}

// =====================
// Buscar produtos para select
// =====================
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC")->fetchAll();

// =====================
// Limpar estoque com quantidade 0 antes de exibir
// =====================
$pdo->query("DELETE FROM estoque WHERE quantidade <= 0");

// =====================
// Buscar estoque
// =====================
$stmt = $pdo->query("
    SELECT 
        e.id AS estoque_id,
        p.nome,
        p.codigo_barra,
        e.quantidade,
        e.lote,
        e.localizacao_atual
    FROM estoque e
    INNER JOIN produtos p ON e.produto_id = p.id
    ORDER BY p.nome, e.lote
");
$estoque = $stmt->fetchAll();
?>

<h1>Armazenagem e Recebimento de Estoque</h1>

<!-- Mensagens -->
<?php if ($erro) echo "<p style='color:red;'>$erro</p>"; ?>
<?php if ($sucesso) echo "<p style='color:green;'>$sucesso</p>"; ?>

<!-- Formulário de Recebimento -->
<h2>Recebimento de Mercadorias</h2>
<form method="POST">
    <select name="produto_id" required>
        <option value="">Selecione o produto</option>
        <?php foreach($produtos as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?> (<?= htmlspecialchars($p['codigo_barra']) ?>)</option>
        <?php endforeach; ?>
    </select>

    <input type="number" name="quantidade" placeholder="Quantidade" required>
    <input type="text" name="lote" placeholder="Lote">
    <input type="text" name="codigo_barra" placeholder="Código de barras" required>
    <input type="text" name="rua" placeholder="Rua" required>
    <input type="text" name="prateleira" placeholder="Prateleira" required>
    <input type="text" name="nivel" placeholder="Nível" required>

    <button type="submit" name="receber">Registrar Recebimento</button>
</form>

<!-- Tabela de Estoque -->
<h2>Estoque Atual</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Produto</th>
        <th>Código de Barras</th>
        <th>Quantidade</th>
        <th>Lote</th>
        <th>Localização</th>
        <th>Transferir</th>
    </tr>
    <?php foreach($estoque as $e): ?>
    <tr>
        <td><?= htmlspecialchars($e['nome']) ?></td>
        <td><?= htmlspecialchars($e['codigo_barra']) ?></td>
        <td><?= $e['quantidade'] ?></td>
        <td><?= htmlspecialchars($e['lote']) ?></td>
        <td><?= htmlspecialchars($e['localizacao_atual']) ?></td>
        <td>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="estoque_id" value="<?= $e['estoque_id'] ?>">
                <input type="text" name="rua" placeholder="Rua" required>
                <input type="text" name="prateleira" placeholder="Prateleira" required>
                <input type="text" name="nivel" placeholder="Nível" required>
                <button type="submit" name="transferir">Transferir</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
