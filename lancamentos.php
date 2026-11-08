<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
require 'functions.php';
verificar_sessao();
$mensagem = '';
$tipo_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $tipo      = $_POST['tipo'] ?? '';
    $valor_raw = str_replace(['.', ','], ['', '.'], trim($_POST['valor'] ?? '0'));
    $valor     = (float)$valor_raw;
    $data      = trim($_POST['data'] ?? date('Y-m-d'));
    if ($descricao === '' || !in_array($tipo, ['entrada', 'saida']) || $valor <= 0 || $data === '') {
        $mensagem = 'Preencha todos os campos corretamente.';
        $tipo_msg = 'erro';
    } else {
        $dados = ler_json('db.json');
        if (!isset($dados['lancamentos'])) $dados['lancamentos'] = [];
        $dados['lancamentos'][] = [
            'id'        => gerar_id(),
            'descricao' => $descricao,
            'categoria' => $categoria,
            'tipo'      => $tipo,
            'valor'     => $valor,
            'data'      => $data,
            'criado_em' => date('Y-m-d H:i:s')
        ];
        salvar_json('db.json', $dados);
        $mensagem = 'Lançamento registrado com sucesso!';
        $tipo_msg = 'sucesso';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lançamentos — Estoque & Vendas</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
  <?php require 'sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Lançamentos</h1>
      <span class="page-sub">Registre entradas e saídas de estoque/vendas</span>
    </div>
    <?php if ($mensagem): ?>
      <div class="alert alert-<?= $tipo_msg ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>
    <div class="section-box form-section">
      <h2 class="section-title">Novo Lançamento</h2>
      <form method="POST" action="lancamentos.php" class="form-grid">
        <div class="form-group">
          <label>Descrição *</label>
          <input type="text" name="descricao" placeholder="Ex: Venda de produto X" required>
        </div>
        <div class="form-group">
          <label>Categoria</label>
          <input type="text" name="categoria" placeholder="Ex: Eletrônicos, Alimentos...">
        </div>
        <div class="form-group">
          <label>Tipo *</label>
          <select name="tipo" required>
            <option value="">Selecione...</option>
            <option value="entrada">✅ Entrada</option>
            <option value="saida">🔴 Saída</option>
          </select>
        </div>
        <div class="form-group">
          <label>Valor (R$) *</label>
          <input type="number" name="valor" step="0.01" min="0.01" placeholder="0,00" required>
        </div>
        <div class="form-group">
          <label>Data *</label>
          <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group form-full">
          <button type="submit" class="btn btn-primary">Registrar Lançamento</button>
        </div>
      </form>
    </div>
    <footer class="page-footer">Desenvolvido por: Ana</footer>
  </main>
</div>
</body>
</html>
