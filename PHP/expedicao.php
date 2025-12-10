<?php
require 'conexao.php';
// session_start(); <- REMOVIDO, pois já iniciado no dashboard

// ======================
// Mensagens
// ======================
$erro = '';
$sucesso = '';

// ======================
// Define usuário da sessão
// ======================
$usuario = $_SESSION['nome'] ?? 'Desconhecido';

// ======================
// Solicitar expedição
// ======================
if(isset($_POST['solicitar'])){
    $produto_id = intval($_POST['produto_id']);
    $quantidade = intval($_POST['quantidade']);
    $lote = trim($_POST['lote']);
    $validade = $_POST['validade'] ?: null;
    $rua = trim($_POST['rua']);
    $prateleira = trim($_POST['prateleira']);
    $nivel = trim($_POST['nivel']);
    $cliente = trim($_POST['cliente']);
    $endereco_cliente = trim($_POST['endereco_cliente']);

    if($produto_id == 0 || $quantidade <= 0 || $cliente == ''){
        $erro = "Produto, quantidade e cliente são obrigatórios!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO expedicao 
            (produto_id, quantidade, lote, validade, rua, prateleira, nivel, cliente, endereco_cliente, status, data_solicitacao) 
            VALUES (:produto_id, :quantidade, :lote, :validade, :rua, :prateleira, :nivel, :cliente, :endereco_cliente, 'pendente', NOW())");
        $stmt->execute([
            'produto_id'=>$produto_id,
            'quantidade'=>$quantidade,
            'lote'=>$lote,
            'validade'=>$validade,
            'rua'=>$rua,
            'prateleira'=>$prateleira,
            'nivel'=>$nivel,
            'cliente'=>$cliente,
            'endereco_cliente'=>$endereco_cliente
        ]);
        $sucesso = "Expedição solicitada com sucesso!";
    }
}

// ======================
// Concluir expedição via POST
// ======================
if(isset($_POST['concluir_id'])){
    $id = intval($_POST['concluir_id']);

    // Busca o pedido pendente
    $stmt = $pdo->prepare("SELECT * FROM expedicao WHERE id=:id AND status='pendente'");
    $stmt->execute(['id'=>$id]);
    $pedido = $stmt->fetch();

    if($pedido){
        // Verifica estoque disponível na tabela estoque
        $stmtEstoque = $pdo->prepare("SELECT quantidade FROM estoque 
                                      WHERE produto_id=:produto_id AND lote=:lote");
        $stmtEstoque->execute([
            'produto_id' => $pedido['produto_id'],
            'lote' => $pedido['lote']
        ]);
        $estoque = $stmtEstoque->fetch();

        if(!$estoque || $estoque['quantidade'] < $pedido['quantidade']){
            $erro = "Não há estoque suficiente para concluir a expedição do produto '{$pedido['produto_id']}' (Pedido ID: {$pedido['id']})!";
        } else {
            // Baixa do estoque na tabela estoque
            $stmt2 = $pdo->prepare("UPDATE estoque 
                                    SET quantidade = quantidade - :qtd 
                                    WHERE produto_id=:produto_id AND lote=:lote");
            $stmt2->execute([
                'qtd'=>$pedido['quantidade'],
                'produto_id'=>$pedido['produto_id'],
                'lote'=>$pedido['lote']
            ]);

            // Histórico
            $stmt3 = $pdo->prepare("INSERT INTO historico_movimentacao 
                                    (produto_id, tipo, quantidade, lote, validade, usuario, localizacao, cliente, endereco_cliente, data_movimentacao) 
                                    VALUES (:produto_id,'saida',:quantidade,:lote,:validade,:usuario,:localizacao,:cliente,:endereco_cliente,NOW())");
            $stmt3->execute([
                'produto_id'=>$pedido['produto_id'],
                'quantidade'=>$pedido['quantidade'],
                'lote'=>$pedido['lote'],
                'validade'=>$pedido['validade'],
                'usuario'=>$usuario,
                'localizacao'=>$pedido['rua'].'/'.$pedido['prateleira'].'/'.$pedido['nivel'],
                'cliente'=>$pedido['cliente'],
                'endereco_cliente'=>$pedido['endereco_cliente']
            ]);

            // Atualiza status da expedição
            $stmt4 = $pdo->prepare("UPDATE expedicao SET status='concluida', data_saida=NOW() WHERE id=:id");
            $stmt4->execute(['id'=>$id]);

            $sucesso = "Expedição concluída com sucesso!";
        }
    } else {
        $erro = "Pedido não encontrado ou já concluído!";
    }
}

// ======================
// Buscar produtos e expedições
// ======================
$produtos = $pdo->query("SELECT id, nome, codigo_barra FROM produtos ORDER BY nome ASC")->fetchAll();
$expedicoes = $pdo->query("SELECT e.*, p.nome AS produto_nome 
                           FROM expedicao e 
                           JOIN produtos p ON e.produto_id=p.id 
                           ORDER BY e.data_solicitacao DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Expedição de Produtos</title>
</head>
<body>

<h1>Expedição de Produtos</h1>

<?php if($erro) echo "<p style='color:red;'>$erro</p>"; ?>
<?php if($sucesso) echo "<p style='color:green;'>$sucesso</p>"; ?>

<!-- Formulário para solicitar expedição -->
<form method="POST" action="">
    <select name="produto_id" required>
        <option value="">Selecione o produto</option>
        <?php foreach($produtos as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']).' ('.$p['codigo_barra'].')' ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="quantidade" placeholder="Quantidade" required>
    <input type="text" name="lote" placeholder="Lote">
    <input type="date" name="validade" placeholder="Validade">
    <input type="text" name="rua" placeholder="Rua">
    <input type="text" name="prateleira" placeholder="Prateleira">
    <input type="text" name="nivel" placeholder="Nível">
    <input type="text" name="cliente" placeholder="Nome do cliente/filial" required>
    <input type="text" name="endereco_cliente" placeholder="Endereço do cliente">
    <button type="submit" name="solicitar">Solicitar Expedição</button>
</form>

<h2>Pedidos de Expedição</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Produto</th>
        <th>Quantidade</th>
        <th>Lote</th>
        <th>Validade</th>
        <th>Localização</th>
        <th>Cliente</th>
        <th>Endereço</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>
    <?php foreach($expedicoes as $e): ?>
    <tr>
        <td><?= $e['id'] ?></td>
        <td><?= htmlspecialchars($e['produto_nome']) ?></td>
        <td><?= $e['quantidade'] ?></td>
        <td><?= htmlspecialchars($e['lote']) ?></td>
        <td><?= $e['validade'] ?></td>
        <td><?= $e['rua'].'/'.$e['prateleira'].'/'.$e['nivel'] ?></td>
        <td><?= htmlspecialchars($e['cliente']) ?></td>
        <td><?= htmlspecialchars($e['endereco_cliente']) ?></td>
        <td><?= $e['status'] ?></td>
        <td>
            <?php if($e['status']=='pendente'): ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="concluir_id" value="<?= $e['id'] ?>">
                    <button type="submit" onclick="return confirm('Confirmar saída?')">Concluir</button>
                </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
