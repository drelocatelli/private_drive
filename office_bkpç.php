<?php
// Caminho completo da URL
$fileUrl = 'http://192.168.15.10:7000/shared/request_file.php?file=/shared/private/demo.docx';

// Caminho temporário para salvar o arquivo
$tempFile = './cache/demo.docx';

// Verificar tipo MIME antes de salvar o arquivo
$headers = get_headers($fileUrl, 1);

var_dump($headers['Content-Type']);
if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') !== false) {
    // Salvar o arquivo
    file_put_contents($tempFile, $fileContents);
} else {
    die('O arquivo não é um arquivo .docx válido');
}

// Tenta baixar o arquivo usando file_get_contents
$fileContents = file_get_contents($fileUrl);

if ($fileContents === false) {
    die('Erro ao baixar o arquivo.');
}

// Salva o conteúdo no arquivo temporário
file_put_contents($tempFile, $fileContents);

// Verifica se o arquivo foi salvo corretamente
if (!file_exists($tempFile)) {
    die('Erro ao salvar o arquivo.');
}

// Agora você pode carregar o arquivo .docx com PHPWord
require 'vendor/autoload.php';
use PhpOffice\PhpWord\IOFactory;
$phpWord = IOFactory::load($tempFile);

// Criar um escritor HTML para exportar o conteúdo
$writer = IOFactory::createWriter($phpWord, 'HTML');

// Capturar a saída em um buffer
ob_start();
$writer->save('php://output');
$htmlContent = ob_get_clean();

// Exibir o conteúdo HTML
header('Content-Type: text/html');
echo $htmlContent;

// Apagar o arquivo temporário após o uso
unlink($tempFile);
?>


<style>
    html {
        display: flex;
        justify-content: center;
    }

    body {
        /* overflow: hidden; */
        word-wrap: break-word;
        /* white-space: nowrap; */
        overflow-y: auto;
    }
    

    body {
        width: 80vw;
    }
    
    body * {
        margin-left: unset!important;
        margin-right: unset!important;
        white-space: normal!important;

    }

    h0 {
        font-size: 28px;
    }
</style>