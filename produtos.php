<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
require 'functions.php';
verificar_sessao();
$dados = ler_json('db.json');
$dados['produtos'] = $dados['produtos'] ?? [];

$erro = '';
$sucesso = '';

if (($_GET['acao'] ?? '') === 'excluir') {
    $id_excluir = $_GET['id'] ?? '';
    if ($id_excluir !== '') {
        $dados['produtos'] = array_values(array_filter($dados['produtos'], fn($p) => $p['id'] !== $id_excluir));
        salvar_json('db.json', $dados);
    }
    header('Location: produtos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    $nome = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $quantidade = (float)str_replace(',', '.', $_POST['quantidade'] ?? 0);
    $preco_custo = (float)str_replace(',', '.', $_POST['preco_custo'] ?? 0);
    $preco_venda = (float)str_replace(',', '.', $_POST['preco_venda'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $id_edicao = $_POST['id'] ?? '';

    if ($nome === '') {
        $erro = 'O nome do produto é obrigatório.';
    } else {
        if ($id_edicao !== '') {
            $encontrado = false;
            foreach ($dados['produtos'] as &$p) {
                if ($p['id'] === $id_edicao) {
                    $p['nome'] = $nome;
                    $p['categoria'] = $categoria;
                    $p['quantidade'] = $quantidade;
                    $p['preco_custo'] = $preco_custo;
                    $p['preco_venda'] = $preco_venda;
                    $p['descricao'] = $descricao;
                    $encontrado = true;
                    break;
                }
            }
            unset($p);
            $sucesso = $encontrado ? 'Produto atualizado com sucesso.' : 'Produto não encontrado.';
        } else {
            $dados['produtos'][] = [
                'id' => gerar_id(),
                'nome' => $nome,
                'categoria' => $categoria,
                'quantidade' => $quantidade,
                'preco_custo' => $preco_custo,
                'preco_venda' => $preco_venda,
                'descricao' => $descricao,
            ];
            $sucesso = 'Produto cadastrado com sucesso.';
        }
        salvar_json('db.json', $dados);
    }
}

$produto_edicao = null;
if (($_GET['acao'] ?? '') === 'editar') {
    $produto_edicao = buscar_por_id($dados['produtos'], $_GET['id'] ?? '');
}

$filtro_nome = trim($_GET['nome'] ?? '');
$lista = $dados['produtos'];
if ($filtro_nome !== '') {
    $lista = array_filter($lista, fn($p) => stripos($p['nome'], $filtro_nome) !== false);
}
$lista = array_reverse(array_values($lista));
$total_itens_estoque = array_sum(array_map(fn($p) => $p['quantidade'] ?? 0, $dados['produtos']));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Produtos — Estoque & Vendas</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
  <?php require 'sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Produtos</h1>
      <span class="page-sub">Cadastro e controle de estoque de produtos</span>
    </div>

    <?php if ($erro): ?><div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($sucesso): ?><div class="alert alert-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

    <div class="section-box form-section">
      <h2 class="section-title"><?= $produto_edicao ? 'Editar Produto' : 'Novo Produto' ?></h2>
      <form method="POST" action="produtos.php">
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="id" value="<?= htmlspecialchars($produto_edicao['id'] ?? '') ?>">
        <div class="form-grid">
          <div class="form-group">
            <label>Nome</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($produto_edicao['nome'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Categoria</label>
            <input type="text" name="categoria" value="<?= htmlspecialchars($produto_edicao['categoria'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Quantidade em Estoque</label>
            <input type="number" step="0.01" name="quantidade" value="<?= htmlspecialchars($produto_edicao['quantidade'] ?? 0) ?>">
          </div>
          <div class="form-group">
            <label>Preço de Custo</label>
            <input type="text" name="preco_custo" value="<?= htmlspecialchars($produto_edicao['preco_custo'] ?? '') ?>" placeholder="0,00">
          </div>
          <div class="form-group">
            <label>Preço de Venda</label>
            <input type="text" name="preco_venda" value="<?= htmlspecialchars($produto_edicao['preco_venda'] ?? '') ?>" placeholder="0,00">
          </div>
          <div class="form-group form-full">
            <label>Descrição</label>
            <textarea name="descricao" rows="2"><?= htmlspecialchars($produto_edicao['descricao'] ?? '') ?></textarea>
          </div>
        </div>
        <br>
        <button type="submit" class="btn btn-primary"><?= $produto_edicao ? 'Salvar Alterações' : 'Cadastrar Produto' ?></button>
        <?php if ($produto_edicao): ?>
          <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="resumo-bar">
      <div class="resumo-item resumo-blue"><span>Produtos Cadastrados</span><strong><?= count($dados['produtos']) ?></strong></div>
      <div class="resumo-item resumo-gray"><span>Itens em Estoque</span><strong><?= $total_itens_estoque ?></strong></div>
    </div>

    <div class="section-box">
      <form method="GET" action="produtos.php" class="filter-bar">
        <div class="filter-group">
          <label>Nome</label>
          <input type="text" name="nome" value="<?= htmlspecialchars($filtro_nome) ?>" placeholder="Buscar produto...">
        </div>
        <div class="filter-group filter-btn">
          <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
          <a href="produtos.php" class="btn btn-secondary btn-sm">Limpar</a>
        </div>
      </form>
    </div>

    <div class="section-box">
      <?php if (empty($lista)): ?>
        <p class="empty-msg">Nenhum produto cadastrado ainda.</p>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Categoria</th>
            <th>Qtd. Estoque</th>
            <th>Preço Custo</th>
            <th>Preço Venda</th>
            <th>Situação</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['nome']) ?></td>
            <td><?= htmlspecialchars($p['categoria'] ?? '-') ?></td>
            <td><?= htmlspecialchars($p['quantidade'] ?? 0) ?></td>
            <td><?= formatar_moeda($p['preco_custo'] ?? 0) ?></td>
            <td><?= formatar_moeda($p['preco_venda'] ?? 0) ?></td>
            <td>
              <?php if (($p['quantidade'] ?? 0) <= 5): ?>
                <span class="badge badge-baixo">Estoque baixo</span>
              <?php else: ?>
                <span class="badge badge-ok">Normal</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="produtos.php?acao=editar&id=<?= urlencode($p['id']) ?>" class="btn-edit">Editar</a>
              <a href="produtos.php?acao=excluir&id=<?= urlencode($p['id']) ?>" class="btn-del" onclick="return confirm('Excluir este produto?')">✕</a>
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