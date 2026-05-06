<?php
require "db.php"; 
date_default_timezone_set('Europe/Lisbon');

// 1. LÓGICA DE ATUALIZAÇÃO DE CONFIGURAÇÃO (SITE)
if (isset($_POST['update_config'])) {
    $seco = $_POST['seco_limite'];
    $humido = $_POST['humido_limite'];
    $conn->query("UPDATE vaso_config SET seco_limite = $seco, humido_limite = $humido WHERE id = 1");
    header("Location: recebe.php"); // Recarrega para evitar reenvio de formulário
    exit;
}

// 2. LÓGICA DE INSERÇÃO E RESPOSTA (ARDUINO)
$humidade = $_GET['humidade'] ?? null;
if ($humidade !== null) {
    $data = date("Y-m-d");
    $hora = date("H:i:s");

    $stmt = $conn->prepare("INSERT INTO vaso_humidade (data, hora, percentagem) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data, $hora, $humidade);
    $stmt->execute();
    $stmt->close();
    
    // Responde ao Arduino com os limites atuais para ele se atualizar
    $res_config = $conn->query("SELECT * FROM vaso_config WHERE id = 1");
    $config = $res_config->fetch_assoc();
    echo "CONF_SECO:" . $config['seco_limite'] . "|CONF_HUMIDO:" . $config['humido_limite'];
    exit;
}

// 3. LÓGICA PARA O AJAX (DASHBOARD)
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    $res_atual = $conn->query("SELECT percentagem, hora, data FROM vaso_humidade ORDER BY id_humidade DESC LIMIT 1");
    $dados = $res_atual->fetch_assoc();

    $res_config = $conn->query("SELECT * FROM vaso_config WHERE id = 1");
    $config = $res_config->fetch_assoc();

    $sql_rega = "SELECT data, hora FROM vaso_humidade WHERE percentagem >= " . $config['humido_limite'] . " ORDER BY id_humidade DESC LIMIT 1";
    $res_rega = $conn->query($sql_rega);
    $ultima_rega = $res_rega->fetch_assoc();

    echo json_encode([
        "percentagem" => $dados['percentagem'] ?? 0,
        "hora" => $dados['hora'] ?? "--:--",
        "seco" => $config['seco_limite'],
        "humido" => $config['humido_limite'],
        "ultima_rega" => $ultima_rega ? date('d/m H:i', strtotime($ultima_rega['data'] . " " . $ultima_rega['hora'])) : "Sem registo"
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inteligente</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; flex-direction: column; align-items: center; min-height: 100vh; margin: 0; padding: 20px; }
        .card { background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; width: 100%; max-width: 400px; margin-bottom: 20px; }
        .gauge-container { position: relative; margin: 20px auto; }
        .value-display { position: absolute; top: 60%; left: 50%; transform: translate(-50%, -50%); font-size: 3rem; font-weight: bold; }
        .info-rega { margin-top: 10px; padding: 15px; background: #eef6ff; border-radius: 12px; border-left: 5px solid #007bff; text-align: left; }
        .config-panel { background: #fff; padding: 1.5rem; border-radius: 20px; width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .config-row { display: flex; justify-content: space-between; margin: 10px 0; align-items: center; }
        input[type="number"] { width: 60px; padding: 8px; border-radius: 8px; border: 1px solid #ddd; text-align: center; }
        .btn { background: #007bff; color: white; border: none; padding: 10px; width: 100%; border-radius: 10px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="card">
    <h2>Nível de Humidade</h2>
    <div class="gauge-container">
        <canvas id="gaugeChart"></canvas>
        <div class="value-display" id="humidade-valor">--%</div>
    </div>
    <div class="info-rega">
        <small style="color:#555; display:block;">🚿 Última Rega Detectada:</small>
        <span id="txt-ultima-rega" style="font-weight:bold; color:#007bff;">A carregar...</span>
    </div>
    <p style="font-size: 0.8rem; color: #999; margin-top: 15px;" id="hora-atualizacao">Sincronizando...</p>
</div>

<div class="config-panel">
    <h3 style="margin-top:0;">⚙️ Configurações de Rega</h3>
    <form method="POST">
        <div class="config-row">
            <span>Solo Seco (Ligar rega):</span>
            <input type="number" name="seco_limite" id="input-seco" min="0" max="100">
        </div>
        <div class="config-row">
            <span>Solo Húmido (Parar rega):</span>
            <input type="number" name="humido_limite" id="input-humido" min="0" max="100">
        </div>
        <button type="submit" name="update_config" class="btn">Guardar no Arduino</button>
    </form>
</div>

<script>
    const ctx = document.getElementById('gaugeChart').getContext('2d');
    const gaugeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [0, 100],
                backgroundColor: ['#007bff', '#e9ecef'],
                circumference: 180,
                rotation: 270,
                cutout: '80%',
                borderRadius: 10
            }]
        },
        options: { responsive: true, aspectRatio: 1.5, plugins: { legend: { display: false } } }
    });

    function atualizar() {
        fetch('recebe.php?ajax=1')
            .then(r => r.json())
            .then(data => {
                const v = parseInt(data.percentagem);
                gaugeChart.data.datasets[0].data = [v, 100 - v];
                let cor = v < data.seco ? '#dc3545' : (v < data.humido ? '#ffc107' : '#28a745');
                gaugeChart.data.datasets[0].backgroundColor[0] = cor;
                gaugeChart.update();

                document.getElementById('humidade-valor').innerText = v + "%";
                document.getElementById('humidade-valor').style.color = cor;
                document.getElementById('txt-ultima-rega').innerText = data.ultima_rega;
                document.getElementById('hora-atualizacao').innerText = "Última atualização: " + data.hora;
                
                // Atualiza inputs apenas se o utilizador não estiver a escrever
                if(document.activeElement.tagName !== "INPUT") {
                    document.getElementById('input-seco').value = data.seco;
                    document.getElementById('input-humido').value = data.humido;
                }
            });
    }

    setInterval(atualizar, 3000); // 3 segundos é suficiente e mais estável que 100ms
    atualizar();
</script>
</body>
</html>