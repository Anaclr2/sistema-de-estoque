<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
require 'functions.php';
verificar_sessao();
$mensagem = '';
$tipo_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $quantidade_raw = str_replace(['.', ','], ['', '.'], trim($_POST['quantidade'] ?? '0'));
    $quantidade = (int)$quantidade_raw;
    $preco_raw = str_replace(['.', ','], ['', '.'], trim($_POST['preco'] ?? '0'));
    $preco     = (float)$preco_raw;
    if ($nome === '' || $quantidade < 0 || $preco < 0) {
        $mensagem = 'Preencha todos os campos corretamente.';
        $tipo_msg = 'erro';
    } else {
        $dados = ler_json('db.json');
        if (!isset($dados['produtos'])) $dados['produtos'] = [];
        $dados['produtos'][] = [
            'id'         => gerar_id(),
            'nome'       => $nome,
            'categoria'  => $categoria,
            'quantidade' => $quantidade,
            'preco'      => $preco,
            'criado_em'  => date('Y-m-d H:i:s')
        ];
        salvar_json('db.json', $dados);
        $mensagem = 'Produto cadastrado com sucesso!';
        $tipo_msg = 'sucesso';
    }
}
$acao = $_GET['acao'] ?? '';
if ($acao === 'excluir') {
    $id_excluir = $_GET['id'] ?? '';
    if ($id_excluir !== '') {
        $dados = ler_json('db.json');
        $dados['produtos'] = array_values(array_filter($dados['produtos'] ?? [], fn($p) => $p['id'] !== $id_excluir));
        salvar_json('db.json', $dados);
    }
    header('Location: estoque.php');
    exit;
}
$dados = ler_json('db.json');
$produtos = array_reverse($dados['produtos'] ?? []);
$total_qtd = array_sum(array_map(fn($p) => (int)($p['quantidade'] ?? 0), $produtos));
$total_valor = array_sum(array_map(fn($p) => (float)($p['quantidade'] ?? 0) * (float)($p['preco'] ?? 0), $produtos));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Estoque — Estoque & Vendas</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
  <?php require 'sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Estoque</h1>
      <span class="page-sub">Cadastre e gerencie os produtos do estoque</span>
    </div>
    <?php if ($mensagem): ?>
      <div class="alert alert-<?= $tipo_msg ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <div class="section-box form-section">
      <h2 class="section-title">Novo Produto</h2>
      <form method="POST" action="estoque.php" class="form-grid">
        <div class="form-group">
          <label>Nome do Produto *</label>
          <input type="text" name="nome" placeholder="Ex: Fone de Ouvido" required>
        </div>
        <div class="form-group">
          <label>Categoria</label>
          <input type="text" name="categoria" placeholder="Ex: Eletrônicos, Alimentos...">
        </div>
        <div class="form-group">
          <label>Quantidade *</label>
          <input type="number" name="quantidade" step="1" min="0" placeholder="0" required>
        </div>
        <div class="form-group">
          <label>Preço Unitário (R$) *</label>
          <input type="number" name="preco" step="0.01" min="0" placeholder="0,00" required>
        </div>
        <div class="form-group form-full">
          <button type="submit" class="btn btn-primary">Cadastrar Produto</button>
        </div>
      </form>
    </div>
    <div class="resumo-bar">
      <div class="resumo-item resumo-blue"><span>Produtos</span><strong><?= count($produtos) ?></strong></div>
      <div class="resumo-item resumo-gray"><span>Qtd. Total</span><strong><?= $total_qtd ?></strong></div>
      <div class="resumo-item resumo-green"><span>Valor em Estoque</span><strong><?= formatar_moeda($total_valor) ?></strong></div>
    </div>
    <div class="section-box">
      <h2 class="section-title">Produtos Cadastrados</h2>
      <?php if (empty($produtos)): ?>
        <p class="empty-msg">Nenhum produto cadastrado ainda.</p>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Produto</th>
            <th>Categoria</th>
            <th>Quantidade</th>
            <th>Preço Unit.</th>
            <th>Total</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($produtos as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['nome']) ?></td>
            <td><?= htmlspecialchars($p['categoria'] ?? '-') ?></td>
            <td><?= (int)($p['quantidade'] ?? 0) ?></td>
            <td><?= formatar_moeda($p['preco'] ?? 0) ?></td>
            <td><?= formatar_moeda((float)($p['quantidade'] ?? 0) * (float)($p['preco'] ?? 0)) ?></td>
            <td>
              <a href="estoque.php?acao=excluir&id=<?= urlencode($p['id']) ?>" class="btn-del" onclick="return confirm('Excluir este produto?')">✕</a>
            </td>
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