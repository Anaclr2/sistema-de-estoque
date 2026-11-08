<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
require 'functions.php';
verificar_sessao();
$dados = ler_json('db.json');
$lancamentos = $dados['lancamentos'] ?? [];
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_data_ini = $_GET['data_ini'] ?? '';
$filtro_data_fim = $_GET['data_fim'] ?? '';
$filtro_cat  = trim($_GET['categoria'] ?? '');
$lista = $lancamentos;
if ($filtro_tipo !== '') {
    $lista = array_filter($lista, fn($l) => $l['tipo'] === $filtro_tipo);
}
if ($filtro_data_ini !== '') {
    $lista = array_filter($lista, fn($l) => $l['data'] >= $filtro_data_ini);
}
if ($filtro_data_fim !== '') {
    $lista = array_filter($lista, fn($l) => $l['data'] <= $filtro_data_fim);
}
if ($filtro_cat !== '') {
    $lista = array_filter($lista, fn($l) => stripos($l['categoria'] ?? '', $filtro_cat) !== false);
}
$lista = array_reverse(array_values($lista));
$total_e = array_sum(array_map(fn($l) => $l['tipo']==='entrada' ? $l['valor'] : 0, $lista));
$total_s = array_sum(array_map(fn($l) => $l['tipo']==='saida' ? $l['valor'] : 0, $lista));
$saldo = $total_e - $total_s;
$acao = $_GET['acao'] ?? '';
if ($acao === 'excluir') {
    $id_excluir = $_GET['id'] ?? '';
    if ($id_excluir !== '') {
        $dados['lancamentos'] = array_values(array_filter($dados['lancamentos'] ?? [], fn($l) => $l['id'] !== $id_excluir));
        salvar_json('db.json', $dados);
    }
    header('Location: relatorios.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Relatórios — Estoque & Vendas</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
  <?php require 'sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Relatórios</h1>
      <span class="page-sub">Histórico completo de lançamentos</span>
    </div>
    <div class="section-box">
      <form method="GET" action="relatorios.php" class="filter-bar">
        <div class="filter-group">
          <label>Tipo</label>
          <select name="tipo">
            <option value="">Todos</option>
            <option value="entrada" <?= $filtro_tipo === 'entrada' ? 'selected' : '' ?>>Entrada</option>
            <option value="saida" <?= $filtro_tipo === 'saida' ? 'selected' : '' ?>>Saída</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Categoria</label>
          <input type="text" name="categoria" value="<?= htmlspecialchars($filtro_cat) ?>" placeholder="Filtrar...">
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
          <a href="relatorios.php" class="btn btn-secondary btn-sm">Limpar</a>
        </div>
      </form>
    </div>
    <div class="resumo-bar">
      <div class="resumo-item resumo-green"><span>Entradas</span><strong><?= formatar_moeda($total_e) ?></strong></div>
      <div class="resumo-item resumo-red"><span>Saídas</span><strong><?= formatar_moeda($total_s) ?></strong></div>
      <div class="resumo-item resumo-blue"><span>Saldo</span><strong><?= formatar_moeda($saldo) ?></strong></div>
      <div class="resumo-item resumo-gray"><span>Registros</span><strong><?= count($lista) ?></strong></div>
    </div>
    <div class="section-box">
      <?php if (empty($lista)): ?>
        <p class="empty-msg">Nenhum lançamento encontrado com os filtros aplicados.</p>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Data</th>
            <th>Descrição</th>
            <th>Categoria</th>
            <th>Tipo</th>
            <th>Valor</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $l): ?>
          <tr class="row-click lanc-row" data-lanc="<?= htmlspecialchars(json_encode($l, JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>">
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($l['data']))) ?></td>
            <td><?= htmlspecialchars($l['descricao']) ?></td>
            <td><?= htmlspecialchars($l['categoria'] ?? '-') ?></td>
            <td><span class="badge badge-<?= $l['tipo'] ?>"><?= $l['tipo'] === 'entrada' ? '↑ Entrada' : '↓ Saída' ?></span></td>
            <td class="val-<?= $l['tipo'] ?>"><?= formatar_moeda($l['valor']) ?></td>
            <td>
              <a href="relatorios.php?acao=excluir&id=<?= urlencode($l['id']) ?>" class="btn-del" onclick="event.stopPropagation(); return confirm('Excluir este lançamento?')">✕</a>
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

<div class="modal-overlay" id="modalLancamento">
  <div class="modal-box">
    <div class="modal-header">
      <h3>Detalhes do Lançamento</h3>
      <button type="button" class="modal-close" onclick="fecharModalLancamento()">✕</button>
    </div>
    <div class="modal-body" id="modalLancBody"></div>
  </div>
</div>

<script>
var ROTULOS_CAMPOS = {
  id: 'Código',
  data: 'Data',
  descricao: 'Descrição',
  categoria: 'Categoria',
  tipo: 'Tipo',
  valor: 'Valor',
  origem: 'Origem',
  fornecedor: 'Fornecedor',
  produto: 'Produto',
  quantidade: 'Quantidade',
  observacao: 'Observação'
};

function formatarMoedaJS(v) {
  var n = parseFloat(v) || 0;
  return 'R$ ' + n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d)$)/g, '.');
}

function formatarDataJS(d) {
  if (!d) return d;
  var partes = String(d).split('-');
  if (partes.length === 3) return partes[2] + '/' + partes[1] + '/' + partes[0];
  return d;
}

function rotuloCampo(chave) {
  if (ROTULOS_CAMPOS[chave]) return ROTULOS_CAMPOS[chave];
  return chave.charAt(0).toUpperCase() + chave.slice(1).replace(/_/g, ' ');
}

function abrirModalLancamento(l) {
  var body = document.getElementById('modalLancBody');
  var html = '';
  for (var chave in l) {
    if (!l.hasOwnProperty(chave)) continue;
    var valor = l[chave];
    if (chave === 'valor') valor = formatarMoedaJS(valor);
    else if (chave === 'data') valor = formatarDataJS(valor);
    else if (chave === 'tipo') valor = (valor === 'entrada' ? 'Entrada' : 'Saída');
    html += '<div class="modal-row"><span class="modal-label">' + rotuloCampo(chave) + '</span><span class="modal-value">' + valor + '</span></div>';
  }
  body.innerHTML = html;
  document.getElementById('modalLancamento').classList.add('active');
}

function fecharModalLancamento() {
  document.getElementById('modalLancamento').classList.remove('active');
}

document.querySelectorAll('.lanc-row').forEach(function (tr) {
  tr.addEventListener('click', function (e) {
    if (e.target.closest('.btn-del')) return;
    try {
      var dados = JSON.parse(tr.getAttribute('data-lanc'));
      abrirModalLancamento(dados);
    } catch (err) {}
  });
});

document.getElementById('modalLancamento').addEventListener('click', function (e) {
  if (e.target === this) fecharModalLancamento();
});
</script>
</body>
</html>