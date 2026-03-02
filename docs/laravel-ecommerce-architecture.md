# Laravel 10 Premium eCommerce Architecture Blueprint

## 1) High-Level Architecture

- **Framework**: Laravel 10 + PHP 8.2+
- **Templating**: Blade + Vite assets
- **DB**: MySQL 8+
- **Auth**: Laravel Breeze or Jetstream (Breeze recommended for lean startup)
- **Payments**: Stripe Checkout + webhook-driven fulfillment
- **Authorization**: Role-based access control via policies/gates (or `spatie/laravel-permission`)
- **Caching/queues**: Redis for cache, queues, and session in production
- **Search/filtering**: Eloquent scopes + indexed columns + optional Scout/Meilisearch in phase 2
- **Security baseline**: CSRF, validation FormRequests, rate limiting, signed URLs, encrypted secrets, webhook signature verification

### Domain boundaries

- **Storefront domain**: catalog browsing, cart, checkout, account pages
- **Admin domain**: products, categories, inventory, orders, customers
- **Shared domain**: users, addresses, payments, promotions, audit logs

---

## 2) Database Schema (Tables + Key Relationships)

> Naming convention: singular Model / plural table; UUID optional but BIGINT is simpler for v1.

### Core identity

1. `users`
   - `id`, `name`, `email` (unique), `password`, `phone`, `email_verified_at`, timestamps
2. `roles`
   - `id`, `name` (`admin`, `customer`), `slug` (unique), timestamps
3. `role_user` (pivot)
   - `user_id`, `role_id`, composite unique

**Relationships**
- User ↔ Role (many-to-many)

### Catalog

4. `categories`
   - `id`, `name`, `slug` (unique), `parent_id` (nullable, self-reference), `is_active`, `sort_order`, timestamps
5. `products`
   - `id`, `name`, `slug` (unique), `sku` (unique), `description` (longText), `short_description`, `status` (`draft|active|archived`), `price` (decimal 10,2), `compare_at_price` (nullable), `currency` default `USD`, `stock_qty`, `is_featured`, timestamps, softDeletes
6. `category_product` (pivot)
   - `category_id`, `product_id`, composite unique
7. `product_images`
   - `id`, `product_id`, `path`, `alt_text`, `sort_order`, `is_primary`, timestamps
8. `product_attributes`
   - `id`, `name` (e.g., color, size), `slug` unique, timestamps
9. `product_attribute_values`
   - `id`, `product_attribute_id`, `value` (e.g., Red, XL), `slug`, timestamps
10. `product_variants`
    - `id`, `product_id`, `sku` (unique), `price` (nullable), `stock_qty`, `is_active`, timestamps
11. `product_variant_value` (pivot)
    - `product_variant_id`, `product_attribute_value_id`, composite unique

**Relationships**
- Category → children via `parent_id` (self hasMany)
- Product ↔ Category (many-to-many)
- Product hasMany images
- Product hasMany variants
- Variant ↔ attribute values (many-to-many)

### Cart & checkout

12. `carts`
    - `id`, `user_id` nullable (guest carts via session key), `session_id` nullable/indexed, `status` (`active|converted|abandoned`), timestamps
13. `cart_items`
    - `id`, `cart_id`, `product_id`, `product_variant_id` nullable, `quantity`, `unit_price`, timestamps

**Relationships**
- Cart belongsTo user (nullable)
- Cart hasMany cartItems

### Orders & payment

14. `orders`
    - `id`, `order_number` unique, `user_id`, `status` (`pending|paid|processing|shipped|completed|cancelled|refunded`),
      `subtotal`, `discount_total`, `tax_total`, `shipping_total`, `grand_total`, `currency`,
      `payment_status` (`unpaid|paid|failed|refunded`), `placed_at`, timestamps
15. `order_items`
    - `id`, `order_id`, `product_id`, `product_variant_id` nullable, `product_name_snapshot`, `sku_snapshot`, `unit_price`, `quantity`, `line_total`, timestamps
16. `payments`
    - `id`, `order_id`, `provider` (`stripe`), `provider_payment_id`, `provider_checkout_session_id`, `amount`, `currency`, `status`, `paid_at`, `raw_payload` JSON nullable, timestamps
17. `shipments`
    - `id`, `order_id`, `carrier`, `tracking_number`, `status`, `shipped_at`, `delivered_at`, timestamps

**Relationships**
- Order belongsTo user
- Order hasMany orderItems, payments, shipments

### Addressing

18. `addresses`
    - `id`, `user_id`, `type` (`billing|shipping`), `first_name`, `last_name`, `line1`, `line2`, `city`, `state`, `postal_code`, `country_code`, `phone`, `is_default`, timestamps
19. `order_addresses`
    - `id`, `order_id`, `type` (`billing|shipping`), snapshot address fields (same as addresses), timestamps

**Relationships**
- User hasMany addresses
- Order hasMany orderAddresses (snapshot, immutable)

### Optional but recommended

20. `coupons`
21. `coupon_usages`
22. `wishlists`, `wishlist_items`
23. `activity_logs` (admin auditing)

---

## 3) Recommended Folder Structure

```text
app/
  Actions/
    Cart/
    Checkout/
    Orders/
  DTOs/
  Enums/
  Http/
    Controllers/
      Store/
      Admin/
      Auth/
    Middleware/
    Requests/
      Store/
      Admin/
  Models/
  Policies/
  Repositories/
    Contracts/
    Eloquent/
  Services/
    Payments/
      StripeService.php
    Inventory/
  ViewModels/
bootstrap/
config/
database/
  factories/
  migrations/
  seeders/
resources/
  views/
    layouts/
    components/
    store/
      home.blade.php
      products/
      cart/
      checkout/
      account/
    admin/
      dashboard.blade.php
      products/
      orders/
routes/
  web.php
  admin.php (optional split)
```

### Why this structure scales

- Keep controllers thin; move orchestration to `Actions`/`Services`.
- Use `Repositories` only where persistence complexity appears (not everywhere).
- Use `FormRequest` for all input validation.
- `Enums` centralize statuses to avoid magic strings.

---

## 4) Migration Examples

### `create_products_table.php`

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('sku')->unique();
    $table->text('short_description')->nullable();
    $table->longText('description')->nullable();
    $table->enum('status', ['draft', 'active', 'archived'])->default('draft')->index();
    $table->decimal('price', 10, 2);
    $table->decimal('compare_at_price', 10, 2)->nullable();
    $table->char('currency', 3)->default('USD');
    $table->unsignedInteger('stock_qty')->default(0)->index();
    $table->boolean('is_featured')->default(false)->index();
    $table->softDeletes();
    $table->timestamps();

    $table->index(['status', 'price']);
});
```

### `create_orders_table.php`

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled', 'refunded'])
        ->default('pending')->index();
    $table->decimal('subtotal', 10, 2);
    $table->decimal('discount_total', 10, 2)->default(0);
    $table->decimal('tax_total', 10, 2)->default(0);
    $table->decimal('shipping_total', 10, 2)->default(0);
    $table->decimal('grand_total', 10, 2);
    $table->char('currency', 3)->default('USD');
    $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid')->index();
    $table->timestamp('placed_at')->nullable()->index();
    $table->timestamps();

    $table->index(['user_id', 'status']);
});
```

### `create_payments_table.php`

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->cascadeOnDelete();
    $table->string('provider')->default('stripe');
    $table->string('provider_payment_id')->nullable()->index();
    $table->string('provider_checkout_session_id')->nullable()->index();
    $table->decimal('amount', 10, 2);
    $table->char('currency', 3)->default('USD');
    $table->string('status')->index();
    $table->timestamp('paid_at')->nullable();
    $table->json('raw_payload')->nullable();
    $table->timestamps();
});
```

---

## 5) Model Relationships (Key Examples)

```php
// User.php
public function roles() { return $this->belongsToMany(Role::class); }
public function orders() { return $this->hasMany(Order::class); }
public function addresses() { return $this->hasMany(Address::class); }

// Category.php
public function parent() { return $this->belongsTo(Category::class, 'parent_id'); }
public function children() { return $this->hasMany(Category::class, 'parent_id'); }
public function products() { return $this->belongsToMany(Product::class); }

// Product.php
public function categories() { return $this->belongsToMany(Category::class); }
public function images() { return $this->hasMany(ProductImage::class); }
public function variants() { return $this->hasMany(ProductVariant::class); }

// Cart.php
public function user() { return $this->belongsTo(User::class); }
public function items() { return $this->hasMany(CartItem::class); }

// Order.php
public function user() { return $this->belongsTo(User::class); }
public function items() { return $this->hasMany(OrderItem::class); }
public function payments() { return $this->hasMany(Payment::class); }
public function shipments() { return $this->hasMany(Shipment::class); }
```

---

## 6) Controller Structure

### Storefront controllers

- `Store/HomeController` → landing + featured sections
- `Store/ProductController` → index/list, show/detail, filters/sorting
- `Store/CategoryController` → category listing pages
- `Store/CartController` → add/update/remove/apply coupon
- `Store/CheckoutController` → address step, shipping step, payment session init
- `Store/StripeWebhookController` → webhook endpoint (`checkout.session.completed`, etc.)
- `Store/Account/OrderController` → customer order history/details

### Admin controllers

- `Admin/DashboardController`
- `Admin/ProductController` (CRUD)
- `Admin/CategoryController` (CRUD + tree)
- `Admin/OrderController` (index, show, update status, refund trigger)
- `Admin/UserController` (customer/admin management)

### Best-practice flow

Controller → FormRequest (validate) → Action/Service (business logic) → Model/Repository.

---

## 7) Routes (`routes/web.php`) Example

```php
use App\Http\Controllers\Store\{
    HomeController, ProductController, CategoryController, CartController, CheckoutController, StripeWebhookController
};
use App\Http\Controllers\Store\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Admin\{
    DashboardController as AdminDashboardController,
    ProductController as AdminProductController,
    CategoryController as AdminCategoryController,
    OrderController as AdminOrderController,
};

Route::get('/', HomeController::class)->name('home');

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [ProductController::class, 'show'])->name('show');
});

Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/items', [CartController::class, 'store'])->name('items.store');
    Route::patch('/items/{item}', [CartController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [CartController::class, 'destroy'])->name('items.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/session', [CheckoutController::class, 'createStripeSession'])->name('checkout.session');

    Route::get('/account/orders', [AccountOrderController::class, 'index'])->name('account.orders.index');
    Route::get('/account/orders/{order}', [AccountOrderController::class, 'show'])->name('account.orders.show');
});

Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'can:access-admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::resource('products', AdminProductController::class);
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    });
```

---

## 8) Basic Blade Layout Structure

```text
resources/views/
  layouts/
    app.blade.php         // main shell
    store.blade.php       // storefront nav/footer
    admin.blade.php       // admin shell/sidebar
  components/
    ui/
      button.blade.php
      input.blade.php
      badge.blade.php
    product/
      card.blade.php
      gallery.blade.php
  store/
    home.blade.php
    products/index.blade.php
    products/show.blade.php
    cart/index.blade.php
    checkout/index.blade.php
  admin/
    dashboard.blade.php
    products/index.blade.php
    products/form.blade.php
    orders/index.blade.php
```

### Blade conventions

- Use `@extends('layouts.store')` and slot sections `title`, `content`, `meta`.
- Use Blade components for repeatable UI blocks.
- Keep logic minimal in views; compute in controller/ViewModel.

---

## 9) Stripe Integration Implementation Details

1. Install SDK: `composer require stripe/stripe-php`
2. Add env keys:
   - `STRIPE_KEY`
   - `STRIPE_SECRET`
   - `STRIPE_WEBHOOK_SECRET`
3. In checkout action:
   - Validate cart and stock.
   - Create `Order` in `pending/unpaid` state.
   - Create Stripe Checkout Session with order metadata (`order_id`, `user_id`).
   - Redirect to hosted checkout.
4. Webhook endpoint:
   - Verify signature using `STRIPE_WEBHOOK_SECRET`.
   - Handle `checkout.session.completed` + `payment_intent.payment_failed`.
   - Update `payments` and `orders` atomically in DB transaction.
   - Decrement stock only after successful payment confirmation.
5. Idempotency:
   - Store event ID to avoid duplicate processing.

---

## 10) Security Best Practices (Must-Have)

- **Auth hardening**: enforce email verification for checkout/admin.
- **Authorization**: policies for product/order actions; admin gate `access-admin`.
- **Validation**: all write endpoints use FormRequest classes.
- **Mass assignment**: guarded/fillable strictly defined.
- **SQL injection/XSS**: rely on Eloquent bindings and escaped Blade output (`{{ }}`).
- **Rate limiting**: login, register, checkout create-session, webhook endpoints.
- **CSRF**: all state-changing web forms protected.
- **Secret management**: never commit `.env`; rotate Stripe keys.
- **Logging/auditing**: log admin critical actions + payment transitions.
- **Data privacy**: never store raw card data (Stripe-hosted flow).

---

## 11) Scalability & Performance Guidelines

- Add DB indexes on frequent filters (`status`, `price`, `category`, `created_at`).
- Eager load relations to avoid N+1 (`with(['images', 'categories'])`).
- Cache category tree and homepage featured products.
- Queue non-critical jobs: emails, invoices, analytics, webhook heavy processing.
- Use pagination on all listing pages.
- Add read replicas and horizontal scaling after traffic growth.

---

## 12) Step-by-Step Build Order (Execution Plan)

1. **Bootstrap project**
   - Laravel 10 install, `.env`, MySQL connection, Vite setup, base layout.
2. **Auth + roles**
   - Install Breeze, create roles/pivot, middleware/gate for admin.
3. **Catalog core**
   - Migrations/models for categories/products/images/variants.
   - Admin CRUD for categories and products.
4. **Storefront catalog UI**
   - Product list/detail pages, category pages, filters/sort.
5. **Cart system**
   - Session + DB carts, add/update/remove items, stock checks.
6. **Checkout and address flow**
   - Capture shipping/billing; create pending orders.
7. **Stripe integration**
   - Checkout session creation, webhook handling, payment/order state sync.
8. **Order management**
   - Customer order history and admin order workflow updates.
9. **Admin dashboard**
   - KPI widgets: revenue, orders, low stock, conversion metrics.
10. **Hardening pass**
   - policies, rate limits, logging, audit, tests.
11. **Performance pass**
   - indexes, eager loading, cache, queue workers, basic load testing.
12. **Deployment**
   - CI/CD, config cache, queue supervisor, backups, monitoring/alerts.

---

## 13) Suggested Testing Strategy

- **Feature tests**: auth, admin authorization, cart, checkout, webhooks.
- **Unit tests**: pricing service, inventory deductions, order transitions.
- **Integration tests**: Stripe webhook signature + idempotency handling.
- **Browser tests**: critical purchase path (Dusk or Playwright).

