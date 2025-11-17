# ICHRI Tunisia - API Documentation Complete

## 📚 Table of Contents
- [Authentication](#authentication)
- [Products](#products)
- [Orders](#orders)
- [Payments](#payments)
- [Loyalty Program](#loyalty-program)
- [Flash Sales](#flash-sales)
- [Reviews](#reviews)
- [Chat](#chat)
- [Invoices](#invoices)
- [Notifications](#notifications)

## Base URL
- **Development:** `http://localhost:8000/api`
- **Production:** `https://api.ichri.tn`

## Authentication

All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

### Register
```http
POST /register
Content-Type: application/json

{
  "name": "Ahmed Ben Ali",
  "email": "ahmed@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+21612345678"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Ahmed Ben Ali",
    "email": "ahmed@example.com",
    "referral_code": "ABC12345"
  },
  "token": "1|abc123..."
}
```

### Login
```http
POST /login
Content-Type: application/json

{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

### Logout
```http
POST /logout
Authorization: Bearer {token}
```

## Products

### List Products
```http
GET /products?category_id=1&search=phone&min_price=100&max_price=500
```

**Query Parameters:**
- `category_id` (optional): Filter by category
- `search` (optional): Search in name/description
- `min_price` (optional): Minimum price filter
- `max_price` (optional): Maximum price filter
- `sort_by` (optional): price_asc, price_desc, newest, popular
- `per_page` (optional): Results per page (default: 15)

### Get Product Details
```http
GET /products/{id}
```

**Response includes:**
- Product details
- Average rating
- Review count
- Related products
- Vendor information

### Create Product (Vendor only)
```http
POST /products
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "iPhone 15 Pro",
  "description": "Latest iPhone model",
  "price": 4999.99,
  "stock": 50,
  "category_id": 1,
  "status": "active"
}
```

## Orders

### Create Order
```http
POST /orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipping_address": "123 Avenue Habib Bourguiba, Tunis",
  "payment_method": "edinar",
  "coupon_code": "WELCOME10"
}
```

**Payment Methods:**
- `cash` - Cash on Delivery
- `edinar` - E-Dinar (Tunisian electronic payment)
- `konnect` - Konnect Payment Gateway
- `d17` - D17 Payment

### Get User Orders
```http
GET /orders
Authorization: Bearer {token}
```

### Get Order Details
```http
GET /orders/{id}
Authorization: Bearer {token}
```

### Cancel Order
```http
POST /orders/{id}/cancel
Authorization: Bearer {token}
```

**Note:** Only pending/confirmed orders can be cancelled.

## Payments

### Initiate E-Dinar Payment
```http
POST /payments/edinar/initiate
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 123
}
```

**Response:**
```json
{
  "payment_url": "https://payment.edinar.tn/...",
  "transaction_id": "TXN123456"
}
```

### Initiate Konnect Payment
```http
POST /payments/konnect/initiate
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 123
}
```

### Initiate D17 Payment
```http
POST /payments/d17/initiate
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 123
}
```

### Check Payment Status
```http
GET /payments/{id}/status
Authorization: Bearer {token}
```

## Loyalty Program

### Get Loyalty Dashboard
```http
GET /loyalty/dashboard
Authorization: Bearer {token}
```

**Response:**
```json
{
  "points": 5500,
  "tier": {
    "name": "Gold",
    "slug": "gold",
    "benefits": {
      "discount_percentage": 10,
      "points_multiplier": 3,
      "free_shipping_threshold": 50,
      "early_access": true
    }
  },
  "next_tier": {
    "name": "Platinum",
    "min_points": 15000
  },
  "points_to_next_tier": 9500
}
```

### Get Available Missions
```http
GET /loyalty/missions
Authorization: Bearer {token}
```

**Response:**
```json
[
  {
    "id": 1,
    "title": "Make your first purchase",
    "description": "Complete your first order",
    "reward_points": 100,
    "type": "order_placed",
    "progress": {
      "current": 0,
      "required": 1,
      "percentage": 0
    }
  }
]
```

### Apply Referral Code
```http
POST /loyalty/referral/apply
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "ABC12345"
}
```

### Redeem Points
```http
POST /loyalty/redeem
Authorization: Bearer {token}
Content-Type: application/json

{
  "points": 500,
  "type": "discount_voucher"
}
```

**Redemption Types:**
- `discount_voucher` - Get discount coupon
- `free_shipping` - Free shipping voucher
- `gift` - Redeem for gift

## Flash Sales

### Get Active Flash Sales
```http
GET /flash-sales
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "Weekend Super Sale",
    "start_time": "2025-11-20 00:00:00",
    "end_time": "2025-11-22 23:59:59",
    "eligible_tiers": ["gold", "platinum"],
    "products": [
      {
        "id": 1,
        "name": "iPhone 15",
        "original_price": 4999,
        "flash_price": 3999,
        "discount_percentage": 20,
        "stock_limit": 50,
        "sold": 23,
        "per_customer_limit": 2
      }
    ]
  }
]
```

### Get Upcoming Flash Sales
```http
GET /flash-sales/upcoming
```

### Check Eligibility
```http
GET /flash-sales/check-eligibility/{flashSaleProductId}
Authorization: Bearer {token}
```

## Reviews

### Submit Review
```http
POST /reviews
Authorization: Bearer {token}
Content-Type: multipart/form-data

product_id: 1
rating: 5
title: "Excellent product!"
comment: "Very satisfied with my purchase"
photos[]: [file1.jpg, file2.jpg]
```

### Get Product Reviews
```http
GET /products/{id}/reviews?sort=helpful
```

**Sort Options:**
- `newest` - Most recent first
- `helpful` - Most helpful first
- `rating_high` - Highest rating first
- `rating_low` - Lowest rating first

### Vendor Response (Vendor only)
```http
POST /reviews/{id}/respond
Authorization: Bearer {token}
Content-Type: application/json

{
  "response": "Thank you for your feedback!"
}
```

### Moderate Review (Admin only)
```http
POST /admin/reviews/{id}/approve
POST /admin/reviews/{id}/reject
```

## Chat

### Start Conversation
```http
POST /conversations
Authorization: Bearer {token}
Content-Type: application/json

{
  "vendor_id": 5,
  "subject": "Product inquiry"
}
```

### Get Conversations
```http
GET /conversations
Authorization: Bearer {token}
```

### Send Message
```http
POST /conversations/{id}/messages
Authorization: Bearer {token}
Content-Type: multipart/form-data

message: "Hello, I have a question about this product"
attachment: [file.jpg]
```

### Mark as Read
```http
POST /conversations/{id}/read
Authorization: Bearer {token}
```

### Delete Message
```http
DELETE /messages/{id}
Authorization: Bearer {token}
```

**Note:** Messages can only be deleted within 5 minutes of sending.

## Invoices

### Download Invoice PDF
```http
GET /invoices/{orderId}/download
Authorization: Bearer {token}
```

### View Invoice
```http
GET /invoices/{orderId}
Authorization: Bearer {token}
```

### Email Invoice
```http
POST /invoices/{orderId}/email
Authorization: Bearer {token}
```

## Notifications

### Get Notifications
```http
GET /notifications
Authorization: Bearer {token}
```

### Mark as Read
```http
POST /notifications/{id}/read
Authorization: Bearer {token}
```

### Update FCM Token
```http
POST /notifications/fcm-token
Authorization: Bearer {token}
Content-Type: application/json

{
  "fcm_token": "fcm_token_here"
}
```

### Notification Preferences
```http
PUT /notifications/preferences
Authorization: Bearer {token}
Content-Type: application/json

{
  "email_notifications": true,
  "sms_notifications": false,
  "push_notifications": true
}
```

## Multi-Language Support

All endpoints support multi-language responses. Specify language using:

**Header:**
```
X-Locale: fr
```

**Query Parameter:**
```
?lang=ar
```

**Supported Languages:**
- `fr` - Français (Default)
- `ar` - العربية (Arabic with RTL support)
- `en` - English

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

## Rate Limiting

- **Guest users:** 60 requests per minute
- **Authenticated users:** 120 requests per minute
- **Admin/Vendor:** 200 requests per minute

## Status Codes

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

## OpenAPI/Swagger

OpenAPI 3.0 documentation is available at:
```
GET /api/documentation
```

JSON Schema:
```
GET /api/api-docs.json
```

## Support

For API support, contact: tech@ichri.tn
