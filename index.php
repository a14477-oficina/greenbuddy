<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenBuddy - O Teu Vaso Inteligente</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos específicos da Landing Page */
        .navbar {
            width: 100%;
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: absolute;
            top: 0;
            box-sizing: border-box;
        }

        .btn-login {
            background: #2d5a27;
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .hero {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 120px 50px;
            flex-wrap: wrap;
        }

        .hero-text { max-width: 500px; }
        .hero-text h1 { font-size: 3.5rem; color: #2d5a27; margin-bottom: 10px; }
        .hero-text p { font-size: 1.2rem; color: #555; line-height: 1.6; }

        .product-img img {
            max-width: 450px;
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.1));
        }

        .price-tag {
            font-size: 2rem;
            color: #007bff;
            font-weight: bold;
            margin: 20px 0;
            display: block;
        }

        .btn-buy {
            background: #ffc107;
            color: #333;
            padding: 15px 40px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 1.3rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="login.php" class="btn-login">Iniciar Sessão</a>
        <div class="logo-mini">
            <img src="img/logotipo_PAP.png" alt="Logo" style="height: 50px;">
        </div>
    </nav>

    <section class="hero">
        <div class="hero-text">
            <h1>GreenBuddy</h1>
            <p>Nunca mais deixes as tuas plantas morrerem. O sistema de rega inteligente que cuida do que é importante para ti, de forma automática e controlada pelo telemóvel.</p>
            <span class="price-tag">49,99€</span>
            <a href="#" class="btn-buy">Comprar Agora</a>
        </div>
        
        <div class="product-img">
            <img src="img/logotipo_PAP.png" alt="GreenBuddy Vaso">
        </div>
    </section>

</body>
</html>