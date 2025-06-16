# 🚀 Advanced Task Management API

[![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg?style=flat-square&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)
[![API Documentation](https://img.shields.io/badge/API-Documented-brightgreen.svg?style=flat-square)](http://localhost:8000/api/documentation)

A **modern, enterprise-grade Task Management API** built with Laravel 12, featuring comprehensive JWT authentication, role-based access control, and interactive Swagger documentation. Designed with modular architecture, advanced security features, and production-ready deployment configurations.

## 🎯 Project Overview

This API provides a complete task management solution with user authentication, admin management, and comprehensive task operations. Built following Laravel best practices and SOLID principles, it offers:

- **Multi-tenancy**: Separate user and admin interfaces
- **Modular Architecture**: Clean separation of concerns with Laravel Modules
- **Advanced Authentication**: JWT-based auth with refresh tokens and OTP verification
- **Comprehensive API Documentation**: Interactive Swagger/OpenAPI 3.0 documentation

## ✨ Key Features

### 🔐 **Authentication & Security**
- **JWT Authentication** with automatic token refresh
- **Multi-guard System** (User & Admin authentication)
- **Email Verification** with OTP-based system
- **Password Reset** with secure OTP workflow
- **Rate Limiting** on sensitive endpoints
- **Role-Based Access Control** (RBAC) with permissions

### 📋 **Task Management**
- **Complete CRUD Operations** for tasks
- **Advanced Filtering & Pagination** 
- **Task Status Management** with workflow tracking
- **Soft Delete System** with trash/restore functionality
- **User Assignment** and collaboration features

### 👥 **User & Admin Management**
- **User Profile Management** with slug-based URLs
- **Admin Dashboard** with comprehensive controls
- **Permission Management** with hierarchical roles
- **User Ban System** for moderation
- **File Upload Management** for user assets

### 🛠 **Developer Experience**
- **Interactive API Documentation** (Swagger UI)
- **Postman Collection** with auto-token management
- **Comprehensive Test Suite** with PHPUnit


## 🏗 **Architecture & Design Decisions**

### **Modular Architecture**
The application uses **Laravel Modules** for clean separation of concerns:

```
Modules/
├── User/           # User authentication & profile management  
├── Task/           # Task management operations
└── Admin/          # Administrative functions
```

**Benefits:**
- **Maintainability**: Each module is self-contained
- **Scalability**: Easy to add new modules
- **Team Collaboration**: Multiple developers can work on different modules
- **Testing**: Isolated testing per module

### **Repository Pattern Implementation**
Using **Prettus L5-Repository** for data access layer:

```php
// Clean controller with injected repository
public function __construct(
    private TaskRepository $taskRepository
) {}
```

**Benefits:**
- **Separation of Concerns**: Controllers focus on HTTP logic
- **Testability**: Easy mocking of data layer  
- **Flexibility**: Swap implementations without affecting business logic
- **Caching**: Built-in repository caching support

### **JWT Authentication Strategy**
Implemented with **PHP-Open-Source-Saver/JWT-Auth**:

- **Stateless Authentication**: No server-side session storage
- **Token Refresh**: Automatic token renewal for seamless UX
- **Multi-Guard Support**: Separate tokens for users and admins
- **Configurable Expiration**: Flexible token lifetime management

### **API Documentation Strategy**
Using **L5-Swagger** with comprehensive annotations:

- **Interactive Documentation**: Swagger UI for real-time testing
- **Multiple API Docs**: Separate documentation for different user types
- **Schema Validation**: Request/response validation with examples
- **Auto-Generation**: Documentation generated from code annotations

## 🚀 **Quick Start**

### **Prerequisites**
- PHP 8.2 or higher
- Composer 2.x
- MySQL 5.7+ or PostgreSQL
- Node.js 18+ (for asset compilation)
- Redis (optional, for caching)

### **Installation**

#### **Option 1: Local Development**

1. **Clone the repository**
```bash
git clone <repository-url>
cd advanced-task-management-api
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. **Configure database**
```bash
# Edit .env file with your database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run migrations and seeders**
```bash
php artisan migrate --seed
```

6. **Generate API documentation**
```bash
php artisan l5-swagger:generate --all
```

7. **Start development server**
```bash
php artisan serve
# Or use the enhanced dev command with queue workers
composer run dev
```

#### **Option 2: Docker Development**

1. **Build and start containers**
```bash
docker-compose -f docker-compose.dev.yml up -d
```

2. **Install dependencies in container**
```bash
docker exec -it dev-envirdian-app composer install
docker exec -it dev-envirdian-app php artisan migrate --seed
```

### **Access Points**

- **Application**: http://localhost:8000
- **API Documentation**: http://localhost:8000/api/documentation
- **Admin API Docs**: http://localhost:8000/api/admin-documentation  
- **User API Docs**: http://localhost:8000/api/user-documentation

## 📚 **API Documentation**

### **Interactive Documentation**
Access comprehensive Swagger documentation at:
- **Main API**: http://localhost:8000/api/documentation
- **Admin API**: http://localhost:8000/api/admin-documentation

### **Quick API Overview**

#### **Authentication Endpoints**
```bash
POST /api/v1/auth/register     # User registration
POST /api/v1/auth/login        # User authentication  
POST /api/v1/auth/refresh      # Token refresh
POST /api/v1/auth/logout       # User logout
POST /api/v1/auth/forgot-password    # Password reset request
POST /api/v1/auth/reset-password     # Password reset confirmation
```

#### **Task Management Endpoints**
```bash
GET    /api/user/v1/tasks              # List tasks (paginated)
POST   /api/user/v1/tasks              # Create task
GET    /api/user/v1/tasks/{id}         # Get specific task
PUT    /api/user/v1/tasks/{id}         # Update task
PATCH  /api/user/v1/tasks/{id}/status  # Update task status
DELETE /api/user/v1/tasks/{id}         # Soft delete task
```

#### **Admin Endpoints**
```bash
GET    /api/admin/v1/admins            # List administrators
POST   /api/admin/v1/admins            # Create administrator
GET    /api/admin/v1/roles             # List roles
POST   /api/admin/v1/roles             # Create role
GET    /api/admin/v1/permissions       # List permissions
```

### **Authentication Flow**

1. **Register a new user**
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com", 
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123"
  }'
```

2. **Login and get JWT token**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePass123"
  }'
```

3. **Use token for authenticated requests**
```bash
curl -X GET http://localhost:8000/api/user/v1/tasks \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

## 🧪 **Testing**

### **Running Tests**
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

### **Test Structure**
```
tests/
├── Feature/           # Integration tests
│   ├── Auth/         # Authentication tests
│   ├── Task/         # Task management tests
│   └── Admin/        # Admin functionality tests
└── Unit/             # Unit tests
```

## 🛠 **Development Tools**

### **Code Quality**
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run static analysis (if installed)
./vendor/bin/phpstan analyse
```

### **Database Operations**
```bash
# Create new migration
php artisan make:migration create_table_name

# Create model with migration and factory
php artisan make:model ModelName -mf

# Refresh database with seeding
php artisan migrate:refresh --seed
```

### **Module Operations**
```bash
# Create new module
php artisan module:make ModuleName

# Generate module controller
php artisan module:make-controller ControllerName ModuleName

# Generate module model
php artisan module:make-model ModelName ModuleName
```

## 🐳 **Deployment**

### **Docker Production**
```bash
# Build production image
docker build -t task-management-api .

# Run with docker-compose
docker-compose up -d
```

### **Environment Variables**
Key environment variables for production:

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name

# JWT Configuration
JWT_SECRET=your-jwt-secret
JWT_TTL=1440

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host

# File Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
```

## 📦 **Key Dependencies**

### **Core Framework**
- **Laravel 12.0**: Latest Laravel framework
- **PHP 8.2+**: Modern PHP with latest features

### **Authentication & Security**
- **php-open-source-saver/jwt-auth**: JWT authentication
- **cybercog/laravel-ban**: User banning system

### **Architecture & Utilities**
- **nwidart/laravel-modules**: Modular architecture
- **prettus/l5-repository**: Repository pattern
- **tucker-eric/eloquentfilter**: Advanced filtering
- **cviebrock/eloquent-sluggable**: URL-friendly slugs

### **API Documentation**
- **darkaonline/l5-swagger**: OpenAPI/Swagger documentation

### **Development Tools**
- **laravel/telescope**: Application debugging
- **laravel/pint**: Code style fixing
- **PHPUnit**: Testing framework

## 🔧 **Configuration**

### **Key Configuration Files**
- `config/l5-swagger.php` - API documentation settings
- `config/jwt.php` - JWT authentication configuration  
- `config/modules.php` - Module system configuration
- `config/repository.php` - Repository pattern settings

### **Module Configuration**
Each module contains its own configuration in:
```
Modules/{ModuleName}/Config/config.php
```

## 🤝 **Contributing**

### **Development Workflow**
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### **Code Standards**
- Follow **PSR-12** coding standards
- Use **Laravel conventions** for naming
- Write **comprehensive tests** for new features
- Update **API documentation** for endpoint changes
- Follow **commit message conventions**

### **Pull Request Guidelines**
- Ensure all tests pass
- Update documentation if needed
- Add appropriate labels
- Request review from maintainers

## 📄 **License**

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

## 🙏 **Acknowledgments**

- **Laravel Community** for the excellent framework
- **Swagger/OpenAPI** for API documentation standards
- **JWT Working Group** for authentication specifications
- **Open Source Contributors** who made this project possible

## 🆘 **Support**

### **Getting Help**
- 📖 Check the [API Documentation](http://localhost:8000/api/documentation)
- 🐛 Report bugs via [GitHub Issues](repository-url/issues)
- 💬 Join our community discussions
- 📧 Contact the development team

### **Useful Resources**
- [Laravel Documentation](https://laravel.com/docs)
- [JWT Authentication Guide](https://jwt.io/)
- [Swagger/OpenAPI Specification](https://swagger.io/specification/)
- [Docker Documentation](https://docs.docker.com/)

---

**Built with ❤️ using Laravel 12 and modern PHP practices**
