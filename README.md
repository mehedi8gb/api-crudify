# Api Crudify
## Laravel API Engine for Scalable Query-Driven Applications

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mehedi8gb/api-crudify.svg?style=flat-square)](https://packagist.org/packages/mehedi8gb/api-crudify)
[![Total Downloads](https://img.shields.io/packagist/dt/mehedi8gb/api-crudify.svg?style=flat-square)](https://packagist.org/packages/mehedi8gb/api-crudify)
[![License](https://img.shields.io/packagist/l/mehedi8gb/api-crudify.svg?style=flat-square)](https://github.com/mehedi8gb/api-crudify/blob/main/LICENSE.md)

**Api Crudify** is a professional-grade Laravel package that automates generation of robust, scalable, and standardized API CRUD components. It enforces the **Service-Repository** design pattern and implements a **Chain of Responsibility** pipeline for complex API queries — filtering, sorting, relation loading, soft deletes, and pagination — all driven by query parameters.

---

## 🚀 Key Features

- **Standardized Architecture** — Generates Controller, Service, Repository, Model, FormRequests, Resource, Migration, Factory, Seeder, and Feature Test in one command.
- **Chain of Responsibility Query Pipeline** — Modular, ordered handlers process every API request: SoftDelete → Relations → Filter → Sort → Pagination.
- **Advanced Dynamic Filtering** — Frontend-friendly `?q=` shorthand plus low-level `?where=` and `?orWhere=` with relation traversal support.
- **Smart Relation Loading** — Respects explicitly passed relations, model `$with`, or Eloquent eager loads automatically.
- **Soft Delete Awareness** — `?trashed=with` or `?trashed=only` query params built in.
- **Domain-Driven Design Ready** — Full support for nested namespaces: `V1/Inventory/Product`.
- **Auto-Restoration** — Detects and restores any missing base classes on every `crudify:make` run.
- **Route Management** — Automatically registers API routes and `use` statements.
- **Helper Utilities** — Global helper functions for responses, caching, payload filtering, and more.

---

## 📦 Installation

```bash
composer require mehedi8gb/api-crudify --dev
```

Run the installer to bootstrap all base classes and configure autoloading:

```bash
php artisan crudify:install
```

This command will:
- Copy all base classes, interfaces, and query handlers into your `app/` directory
- Add `app/Helpers/Helpers.php` to `composer.json` autoload files
- Run `composer dump-autoload` automatically

---

## 🛠 Usage

```bash
php artisan crudify:make {Name}
```

### Examples

```bash
# Simple CRUD
php artisan crudify:make Product

# Versioned / domain-specific
php artisan crudify:make V1/Inventory/Category

# With Postman schema export
php artisan crudify:make Product --export-api-schema
```

---

## 📂 Generated Components

| Component | Path |
|---|---|
| **Controller** | `app/Http/Controllers/{Path}/{Name}Controller.php` |
| **Model** | `app/Models/{Path}/{Name}.php` |
| **Service** | `app/Services/{Path}/{Name}Service.php` |
| **Repository** | `app/Repositories/{Path}/{Name}Repository.php` |
| **Form Requests** | `app/Http/Requests/{Path}/{Name}StoreRequest.php` & `UpdateRequest.php` |
| **Resource** | `app/Http/Resources/{Path}/{Name}Resource.php` |
| **Migration** | `database/migrations/YYYY_MM_DD_create_{names}_table.php` |
| **Factory** | `database/factories/{Path}/{Name}Factory.php` |
| **Seeder** | `database/seeders/{Path}/{Name}Seeder.php` |
| **Feature Test** | `tests/Feature/{Path}/{Name}Test.php` |

---

## 🏗 Architecture Overview

Every generated CRUD follows a strict 3-layer architecture:

```
HTTP Request
     │
     ▼
┌─────────────────────────────────┐
│         Controller              │  ← HTTP only: validate, call service, return response
│   (extends Controller)          │
└──────────────┬──────────────────┘
               │
               ▼
┌─────────────────────────────────┐
│           Service               │  ← Business logic, orchestration
│   (extends BaseService)         │
└──────────────┬──────────────────┘
               │
               ▼
┌─────────────────────────────────┐
│          Repository             │  ← Data access only, Eloquent queries
│   (extends BaseRepository)      │
└──────────────┬──────────────────┘
               │
               ▼
┌─────────────────────────────────┐
│     Query Pipeline              │  ← Chain of Responsibility
│  SoftDelete → Relations →       │
│  Filter → Sort → Pagination     │
└─────────────────────────────────┘
```

### Real-World Example

**Controller** — HTTP only, no business logic:
```php
final class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service
    ) {}

    public function index(): JsonResponse
    {
        $collection = $this->service->getProductsCollection();
        return $this->successResponse('Products retrieved successfully', $collection);
    }
}
```

**Service** — orchestrates business logic:
```php
class ProductService extends BaseService
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected Request $request
    ) {
        parent::__construct($productRepository, $request);
    }

    public function getProductsCollection(): array
    {
        $data = $this->productRepository->getProductsData();
        return $this->prepareResourceResponse($data, ProductResource::class);
    }
}
```

**Repository** — data access only:
```php
class ProductRepository extends BaseRepository
{
    public function __construct(Product $model, protected Request $request)
    {
        parent::__construct($model, $request);
    }

    public function getProductsData(array $with = ['category', 'tags']): array
    {
        return $this->handleApiQueryRequest($this->query(), $with);
    }
}
```

---

## ⚡ Query Pipeline — Chain of Responsibility

Every API request through `handleApiQueryRequest()` passes through this ordered pipeline:

```
Builder + Request
       │
       ▼
┌──────────────────┐
│ SoftDeleteHandler│  ?trashed=with|only|default
└────────┬─────────┘
         ▼
┌──────────────────┐
│  RelationHandler │  Explicit $with → model $with → Eloquent eager loads
└────────┬─────────┘
         ▼
┌──────────────────┐
│  FilterHandler   │  ?q= / ?where= / ?orWhere= / ?exclude= / ?operator=
└────────┬─────────┘
         ▼
┌──────────────────┐
│   SortHandler    │  ?sortBy= / ?sortOrder=asc|desc
└────────┬─────────┘
         ▼
┌──────────────────┐
│PaginationHandler │  ?page= / ?limit= / ?limit=all
└────────┬─────────┘
         ▼
  { meta, data }
```

---

## 🔍 Query Parameter Reference

### Filtering

| Parameter | Description | Example |
|---|---|---|
| `?q=` | Frontend-friendly shorthand filter | `?q=title=phone` |
| `?where=` | Direct column filter | `?where=status,active` |
| `?orWhere=` | OR column filter | `?orWhere=type,admin` |
| `?operator=` | Comparison operator | `?operator==` or `?operator=like` |
| `?exclude=` | Exclude a specific value | `?exclude=status,deleted` |

**Relation filtering via `?where=`:**
```
?where=with:category,name,Electronics
?where=with:category.parent,name,Tech
```

**Frontend shorthand `?q=` with pipe-separated conditions:**
```
?q=title=phone|status=active
?q=category.name=Electronics
```

**Complex nested relational query conditions:**
```
?q=posts.category.child.status=active
?q=posts.createdBy.userId=10
```

**`?or=true` converts multiple `?q=` conditions to `orWhere`:**
```
?q=title=phone|brand=apple&or=true
```

---

### Sorting

| Parameter | Default | Example |
|---|---|---|
| `?sortBy=` | `created_at` | `?sortBy=price` |
| `?sortOrder=` | `desc` | `?sortOrder=asc` |

> Safe against SQL injection — column existence is verified against the schema before applying.
> Skipped if the builder already has an `orderBy` applied.

---

### Soft Deletes

| Parameter | Behaviour |
|---|---|
| `?trashed=with` | Include soft-deleted records |
| `?trashed=only` | Return only soft-deleted records |
| _(omitted)_ | Exclude soft-deleted records (default) |

---

### Pagination

| Parameter | Default | Description |
|---|---|---|
| `?page=` | `1` | Current page |
| `?limit=` | `10` | Records per page |
| `?limit=all` | — | Returns all records, no pagination |

**Response structure:**
```json
{
  "meta": {
    "page": 1,
    "limit": 10,
    "total": 245,
    "totalPage": 25
  },
  "data": [ ... ]
}
```

---

### Relation Loading

Relations are resolved in this priority order:
1. Explicit `$with` array passed to `handleApiQueryRequest()`
2. Model-level `$with` property
3. Eloquent registered eager loads via `getEagerLoads()`

---

## 🧰 Helper Functions

Global utility functions available after install:

| Function | Description |
|---|---|
| `sendSuccessResponse($message, $data, $status)` | Standardized JSON success response |
| `sendErrorResponse($exception, $status)` | Standardized JSON error response with environment-aware detail |
| `validationException($payload, $key)` | Throw a `ValidationException` from array or string |
| `filterPayload($data, $allowedKeys)` | Keep only allowed keys from an array |
| `cacheQuery($query, $method, $args, $ttl)` | Execute and cache a query with tag-based invalidation |
| `generateCacheKey($builder, $request, $tenantId)` | Generate deterministic cache key from query state |
| `getCreatedAtColumn($builder\|$model)` | Resolve model's `CREATED_AT` column safely |
| `getResourceClass($model)` | Auto-resolve `{Model}Resource` class or fallback to `DefaultResource` |
| `deepMerge($original, $new)` | Deep array merge with force-replace support |
| `processNestedArray($existing, $payload)` | Merge and deduplicate nested arrays by `id` |
| `convertStatus($status)` | Convert boolean to `1`/`0` |
| `generateUniqueNumber($prefix)` | Generate a unique prefixed identifier |
| `getFormatedDate($carbon)` | Human-readable date with diff: `"2 days ago (14th April at 08:21 AM in 2025)"` |
---

## ⚙️ Base Classes Installed

After `crudify:install`, the following are placed in your `app/` directory:

```
app/
├── IContracts/
│   ├── Repositories/  (IRepository, IReadRepository, IWriteRepository)
│   └── Services/      (IService, IReadService, IWriteService)
├── Repositories/V1/BaseRepository.php
├── Services/V1/BaseService.php
├── Models/Model.php
├── Helpers/Helpers.php
├── Core/
│   ├── Query/
│   │   ├── HandleApiQueryRequest.php
│   │   ├── Contracts/IQueryHandler.php
│   │   └── Handlers/
│   │       ├── AbstractQueryHandler.php
│   │       ├── Core/
│   │       │   ├── FilterHandler.php
│   │       │   ├── PaginationHandler.php
│   │       │   ├── RelationHandler.php
│   │       │   ├── SoftDeleteHandler.php
│   │       │   └── SortHandler.php
│   │       └── Optimization/CacheHandler.php
│   └── ClientQuery/   (mirror of Query for client-facing APIs)
└── Http/Resources/DefaultResource.php
```

---

## 📜 Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## ✨ Credits

- [MD Mehedi Hasan](https://github.com/mehedi8gb)

## 📜 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
