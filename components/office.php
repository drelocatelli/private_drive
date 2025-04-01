<?php
require_once('../session_verification.php');
require_once '../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

// Verifica se o arquivo foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['docxFile'])) {
    try {
        // Caminho temporário do arquivo recebido
        $uploadedFile = $_FILES['docxFile']['tmp_name'];

        // Carregar o arquivo .docx usando PhpWord

        // Carregar o arquivo .docx
        $phpWord = IOFactory::load($uploadedFile);

        // Criar um writer HTML para gerar o conteúdo HTML
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        // Capturar a saída do HTML em um buffer
        ob_start();
        $htmlWriter->save('php://output');
        $htmlContent = ob_get_clean();

        // Usar expressões regulares para extrair o conteúdo dentro da tag <body>
        preg_match('/<body.*?>(.*?)<\/body>/is', $htmlContent, $matches);

        // Se encontrar o conteúdo dentro da tag <body>, retornar
        if (isset($matches[1])) {
            $wrappedContent = '<div id="documento">' . $matches[1] . '</div>';
            echo $wrappedContent;  // Exibe o conteúdo envolvido pela div
        } else {
            echo 'Erro ao extrair conteúdo dentro da tag <body>';
        }

    } catch (Exception $e) {
        echo 'Erro ao processar o arquivo: ' . $e->getMessage();
    }
}
?>


<style>

    #documento {
        word-wrap: break-word;
        overflow-y: auto;
        margin-left: unset!important;
        margin-right: unset!important;
        white-space: normal!important;
    }
    
    h0 {
        font-size: 28px;
    }
</style>