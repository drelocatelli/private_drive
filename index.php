<?php
    session_start();

    if(isset($_SESSION['user_id'])) {
        header("Location: painel.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="login.php" method="post">
        <div>
            <label for="user">Usuário:</label>
            <input type="text" id="user" name="user" required>
        </div>
        <div>
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit">Entrar</button>
        </div>
    </form>
</body>
</html>