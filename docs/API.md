# API Documentation - ICHRI Tunisia

Documentation complète de l'API REST.

## Base URL

```
Production: https://api.ichri.tn/api
Development: http://localhost:8000/api
```

## Authentication

JWT Bearer Token:
```
Authorization: Bearer {token}
```

## Endpoints

### Authentication

#### POST /auth/register
Créer un nouveau compte

**Request:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+216 XX XXX XXX"
}
```

**Response:**
```json
{
  "token": "eyJ0eXAiOi...",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com"
  }
}
```

#### POST /auth/login
Connexion utilisateur

**Request:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

#### POST /auth/logout
Déconnexion (requires auth)

### Products

#### GET /products
Liste des produits

**Query Parameters:**
- `page` (int): Page number
- `per_page` (int): Items per page
- `category` (int): Filter by category
- `search` (string): Search query
- `min_price` (float): Minimum price
- `max_price` (float): Maximum price
- `sort_by` (string): newest|price_asc|price_desc|popular

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "price": 99.99,
      "image": "image.jpg",
      "stock": 10
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 10,
  "total": 50
}
```

#### GET /products/{id}
Détail produit

#### POST /products (Admin only)
Créer produit

### Cart

#### GET /cart
Obtenir le panier

#### POST /cart/add
Ajouter au panier

**Request:**
```json
{
  "product_id": 1,
  "quantity": 2,
  "variant_id": null
}
```

#### PUT /cart/update/{itemId}
Mettre à jour quantité

#### DELETE /cart/remove/{itemId}
Supprimer du panier

### Orders

#### POST /orders/create
Créer une commande

**Request:**
```json
{
  "shipping_address": "123 Main St",
  "shipping_method_id": 1,
  "payment_method": "cash",
  "notes": "Leave at door"
}
```

#### GET /orders
Liste des commandes

#### GET /orders/{id}
Détail commande

### More Endpoints

Complete API documentation available at:
https://docs.ichri.tn/api
