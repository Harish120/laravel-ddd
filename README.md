# 🛒 Laravel DDD E-Commerce Demo

A demonstration e-commerce application built with **Laravel** and **Domain-Driven Design (DDD)** principles. This project showcases how to structure a Laravel application using DDD patterns, including bounded contexts, aggregates, value objects, and repository patterns.

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Quick Start](#-quick-start)
- [API Endpoints](#-api-endpoints)
- [Project Structure](#-project-structure)
- [Architecture](#-architecture)
- [Testing](#-testing)
- [Contributing](#-contributing)

## ✨ Features

- **Domain-Driven Design Architecture**
  - Bounded contexts (Catalog, Customer, Ordering)
  - Rich domain models with business logic
  - Value objects (Money, Address, Status objects)
  - Repository pattern with dependency injection
  - Domain events

- **E-Commerce Functionality**
  - Product catalog management
  - Customer management
  - Order processing with state machine
  - Stock management
  - Order status transitions

- **Clean Architecture**
  - 4-layer architecture (Domain, Application, Infrastructure, Presentation)
  - Separation of concerns
  - Framework-independent domain layer
  - Testable and maintainable code

## 📦 Requirements

- PHP >= 8.2
- Composer
- Laravel 12.x
- MySQL/PostgreSQL/SQLite
- Node.js & NPM (for frontend assets)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Harish120/laravel-ddd.git
cd laravel-ddd
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with your database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_ddd
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## 🎯 Quick Start

### Create a Product

```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laptop",
    "description": "High-performance laptop",
    "price": 999.99,
    "currency": "USD",
    "stock_quantity": 10
  }'
```

### Create an Order

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": "customer-uuid-here",
    "items": [
      {
        "product_id": "product-uuid-here",
        "quantity": 2
      }
    ],
    "shipping_address": {
      "street": "123 Main St",
      "city": "New York",
      "state": "NY",
      "zip_code": "10001",
      "country": "USA"
    }
  }'
```

## 📡 API Endpoints

### Products

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/products` | List all products |
| POST | `/api/products` | Create a new product |
| GET | `/api/products/{id}` | Get product details |
| PUT | `/api/products/{id}` | Update product |
| POST | `/api/products/{id}/publish` | Publish product |

### Orders

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/orders` | Create a new order |
| GET | `/api/orders/{id}` | Get order details |
| POST | `/api/orders/{id}/confirm` | Confirm order |

### Example Request/Response

**Create Product:**
```json
POST /api/products
{
  "name": "Smartphone",
  "description": "Latest smartphone model",
  "price": 699.99,
  "currency": "USD",
  "stock_quantity": 50,
  "sku": "PHONE-001"
}
```

**Response:**
```json
{
  "data": {
    "id": "uuid-here",
    "sku": "PHONE-001",
    "name": "Smartphone",
    "description": "Latest smartphone model",
    "price": {
      "amount": 699.99,
      "currency": "USD"
    },
    "stock_quantity": 50,
    "status": "draft",
    "category_id": null,
    "images": []
  }
}
```

## 📁 Project Structure

```
app/
├── Domain/                    # Domain Layer (Business Logic)
│   ├── Catalog/              # Product catalog bounded context
│   │   ├── Entities/        # Product entity
│   │   ├── ValueObjects/    # ProductStatus
│   │   ├── DomainEvents/    # ProductCreated, ProductStockReduced
│   │   └── Repositories/    # ProductRepository interface
│   ├── Customer/            # Customer bounded context
│   └── Ordering/            # Order processing bounded context
│
├── Application/              # Application Layer (Use Cases)
│   ├── Catalog/
│   │   ├── Services/        # ProductService
│   │   └── DTOs/           # Data Transfer Objects
│   └── Ordering/
│       ├── Services/        # OrderService
│       └── DTOs/
│
├── Infrastructure/           # Infrastructure Layer (Technical)
│   ├── Catalog/
│   │   ├── Models/         # Eloquent models
│   │   └── Repositories/   # EloquentProductRepository
│   └── Ordering/
│       ├── Models/
│       └── Repositories/
│
├── Http/                    # Presentation Layer (API)
│   └── Controllers/
│       └── Api/
│           ├── ProductController.php
│           └── OrderController.php
│
└── Shared/                  # Shared Kernel
    ├── Entity.php          # Base entity class
    ├── ValueObjects/       # Money, Address
    └── Exceptions/         # Domain exceptions
```

## 🏗️ Architecture

This project implements **Domain-Driven Design (DDD)** with a **4-layer architecture**:

1. **Domain Layer**: Pure business logic, no framework dependencies
2. **Application Layer**: Orchestrates domain objects for use cases
3. **Infrastructure Layer**: Handles technical concerns (database, external services)
4. **Presentation Layer**: HTTP controllers and API routes

### Key DDD Concepts

- **Bounded Contexts**: Separate contexts for Catalog, Customer, Ordering
- **Aggregates**: Product and Order are aggregate roots
- **Value Objects**: Money, Address, Status objects
- **Repository Pattern**: Interfaces in domain, implementations in infrastructure
- **Domain Events**: ProductCreated, OrderConfirmed, etc.

For detailed architecture documentation, see [ARCHITECTURE.md](./ARCHITECTURE.md).

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Or with coverage:

```bash
php artisan test --coverage
```

## 📚 Learning Resources

This project demonstrates:

- ✅ Domain-Driven Design patterns
- ✅ Clean Architecture principles
- ✅ Repository pattern implementation
- ✅ Value objects and entities
- ✅ Bounded contexts
- ✅ Dependency injection
- ✅ State machine pattern (Order status)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m ':sparkles: feat: Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Commit Message Format

We follow conventional commits with emoji prefixes:

- `:sparkles: feat:` - New feature
- `:bug: fix:` - Bug fix
- `:hammer: refactor:` - Code refactoring
- `:memo: docs:` - Documentation
- `:white_check_mark: test:` - Tests
- `:art: style:` - Code style

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👤 Author

**Harish**

- GitHub: [@Harish120](https://github.com/Harish120)

## 🙏 Acknowledgments

- Laravel Framework
- Domain-Driven Design community
- All contributors and supporters

---

**Note**: This is a demonstration project showcasing DDD patterns in Laravel. For production use, consider additional features like authentication, authorization, validation, error handling, and comprehensive testing.
