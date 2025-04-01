<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../session_verification.php');

function dirToLinks($path, $level = 0) {
    if (!is_readable($path)) {
        echo "<p>Erro: O diretório não pode ser lido ($path).</p>";
        return;
    }

    $iterator = new DirectoryIterator($path);
    echo "<ul>";

    foreach ($iterator as $item) {
        if ($item->isDot() || ($level === 0 && $item->getFilename() === 'index.php')) {
            continue;
        }

        $fullPath = $item->getPathname();
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fullPath);

        if ($item->isFile()) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($fullPath) ?: 'application/octet-stream';
            echo "<li>📄 <a target=\"_blank\" href=\"request_file.php?file=$relativePath\" relative-path=\"$relativePath\" mimetype=\"$mimeType\">{$item->getFilename()}</a></li>";
        } elseif ($item->isDir()) {
            echo "<li><details>";
            echo "<summary>📁 {$item->getFilename()}</summary>";
            dirToLinks($fullPath, $level + 1);
            echo "</details></li>";
        }
    }

    echo "</ul>";
}

$path = realpath(__DIR__ . '/../private'); // Evita problemas de path
dirToLinks($path);
?>


