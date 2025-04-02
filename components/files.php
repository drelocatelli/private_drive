<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../session_verification.php');

function cleanOldCache($cacheDir, $maxAge = 86400) { // 86400 segundos = 1 dia
    $files = glob($cacheDir . '/*.json');
    foreach ($files as $file) {
        // Verifica se o arquivo é mais antigo que o período máximo e apaga
        if (filemtime($file) < time() - $maxAge) {
            unlink($file);
        }
    }
}

function dirToLinks($path, $level = 0) {
    if (!is_readable($path)) {
        echo "<p>Erro: O diretório não pode ser lido ($path), veja se há permissões (chmod).</p>";
        return;
    }

    $cachedData = [];

    $cacheDir = __DIR__ . '/../cache';
    cleanOldCache($cacheDir); // Limpa caches antigos antes de usar

    $cacheFile = $cacheDir . '/' . md5($path) . '.json';
    // Se o cache existir e for recente, use-o
    if (file_exists($cacheFile) && filemtime($cacheFile) > time() - 1000) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);
    }

    if(empty($cachedData) || isset($_GET['force'])) {
        // Caso contrário, gere a lista de arquivos
        $iterator = new DirectoryIterator($path);
        $cachedData = [];

        foreach ($iterator as $item) {
            if ($item->isDot() || ($level === 0 && $item->getFilename() === 'index.php')) {
                continue;
            }

            $fullPath = $item->getPathname();
            $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fullPath);
            $itemData = [
                'name' => $item->getFilename(),
                'path' => $relativePath,
                'type' => $item->isDir() ? 'dir' : 'file',
            ];

            // Adicionando mimetype apenas se for um arquivo
            if ($item->isFile()) {
                $itemData['mimetype'] = mime_content_type($fullPath);
            } else {
                $itemData['mimetype'] = null;
            }

            $cachedData[] = $itemData;
        }

        // Salva o cache
        file_put_contents($cacheFile, json_encode($cachedData));
    }

    // Separar os diretórios e arquivos
    $dirs = array_filter($cachedData, fn($item) => $item['type'] === 'dir');
    $files = array_filter($cachedData, fn($item) => $item['type'] === 'file');

    // Ordenar por nome
    usort($dirs, fn($a, $b) => strcmp($a['name'], $b['name']));
    usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));

    // Concatenar diretórios primeiro e depois arquivos
    $sortedData = array_merge($dirs, $files);

    echo "<ul>";
    foreach ($sortedData as $item) {
        if ($item['type'] === 'file') {
            // Usando o mimetype na tag <a>
            echo "<li>📄 <a target=\"_blank\" href=\"request_file.php?file={$item['path']}\" relative-path=\"{$item['path']}\" mimetype=\"{$item['mimetype']}\">{$item['name']}</a></li>";
        } elseif ($item['type'] === 'dir') {
            echo "<li><details>";
            echo "<summary>📁 {$item['name']}</summary>";
            dirToLinks($path . '/' . $item['name'], $level + 1);
            echo "</details></li>";
        }
    }
    echo "</ul>";
}

// Caminho do diretório
$path = realpath(__DIR__ . '/../private'); // Evita problemas de path
dirToLinks($path);
?>
