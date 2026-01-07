# Domain-Driven Design (DDD) Architecture Overview

## 📐 Architecture Layers

Our Laravel DDD implementation follows a **4-layer architecture** with clear separation of concerns:

```
┌─────────────────────────────────────────┐
│   Presentation Layer (Controllers)      │
├─────────────────────────────────────────┤
│   Application Layer (Services, DTOs)    │
├─────────────────────────────────────────┤
│   Domain Layer (Entities, Value Objects)│
├─────────────────────────────────────────┤
│   Infrastructure Layer (Repositories)   │
└─────────────────────────────────────────┘
```

---

## 🏗️ Layer 1: Domain Layer (Core Business Logic)

**Location:** `app/Domain/`

This is the **heart of DDD** - it contains pure business logic with **zero dependencies** on frameworks or infrastructure.

### Structure by Bounded Context:

```
Domain/
├── Catalog/          # Product catalog management
├── Customer/         # Customer management
├── Ordering/         # Order processing
├── Payment/          # Payment processing (prepared)
├── Shipping/         # Shipping management (prepared)
└── Inventory/        # Inventory management (prepared)
```

### Components in Each Bounded Context:

#### 1. **Entities** (`Entities/`)
Rich domain models with business logic and invariants.

**Example: `Product` Entity**
- Contains business rules (e.g., "Product must have SKU before publishing")
- Encapsulates behavior (e.g., `reduceStock()`, `publish()`, `isAvailable()`)
- Maintains invariants (e.g., stock cannot be negative)
- No framework dependencies

**Example: `Order` Entity**
- State machine for order status transitions
- Business rules (e.g., "Cannot add items to non-pending orders")
- Calculates totals automatically
- Validates state transitions

#### 2. **Value Objects** (`ValueObjects/`)
Immutable objects defined by their attributes, not identity.

**Examples:**
- `ProductStatus`: Draft, Active, Archived
- `OrderStatus`: Pending → Confirmed → Processing → Shipped → Delivered
- `Money`: Amount + Currency with operations (add, subtract, multiply)
- `Address`: Complete address information

**Key Characteristics:**
- Immutable (readonly properties)
- Equality by value, not reference
- No identity (no ID field)

#### 3. **Domain Events** (`DomainEvents/`)
Events that represent something important happened in the domain.

**Examples:**
- `ProductCreated`: When a new product is added
- `ProductStockReduced`: When inventory decreases
- `OrderCreated`: When an order is placed
- `OrderConfirmed`: When an order is confirmed

#### 4. **Repository Interfaces** (`Repositories/`)
Contracts defining how to persist and retrieve entities.

**Example:**
```php
interface ProductRepository {
    public function findById(string $id): ?Product;
    public function findBySku(string $sku): ?Product;
    public function save(Product $product): void;
    public function delete(Product $product): void;
}
```

**Key Point:** Interfaces are in Domain layer, implementations are in Infrastructure layer.

---

## 🎯 Layer 2: Application Layer (Use Cases)

**Location:** `app/Application/`

Orchestrates domain objects to fulfill use cases. Contains **no business logic** - it coordinates.

### Structure:

```
Application/
├── Catalog/
│   ├── Services/        # ProductService
│   ├── DTOs/           # Data Transfer Objects
│   ├── Commands/       # CQRS Commands (prepared)
│   └── Queries/        # CQRS Queries (prepared)
└── Ordering/
    ├── Services/        # OrderService
    └── DTOs/
```

### Components:

#### 1. **Application Services** (`Services/`)
Orchestrate domain objects to execute use cases.

**Example: `ProductService`**
- `createProduct()`: Creates product, generates SKU, saves via repository
- `updateProduct()`: Retrieves, updates, saves
- `publishProduct()`: Changes status to active
- Converts between Domain Entities and DTOs

**Example: `OrderService`**
- `createOrder()`: Creates order, validates stock, reduces inventory
- `confirmOrder()`: Validates and confirms order
- Coordinates between Order and Product aggregates

#### 2. **DTOs (Data Transfer Objects)** (`DTOs/`)
Simple data containers for transferring data between layers.

**Examples:**
- `CreateProductDTO`: Input for creating products
- `ProductDTO`: Output representation
- `CreateOrderDTO`: Input for creating orders
- `OrderItemDTO`: Order item data

**Purpose:** 
- Decouple layers
- Prevent exposing domain entities directly
- Control what data is exposed

---

## 🔧 Layer 3: Infrastructure Layer (Technical Details)

**Location:** `app/Infrastructure/`

Handles technical concerns: database, external services, file storage, etc.

### Structure:

```
Infrastructure/
├── Catalog/
│   ├── Models/              # Eloquent Models
│   ├── Repositories/        # EloquentProductRepository
│   └── ExternalServices/    # External API integrations
└── Ordering/
    ├── Models/              # OrderModel, OrderItemModel
    └── Repositories/        # EloquentOrderRepository
```

### Components:

#### 1. **Eloquent Models** (`Models/`)
Laravel's ORM models for database mapping.

**Example: `ProductModel`**
- Maps to `products` table
- Handles database-specific concerns (casts, fillable, etc.)
- Uses UUIDs for primary keys

#### 2. **Repository Implementations** (`Repositories/`)
Concrete implementations of domain repository interfaces.

**Example: `EloquentProductRepository`**
- Implements `ProductRepository` interface
- Converts between Domain Entities ↔ Eloquent Models
- Handles persistence logic
- Maps value objects to database columns

**Key Pattern:** Repository maps domain entities to/from database models.

---

## 🌐 Layer 4: Presentation Layer (API)

**Location:** `app/Http/Controllers/Api/`

Handles HTTP requests/responses. Thin layer that delegates to Application Services.

### Components:

#### 1. **Controllers** (`Api/`)
- Receive HTTP requests
- Validate input
- Call Application Services
- Format responses
- Handle exceptions

**Example: `ProductController`**
- `index()`: List all products
- `store()`: Create product
- `show()`: Get single product
- `update()`: Update product
- `publish()`: Publish product

#### 2. **Routes** (`routes/api.php`)
RESTful API endpoints:
```
GET    /api/products
POST   /api/products
GET    /api/products/{id}
PUT    /api/products/{id}
POST   /api/products/{id}/publish

POST   /api/orders
GET    /api/orders/{id}
POST   /api/orders/{id}/confirm
```

---

## 🔗 Shared Kernel

**Location:** `app/Shared/`

Common components used across bounded contexts.

### Components:

1. **Base Classes:**
   - `Entity`: Base class for all domain entities
   - `ValueObject`: Base class for value objects
   - `Repository`: Base repository interface

2. **Value Objects:**
   - `Money`: Currency and amount operations
   - `Address`: Shipping/billing addresses

3. **Exceptions:**
   - `DomainException`: Base domain exception
   - `EntityNotFoundException`: When entity not found

---

## 🔄 Dependency Injection & Service Binding

**Location:** `app/Providers/RepositoryServiceProvider.php`

We use Laravel's service container to bind repository interfaces to implementations:

```php
$this->app->bind(ProductRepository::class, EloquentProductRepository::class);
$this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
```

**Benefits:**
- Domain layer doesn't know about Eloquent
- Easy to swap implementations (e.g., MongoDB, Redis)
- Testable (can mock repositories)

---

## 📊 Data Flow Example: Creating an Order

```
1. HTTP Request → OrderController::store()
   ↓
2. Controller validates input, creates CreateOrderDTO
   ↓
3. OrderService::createOrder()
   ↓
4. OrderService creates Order entity (Domain)
   ↓
5. OrderService validates stock via ProductRepository
   ↓
6. Product.reduceStock() (Domain business logic)
   ↓
7. OrderRepository.save() → EloquentOrderRepository
   ↓
8. EloquentOrderRepository converts Order → OrderModel
   ↓
9. OrderModel saves to database
   ↓
10. Response returned to client
```

---

## 🎯 Key DDD Patterns Implemented

### 1. **Bounded Contexts**
Separate contexts for Catalog, Customer, Ordering, etc. Each has its own:
- Entities
- Value Objects
- Repository Interfaces
- Domain Events

### 2. **Aggregates**
- `Product` is an aggregate root
- `Order` is an aggregate root (contains `OrderItem` entities)
- Aggregates maintain consistency boundaries

### 3. **Repository Pattern**
- Interfaces in Domain layer
- Implementations in Infrastructure layer
- Abstracts persistence

### 4. **Value Objects**
- `Money`, `Address`, `ProductStatus`, `OrderStatus`
- Immutable, compared by value

### 5. **Domain Events**
- `ProductCreated`, `OrderConfirmed`, etc.
- Can be used for event sourcing, integration, etc.

### 6. **Application Services**
- Orchestrate domain objects
- No business logic (that's in entities)
- Coordinate between aggregates

### 7. **DTOs**
- Transfer data between layers
- Prevent exposing domain entities

---

## 🗄️ Database Schema

### Products Table
- UUID primary key
- Stores Money as `price_amount` + `price_currency`
- Stores ProductStatus as string
- JSON for images array

### Orders Table
- UUID primary key
- Stores all Money values separately (subtotal, shipping, tax, total)
- JSON for addresses
- Status as string

### Order Items Table
- UUID primary key
- Foreign key to orders
- Stores product snapshot (name, SKU, price at time of order)

---

## 🚀 Benefits of This Architecture

1. **Testability:** Domain logic can be tested without database
2. **Maintainability:** Clear separation of concerns
3. **Flexibility:** Easy to swap infrastructure (database, external services)
4. **Business Focus:** Domain layer expresses business rules clearly
5. **Scalability:** Bounded contexts can evolve independently
6. **Framework Independence:** Domain layer doesn't depend on Laravel

---

## 📝 Next Steps / Prepared Areas

The following bounded contexts are prepared but not yet implemented:
- **Payment:** Payment processing
- **Shipping:** Shipping management
- **Inventory:** Advanced inventory management

These follow the same patterns and can be extended as needed.

---

## 🔍 Key Files to Review

1. **Domain Entities:**
   - `app/Domain/Catalog/Entities/Product.php`
   - `app/Domain/Ordering/Entities/Order.php`

2. **Value Objects:**
   - `app/Shared/ValueObjects/Money.php`
   - `app/Domain/Ordering/ValueObjects/OrderStatus.php`

3. **Application Services:**
   - `app/Application/Catalog/Services/ProductService.php`
   - `app/Application/Ordering/Services/OrderService.php`

4. **Repository Implementation:**
   - `app/Infrastructure/Catalog/Repositories/EloquentProductRepository.php`

5. **Controllers:**
   - `app/Http/Controllers/Api/ProductController.php`
   - `app/Http/Controllers/Api/OrderController.php`

---

This architecture follows DDD principles and provides a solid foundation for a scalable, maintainable e-commerce application.

