<?php
require_once('session_verification.php');

if(isset($_GET['file'])) {
    $file = 'private/' . basename($_GET['file']); // Evita caminhos de diretórios perigosos

    // Verifique se o arquivo existe
    if (file_exists($file)) {
        // Defina o tipo de conteúdo e o cabeçalho para forçar o download
        header('Content-Type: ' . mime_content_type($file));
        header('Content-Disposition: inline; filename="' . basename($file) . '"');
        readfile($file); // Envia o conteúdo do arquivo para o navegador
        exit;
    } else {
        $file = preg_replace('#^/shared/#', '', $_GET['file']);
        if(file_exists($file)) {
            header('Content-Type: ' . mime_content_type($file));
            header('Content-Disposition: inline; filename="' . basename($file) . '"');
            readfile($file);
            exit;
        }

        print 'Arquivo não existe';
       
    }

    exit;
}