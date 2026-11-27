<?php
$apiUrl = getenv('API_URL');
$tarefas = [];
$mensagemErro = null;

if (!$apiUrl && PHP_SAPI === 'cli-server') {
    $apiUrl = 'http://127.0.0.1:8001/';
}

function requisicaoGetNativa($url) {
    $partes = parse_url($url);
    $esquema = $partes['scheme'] ?? 'http';
    $host = $partes['host'] ?? '127.0.0.1';
    $porta = $partes['port'] ?? ($esquema === 'https' ? 443 : 80);
    $caminho = ($partes['path'] ?? '/') . (isset($partes['query']) ? '?' . $partes['query'] : '');
    
    $prefixo = ($esquema === 'https') ? 'ssl://' : '';
    $fp = @fsockopen($prefixo . $host, $porta, $errno, $errstr, 10);
    
    if (!$fp) {
        return false;
    }

    $requisicao = "GET $caminho HTTP/1.0\r\n";
    $requisicao .= "Host: $host\r\n";
    $requisicao .= "Connection: Close\r\n\r\n";

    fwrite($fp, $requisicao);
    $resposta = '';
    while (!feof($fp)) {
        $resposta .= fgets($fp, 1024);
    }
    fclose($fp);

    $separador = strpos($resposta, "\r\n\r\n");
    if ($separador === false) {
        return false;
    }

    return substr($resposta, $separador + 4);
}

if (!$mensagemErro) {
    if (!$apiUrl) {
        $esquemaApi = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $portaApi = (int) ($_SERVER['SERVER_PORT'] ?? ($esquemaApi === 'https' ? 443 : 80));
        $portaUrl = (($esquemaApi === 'https' && $portaApi !== 443) || ($esquemaApi === 'http' && $portaApi !== 80))
            ? ':' . $portaApi
            : '';
        $diretorioProjeto = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/site/index.php'));
        $caminhoProjeto = rtrim(str_replace('\\', '/', $diretorioProjeto), '/');
        $apiUrl = $esquemaApi . '://127.0.0.1' . $portaUrl . $caminhoProjeto . '/api/';
    }

    $respostaJson = requisicaoGetNativa($apiUrl);

    if ($respostaJson === false) {
        $mensagemErro = "Falha de conexão na URL: " . $apiUrl;
    } else {
        $tarefas = json_decode($respostaJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $mensagemErro = "JSON inválido recebido da API.";
            $tarefas = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciador de Tarefas</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; padding-top: 50px; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h1 { text-align: center; color: #2c3e50; }
        ul { list-style: none; padding: 0; }
        li { padding: 12px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
        .concluida .texto { text-decoration: line-through; color: #95a5a6; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-ok { background: #d4edda; color: #155724; }
        .badge-pendente { background: #fff3cd; color: #856404; }
        .erro { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Lista de Tarefas</h1>
    <?php if ($mensagemErro): ?>
        <div class="erro"><?= htmlspecialchars($mensagemErro) ?></div>
    <?php elseif (empty($tarefas)): ?>
        <p style="text-align: center;">Nenhuma tarefa encontrada.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tarefas as $tarefa): ?>
                <?php $status = $tarefa['concluida'] ?? false; ?>
                <li class="<?= $status ? 'concluida' : '' ?>">
                    <span class="texto"><?= htmlspecialchars($tarefa['texto']) ?></span>
                    <span class="badge <?= $status ? 'badge-ok' : 'badge-pendente' ?>">
                        <?= $status ? 'Concluída' : 'Pendente' ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>
