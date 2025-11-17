<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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
    </div>

    <div class="content">
        <h2>{{ $title }}</h2>

        <p>Bonjour {{ $user->name }},</p>

        <p>{{ $body }}</p>

        @if(isset($data['order_number']))
        <div class="info-box">
            <strong>Numéro de commande:</strong> {{ $data['order_number'] }}<br>
            @if(isset($data['status']))
            <strong>Statut:</strong> {{ ucfirst($data['status']) }}<br>
            @endif
            @if(isset($data['amount']))
            <strong>Montant:</strong> {{ $data['amount'] }} TND
            @endif
        </div>
        @endif

        @if(isset($data['action_url']))
        <a href="{{ $data['action_url'] }}" class="button">Voir les détails</a>
        @endif
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} ICHRI Tunisia. Tous droits réservés.</p>
        <p>Cet email a été envoyé à {{ $user->email }}</p>
        <p>
            <a href="mailto:support@ichri.tn">Support</a> |
            <a href="#">Conditions d'utilisation</a> |
            <a href="#">Politique de confidentialité</a>
        </p>
    </div>
</body>
</html>
