<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.6;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #FF9900;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #FF9900;
            font-size: 32pt;
            margin-bottom: 5px;
        }
        .header-info {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .invoice-title {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .invoice-meta {
            margin-bottom: 20px;
        }
        .invoice-meta table {
            width: 100%;
        }
        .invoice-meta td {
            padding: 5px 10px;
        }
        .invoice-meta .label {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 40%;
        }
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .address-box {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .address-box h3 {
            color: #FF9900;
            margin-bottom: 10px;
            font-size: 12pt;
        }
        .address-gap {
            display: table-cell;
            width: 4%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead {
            background-color: #FF9900;
            color: white;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table th {
            font-weight: bold;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .items-table .text-right {
            text-align: right;
        }
        .totals {
            width: 40%;
            margin-left: auto;
            margin-bottom: 30px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        .totals .label {
            font-weight: bold;
        }
        .totals .total-row {
            background-color: #FF9900;
            color: white;
            font-size: 13pt;
            font-weight: bold;
        }
        .payment-info {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #FF9900;
        }
        .payment-info h3 {
            color: #FF9900;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10pt;
        }
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-failed {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>ICHRI</h1>
        <div class="header-info">
            <div class="header-left">
                <strong>{{ $company['name'] }}</strong><br>
                {{ $company['address'] }}<br>
                Tél: {{ $company['phone'] }}<br>
                Email: {{ $company['email'] }}<br>
                Web: {{ $company['website'] }}
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTURE</div>
                N° {{ $invoice_number }}<br>
                Date: {{ $invoice_date }}
            </div>
        </div>
    </div>

    <!-- Invoice Meta -->
    <div class="invoice-meta">
        <table>
            <tr>
                <td class="label">Numéro de commande</td>
                <td>{{ $order->order_number }}</td>
                <td class="label">Date de commande</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Statut paiement</td>
                <td>
                    @if($order->payment_status === 'paid')
                        <span class="status-badge status-paid">PAYÉ</span>
                    @elseif($order->payment_status === 'pending')
                        <span class="status-badge status-pending">EN ATTENTE</span>
                    @else
                        <span class="status-badge status-failed">ÉCHOUÉ</span>
                    @endif
                </td>
                <td class="label">Méthode de paiement</td>
                <td>{{ strtoupper($order->payment_method ?? 'N/A') }}</td>
            </tr>
        </table>
    </div>

    <!-- Addresses -->
    <div class="addresses">
        <div class="address-box">
            <h3>Client</h3>
            <strong>{{ $order->user->name }}</strong><br>
            Email: {{ $order->user->email }}<br>
            @if($order->user->phone)
            Tél: {{ $order->user->phone }}<br>
            @endif
        </div>
        <div class="address-gap"></div>
        <div class="address-box">
            <h3>Adresse de livraison</h3>
            @if($order->shippingAddress)
                {{ $order->shippingAddress->street }}<br>
                {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->postal_code }}<br>
                {{ $order->shippingAddress->country }}
            @else
                Non spécifiée
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>SKU</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Quantité</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->product_name }}</strong>
                    @if($item->variant_details)
                        <br><small>{{ implode(', ', $item->variant_details) }}</small>
                    @endif
                </td>
                <td>{{ $item->product_sku }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }} TND</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->total_price, 2) }} TND</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <table>
            <tr>
                <td class="label">Sous-total</td>
                <td class="text-right">{{ number_format($order->subtotal, 2) }} TND</td>
            </tr>
            @if($order->shipping_cost > 0)
            <tr>
                <td class="label">Frais de livraison</td>
                <td class="text-right">{{ number_format($order->shipping_cost, 2) }} TND</td>
            </tr>
            @endif
            @if($order->tax_amount > 0)
            <tr>
                <td class="label">TVA (19%)</td>
                <td class="text-right">{{ number_format($order->tax_amount, 2) }} TND</td>
            </tr>
            @endif
            @if($order->discount_amount > 0)
            <tr>
                <td class="label">Remise</td>
                <td class="text-right">-{{ number_format($order->discount_amount, 2) }} TND</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label">TOTAL</td>
                <td class="text-right">{{ number_format($order->total_amount, 2) }} TND</td>
            </tr>
        </table>
    </div>

    <!-- Payment Info -->
    @if($order->paymentTransactions->isNotEmpty())
    <div class="payment-info">
        <h3>Informations de paiement</h3>
        @foreach($order->paymentTransactions as $transaction)
            <strong>Transaction #{{ $transaction->transaction_id ?? 'N/A' }}</strong><br>
            Gateway: {{ strtoupper($transaction->gateway) }}<br>
            Statut: {{ strtoupper($transaction->status) }}<br>
            @if($transaction->completed_at)
            Date: {{ $transaction->completed_at->format('d/m/Y H:i') }}<br>
            @endif
        @endforeach
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Merci pour votre commande chez ICHRI Tunisia</p>
        <p>Pour toute question concernant cette facture, contactez-nous à {{ $company['email'] }}</p>
        <p style="margin-top: 10px;">
            <small>
                {{ $company['name'] }} - Registre du commerce: {{ $company['registration'] }} -
                TVA: {{ $company['vat_number'] }}
            </small>
        </p>
    </div>
</body>
</html>
