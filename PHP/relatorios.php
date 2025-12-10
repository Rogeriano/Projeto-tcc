<?php
require 'conexao.php';
session_start();

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$categoria_id = $_GET['categoria_id'] ?? '';

// Buscar categorias
$categorias_stmt = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
$categorias = $categorias_stmt->fetchAll();

// ======================
// Produtos em estoque
// ======================
$where_produtos = [];
$params_produtos = [];

if ($categoria_id != '') {
    $where_produtos[] = "p.categoria_id = :categoria_id";
    $params_produtos['categoria_id'] = $categoria_id;
}

$sql_produtos = "SELECT 
                    p.id, 
                    p.nome, 
                    p.codigo_barra, 
                    p.preco_custo, 
                    p.preco_venda, 
                    p.localizacao,
                    c.nome AS categoria_nome,
                    COALESCE(SUM(e.quantidade),0) AS quantidade_total,
                    COALESCE(SUM(e.quantidade) * p.preco_custo,0) AS valor_total_custo,
                    COALESCE(SUM(e.quantidade) * p.preco_venda,0) AS valor_total_venda
                 FROM produtos p
                 LEFT JOIN categorias c ON p.categoria_id = c.id
                 LEFT JOIN estoque e ON p.id = e.produto_id";

if (count($where_produtos)) {
    $sql_produtos .= " WHERE " . implode(' AND ', $where_produtos);
}

$sql_produtos .= " GROUP BY p.id, p.nome, p.codigo_barra, p.preco_custo, p.preco_venda, p.localizacao, c.nome
                   ORDER BY p.nome ASC";

$stmt_produtos = $pdo->prepare($sql_produtos);
$stmt_produtos->execute($params_produtos);
$produtos = $stmt_produtos->fetchAll();

?>

<h1>Relatório de Estoque</h1>

<!-- Filtros -->
<form method="GET" style="margin-bottom:20px;">
    <input type="hidden" name="page" value="relatorios">
    <label>Data início: <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>"></label>
    <label>Data fim: <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>"></label>
    <label>Categoria:
        <select name="categoria_id">
            <option value="">Todas</option>
            <?php foreach($categorias as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($categoria_id == $c['id'])?'selected':'' ?>><?= htmlspecialchars($c['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button type="submit">Filtrar</button>
</form>

<!-- Tabela de produtos -->
<h2>Produtos em Estoque</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Categoria</th>
        <th>Código de Barras</th>
        <th>Localização</th>
        <th>Quantidade</th>
        <th>Preço Custo</th>
        <th>Preço Venda</th>
        <th>Valor Total Custo</th>
        <th>Valor Total Venda</th>
    </tr>
    <?php foreach($produtos as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['nome']) ?></td>
        <td><?= htmlspecialchars($p['categoria_nome']) ?></td>
        <td><?= htmlspecialchars($p['codigo_barra']) ?></td>
        <td><?= htmlspecialchars($p['localizacao']) ?></td>
        <td><?= $p['quantidade_total'] ?></td>
        <td><?= number_format($p['preco_custo'],2,",",".") ?></td>
        <td><?= number_format($p['preco_venda'],2,",",".") ?></td>
        <td><?= number_format($p['valor_total_custo'],2,",",".") ?></td>
        <td><?= number_format($p['valor_total_venda'],2,",",".") ?></td>
    </tr>
    <?php endforeach; ?>
</table>
