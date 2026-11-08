<?php
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <span class="brand-icon">📦</span>
    <span class="brand-name">Estoque<br><small>& Vendas</small></span>
  </div>
  <nav class="sidebar-nav">
    <a href="dashboard.php" class="nav-item <?= $pagina_atual === 'dashboard.php' ? 'active' : '' ?>">
      <span class="nav-icon">🏠</span> Dashboard
    </a>
    <a href="lancamentos.php" class="nav-item <?= $pagina_atual === 'lancamentos.php' ? 'active' : '' ?>">
      <span class="nav-icon">📝</span> Lançamentos
    </a>
    <a href="estoque.php" class="nav-item <?= $pagina_atual === 'estoque.php' ? 'active' : '' ?>">
      <span class="nav-icon">📦</span> Estoque
    </a>
    <a href="produtos.php" class="nav-item <?= $pagina_atual === 'produtos.php' ? 'active' : '' ?>">
      <span class="nav-icon">🏷️</span> Produtos
    </a>
    <a href="fornecedores.php" class="nav-item <?= $pagina_atual === 'fornecedores.php' ? 'active' : '' ?>">
      <span class="nav-icon">🚚</span> Fornecedores
    </a>
    <a href="compras.php" class="nav-item <?= $pagina_atual === 'compras.php' ? 'active' : '' ?>">
      <span class="nav-icon">🛒</span> Compras
    </a>
    <a href="relatorios.php" class="nav-item <?= $pagina_atual === 'relatorios.php' ? 'active' : '' ?>">
      <span class="nav-icon">📊</span> Relatórios
    </a>
  </nav>
  <div class="sidebar-footer">
    <span class="sidebar-user">👤 admin</span>
    <a href="logout.php" class="btn-logout">Sair</a>
  </div>
</aside>