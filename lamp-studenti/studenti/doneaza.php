<?php
session_start();
require 'db.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doneaza - Labuta Fericita</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .donation-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .donation-container h2 {
            color: #333;
        }
        .donation-container p {
            color: #666;
            line-height: 1.6;
        }
        .donation-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .donation-method {
            padding: 20px;
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .donation-method:hover {
            border-color: #4CAF50;
            background-color: #f9fff9;
        }
        .donation-method h3 {
            margin-top: 0;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <header>
        <h1>🤝 Ajuta-ne sa Salvam Vietile</h1>
        <p>Donarile tale ne ajuta sa ingrijim si sa salvam cainii din adaposturi</p>
    </header>

    <div class="donation-container">
        <h2>Modalitati de Donare</h2>
        <p>Labuta Fericita este o organizatie non-profit dedicata salvarii si ingrijirii cainilor din adaposturi. Orice donatie, mica sau mare, ne ajuta sa oferim mancare, medicamente si ingrijire veterinara acestor prieteni necuvantatori.</p>

        <div class="donation-methods">
            <div class="donation-method">
                <h3>💳 Plata Online</h3>
                <p>Transfer bancar direct in contul nostru</p>
                <p><strong>IBAN: RO12ABCD1234567890</strong></p>
            </div>
            <div class="donation-method">
                <h3>📞 Plata Prin SMS</h3>
                <p>Trimite SMS cu textul "LABUTA" la <strong>1234</strong></p>
                <p>2 EUR pe SMS</p>
            </div>
            <div class="donation-method">
                <h3>🎁 Donatie Materiale</h3>
                <p>Mancare pentru caini, jucarii, pturi</p>
                <p>Contacteaza-ne pentru detalii</p>
            </div>
        </div>

        <h2>De Ce sa Donezi?</h2>
        <ul style="color: #666; line-height: 1.8;">
            <li>✓ Ajuti cainii din adaposturi sa fie salvati</li>
            <li>✓ Orice donatie este deductibila la taxe</li>
            <li>✓ Primesti rapoarte regulate despre cum sunt folosite fondurile</li>
            <li>✓ Esti parte a unei comunități care se ingrijeste de animale</li>
        </ul>

        <p style="text-align: center; margin-top: 40px; color: #4CAF50; font-weight: bold;">
            Multumim din inima pentru sprijinul tau! 🐾
        </p>
    </div>

</body>
</html>
