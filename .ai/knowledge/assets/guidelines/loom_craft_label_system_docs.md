# LoomCraft Shipping Label System

## Document 1: Backend Development Process (Laravel 12)

### Objective
Generate a **print-ready PDF shipping label** using Laravel 12 based on an order number.

---

## 1. Architecture Overview

Flow:

Mobile App → API (order number) → Laravel Backend → Build Label Data → Render Blade Template → Generate PDF → Return to Mobile → Print

---

## 2. API Design

### Endpoint
```
GET /api/orders/{orderNumber}/label
```

### Response
- Returns PDF stream or downloadable file

Optional:
```
GET /api/orders/{orderNumber}/label-data
```
Returns structured JSON for debugging/testing

---

## 3. Data Structure (DTO)

Create a DTO: `OrderLabelDTO`

Fields:

```php
class OrderLabelDTO {
    public string $order_id;
    public string $order_date;
    public string $payment_method;
    public string $shipping_method;
    public float $weight;

    public string $customer_name;
    public string $address_line;
    public string $city;
    public string $province;
    public string $country;
    public string $phone;

    public string $product_name;
    public string $material;
    public string $color;
    public string $size;
    public string $sku;
    public int $qty;

    public float $unit_price;
    public float $subtotal;
    public float $shipping_cost;
    public float $total;

    public string $barcode_value;
    public string $qr_value;
}
```

---

## 4. Services

### 4.1 LabelDataBuilder

Responsibilities:
- Fetch order from database
- Map to DTO
- Format values (dates, currency)

---

### 4.2 Barcode Service

Use libraries:
- milon/barcode OR picqer/php-barcode-generator

Output:
- PNG or SVG base64

---

### 4.3 QR Code Service

Use:
- simplesoftwareio/simple-qrcode

QR content example:
```
https://loomcraft.work/order/{orderNumber}
```

---

### 4.4 PDF Generator

Recommended libraries:
- barryvdh/laravel-dompdf (simple)
- spatie/browsershot (high quality)

---

## 5. Blade Template

Location:
```
resources/views/print/labels/shipping.blade.php
```

Guidelines:
- Fixed width layout (e.g., 4x6 or A4 section)
- Black & white only
- No gradients
- Use strong typography hierarchy
- Avoid thin fonts

Sections:

1. Header (Logo + Company Info)
2. Order Details
3. Customer Details
4. Product Table
5. Totals
6. Instructions
7. Barcode + QR

---

## 6. Controller Example

```php
public function label($orderNumber)
{
    $dto = app(LabelDataBuilder::class)->build($orderNumber);

    $pdf = PDF::loadView('print.labels.shipping', [
        'data' => $dto
    ])->setPaper([0, 0, 288, 432]); // 4x6 inches

    return $pdf->stream("label-{$orderNumber}.pdf");
}
```

---

## 7. Performance Strategy

- Cache generated PDFs
- Regenerate only if order updated
- Use queue for heavy generation (optional)

---

## 8. Print Considerations

- Use high contrast (pure black/white)
- Ensure barcode quiet zone
- Minimum font size: 10–12pt
- Avoid background fills for thermal printers

---

## 9. File Storage (Optional)

```
storage/app/labels/{orderNumber}.pdf
```

---

## 10. Security

- Validate order ownership if needed
- Prevent public guessing of order numbers

---
