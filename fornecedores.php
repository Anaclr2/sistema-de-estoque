<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
require 'functions.php';
verificar_sessao();
$dados = ler_json('db.json');
$dados['fornecedores'] = $dados['fornecedores'] ?? [];

$erro = '';
$sucesso = '';

if (($_GET['acao'] ?? '') === 'excluir') {
    $id_excluir = $_GET['id'] ?? '';
    if ($id_excluir !== '') {
        $dados['fornecedores'] = array_values(array_filter($dados['fornecedores'], fn($f) => $f['id'] !== $id_excluir));
        salvar_json('db.json', $dados);
    }
    header('Location: fornecedores.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'salvar') {
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $id_edicao = $_POST['id'] ?? '';

    if ($nome === '') {
        $erro = 'O nome do fornecedor é obrigatório.';
    } elseif ($email !== '' && !validar_email($email)) {
        $erro = 'Informe um e-mail válido.';
    } else {
        if ($id_edicao !== '') {
            foreach ($dados['fornecedores'] as &$f) {
                if ($f['id'] === $id_edicao) {
                    $f['nome'] = $nome;
                    $f['cnpj'] = $cnpj;
                    $f['telefone'] = $telefone;
                    $f['email'] = $email;
                    $f['endereco'] = $endereco;
                    break;
                }
            }
            unset($f);
            $sucesso = 'Fornecedor atualizado com sucesso.';
        } else {
            $dados['fornecedores'][] = [
                'id' => gerar_id(),
                'nome' => $nome,
                'cnpj' => $cnpj,
                'telefone' => $telefone,
                'email' => $email,
                'endereco' => $endereco,
            ];
            $sucesso = 'Fornecedor cadastrado com sucesso.';
        }
        salvar_json('db.json', $dados);
    }
}

$fornecedor_edicao = null;
if (($_GET['acao'] ?? '') === 'editar') {
    $fornecedor_edicao = buscar_por_id($dados['fornecedores'], $_GET['id'] ?? '');
}

$filtro_nome = trim($_GET['nome'] ?? '');
$lista = $dados['fornecedores'];
if ($filtro_nome !== '') {
    $lista = array_filter($lista, fn($f) => stripos($f['nome'], $filtro_nome) !== false);
}
$lista = array_reverse(array_values($lista));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fornecedores — Estoque & Vendas</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-layout">
  <?php require 'sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Fornecedores</h1>
      <span class="page-sub">Cadastro de fornecedores</span>
    </div>

    <?php if ($erro): ?><div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <?php if ($sucesso): ?><div class="alert alert-sucesso"><?= htmlspecialchars($sucesso) ?></div><?php endif; ?>

    <div class="section-box form-section">
      <h2 class="section-title"><?= $fornecedor_edicao ? 'Editar Fornecedor' : 'Novo Fornecedor' ?></h2>
      <form method="POST" action="fornecedores.php">
        <input type="hidden" name="acao" value="salvar">
        <input type="hidden" name="id" value="<?= htmlspecialchars($fornecedor_edicao['id'] ?? '') ?>">
        <div class="form-grid">
          <div class="form-group">
            <label>Nome / Razão Social</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($fornecedor_edicao['nome'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>CNPJ</label>
            <input type="text" name="cnpj" value="<?= htmlspecialchars($fornecedor_edicao['cnpj'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($fornecedor_edicao['telefone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" value="<?= htmlspecialchars($fornecedor_edicao['email'] ?? '') ?>">
          </div>
          <div class="form-group form-full">
            <label>Endereço</label>
            <input type="text" name="endereco" value="<?= htmlspecialchars($fornecedor_edicao['endereco'] ?? '') ?>">
          </div>
        </div>
        <br>
        <button type="submit" class="btn btn-primary"><?= $fornecedor_edicao ? 'Salvar Alterações' : 'Cadastrar Fornecedor' ?></button>
        <?php if ($fornecedor_edicao): ?>
          <a href="fornecedores.php" class="btn btn-secondary">Cancelar</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="section-box">
      <form method="GET" action="fornecedores.php" class="filter-bar">
        <div class="filter-group">
          <label>Nome</label>
          <input type="text" name="nome" value="<?= htmlspecialchars($filtro_nome) ?>" placeholder="Buscar fornecedor...">
        </div>
        <div class="filter-group filter-btn">
          <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
          <a href="fornecedores.php" class="btn btn-secondary btn-sm">Limpar</a>
        </div>
      </form>
    </div>

    <div class="section-box">
      <?php if (empty($lista)): ?>
        <p class="empty-msg">Nenhum fornecedor cadastrado ainda.</p>
      <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>CNPJ</th>
            <th>Telefone</th>
            <th>E-mail</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $f): ?>
          <tr>
            <td><?= htmlspecialchars($f['nome']) ?></td>
            <td><?= htmlspecialchars($f['cnpj'] ?? '-') ?></td>
            <td><?= htmlspecialchars($f['telefone'] ?? '-') ?></td>
            <td><?= htmlspecialchars($f['email'] ?? '-') ?></td>
            <td>
              <a href="fornecedores.php?acao=editar&id=<?= urlencode($f['id']) ?>" class="btn-edit">Editar</a>
              <a href="fornecedores.php?acao=excluir&id=<?= urlencode($f['id']) ?>" class="btn-del" onclick="return confirm('Excluir este fornecedor?')">✕</a>
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