<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
if (empty($_SESSION['logado'])) { header('Location: login.php'); exit; }
require 'functions.php';
$dados = ler_json('db.json');
$lancamentos = $dados['lancamentos'] ?? [];
$produtos = $dados['produtos'] ?? [];
$fornecedores = $dados['fornecedores'] ?? [];
$compras = $dados['compras'] ?? [];
$total_entradas = 0;
$total_saidas = 0;
$total_produtos = count($produtos);
$total_fornecedores = count($fornecedores);
$total_compras_valor = array_sum(array_map(fn($c) => $c['valor_total'] ?? 0, $compras));
foreach ($lancamentos as $l) {
    if ($l['tipo'] === 'entrada') $total_entradas += $l['valor'];
    else $total_saidas += $l['valor'];
}
$saldo = $total_entradas - $total_saidas;
$recentes = array_slice(array_reverse($lancamentos), 0, 5);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Estoque & Vendas — Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
  <?php require 'sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Dashboard</h1>
      <span class="page-sub">Visão geral do estoque e vendas</span>
    </div>
    <div class="cards-grid">
      <div class="card card-green">
        <div class="card-icon">↑</div>
        <div class="card-info">
          <span class="card-label">Total Entradas</span>
          <span class="card-value"><?= formatar_moeda($total_entradas) ?></span>
        </div>
      </div>
      <div class="card card-red">
        <div class="card-icon">↓</div>
        <div class="card-info">
          <span class="card-label">Total Saídas</span>
          <span class="card-value"><?= formatar_moeda($total_saidas) ?></span>
        </div>
      </div>
      <div class="card card-blue">
        <div class="card-icon">=</div>
        <div class="card-info">
          <span class="card-label">Saldo</span>
          <span class="card-value"><?= formatar_moeda($saldo) ?></span>
        </div>
      </div>
      <div class="card card-gray">
        <div class="card-icon">📝</div>
        <div class="card-info">
          <span class="card-label">Lançamentos</span>
          <span class="card-value"><?= count($lancamentos) ?></span>
        </div>
      </div>
      <div class="card card-blue">
        <div class="card-icon">📦</div>
        <div class="card-info">
          <span class="card-label">Produtos em Estoque</span>
          <span class="card-value"><?= $total_produtos ?></span>
        </div>
      </div>
      <div class="card card-gray">
        <div class="card-icon">🚚</div>
        <div class="card-info">
          <span class="card-label">Fornecedores</span>
          <span class="card-value"><?= $total_fornecedores ?></span>
        </div>
      </div>
      <div class="card card-red">
        <div class="card-icon">🛒</div>
        <div class="card-info">
          <span class="card-label">Total em Compras</span>
          <span class="card-value"><?= formatar_moeda($total_compras_valor) ?></span>
        </div>
      </div>
    </div>
    <div class="section-box">
      <h2 class="section-title">Últimos Lançamentos</h2>
      <?php if (empty($recentes)): ?>
        <p class="empty-msg">Nenhum lançamento registrado ainda.</p>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr><th>Data</th><th>Descrição</th><th>Categoria</th><th>Tipo</th><th>Valor</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recentes as $l): ?>
          <tr>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($l['data']))) ?></td>
            <td><?= htmlspecialchars($l['descricao']) ?></td>
            <td><?= htmlspecialchars($l['categoria'] ?? '-') ?></td>
            <td><span class="badge badge-<?= $l['tipo'] ?>"><?= $l['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?></span></td>
            <td class="val-<?= $l['tipo'] ?>"><?= formatar_moeda($l['valor']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    <footer class="page-footer">Desenvolvido por: Ana</footer>
  </main>
</div>
</body>
</html>