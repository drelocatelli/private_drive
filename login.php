<?php

require_once 'conn.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = filter_var($_POST['user'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $query = "SELECT * FROM login WHERE user = :user LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user', $user, PDO::PARAM_STR);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($password === $user['password']) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['user'];

            header("Location: painel.php");
        } else {
            die('Senha incorreta!');
        }
        
    } else {
        die('Usuário não existe');
    }

}