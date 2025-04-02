<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../session_verification.php');

function cleanOldCache($cacheDir, $maxAge = 86400) {
    $files = glob($cacheDir . '/*.json');
    foreach ($files as $file) {
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
    cleanOldCache($cacheDir);
    
    $cacheFile = $cacheDir . '/' . md5($path) . '.json';
    if (file_exists($cacheFile) && filemtime($cacheFile) > time() - 1000) {
        $cachedData = json_decode(file_get_contents($cacheFile), true);
    }

    if (empty($cachedData) || isset($_GET['force'])) {
        $cachedData = [];
        
        if ($handle = opendir($path)) {
            while (($entry = readdir($handle)) !== false) {
                if ($entry === '.' || $entry === '..' || ($level === 0 && $entry === 'index.php')) {
                    continue;
                }
                
                $fullPath = $path . DIRECTORY_SEPARATOR . $entry;
                $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fullPath);
                $itemData = [
                    'name' => $entry,
                    'path' => $relativePath,
                    'type' => is_dir($fullPath) ? 'dir' : 'file',
                ];
                
                if (is_file($fullPath)) {
                    $itemData['mimetype'] = mime_content_type($fullPath);
                } else {
                    $itemData['mimetype'] = null;
                }
                
                $cachedData[] = $itemData;
            }
            closedir($handle);
        }
        
        file_put_contents($cacheFile, json_encode($cachedData));
    }

    $dirs = array_filter($cachedData, fn($item) => $item['type'] === 'dir');
    $files = array_filter($cachedData, fn($item) => $item['type'] === 'file');
    
    usort($dirs, fn($a, $b) => strcmp($a['name'], $b['name']));
    usort($files, fn($a, $b) => strcmp($a['name'], $b['name']));
    
    $sortedData = array_merge($dirs, $files);

    echo "<ul>";
    foreach ($sortedData as $item) {
        if ($item['type'] === 'file') {
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

$path = realpath(__DIR__ . '/../private');
dirToLinks($path);
?>
