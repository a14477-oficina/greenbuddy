<?php
require "db.php"; 
session_start();

$erro = "";
$modo = $_GET['modo'] ?? 'login';

// --- LÓGICA DE LOGIN ---
if (isset($_POST['btn-login'])) {
    // Escapar caracteres para evitar SQL Injection (segurança básica)
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['senha'];

    // CORREÇÃO: Tabela alterada para 'utilizadores'
    $stmt = $conn->prepare("SELECT id_utilizador, username, senha FROM utilizadores WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verifica se a senha coincide com o hash na BD
        if (password_verify($pass, $row['senha'])) {
            $_SESSION['user_id'] = $row['id_utilizador'];
            $_SESSION['username'] = $row['username'];
            
            header("Location: recebe.php"); // Redireciona para o painel de rega
            exit;
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Utilizador não encontrado!";
    }
}

// --- LÓGICA DE REGISTO ---
if (isset($_POST['btn-registar'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $mail = mysqli_real_escape_string($conn, $_POST['email']);
    $tel  = mysqli_real_escape_string($conn, $_POST['telemovel']);

    // CORREÇÃO: Tabela alterada para 'utilizadores'
    $stmt = $conn->prepare("INSERT INTO utilizadores (username, senha, email, telemovel) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $user, $pass, $mail, $tel);
    
    if ($stmt->execute()) {
        header("Location: login.php?modo=login&sucesso=1");
        exit;
    } else {
        $erro = "Erro ao registar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenBuddy - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f0f4f1; margin: 0; font-family: 'Segoe UI', sans-serif; }
        .auth-card { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 380px; text-align: center; }
        .logo-login img { max-width: 180px; margin-bottom: 1.5rem; }
        .input-group { text-align: left; margin-bottom: 15px; }
        .input-group label { display: block; font-size: 0.85rem; color: #666; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; font-size: 1rem; }
        .btn { width: 100%; padding: 14px; background: #2d5a27; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: 0.3s; margin-top: 10px; }
        .btn:hover { background: #3e7a36; }
        .erro-msg { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; }
        .sucesso-msg { color: #155724; background: #d4edda; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 0.85rem; }
        .toggle-link { display: block; margin-top: 20px; font-size: 0.9rem; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="logo-login">
        <img src="img/logotipo_PAP.png" alt="GreenBuddy">
    </div>

    <?php 
        if($erro) echo "<div class='erro-msg'>$erro</div>"; 
        if(isset($_GET['sucesso'])) echo "<div class='sucesso-msg'>Conta criada com sucesso! Faça login.</div>";
    ?>

    <?php if ($modo == 'login'): ?>
        <h2>Bem-vindo</h2>
        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" required>
            </div>
            <button type="submit" name="btn-login" class="btn">Entrar</button>
            <a href="login.php?modo=registo" class="toggle-link">Ainda não tem conta? Registe-se</a>
        </form>
    <?php else: ?>
        <h2>Criar Conta</h2>
        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Telemóvel</label>
                <input type="text" name="telemovel" required>
            </div>
            <div class="input-group">
                <label>Senha</label>
                <input type="password" name="senha" required>
            </div>
            <button type="submit" name="btn-registar" class="btn">Registar</button>
            <a href="login.php?modo=login" class="toggle-link">Já tem conta? Entrar</a>
        </form>
    <?php endif; ?>
</div>

</body>
</html>