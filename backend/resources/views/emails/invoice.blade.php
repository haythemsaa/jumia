<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #FF9900;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #FF9900;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .info-box {
            background-color: white;
            padding: 15px;
            border-left: 4px solid #FF9900;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ICHRI Tunisia</h1>
        <h2>Votre facture</h2>
    </div>

    <div class="content">
        <p>Bonjour {{ $user->name }},</p>

        <p>Merci pour votre commande ! Veuillez trouver votre facture en pièce jointe.</p>

        <div class="info-box">
            <strong>Numéro de commande:</strong> {{ $order->order_number }}<br>
            <strong>Date:</strong> {{ $order->created_at->format('d/m/Y') }}<br>
            <strong>Montant total:</strong> {{ number_format($order->total_amount, 2) }} TND
        </div>

        <p>La facture est également disponible dans votre espace client.</p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} ICHRI Tunisia. Tous droits réservés.</p>
        <p>
            <a href="mailto:support@ichri.tn">Support</a> |
            <a href="#">Conditions d'utilisation</a>
        </p>
    </div>
</body>
</html>
