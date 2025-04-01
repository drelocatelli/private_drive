<?php
if (session_status() == PHP_SESSION_NONE) {
   session_start(); // Inicia a sessão se ainda não estiver ativa
}

if(!isset($_SESSION['user_id'])) {
   echo 'Você não tem acesso à página';
   exit;
}
