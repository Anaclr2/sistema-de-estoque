<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
require 'functions.php';
verificar_sessao();
$dados = ler_json('db.json');
$dados['produtos'] = $dados['produtos'] ?? [];
$dados['fornecedores'] = $dados['fornecedores'] ?? [];
$dados['compras'] = $dados['compras'] ?? [];
$dados['lancamentos'] = $dados['lancamentos'] ?? [];

$erro = '';
$sucesso = '';

if (($_GET['acao'] ?? '') === 'excluir') {
    $id_excluir = $_GET['id'] ?? '';
    if ($id_excluir !== '') {
        $compra = buscar_por_id($dados['compras'], $id_excluir);
        if ($compra) {
            foreach ($dados['produtos'] as &$p) {
                if ($p['id'] === $compra['produto_id']) {
                    $p['quantidade'] = max(0, ($p['quantidade'] ?? 0) - ($compra['quantidade'] ?? 0));
                    break;
                }
            }
            unset($p);
            if (!empty($compra['lancamento_id'])) {
                $dados['lancamentos'] = array_values(array_filter($dados['lancamentos'], fn($l) => $l['id'] !== $compra['lancamento_id']));
            }
            $dados['compras'] = array_values(array_filter($dados['compras'], fn($c) => $c['id'] !== $id_excluir));
            salvar_json('db.json', $dados);
        }
    }
    header('Location: compras.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    $fornecedor_id = $_POST['fornecedor_id'] ?? '';
    $produto_id = $_POST['produto_id'] ?? '';
    $data_compra = $_POST['data'] ?? date('Y-m-d');
    $quantidade = (float)str_replace(',', '.', $_POST['quantidade'] ?? 0);
    $valor_unitario = (float)str_replace(',', '.', $_POST['valor_unitario'] ?? 0);
    $observacao = trim($_POST['observacao'] ?? '');

    $fornecedor = buscar_por_id($dados['fornecedores'], $fornecedor_id);
    $produto = buscar_por_id($dados['produtos'], $produto_id);

    if (!$fornecedor || !$produto) {
        $erro = 'Selecione um fornecedor e um produto válidos.';
    } elseif ($quantidade <= 0 || $valor_unitario <= 0) {
        $erro = 'Informe quantidade e valor unitário maiores que zero.';
    } else {
        $valor_total = $quantidade * $valor_unitario;
        $lancamento_id = gerar_id();

        $dados['lancamentos'][] = [
            'id' => $lancamento_id,
            'data' => $data_compra,
            'descricao' => 'Compra de ' . $produto['nome'] . ' - ' . $fornecedor['nome'],
            'categoria' => 'Compras',
            'tipo' => 'saida',
            'valor' => $valor_total,
            'origem' => 'compra',
            'fornecedor' => $fornecedor['nome'],
            'produto' => $produto['nome'],
            'quantidade' => $quantidade,
        ];

        foreach ($dados['produtos'] as &$p) {
            if ($p['id'] === $produto_id) {
                $p['quantidade'] = ($p['quantidade'] ?? 0) + $quantidade;
                break;
            }
        }
        unset($p);

        $dados['compras'][] = [
            'id' => gerar_id(),
            'data' => $data_compra,
            'fornecedor_id' => $fornecedor_id,
            'fornecedor_nome' => $fornecedor['nome'],
            'produto_id' => $produto_id,
            'produto_nome' => $produto['nome'],
            'quantidade' => $quantidade,
            'valor_unitario' => $valor_unitario,
            'valor_total' => $valor_total,
            'observacao' => $observacao,
            'lancamento_id' => $lancamento_id,
        ];

        salvar_json('db.json', $dados);
        $sucesso = 'Compra registrada com sucesso. Estoque atualizado e lançamento financeiro criado automaticamente.';
    }
}

$filtro_fornecedor = $_GET['fornecedor_id'] ?? '';
$filtro_data_ini = $_GET['data_ini'] ?? '';
$filtro_data_fim = $_GET['data_fim'] ?? '';

$lista = $dados['compras'];
if ($filtro_fornecedor !== '') {
    $lista = array_filter($lista, fn($c) => $c['fornecedor_id'] === $filtro_fornecedor);
}
if ($filtro_data_ini !== '') {
    $lista = array_filter($lista, fn($c) => $c['data'] >= $filtro_data_ini);
}
if ($filtro_data_fim !== '') {
    $lista = array_filter($lista, fn($c) => $c['data'] <= $filtro_data_fim);
}
$lista = array_reverse(array_values($lista));

$total_compras_valor = array_sum(array_map(fn($c) => $c['valor_total'] ?? 0, $lista));
$total_itens_comprados = array_sum(array_map(fn($c) => $c['quantidade'] ?? 0, $lista));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compras — Estoque & Vendas</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
  <?php require 'sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Compras</h1>
      <span class="page-sub">Registro de compras conectado ao estoque e ao financeiro</span>
    </div>

    <?php if ($erro): ?><div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($sucesso): ?><div class="alert alert-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

    <div class="section-box form-section">
      <h2 class="section-title">Nova Compra</h2>
      <?php if (empty($dados['produtos']) || empty($dados['fornecedores'])): ?>
        <p class="empty-msg">Cadastre ao menos um <a href="produtos.php">produto</a> e um <a href="fornecedores.php">fornecedor</a> antes de registrar uma compra.</p>
      <?php else: ?>
      <form method="POST" action="compras.php">
        <input type="hidden" name="acao" value="salvar">
        <div class="form-grid">
          <div class="form-group">
            <label>Fornecedor</label>
            <select name="fornecedor_id" required>
              <option value="">Selecione...</option>
              <?php foreach ($dados['fornecedores'] as $f): ?>
                <option value="<?= htmlspecialchars($f['id']) ?>"><?= htmlspecialchars($f['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Produto</label>
            <select name="produto_id" required>
              <option value="">Selecione...</option>
              <?php foreach ($dados['produtos'] as $p): ?>
                <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Data</label>
            <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Quantidade</label>
            <input type="number" step="0.01" name="quantidade" required>
          </div>
          <div class="form-group">
            <label>Valor Unitário</label>
            <input type="text" name="valor_unitario" placeholder="0,00" required>
          </div>
          <div class="form-group form-full">
            <label>Observação</label>
            <input type="text" name="observacao" placeholder="Opcional">
          </div>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Registrar Compra</button>
      </form>
      <?php endif; ?>
    </div>

    <div class="section-box">
      <form method="GET" action="compras.php" class="filter-bar">
        <div class="filter-group">
          <label>Fornecedor</label>
          <select name="fornecedor_id">
            <option value="">Todos</option>
            <?php foreach ($dados['fornecedores'] as $f): ?>
              <option value="<?= htmlspecialchars($f['id']) ?>" <?= $filtro_fornecedor === $f['id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label>De</label>
          <input type="date" name="data_ini" value="<?= htmlspecialchars($filtro_data_ini) ?>">
        </div>
        <div class="filter-group">
          <label>Até</label>
          <input type="date" name="data_fim" value="<?= htmlspecialchars($filtro_data_fim) ?>">
        </div>
        <div class="filter-group filter-btn">
          <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
          <a href="compras.php" class="btn btn-secondary btn-sm">Limpar</a>
        </div>
      </form>
    </div>

    <div class="resumo-bar">
      <div class="resumo-item resumo-red"><span>Total Gasto</span><strong><?= formatar_moeda($total_compras_valor) ?></strong></div>
      <div class="resumo-item resumo-gray"><span>Itens Comprados</span><strong><?= $total_itens_comprados ?></strong></div>
      <div class="resumo-item resumo-blue"><span>Registros</span><strong><?= count($lista) ?></strong></div>
    </div>

    <div class="section-box">
      <?php if (empty($lista)): ?>
        <p class="empty-msg">Nenhuma compra registrada com os filtros aplicados.</p>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Produto</th>
            <th>Fornecedor</th>
            <th>Qtd.</th>
            <th>Valor Unit.</th>
            <th>Valor Total</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $c): ?>
          <tr>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['data']))) ?></td>
            <td><?= htmlspecialchars($c['produto_nome']) ?></td>
            <td><?= htmlspecialchars($c['fornecedor_nome']) ?></td>
            <td><?= htmlspecialchars($c['quantidade']) ?></td>
            <td><?= formatar_moeda($c['valor_unitario']) ?></td>
            <td class="val-saida"><?= formatar_moeda($c['valor_total']) ?></td>
            <td>
              <a href="compras.php?acao=excluir&id=<?= urlencode($c['id']) ?>" class="btn-del" onclick="return confirm('Excluir esta compra? O estoque e o lançamento financeiro vinculados serão revertidos.')">✕</a>
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