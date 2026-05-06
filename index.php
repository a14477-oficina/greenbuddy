<?php
require "db.php";
session_start();

$erro = "";
$modo = $_GET['modo'] ?? 'login'; // Alterna entre 'login' e 'registo'

// --- LÓGICA DE REGISTO ---
if (isset($_POST['btn-registar'])) {
    $user = $_POST['username'];
    $pass = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Segurança!
    $mail = $_POST['email'];
    $tel  = $_POST['telemovel'];

    $stmt = $conn->prepare("INSERT INTO utilizador (username, senha, email, telemovel) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $user, $pass, $mail, $tel);
    
    if ($stmt->execute()) {
        header("Location: index.php?modo=login&sucesso=1");
    } else {
        $erro = "Erro ao criar conta. Username já existe?";
    }
}

// --- LÓGICA DE LOGIN ---
if (isset($_POST['btn-login'])) {
    $user = $_POST['username'];
    $pass = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id_utilizador, senha FROM utilizador WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['senha'])) {
            $_SESSION['user_id'] = $row['id_utilizador'];
            header("Location: recebe.php"); // Vai para o dashboard que criámos antes
            exit;
        }
    }
    $erro = "Username ou senha incorretos!";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>GreenBuddy - Acesso</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-card">
    <div class="logo-container">
        <img src="logotipo_PAP.png" alt="GreenBuddy Logo">
    </div>

    <?php if ($modo == 'login'): ?>
        <h2>Iniciar Sessão</h2>
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
            <a href="index.php?modo=registo" class="toggle-link">Não tem conta? Criar agora</a>
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
            <button type="submit" name="btn-registar" class="btn">Finalizar Registo</button>
            <a href="index.php?modo=login" class="toggle-link">Já tenho conta</a>
        </form>
    <?php endif; ?>

    <?php if ($erro): ?>
        <p style="color: red; margin-top: 10px; font-size: 0.8rem;"><?php echo $erro; ?></p>
    <?php endif; ?>
</div>

</body>
</html>