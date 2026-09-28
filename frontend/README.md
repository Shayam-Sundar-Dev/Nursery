# Verdant Botanical Nursery & Garden — React Ecommerce Storefront

A modern, high-performance **React.js (Vite + Tailwind CSS)** ecommerce frontend designed for climate-controlled botanical nurseries and exotic living houseplants.

Directly connected to the **Laravel Botanical Nursery Backend API**.

---

## 🌿 Key Features & Integrated APIs

1. **Dynamic Site Management & Announcement System:**
   - Powered by `/api/v1/site-settings`.
   - Dynamic currency symbol (`₹`) & currency code (`INR`).
   - Live announcement bar with customizable background colors, text colors, and promo links.
   - Dynamic nursery contact details, greenhouse address, operating hours, and social media handles.
   - Automatic storefront maintenance mode overlay with status messaging.

2. **Hero Promo Sliders & Visual Banners:**
   - Powered by `/api/v1/home/sliders`.
   - Auto-rotating slides with pause-on-hover, custom button CTAs, badge labels, and smooth transitions.

3. **Botanical Houseplant Catalog & Filtering:**
   - Powered by `/api/v1/categories` and `/api/v1/products`.
   - Search across botanical Latin names and common names.
   - Filter by light requirement (*Low Light*, *Bright Indirect*, *Direct Sunlight*).
   - Filter by pet safety (*🐾 Pet Friendly*).
   - Sort by popularity, price (low/high), or newest greenhouse drops.

4. **Interactive Plant Matcher Quiz:**
   - Powered by `POST /api/v1/plant-finder`.
   - 4-question lifestyle quiz analyzing light exposure, pet safety, watering frequency, and experience level.
   - Returns curated botanical matches with tailored care rationale.

5. **Botanical Care Guides & Variant Selection:**
   - Powered by `/api/v1/products/{slug}`.
   - High-resolution gallery thumbnails.
   - Pot size & planter variant picker (4" Nursery Pot, 6" Ceramic Planter, etc.) with live stock checks.
   - Detailed care guide: Lighting, Watering frequency, Humidity level, and Pet Toxicity advisories.

6. **Persistent Botanical Cart & Thermal Packaging Protection:**
   - Persistent across browser sessions via `localStorage`.
   - Free shipping progress meter (*"Add ₹X more to unlock free climate-safe delivery"*).
   - Optional 72-hour thermal root insulation pack (`+₹4.50`) for frost and heat protection.
   - Real-time promotional coupon validation (`POST /api/v1/coupons/validate`) with instant discount calculation (e.g. `SPRINGBLOOM`, `WELCOME10`).

7. **Atomic Botanical Checkout:**
   - Real-time server breakdown via `POST /api/v1/checkout/summary`.
   - Atomic order submission via `POST /api/v1/checkout/orders`.
   - Instant confirmation screen displaying generated order number and estimated delivery dates.

8. **Live Plant Transit Tracking Timeline:**
   - Powered by `GET /api/v1/orders/{orderNumber}/track`.
   - Visual 4-step fulfillment progression: Greenhouse allocation & moisture check -> Thermal packing -> Specialized climate courier transit -> Safe arrival.
   - Thermal telemetry advisory and itemized package breakdown.

9. **Digital Garden Companion Care:**
   - Powered by `GET /api/v1/my-plants`, `POST /api/v1/my-plants`, and `PATCH /api/v1/my-plants/{id}/water`.
   - Companion care journal tracking watering frequencies and overdue hydration alerts.
   - 1-click "Log Plant Watered Today" action with instant schedule update.

---

## 🚀 Running the Storefront

### 1. Start the Laravel Backend
In `/Users/shyam/Develops/nursery-garden/backend`:
```bash
php artisan serve
# Running at http://127.0.0.1:8000
```

### 2. Start the React Frontend
In `/Users/shyam/Develops/nursery-garden/frontend`:
```bash
npm run dev
# Running at http://localhost:5173
```

Vite proxies `/api` and `/storage` requests to `http://127.0.0.1:8000`.
