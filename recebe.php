<?php
require "db.php"; 

// 1. LÓGICA DE INSERÇÃO (PARA O ARDUINO)
$humidade = $_GET['humidade'] ?? null;

if ($humidade !== null) {
    date_default_timezone_set('Europe/Lisbon');
    $data = date("Y-m-d");
    $hora = date("H:i:s");

    $stmt = $conn->prepare("INSERT INTO vaso_humidade (data, hora, percentagem) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data, $hora, $humidade);
    $stmt->execute();
    $stmt->close();
    // Se for o Arduino a aceder, paramos aqui para não enviar o HTML todo
    if(isset($_GET['humidade'])) { echo "OK"; exit; }
}

// 2. SE FOR UM ACESSO VIA AJAX (PEDIDO DE ATUALIZAÇÃO)
if (isset($_GET['ajax'])) {
    $sql = "SELECT data, hora, percentagem FROM vaso_humidade ORDER BY id_humidade DESC LIMIT 15";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . date('d/m/Y', strtotime($row["data"])) . "</td>
                    <td>" . $row["hora"] . "</td>
                    <td style='font-weight:bold; color:#28a745;'>" . $row["percentagem"] . "%</td>
                  </tr>";
        }
    }
    exit; // Importante para não repetir o HTML da página
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Monitor em Tempo Real</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 30px; background-color: #f0f2f5; }
        .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { color: #007bff; text-align: center; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
        th { background-color: #007bff; color: white; }
        .status { text-align: center; font-size: 0.8em; color: #888; margin-bottom: 10px; }
    </style>

    <script>
        // Função que vai buscar os dados ao PHP sem recarregar a página
        function atualizarTabela() {
            fetch('recebe.php?ajax=1')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('corpo-tabela').innerHTML = html;
                    document.getElementById('ultima-atualizacao').innerText = "Última atualização: " + new Gregorian().toLocaleTimeString();
                })
                .catch(error => console.warn('Erro ao atualizar:', error));
        }

        // Executa a função a cada 3 segundos (3000 milissegundos)
        setInterval(atualizarTabela, 3000);
    </script>
</head>
<body>

<div class="container">
    <h2>🌱 Monitor em Tempo Real</h2>
    <div id="ultima-atualizacao" class="status">A carregar...</div>
    
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Hora</th>
                <th>Humidade</th>
            </tr>
        </thead>
        <tbody id="corpo-tabela">
            </tbody>
    </table>
</div>

<script>
    // Carrega os dados assim que a página abre
    atualizarTabela();
</script>

</body>
</html>