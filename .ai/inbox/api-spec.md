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
    "password": "password"
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
      "status": "pending",
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
      "status": "processing",
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
        - Show `status`, `id`, and specific item details (name, quantity, price).
- **Vendor Response Example**:
  ```json
  {
    "id": 101,
    "status": "processing",
    "items": [
      {
        "id": 50,
        "product_name": "Handwoven Silk Scarf",
        "quantity": 1,
        "unit_price": 450.00,
        "status": "processing"
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
    "status": "accepted"
  }
  ```
- **Allowed Statuses (Vendor)**:
    - `accepted`, `rejected`, `processing`, `hand_over_to_admin`.
- **Allowed Statuses (Admin)**:
    - All database enums (e.g., `shipped`, `delivered`, `disputed`, `cancelled`).
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
