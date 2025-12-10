<?php
require 'conexao.php';

$erro = '';
$sucesso = '';

// =====================
// Processar recebimento
// =====================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produto_id = intval($_POST['produto_id']);
    $quantidade = intval($_POST['quantidade']);
    $lote = trim($_POST['lote']);
    $validade = $_POST['validade'] ?: null;
    $localizacao = trim($_POST['localizacao']);

    if ($produto_id == 0 || $quantidade <= 0) {
        $erro = "Produto e quantidade são obrigatórios!";
    } else {
        // Inserir no estoque
        $stmt = $pdo->prepare("
            INSERT INTO estoque (produto_id, quantidade, lote, validade, localizacao_atual, data_recebimento)
            VALUES (:produto_id, :quantidade, :lote, :validade, :localizacao, NOW())
        ");
        $stmt->execute([
            'produto_id' => $produto_id,
            'quantidade' => $quantidade,
            'lote' => $lote,
            'validade' => $validade,
            'localizacao' => $localizacao
        ]);

        // Inserir histórico de entrada
        $stmt2 = $pdo->prepare("
            INSERT INTO historico_entrada (produto_id, quantidade, lote, validade, fornecedor_id, localizacao, data_entrada)
            VALUES (:produto_id, :quantidade, :lote, :validade, NULL, :localizacao, NOW())
        ");
        $stmt2->execute([
            'produto_id' => $produto_id,
            'quantidade' => $quantidade,
            'lote' => $lote,
            'validade' => $validade,
            'localizacao' => $localizacao
        ]);

        $sucesso = "Mercadoria recebida com sucesso!";
    }
}

// =====================
// Buscar produtos para select
// =====================
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC")->fetchAll();
?>

<h1>Recebimento de Mercadorias</h1>

<?php if ($erro) echo "<p style='color:red;'>$erro</p>"; ?>
<?php if ($sucesso) echo "<p style='color:green;'>$sucesso</p>"; ?>

<form method="POST">
    <select name="produto_id" required>
        <option value="">Selecione o produto</option>
        <?php foreach($produtos as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
        <?php endforeach; ?>
    </select>

    <input type="number" name="quantidade" placeholder="Quantidade" required>
    <input type="text" name="lote" placeholder="Lote">
    <input type="date" name="validade" placeholder="Validade">
    <input type="text" name="localizacao" placeholder="Localização atual">

    <button type="submit">Registrar Recebimento</button>
</form>
