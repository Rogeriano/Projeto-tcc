<?php
require 'conexao.php';

// ======================
// Produtos
// ======================

// Total de produtos
$totalProdutos = $pdo->query("SELECT COUNT(*) AS total FROM produtos")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Estoque disponível (quantidade > 0)
$totalArmazenagem = $pdo->query("SELECT COUNT(*) AS total FROM estoque WHERE quantidade > 0")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Produtos próximos da validade (até 30 dias)
$proximosValidade = $pdo->query("
    SELECT COUNT(*) AS total 
    FROM estoque 
    WHERE validade IS NOT NULL AND validade <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Estoque baixo (quantidade <= 5)
$estoqueBaixo = $pdo->query("
    SELECT COUNT(*) AS total 
    FROM estoque 
    WHERE quantidade <= 5
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// ======================
// Movimentações
// ======================

// Total de expedições
$totalExpedicao = $pdo->query("
    SELECT COUNT(*) AS total 
    FROM estoque_movimentacoes 
    WHERE tipo = 'saida'
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// ======================
// Valores em R$
// ======================

// Valor total do estoque
$totalValorEstoque = $pdo->query("
    SELECT SUM(e.quantidade * p.preco_custo) AS total
    FROM estoque e
    JOIN produtos p ON e.produto_id = p.id
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Valor total de expedições concluídas
$totalValorExpedicao = $pdo->query("
    SELECT SUM(e.quantidade * p.preco_venda) AS total
    FROM expedicao e
    JOIN produtos p ON e.produto_id = p.id
    WHERE e.status = 'concluida'
")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// ======================
// Relatórios (exemplo)
$totalRelatorios = 0; // ajustar se tiver tabela de logs
?>

<!-- ======================
     Cards Dashboard
====================== -->
<div class="cards">
    <div class="card">
        <h2>Total de Produtos</h2>
        <p><?= $totalProdutos ?></p>
    </div>
    <div class="card">
        <h2>Estoque Baixo</h2>
        <p><?= $estoqueBaixo ?></p>
    </div>
    <div class="card">
        <h2>Próx. da Validade</h2>
        <p><?= $proximosValidade ?></p>
    </div>
    <div class="card">
        <h2>Expedições</h2>
        <p><?= $totalExpedicao ?></p>
    </div>
    <div class="card">
        <h2>Armazenagem</h2>
        <p><?= $totalArmazenagem ?></p>
    </div>
    <div class="card">
        <h2>Valor Estoque</h2>
        <p>R$ <?= number_format($totalValorEstoque,2,',','.') ?></p>
    </div>
    <div class="card">
        <h2>Valor Expedições</h2>
        <p>R$ <?= number_format($totalValorExpedicao,2,',','.') ?></p>
    </div>
</div>

<!-- ======================
     Gráfico de Movimentações
====================== -->
<div class="chart-container">
    <canvas id="graficoMovimentacoes"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctxMov = document.getElementById('graficoMovimentacoes');
new Chart(ctxMov, {
    type: 'bar',
    data: {
        labels: ['Expedição', 'Armazenagem'],
        datasets: [
            {
                label: 'Quantidade',
                data: [<?= $totalExpedicao ?>, <?= $totalArmazenagem ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'Valor (R$)',
                data: [<?= $totalValorExpedicao ?>, <?= $totalValorEstoque ?>],
                backgroundColor: 'rgba(255, 206, 86, 0.6)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
    }
});
</script>

<style>
.cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.card {
    background-color: #f5f5f5;
    padding: 20px;
    border-radius: 8px;
    flex: 1 1 200px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
}
.card h2 {
    margin-bottom: 10px;
    font-size: 1.2rem;
}
.card p {
    font-size: 1.8rem;
    font-weight: bold;
    margin: 0;
}
.chart-container {
    width: 100%;
    max-width: 900px;
}
</style>
