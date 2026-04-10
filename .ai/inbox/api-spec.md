# LoomCraft API Specification (v1)

This document provides instructions for the Backend AI Agent to implement the API endpoints required for the LoomCraft Admin/Vendor mobile application in **Laravel 12**.

## 1. Base Configuration
- **Base URL**: `https://loomcraft.work/api/v1`
- **Local URL**: `http://loom-craft.local/api/v1`
- **Authentication**: Laravel Sanctum (Bearer Token).
- **Format**: All requests and responses must be `application/json`.

---

## 2. Authentication

### Login
- **Endpoint**: `POST /login`
- **Request Body**:
  ```json
  {
    "email": "user@example.com",
    "password": "password",
    "device_name": "android"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "token": "sanctum_token_here",
    "user": {
      "id": 1,
      "name": "John Doe",
      "role": "admin|vendor",
      "vendor_id": 123 
    }
  }
  ```
- **Note**: `vendor_id` is only required if the role is `vendor`.

---

## 3. Order Management

### List Orders
- **Endpoint**: `GET /orders`
- **Logic**:
    - **Admin**: Return all orders.
    - **Vendor**: Return only orders where `order_items` contains at least one `product_id` belonging to the authenticated `vendor_id`.
- **Response (Admin)**:
  ```json
  [
    {
      "id": 101,
      "public_id": "ORD-EXAMPLE123",
      "status": "paid",
      "total": 1500.00,
      "customer_name": "Jane Smith",
      "items_count": 3,
      "created_at": "Apr 05, 2026 10:00 AM"
    }
  ]
  ```
- **Response (Vendor)**:
  ```json
  [
    {
      "id": 101,
      "status": "confirmed",
      "vendor_items_total": 450.00,
      "items_count": 1, 
      "created_at": "Apr 05, 2026 10:00 AM"
    }
  ]
  ```
  *(Note: Vendor response hides customer name and global order total)*

---

### Order Details
- **Endpoint**: `GET /orders/{id}`
- **Logic**:
    - **Admin**: Return full mobile-facing order detail data for all order items.
    - **Vendor**:
        - Show **only** `order_items` belonging to them.
        - **HIDE** customer email and payment-level details that are not rendered in the vendor app.
        - Show `status`, `id`, and specific item details (name, quantity, price, product image).
- **Current API issue to fix**:
    - The current API response includes backend-oriented fields the mobile order view does not use, for example:
        - `subtotal`
        - `commission_total`
        - `shipping_responsibility`
        - nested `customer.email`
        - nested `payment`
        - `shipments`
    - These should be removed from the mobile order-detail response unless the app explicitly starts rendering them later.
- **Required mobile format (Admin)**:
  ```json
  {
    "id": 10,
    "public_id": "ORD-VDQTOXTXEZL4DAU2NMLJEBYBHHCY",
    "status": "delivered",
    "currency": "LKR",
    "total": 15840.00,
    "created_at": "Mar 31, 2026 12:06 PM",
    "customer_name": "Ishara Dhanoji Karunadasa",
    "items": [
      {
        "id": 20,
        "product_id": 25,
        "product_name": "Wall Hanger - Ember Step Mosaic",
        "product_code": "DR-WH-01009",
        "vendor_id": 2,
        "vendor_name": "Dumbara Rata",
        "quantity": 1,
        "unit_price": 15840.00,
        "line_total": 15840.00,
        "status": "delivered",
        "currency": "LKR",
        "image_url": "https://loomcraft.work/storage/products/wall-hanger-ember-step-mosaic.jpg",
        "product_media": [
          {
            "id": 301,
            "type": "image",
            "path": "products/wall-hanger-ember-step-mosaic.jpg",
            "media_url": "https://loomcraft.work/storage/products/wall-hanger-ember-step-mosaic.jpg",
            "thumbnail_url": "https://loomcraft.work/storage/products/wall-hanger-ember-step-mosaic-thumb.jpg",
            "alt_text": "Wall Hanger - Ember Step Mosaic",
            "sort_order": 0
          }
        ]
      }
    ],
    "addresses": [
      {
        "type": "shipping",
        "full_name": "Ishara Dhanoji Karunadasa",
        "line1": "1/84",
        "line2": "Weediyawaththa",
        "city": "Kundasale",
        "region": null,
        "postal_code": "20168",
        "country_code": "LK",
        "phone": null
      },
      {
        "type": "billing",
        "full_name": "Ishara Dhanoji Karunadasa",
        "line1": "1/84",
        "line2": "Weediyawaththa",
        "city": "Kundasale",
        "region": null,
        "postal_code": "20168",
        "country_code": "LK",
        "phone": null
      }
    ]
  }
  ```
- **Required mobile format (Vendor)**:
  ```json
  {
    "id": 10,
    "public_id": "ORD-VDQTOXTXEZL4DAU2NMLJEBYBHHCY",
    "status": "delivered",
    "currency": "LKR",
    "total": 15840.00,
    "created_at": "Mar 31, 2026 12:06 PM",
    "items": [
      {
        "id": 20,
        "product_id": 25,
        "product_name": "Wall Hanger - Ember Step Mosaic",
        "product_code": "DR-WH-01009",
        "quantity": 1,
        "unit_price": 15840.00,
        "line_total": 15840.00,
        "status": "delivered",
        "currency": "LKR",
        "image_url": "https://loomcraft.work/storage/products/wall-hanger-ember-step-mosaic.jpg",
        "product_media": [
          {
            "id": 301,
            "type": "image",
            "path": "products/wall-hanger-ember-step-mosaic.jpg",
            "media_url": "https://loomcraft.work/storage/products/wall-hanger-ember-step-mosaic.jpg",
            "thumbnail_url": "https://loomcraft.work/storage/products/wall-hanger-ember-step-mosaic-thumb.jpg",
            "alt_text": "Wall Hanger - Ember Step Mosaic",
            "sort_order": 0
          }
        ]
      }
    ]
  }
  ```
- **Field notes**:
    - `created_at` should be used instead of `placed_at` so list and detail payloads stay consistent.
    - Mobile API dates should use one human-readable format consistently: `M d, Y g:i A` such as `Apr 05, 2026 10:00 AM`.
    - `customer_name` should be flattened for the mobile client instead of nested under `customer`.
    - `image_url` should be the primary full-size display image for the ordered product.
    - `product_media` should be optional but, when present, sorted by `sort_order ASC`.
    - All image URLs returned to mobile must be fully qualified absolute URLs.
    - `country_code` should reflect the real country value. For the example above it likely should be `LK`, not `US`.

---

### Update Order Status
- **Endpoint**: `PATCH /orders/{id}/status`
- **Request Body**:
  ```json
  {
    "status": "confirmed"
  }
  ```
- **Allowed Statuses (Vendor)**:
    - `shipped` only, and only when the current order status is `paid` or `confirmed`.
- **Allowed Statuses (Admin)**:
    - `pending`, `paid`, `confirmed`, `shipped`, `delivered`, `cancelled`.
- **Logic**:
    - Validate that the user has permission to move the order to the requested state.
    - Trigger an internal event to notify relevant parties (Admin/Customer).

---

## 4. Notifications

### Register FCM Token
- **Endpoint**: `POST /notifications/register`
- **Body**: `{"fcm_token": "token_string"}`
- **Logic**: Associate the token with the authenticated user for push notifications.

---

## 5. Admin Utilities

### Shipping Sticker Data
- **Endpoint**: `GET /admin/orders/{id}/sticker-data`
- **Logic**: Provide high-density JSON data for mobile sticker generation.
- **Payload**: Include full customer name, full address, phone, and list of all products in the order.

---

## Backend AI Implementation Notes (Laravel 12)
1. Use **API Resources** for consistent JSON formatting.
2. Use **Form Requests** for validation.
3. Implement **Policies** to handle Vendor vs Admin authorization logic.
4. Use **Sanctum** for token-based auth.
5. Ensure the database matches `.ai/resources/db-schema.md`.
6. Treat `.ai/resources/loom-craft-public-api.postman_collection.json` as the most up-to-date mobile API contract when there is a conflict with older notes.
