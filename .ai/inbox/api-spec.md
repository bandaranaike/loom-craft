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
      "created_at": "2026-04-05T..."
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
      "created_at": "2026-04-05T..."
    }
  ]
  ```
  *(Note: Vendor response hides customer name and global order total)*

---

### Order Details
- **Endpoint**: `GET /orders/{id}`
- **Logic**:
    - **Admin**: Full access to `order_addresses`, `payments`, `shipments`, and all `order_items`.
    - **Vendor**:
        - Show **only** `order_items` belonging to them.
        - **HIDE** `order_addresses`, customer phone/email, and total order payment details.
        - Show `status`, `id`, and specific item details (name, quantity, price, product image).
- **Order item media requirement**:
    - Each returned order item should include enough product media data for the mobile app to render an image without making a separate product-media request.
    - The backend should source this from the `product_media` table using `order_items.product_id`.
    - Preferred fields per item:
      ```json
      {
        "id": 50,
        "product_id": 77,
        "product_name": "Handwoven Silk Scarf",
        "quantity": 1,
        "unit_price": 450.00,
        "currency": "LKR",
        "image_url": "https://loomcraft.work/storage/products/scarf-main.jpg",
        "product_media": [
          {
            "id": 301,
            "type": "image",
            "path": "products/scarf-main.jpg",
            "media_url": "https://loomcraft.work/storage/products/scarf-main.jpg",
            "thumbnail_url": "https://loomcraft.work/storage/products/scarf-main-thumb.jpg",
            "alt_text": "Handwoven Silk Scarf main image",
            "sort_order": 0
          }
        ]
      }
      ```
    - `image_url` should be the primary display image for the ordered product.
    - `product_media` should be sorted by `sort_order ASC`.
    - All URLs returned to mobile should be fully qualified absolute URLs.
    - If only one media field is feasible immediately, return `image_url`.
- **Vendor Response Example**:
  ```json
  {
    "id": 101,
    "status": "confirmed",
    "currency": "LKR",
    "items": [
      {
        "id": 50,
        "product_id": 77,
        "product_name": "Handwoven Silk Scarf",
        "quantity": 1,
        "unit_price": 450.00,
        "status": "confirmed",
        "image_url": "https://loomcraft.work/storage/products/scarf-main.jpg"
      }
    ]
  }
  ```

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
5. Ensure the database matches `.ai/knowledge/core/db-schema.md`, and that `orders.status` only accepts `pending`, `paid`, `confirmed`, `shipped`, `delivered`, or `cancelled`.
6. Treat `.ai/resources/loom-craft-public-api.postman_collection.json` as the most up-to-date mobile API contract when there is a conflict with older notes.
