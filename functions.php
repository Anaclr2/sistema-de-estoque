<?php
function ler_json($arquivo) {
    if (!file_exists($arquivo)) return [];
    $f = fopen($arquivo, 'r');
    if (!$f) return [];
    flock($f, LOCK_SH);
    $conteudo = fread($f, max(1, filesize($arquivo)));
    flock($f, LOCK_UN);
    fclose($f);
    $dados = json_decode($conteudo, true);
    return is_array($dados) ? $dados : [];
}

function salvar_json($arquivo, $dados) {
    $f = fopen($arquivo, 'c');
    if (!$f) return false;
    flock($f, LOCK_EX);
    ftruncate($f, 0);
    rewind($f);
    fwrite($f, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    flock($f, LOCK_UN);
    fclose($f);
    return true;
}

function verificar_sessao() {
    if (empty($_SESSION['logado'])) {
        header('Location: login.php');
        exit;
    }
}

function formatar_moeda($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function gerar_id() {
    return uniqid('', true);
}

function buscar_por_id($lista, $id) {
    if (!is_array($lista) || $id === '' || $id === null) return null;
    foreach ($lista as $item) {
        if (isset($item['id']) && $item['id'] === $id) return $item;
    }
    return null;
}