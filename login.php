<?php
session_start();
if (!empty($_GET['_db_fresh'])) { $_SESSION = []; session_regenerate_id(true); }
if (!empty($_SESSION['logado'])) { header('Location: index.php'); exit; }
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha'] ?? '');
    if ($usuario === 'admin' && $senha === 'admin') {
        $_SESSION['logado'] = true;
        $_SESSION['usuario'] = 'admin';
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Usuário ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Estoque & Vendas</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background: #f1f5f9; }
  .left-col {
    flex: 1; background: linear-gradient(145deg, #0f172a 0%, #1e3a5f 60%, #1d4ed8 100%);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: #fff; padding: 48px 40px; text-align: center;
  }
  .left-col .brand-icon { font-size: 3.5rem; margin-bottom: 20px; }
  .left-col h1 { font-size: 2.4rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 14px; }
  .left-col p { font-size: 1rem; opacity: .78; max-width: 300px; line-height: 1.6; }
  .right-col {
    flex: 1; display: flex; align-items: center; justify-content: center;
    background: #fff; padding: 48px 32px;
  }
  .login-box { width: 100%; max-width: 360px; }
  .login-box h2 { font-size: 1.6rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
  .login-box p { font-size: .9rem; color: #64748b; margin-bottom: 28px; }
  .form-group { margin-bottom: 18px; }
  .form-group label { display: block; font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
  .form-group input {
    width: 100%; padding: 11px 14px; border: 1.5px solid #d1d5db;
    border-radius: 8px; font-size: .95rem; color: #111827;
    outline: none; transition: border-color .2s;
  }
  .form-group input:focus { border-color: #1d4ed8; }
  .btn-login {
    width: 100%; padding: 12px; background: #1d4ed8; color: #fff;
    border: none; border-radius: 8px; font-size: 1rem; font-weight: 700;
    cursor: pointer; transition: background .2s;
  }
  .btn-login:hover { background: #1e40af; }
  .erro-box { background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px;
    padding: 10px 14px; color: #991b1b; font-size: .85rem; margin-bottom: 16px; }
  .cred-box { margin-top: 18px; padding: 11px 14px; background: #fef3c7;
    border: 1px solid #f59e0b; border-radius: 8px; font-size: .78rem; color: #78350f; }
  @media (max-width: 800px) {
    body { flex-direction: column; }
    .left-col { flex: none; height: 30vh; padding: 24px; }
    .left-col h1 { font-size: 1.6rem; }
    .right-col { flex: 1; }
  }
</style>
</head>
<body>
<div class="left-col">
  <div class="brand-icon">📦</div>
  <h1>Estoque & Vendas</h1>
  <p>Gerencie entradas, saídas e relatórios do seu negócio em um só lugar.</p>
</div>
<div class="right-col">
  <div class="login-box">
    <h2>Entrar</h2>
    <p>Acesse sua conta para continuar</p>
    <?php if ($erro): ?>
      <div class="erro-box"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="usuario">Usuário</label>
        <input type="text" id="usuario" name="usuario" placeholder="admin" autocomplete="username" required>
      </div>
      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="••••••" autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn-login">Entrar</button>
    </form>
    <div class="cred-box">
      <strong>🔑 Credenciais de teste:</strong> usuário <code>admin</code> | senha <code>admin</code><br>
      <span style="font-size:.7rem;opacity:.85">Remova esta caixa antes de publicar em produção.</span>
    </div>
  </div>
</div>
</body>
</html>
