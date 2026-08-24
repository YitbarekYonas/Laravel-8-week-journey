# 🚀 Laravel Backend Development Journey

[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-brightgreen.svg)](https://laravel.com/)
[![Composer](https://img.shields.io/badge/Composer-2.x-red.svg)](https://getcomposer.org/)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](http://makeapullrequest.com)

> **An 8-Week Journey from Laravel Beginner to Production-Ready Developer**

Welcome to my Laravel learning journey! This repository documents my complete path from understanding the fundamentals to building production-ready APIs with Laravel 11.x and PHP 8.2+.

---

## 📚 Table of Contents

- [Overview](#-overview)
- [Why This Repository](#-why-this-repository)
- [Tech Stack](#-tech-stack)
- [8-Week Curriculum](#-8-week-curriculum)
- [How to Navigate](#-how-to-navigate)
- [How to Run](#-how-to-run)
- [Project Structure](#-project-structure)
- [Progress Tracking](#-progress-tracking)
- [Key Learnings So Far](#-key-learnings-so-far)
- [Resources](#-resources)
- [Connect With Me](#-connect-with-me)

---

## 📖 Overview

This repository is a **hands-on, project-based learning journey** through Laravel development. Each week builds upon the previous, progressively adding complexity until we reach a production-ready application.

### 🎯 Course Goals

| Goal | Description |
|------|-------------|
| **Master Fundamentals** | Understand Service Container, Providers, Facades |
| **Build REST APIs** | Create production-ready RESTful services |
| **Work with Databases** | Eloquent ORM, Relationships, Migrations |
| **Secure Applications** | Sanctum/JWT Authentication, Policies |
| **Write Tests** | PHPUnit, Feature Tests, Database Testing |
| **Deploy to Production** | Docker, CI/CD, Cloud Deployment |
| **Create Portfolio** | Build a complete project to showcase |

### 📅 Journey Timeline

- **Start Date:** August 24, 2026
- **Duration:** 8 Weeks
- **Commitment:** 2-3 hours daily
- **Format:** Daily concepts + Weekly mini-projects

---

## 🤔 Why This Repository

This isn't just a collection of code snippets. This repository represents:

| Aspect | What It Shows |
|--------|---------------|
| **Consistent Learning** | 8 weeks of dedicated daily practice |
| **Progressive Complexity** | Each week builds on the previous |
| **Real-World Projects** | Mini-projects that demonstrate practical skills |
| **Clean Code** | Following Laravel best practices from Day 1 |
| **Documentation** | Each concept is explained, not just implemented |
| **Portfolio Ready** | Complete projects to showcase to employers |

### What You'll Find Here

| Element | Description |
|---------|-------------|
| **Weekly Projects** | Working Laravel applications for each week |
| **READMEs** | Detailed explanations of each week's concepts |
| **Postman Collections** | API testing documentation |
| **Common Mistakes** | Problems I encountered and how to fix them |
| **Best Practices** | Production-grade code from Day 1 |

---

## 🛠️ Tech Stack

### Core Technologies

| Category | Technology | Version |
|----------|------------|---------|
| **Language** | PHP | 8.2+ |
| **Framework** | Laravel | 11.x |
| **Package Manager** | Composer | 2.x |
| **Web Server** | PHP-FPM + Nginx | Latest |

### Database & Persistence

| Category | Technology |
|----------|------------|
| **ORM** | Eloquent ORM |
| **Dev Database** | SQLite / MySQL |
| **Production Database** | PostgreSQL 15+ |
| **Migrations** | Laravel Migrations |
| **Caching** | Redis |

### Security & API

| Category | Technology |
|----------|------------|
| **Authentication** | Laravel Sanctum / tymon/jwt-auth |
| **Authorization** | Gates & Policies |
| **API Testing** | Postman, Laravel HTTP Client |
| **Documentation** | Laravel API Resources |

### Testing & Quality

| Category | Technology |
|----------|------------|
| **Unit Testing** | PHPUnit |
| **Mocking** | Mockery |
| **Database Testing** | RefreshDatabase, DatabaseTransactions |
| **Feature Testing** | Laravel TestCase |
| **Assertions** | PHPUnit Assertions |

### DevOps & Deployment

| Category | Technology |
|----------|------------|
| **Containerization** | Docker |
| **Orchestration** | Docker Compose |
| **CI/CD** | GitHub Actions |
| **Cloud Deployment** | Railway / Render |

### Developer Tools

| Category | Technology |
|----------|------------|
| **IDE** | VS Code / PhpStorm |
| **Debugging** | Laravel Debugbar |
| **Development** | Laravel Sail |
| **Code Quality** | Laravel Pint |

---

## 📚 8-Week Curriculum

### Complete Roadmap

| Week | Topic | Focus Areas | Mini-Project | Status |
|------|-------|-------------|--------------|--------|
| **Week 1** | [Laravel Core](week01-core/) | Service Container, Providers, Routing | Config-Driven Application | ✅ Complete |
| **Week 2** | [HTTP Layer](week02-http/) | Controllers, Requests, Responses | RESTful Task API | ⏳ Upcoming |
| **Week 3** | [Database & Eloquent](week03-eloquent/) | Migrations, Models, Relationships | Library Management (Database Layer) | ⏳ Upcoming |
| **Week 4** | [Advanced Eloquent](week04-advanced-eloquent/) | Querying, Transactions, Performance | Library System Query Layer | ⏳ Upcoming |
| **Week 5** | [Authentication & Authorization](week05-auth/) | Sanctum/JWT, Policies | JWT Auth Service | ⏳ Upcoming |
| **Week 6** | [Production Polish](week06-production/) | Exceptions, Validation, Logging | Production-Hardened Task Manager | ⏳ Upcoming |
| **Week 7** | [Testing](week07-testing/) | PHPUnit, Feature Tests, Mocks | Complete Test Suite (80+ tests) | ⏳ Upcoming |
| **Week 8** | [Deployment](week08-deployment/) | Docker, CI/CD, Cloud | Full Deployment Pipeline | ⏳ Upcoming |
| **Capstone** | [Final Project](final-capstone/) | Complete Blog Platform API | Production-Ready App | ⏳ Upcoming |

---

## 📊 Weekly Breakdown

### Week 1: Laravel Core 
**Goal:** Understand what Laravel actually does under the hood

| Day | Topic | Key Concepts |
|-----|-------|--------------|
| Day 1 | Installation & Project Structure | Artisan CLI, Service Container Basics |
| Day 2 | Service Container Deep Dive | Binding, Resolving, Dependency Injection |
| Day 3 | Service Providers & Facades | register vs boot, Deferred Providers |
| Day 4 | Configuration System | .env, Config Caching, Environment Config |
| Day 5 | Routing Fundamentals | Route Parameters, Groups, Middleware |
| Day 6 | Request Lifecycle | HTTP Kernel, Middleware Pipeline |
| Day 7 | Mini-Project | Config-Driven Application |

**Key Achievement:** Created custom service provider and facade with environment-based configuration!

---

### Week 2: HTTP Layer
**Goal:** Build real, testable HTTP endpoints

| Day | Topic | Key Concepts |
|-----|-------|--------------|
| Day 1 | Controllers | Resource Controllers, Route Model Binding |
| Day 2 | Form Requests | Validation, Accessing Input, File Uploads |
| Day 3 | Responses | JSON Responses, Status Codes, Headers |
| Day 4 | Custom Middleware | Creating Middleware, Groups |
| Day 5 | API Resources | JsonResource, Conditional Attributes |
| Day 6 | Postman Collection | Testing Endpoints, Automation |
| Day 7 | Mini-Project | RESTful Task API |

---

### Week 3: Database & Eloquent ORM
**Goal:** Persist data for real

| Day | Topic | Key Concepts |
|-----|-------|--------------|
| Day 1 | Migrations | Tables, Column Types, Indexes |
| Day 2 | Eloquent Basics | Models, Mass Assignment, Accessors |
| Day 3 | Query Builder vs Eloquent | Scopes, Chunking, Lazy Collections |
| Day 4 | Relationships | One-to-One, One-to-Many, Many-to-Many |
| Day 5 | Advanced Relationships | Polymorphic, hasManyThrough |
| Day 6 | Eloquent Events & Observers | creating, created, updating |
| Day 7 | Mini-Project | Library Management (Database Layer) |

---

### Week 4: Advanced Eloquent
**Goal:** Query like a professional

| Day | Topic | Key Concepts |
|-----|-------|--------------|
| Day 1 | Advanced Querying | Subqueries, Raw Expressions, JSON Columns |
| Day 2 | Pagination | paginate(), simplePaginate(), cursorPaginate() |
| Day 3 | Database Transactions | DB::transaction(), Deadlock Handling |
| Day 4 | Seeders & Factories | Faker, Database Seeding Strategies |
| Day 5 | Repository Pattern | Decoupling Eloquent from Business Logic |
| Day 6 | Performance Optimization | N+1 Detection, withCount, loadMissing |
| Day 7 | Mini-Project | Library System Query Layer |

---

### Week 5: Authentication & Authorization
**Goal:** Secure your APIs the way production does

| Day | Topic | Key Concepts |
|-----|-------|--------------|
| Day 1 | Authentication Fundamentals | Guards, Providers, Session vs Token |
| Day 2 | Laravel Sanctum | API Token Auth, Token Abilities |
| Day 3 | Password Handling | Hashing, Reset Flow, bcrypt vs argon2 |
| Day 4 | JWT with tymon/jwt-auth | Token Generation, Refresh Tokens |
| Day 5 | Refresh Token Implementation | Storing, Revocation, Rotation |
| Day 6 | Two-Token System | Access + Refresh, Logout-All |
| Day 7 | Mini-Project | JWT Auth Service |

---

### Week 6: Production Polish
**Goal:** Make the API robust and clean

| Day | Topic | Key Concepts |
|-----|-------|--------------|
| Day 1 | Authorization | Gates, Policies, Owner-or-Admin Pattern |
| Day 2 | Exception Handling | Handler.php, Custom Exceptions |
| Day 3 | Validation Deep Dive | Custom Rules, Rule Objects, after Hooks |
| Day 4 | API Resources & DTOs | Conditional Fields, whenLoaded() |
| Day 5 | Fractal Pattern | Transformers, Includes, Sparse Fieldsets |
| Day 6 | Logging | Channels, Structured Context, Custom Handlers |
| Day 7 | Mini-Project | Production-Hardened Task Manager |

---

### Week 7: Testing
**Goal:** Be able to prove your code works

| Day | Topic | Key Concepts |
|-----|-------|--------------|
| Day 1 | PHPUnit Fundamentals | Test Structure, Assertions, Data Providers |
| Day 2 | Mocking with Mockery | mock(), shouldReceive(), Spies |
| Day 3 | Feature Tests | actingAs(), getJson(), postJson() |
| Day 4 | Database Testing | RefreshDatabase, DatabaseTransactions |
| Day 5 | HTTP Testing with Real Database | RefreshDatabase + PostgreSQL |
| Day 6 | Test Factories & Fixtures | State Methods, Reusable Helpers |
| Day 7 | Mini-Project | Complete Test Suite (80+ tests) |

---

### Week 8: Deployment
**Goal:** Ship it!

| Day | Topic | Key Concepts |
|-----|-------|--------------|
| Day 1 | Docker Fundamentals | Multi-stage Dockerfile, PHP-FPM + Nginx |
| Day 2 | Docker Compose | App + PostgreSQL + Redis + Nginx |
| Day 3 | Environment Configuration | Config Caching, .env vs Environment Variables |
| Day 4 | CI with GitHub Actions | PHPUnit, PostgreSQL Container, Composer Caching |
| Day 5 | CD & Build Artifacts | Docker Images, SHA Tags, Layer Caching |
| Day 6 | Deployment to Railway/Render | Managed PostgreSQL, Migrations, Workers |
| Day 7 | Final Mini-Project | Complete Deployment Pipeline |

---

## 🔗 Spring Boot → Laravel Comparison

| Spring Boot Concept | Laravel Equivalent |
|---------------------|-------------------|
| ApplicationContext | Service Container |
| @Bean / @Component | Service Provider bindings |
| @ConfigurationProperties | config() helper + .env |
| @RestController | Controller + Route::apiResource() |
| Spring Data JPA | Eloquent ORM |
| @Valid / @NotBlank | Form Request validation rules |
| @ControllerAdvice | Exception Handler (Handler.php) |
| @PreAuthorize | Gate::authorize() / Policies |
| Spring Security JWT | Sanctum / tymon/jwt-auth |
| @DataJpaTest | RefreshDatabase + factories |
| Testcontainers | MySQL/PostgreSQL in GitHub Actions |

---

## 📂 Project Structure

```
laravel-8-week-journey/
├── week01-core/                 # Mini-project: Config-Driven Application
├── week02-http/                 # Mini-project: RESTful Task API
├── week03-eloquent/             # Mini-project: Library Management (Database Layer)
├── week04-advanced-eloquent/    # Mini-project: Library System Query Layer
├── week05-auth/                 # Mini-project: JWT Auth Service
├── week06-production/           # Mini-project: Production-Hardened Task Manager
├── week07-testing/              # Mini-project: Complete Test Suite (80+ tests)
├── week08-deployment/           # Mini-project: Full Deployment Pipeline
└── final-capstone/              # The complete Blog Platform (your showcase)
    ├── README.md                # Comprehensive: tech stack, ERD diagram, API docs, deployment
```

---

## 🚀 How to Run

### Prerequisites

```bash
# Check PHP version
php -v  # Should be 8.2+

# Check Composer version
composer -v  # Should be 2.x

# Check MySQL/PostgreSQL
mysql --version
```

### Running the Application

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/laravel-8-week-journey.git
cd laravel-8-week-journey

# Install dependencies
composer install

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=laravel_journey
# DB_USERNAME=postgres
# DB_PASSWORD=password

# Run migrations
php artisan migrate

# Seed database (if available)
php artisan db:seed

# Start development server
php artisan serve
```

### Using Docker

```bash
# Build and start containers
docker-compose up -d

# Run migrations inside container
docker-compose exec app php artisan migrate

# Access the application
open http://localhost:8000
```

---

## 📈 Progress Tracking

### Weekly Overview

- [ ] Week 1: Laravel Core
- [ ] Week 2: HTTP Layer
- [ ] Week 3: Database & Eloquent ORM
- [ ] Week 4: Advanced Eloquent
- [ ] Week 5: Authentication & Authorization
- [ ] Week 6: Production Polish
- [ ] Week 7: Testing
- [ ] Week 8: Deployment
- [ ] Final Capstone: Blog Platform

### Daily Checkpoints

Each week folder contains daily checkpoints with:
- ✅ Concept understood
- ✅ Code implemented
- ✅ Notes taken
- ✅ Mini-project completed

---

## 🎯 Key Learnings So Far

### Week 1: Core Concepts

> **Service Container is the heart of Laravel.** Every time you use `app()` or dependency injection, you're touching the container. It's not magic - it's a simple key-value store with autowiring.

| Concept | My Understanding |
|---------|------------------|
| Service Container | A powerful dependency injection container that manages class dependencies |
| Service Providers | The bootstrap code that registers services into the container |
| Facades | Static proxies to service container bindings |
| Middleware | Layers of request processing before hitting the controller |

### Week 2: HTTP Layer

> **Route Model Binding is a game-changer.** Instead of manually finding models by ID, Laravel does it automatically. This is dependency injection for routes.

| Concept | My Understanding |
|---------|------------------|
| Route Model Binding | Automatic model resolution based on route parameters |
| Form Requests | Dedicated request classes with validation logic |
| API Resources | Transformers for consistent API responses |
| Middleware Groups | Reusable middleware sets for route groups |

---

## 📝 Resources

### Official Documentation

- [Laravel Documentation](https://laravel.com/docs) - Official framework docs
- [Laravel API Reference](https://laravel.com/api) - Class reference

### Learning Resources

| Resource | Description |
|----------|-------------|
| [Laracasts](https://laracasts.com) | Premium video tutorials |
| [Laravel News](https://laravel-news.com) | Latest Laravel news and packages |
| [Laravel Daily](https://laraveldaily.com) | Practical tips and tutorials |

### Community

- [Laravel Reddit](https://reddit.com/r/laravel)
- [Laravel Discord](https://discord.gg/laravel)
- [Laravel Twitter/X](https://twitter.com/laravelphp)

### Packages Used

| Package | Purpose |
|---------|---------|
| [laravel/sanctum](https://github.com/laravel/sanctum) | API Token Authentication |
| [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) | JWT Authentication |
| [barryvdh/laravel-debugbar](https://github.com/barryvdh/laravel-debugbar) | Development Debugging |
| [laravel/pint](https://github.com/laravel/pint) | Code Style Fixer |

---

### Why Follow This Journey?

- ✅ Real progress from beginner to advanced
- ✅ Honest mistakes and how I fixed them
- ✅ Clean, production-ready code
- ✅ Complete project portfolio

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- [Laravel Documentation Team](https://laravel.com/) - For comprehensive docs
- [Laracasts](https://laracasts.com/) - For inspiring the learning format
- [Spring Boot Journey](https://github.com/your-spring-boot-repo) - For the structure inspiration

---
