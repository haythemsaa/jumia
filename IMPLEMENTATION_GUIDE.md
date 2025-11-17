# Guide d'Implémentation - Fonctionnalités Critiques ICHRI

## 🔴 Phase 1: Fonctionnalités Critiques (Priorité Haute)

---

## 1. Système de Paiement en Ligne

### Gateway de Paiement Tunisiens

#### A. E-Dinar (Monétique Tunisie)
```php
// backend/app/Services/Payment/EDinarGateway.php
class EDinarGateway implements PaymentGatewayInterface
{
    private $merchantId;
    private $terminalId;
    private $merchantPassword;

    public function initiatePayment(Order $order): string
    {
        $params = [
            'MerchantId' => $this->merchantId,
            'TerminalId' => $this->terminalId,
            'Amount' => $order->total * 1000, // Millimes
            'OrderId' => $order->order_number,
            'Currency' => 788, // TND
            'Language' => 'fr',
            'ReturnUrl' => route('payment.return'),
            'CancelUrl' => route('payment.cancel'),
        ];

        // Signature SHA256
        $params['Signature'] = $this->generateSignature($params);

        return $this->buildPaymentUrl($params);
    }
}
```

#### B. Konnect Payment
```php
// backend/app/Services/Payment/KonnectGateway.php
class KonnectGateway implements PaymentGatewayInterface
{
    public function initiatePayment(Order $order): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('payment.konnect.api_key'),
        ])->post('https://api.konnect.network/api/v2/payments/init-payment', [
            'receiverWalletId' => config('payment.konnect.wallet_id'),
            'amount' => $order->total * 1000,
            'orderId' => $order->order_number,
            'webhook' => route('payment.webhook.konnect'),
            'successUrl' => route('order.success'),
            'failUrl' => route('order.fail'),
        ]);

        return $response->json();
    }
}
```

#### C. D17 Payment
```javascript
// frontend/lib/services/d17Payment.ts
export class D17PaymentService {
  async initPayment(orderId: string, amount: number) {
    const response = await axios.post('/api/payments/d17/init', {
      order_id: orderId,
      amount: amount,
      return_url: `${window.location.origin}/payment/return`,
    });

    // Redirect to D17 payment page
    window.location.href = response.data.payment_url;
  }
}
```

### Base de Données
```sql
-- Migration: add payment gateways
CREATE TABLE payment_transactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED,
    payment_id BIGINT UNSIGNED,
    gateway VARCHAR(50), -- edinar, konnect, d17
    transaction_id VARCHAR(255) UNIQUE,
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'TND',
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded'),
    gateway_response JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (payment_id) REFERENCES payments(id)
);
```

---

## 2. Système de Notifications

### A. Push Notifications (Firebase Cloud Messaging)

#### Backend
```php
// backend/app/Services/NotificationService.php
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'));
        $this->messaging = $firebase->createMessaging();
    }

    public function sendOrderNotification(User $user, Order $order, string $status)
    {
        $deviceTokens = $user->deviceTokens->pluck('token')->toArray();

        $message = CloudMessage::new()
            ->withNotification([
                'title' => $this->getTitle($status),
                'body' => $this->getBody($order, $status),
                'image' => asset('images/logo.png'),
            ])
            ->withData([
                'order_id' => $order->id,
                'status' => $status,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);

        $this->messaging->sendMulticast($message, $deviceTokens);

        // Also save to database
        Notification::create([
            'user_id' => $user->id,
            'type' => 'order_status',
            'title' => $this->getTitle($status),
            'body' => $this->getBody($order, $status),
            'data' => ['order_id' => $order->id],
            'read_at' => null,
        ]);
    }
}
```

#### Flutter
```dart
// mobile/lib/services/notification_service.dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class NotificationService {
  final FirebaseMessaging _fcm = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  Future<void> initialize() async {
    // Request permission
    await _fcm.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    // Get FCM token
    String? token = await _fcm.getToken();
    if (token != null) {
      await _saveTokenToBackend(token);
    }

    // Handle foreground messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      _showLocalNotification(message);
    });

    // Handle background messages
    FirebaseMessaging.onBackgroundMessage(_backgroundHandler);
  }

  Future<void> _showLocalNotification(RemoteMessage message) async {
    const AndroidNotificationDetails androidDetails =
        AndroidNotificationDetails(
      'ichri_channel',
      'ICHRI Notifications',
      importance: Importance.max,
      priority: Priority.high,
    );

    await _localNotifications.show(
      message.hashCode,
      message.notification?.title,
      message.notification?.body,
      NotificationDetails(android: androidDetails),
    );
  }
}
```

### B. Email Notifications

```php
// backend/app/Mail/OrderConfirmation.php
use Illuminate\Mail\Mailable;

class OrderConfirmation extends Mailable
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->view('emails.orders.confirmation')
            ->subject('Confirmation de commande #' . $this->order->order_number)
            ->with([
                'order' => $this->order,
                'items' => $this->order->items,
                'total' => $this->order->total,
            ]);
    }
}
```

```blade
{{-- backend/resources/views/emails/orders/confirmation.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { background: #FF9900; color: white; padding: 20px; }
        .content { padding: 20px; }
        .order-details { background: #f5f5f5; padding: 15px; margin: 20px 0; }
        .total { font-size: 24px; font-weight: bold; color: #FF9900; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ICHRI Tunisia</h1>
    </div>
    <div class="content">
        <h2>Merci pour votre commande!</h2>
        <p>Bonjour {{ $order->user->name }},</p>
        <p>Votre commande <strong>#{{ $order->order_number }}</strong> a été confirmée.</p>

        <div class="order-details">
            <h3>Détails de la commande:</h3>
            @foreach($items as $item)
            <div>
                {{ $item->product->name }} × {{ $item->quantity }}
                = {{ $item->price * $item->quantity }} TND
            </div>
            @endforeach
            <hr>
            <div class="total">Total: {{ $total }} TND</div>
        </div>

        <p>Vous recevrez un email de confirmation dès l'expédition.</p>
        <p><a href="{{ route('orders.show', $order->id) }}">Suivre ma commande</a></p>
    </div>
</body>
</html>
```

### C. SMS Notifications (Tunisie)

```php
// backend/app/Services/SMSService.php
class SMSService
{
    public function sendOrderSMS(Order $order, string $status)
    {
        $phone = $order->user->phone;
        $message = $this->formatMessage($order, $status);

        // Utiliser un provider SMS tunisien (ex: Tunis SMS)
        Http::post('https://api.tunissms.tn/api/SendSMS', [
            'username' => config('sms.username'),
            'password' => config('sms.password'),
            'sender' => 'ICHRI',
            'recipient' => $phone,
            'message' => $message,
        ]);
    }

    private function formatMessage(Order $order, string $status): string
    {
        return match($status) {
            'confirmed' => "ICHRI: Commande #{$order->order_number} confirmée. Total: {$order->total} TND",
            'shipped' => "ICHRI: Votre commande #{$order->order_number} est expédiée!",
            'delivered' => "ICHRI: Commande #{$order->order_number} livrée. Merci!",
            default => "ICHRI: Mise à jour commande #{$order->order_number}",
        };
    }
}
```

---

## 3. Système d'Avis et Reviews

### Backend Models & Controllers

```php
// backend/app/Models/ProductReview.php (déjà existant - à compléter)
class ProductReview extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'rating', 'title', 'comment',
        'is_verified_purchase', 'helpful_count', 'images', 'status'
    ];

    protected $casts = [
        'images' => 'array',
        'is_verified_purchase' => 'boolean',
    ];

    // Ajouter ces méthodes
    public function markAsHelpful()
    {
        $this->increment('helpful_count');
    }

    public function addImages(array $images)
    {
        $this->images = array_merge($this->images ?? [], $images);
        $this->save();
    }

    public function vendorResponse(): HasOne
    {
        return $this->hasOne(ReviewResponse::class);
    }
}

// backend/app/Http/Controllers/Api/ReviewController.php
class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|min:10',
            'images.*' => 'image|max:5120', // 5MB max
        ]);

        // Vérifier si l'utilisateur a acheté le produit
        $hasPurchased = Order::where('user_id', auth()->id())
            ->whereHas('items', fn($q) => $q->where('product_id', $product->id))
            ->where('status', 'delivered')
            ->exists();

        $review = $product->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => $hasPurchased,
            'status' => 'pending', // Modération
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $images[] = $path;
            }
            $review->update(['images' => $images]);
        }

        // Recalculer le rating du produit
        $product->updateRating();

        return response()->json($review, 201);
    }

    public function markHelpful(ProductReview $review)
    {
        $review->markAsHelpful();
        return response()->json(['message' => 'Marked as helpful']);
    }

    public function vendorRespond(Request $request, ProductReview $review)
    {
        // Vérifier que c'est le vendeur du produit
        if ($review->product->vendor_id !== auth()->user()->vendor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $review->vendorResponse()->updateOrCreate(
            ['product_review_id' => $review->id],
            [
                'vendor_id' => auth()->user()->vendor->id,
                'response' => $request->response,
            ]
        );

        return response()->json(['message' => 'Response added']);
    }
}
```

### Frontend Component

```typescript
// frontend/components/products/ReviewForm.tsx
'use client';

import { useState } from 'react';
import { FiStar, FiUpload } from 'react-icons/fi';
import { reviewService } from '@/lib/api/reviews';
import toast from 'react-hot-toast';

export default function ReviewForm({ productId }: { productId: number }) {
  const [rating, setRating] = useState(0);
  const [hoverRating, setHoverRating] = useState(0);
  const [title, setTitle] = useState('');
  const [comment, setComment] = useState('');
  const [images, setImages] = useState<File[]>([]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    const formData = new FormData();
    formData.append('rating', rating.toString());
    formData.append('title', title);
    formData.append('comment', comment);
    images.forEach(img => formData.append('images[]', img));

    try {
      await reviewService.submitReview(productId, formData);
      toast.success('Avis soumis avec succès!');
      // Reset form
    } catch (error) {
      toast.error('Erreur lors de la soumission');
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label className="block mb-2">Votre note:</label>
        <div className="flex space-x-2">
          {[1, 2, 3, 4, 5].map((star) => (
            <button
              key={star}
              type="button"
              onClick={() => setRating(star)}
              onMouseEnter={() => setHoverRating(star)}
              onMouseLeave={() => setHoverRating(0)}
            >
              <FiStar
                size={32}
                className={
                  star <= (hoverRating || rating)
                    ? 'fill-orange-500 text-orange-500'
                    : 'text-gray-300'
                }
              />
            </button>
          ))}
        </div>
      </div>

      <input
        type="text"
        placeholder="Titre de votre avis"
        value={title}
        onChange={(e) => setTitle(e.target.value)}
        className="w-full px-4 py-2 border rounded"
        required
      />

      <textarea
        placeholder="Votre avis détaillé (minimum 10 caractères)"
        value={comment}
        onChange={(e) => setComment(e.target.value)}
        className="w-full px-4 py-2 border rounded h-32"
        required
        minLength={10}
      />

      <div>
        <label className="flex items-center space-x-2 cursor-pointer">
          <FiUpload />
          <span>Ajouter des photos</span>
          <input
            type="file"
            multiple
            accept="image/*"
            className="hidden"
            onChange={(e) => setImages(Array.from(e.target.files || []))}
          />
        </label>
        {images.length > 0 && (
          <p className="text-sm text-gray-600 mt-2">
            {images.length} image(s) sélectionnée(s)
          </p>
        )}
      </div>

      <button
        type="submit"
        className="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600"
      >
        Publier mon avis
      </button>
    </form>
  );
}
```

---

## 4. Système de Retours et Remboursements

### Backend Implementation

```php
// Migration
Schema::create('return_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained();
    $table->foreignId('order_item_id')->nullable()->constrained();
    $table->foreignId('user_id')->constrained();
    $table->enum('type', ['return', 'exchange', 'refund']);
    $table->enum('reason', [
        'defective', 'wrong_item', 'not_as_described',
        'damaged', 'changed_mind', 'other'
    ]);
    $table->text('description');
    $table->json('images')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected', 'processing', 'completed']);
    $table->text('admin_notes')->nullable();
    $table->string('return_label_url')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();
});

// backend/app/Http/Controllers/Api/ReturnController.php
class ReturnController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'type' => 'required|in:return,exchange,refund',
            'reason' => 'required|string',
            'description' => 'required|string|min:20',
            'images.*' => 'image|max:5120',
        ]);

        // Vérifier que la commande appartient à l'utilisateur
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Vérifier période de retour (30 jours)
        if ($order->delivered_at && $order->delivered_at->addDays(30)->isPast()) {
            return response()->json([
                'error' => 'La période de retour est expirée'
            ], 422);
        }

        $returnRequest = ReturnRequest::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        // Notifier le vendeur et l'admin
        Notification::send([admin(), $order->vendor->user],
            new ReturnRequestNotification($returnRequest));

        return response()->json($returnRequest, 201);
    }

    public function approve(ReturnRequest $returnRequest)
    {
        $returnRequest->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Générer étiquette de retour
        $label = $this->generateReturnLabel($returnRequest);
        $returnRequest->update(['return_label_url' => $label]);

        // Notifier le client
        $returnRequest->user->notify(
            new ReturnApprovedNotification($returnRequest)
        );

        return response()->json($returnRequest);
    }

    private function generateReturnLabel(ReturnRequest $returnRequest): string
    {
        // Intégration avec transporteur pour générer étiquette
        // Retourner URL du PDF
        return 'https://...';
    }
}
```

---

## 5. Configuration Environnements

### .env additions

```bash
# Payment Gateways
EDINAR_MERCHANT_ID=
EDINAR_TERMINAL_ID=
EDINAR_PASSWORD=
EDINAR_API_URL=https://test.edinar.tn/payment

KONNECT_API_KEY=
KONNECT_WALLET_ID=

D17_MERCHANT_ID=
D17_API_KEY=

# Firebase (Notifications)
FIREBASE_CREDENTIALS=storage/firebase-credentials.json
FIREBASE_DATABASE_URL=https://ichri-tunisia.firebaseio.com

# SMS
SMS_USERNAME=
SMS_PASSWORD=
SMS_API_URL=https://api.tunissms.tn/api/SendSMS

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@ichri.tn
MAIL_FROM_NAME="ICHRI Tunisia"
```

---

## 📦 Packages à Installer

### Backend (Laravel)
```bash
composer require kreait/firebase-php
composer require twilio/sdk  # Pour SMS alternatif
composer require barryvdh/laravel-dompdf  # Pour factures PDF
composer require spatie/laravel-backup  # Backups automatiques
composer require spatie/laravel-permission  # Gestion permissions avancée
```

### Frontend (Next.js)
```bash
npm install firebase
npm install @stripe/stripe-js  # Si Stripe aussi
npm install react-image-gallery
npm install react-zoom-pan-pinch
```

### Mobile (Flutter)
```bash
flutter pub add firebase_core
flutter pub add firebase_messaging
flutter pub add flutter_local_notifications
flutter pub add image_picker
```

---

*Document technique - Dernière mise à jour: 17 Novembre 2025*
