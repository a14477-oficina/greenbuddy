<?php

require "db.php"; // ligação à base de dados

// dados vindos do Arduino
$humidade = $_GET['humidade'] ?? null;

// validação
if ($humidade === null) {
    echo "Nenhum dado recebido";
    exit;
}

// data e hora separadas
$data = date("Y-m-d");
$hora = date("H:i:s");

// INSERT na base de dados

$stmt = $conn->prepare("
    INSERT INTO vaso_humidade (data, hora, percentagem)
    VALUES (?, ?, ?)
");

$stmt->bind_param("sss", $data, $hora, $humidade);

if ($stmt->execute()) {
    echo "OK - Guardado na base de dados";
} else {
    echo "Erro ao inserir: " . $stmt->error;
}

$stmt->close();
$conn->close();


?>