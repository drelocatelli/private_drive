<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Inicia a sessão se ainda não estiver ativa
 }
require_once(__DIR__ . '/../session_verification.php');

function dirToLinks($path = __DIR__, $baseUrl = '', $level = 0) {
    $items = scandir($path);

    echo "<ul>";

    foreach($items as $item) {
        // Ignorar itens ocultos e o index.php na raiz
        if (strpos($item, '.') === 0 || ($level === 0 && $item === 'index.php')) {
            continue;
        }

        $fullPath = realpath($path . DIRECTORY_SEPARATOR . $item);
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fullPath);

        // Se for um arquivo, exibir link direto
        if (is_file($fullPath)) {
            $path = __DIR__ . '';
            $mimeType = mime_content_type($fullPath);
            echo "<li>📄 <a href=\"request_file.php?file=$relativePath\" relative-path=\"$relativePath\" mimetype=\"$mimeType\">$item</a></li>";
        }
        // Se for um diretório, exibir link para navegar e listar subdiretórios
        else if (is_dir($fullPath)) {
            echo "<li><details><summary>📁 $item</summary>";
            dirToLinks($fullPath, $relativePath, $level + 1);
            echo "</details></li>";
        }
    }

    echo "</ul>";
}

// Exibir os diretórios e arquivos como links
$path = __DIR__ . '/../private';
dirToLinks($path);

?>


