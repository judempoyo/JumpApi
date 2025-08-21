# JUMP_API - Smart PHP REST API

## Overview
**JUMP_API** is a modern, automatic PHP REST API that dynamically generates complete CRUD endpoints for all your data models. It natively supports multiple databases and offers robust error handling with standardized JSON responses.

### ✨ Features
- 🚀 **Automatic route generation**: Auto-generated complete CRUD for each model.
- 🎯 **Pure OOP architecture**: Modern, maintainable code.
- 🛡️ **Built-in security**: CORS, validation, injection protection.
- 📊 **Standardized responses**: Consistent JSON format with error handling.
- ⚡ **Performance**: Lightweight, fast, and without heavy dependencies.
- 🔧 **Simple configuration**: `.env` file for all configuration.

---

## 🏗️ Technical Architecture
```
JUMP_API/
├── .env                    # Environment configuration
├── .env.example            # Configuration template
├── config/
│   ├── Database.php        # Multi-database manager
│   └── config.php          # Configuration loader
├── core/
│   ├── ErrorHandler.php    # Error management
│   └── Response.php        # Standardized response handler
├── Middleware/             # Middleware components
├── controllers/
│   └── ApiController.php   # Main API controller
├── models/                 # Data models
│   ├── Model.php           # Base abstract model
│   ├── UserModel.php       # User model (example)
│   └── ProductModel.php    # Product model (example)
├── routes/
│   └── routes.php          # Route definitions
├── tests/                  # Test suite
│   └── ProductTest.php     # Product tests
├── vendor/                 # Composer dependencies
├── .htaccess               # Apache rewrite rules
├── composer.json           # Composer configuration
├── index.php               # Main entry point
└── readme.md               # This file
```

---

## 🚀 Getting Started

### Prerequisites
- **PHP** 8.4+
- **MySQL** 5.7+ or **MariaDB** 10.3+
- **Apache/Nginx** with `mod_rewrite`
- **Composer** (optional)

### Installation

1. **Clone the Repository**:
  ```bash
  git clone https://github.com/judempoyo/JumpApi.git
  cd JumpApi
  ```

2. **Install Dependencies**:
  ```bash
  composer install
  ```

3. **Configure Environment**:
  ```bash
  cp .env.example .env
  # Edit the .env file with your parameters
  ```

4. **Database Configuration**:
  Open the `.env` file and modify the parameters:
  ```env
  # Main database
  DB_HOST=localhost
  DB_PORT=3306
  DB_NAME=jump_api
  DB_USER=root
  DB_PASS=your_password
  ```


5. **Deploy the API**:
  ```bash
  # Local development
  php -S localhost:8000

  # Or deploy on Apache/Nginx
  ```

---

## 📡 Base URL
```
http://localhost:8000/api/v1/
```

---

## 🔌 Automatic Endpoints

### For the User model (`models/UserModel.php`)
| Method | Endpoint               | Description            |
|--------|------------------------|------------------------|
| GET    | `/api/v1/users`        | List all users         |
| GET    | `/api/v1/users/{id}`   | Get a specific user    |
| POST   | `/api/v1/users`        | Create a new user      |
| PUT    | `/api/v1/users/{id}`   | Update a user          |
| DELETE | `/api/v1/users/{id}`   | Delete a user          |

### For the Product model (`models/ProductModel.php`)
| Method | Endpoint               | Description            |
|--------|------------------------|------------------------|
| GET    | `/api/v1/products`     | List all products      |
| GET    | `/api/v1/products/{id}`| Get a specific product |
| POST   | `/api/v1/products`     | Create a new product   |
| PUT    | `/api/v1/products/{id}`| Update a product       |
| DELETE | `/api/v1/products/{id}`| Delete a product       |

---

## 📋 Request Examples

### Get all users
```bash
curl -X GET "http://localhost:8000/api/v1/users"
```

### Create a new user
```bash
curl -X POST "http://localhost:8000/api/v1/users" \
  -H "Content-Type: application/json" \
  -d '{
   "name": "John Doe",
   "email": "john@example.com",
   "password": "securepassword123"
  }'
```

---

## 📦 Response Format

### Success Response (200 OK)
```json
{
	"status": 200,
	"message": "Success",
	"data": {
		"id": 1,
		"name": "Jude",
		"email": "mpoyojude0@gmail.com",
	},
	"timestamp": "2025-08-21T03:18:26+02:00"
}
```

### Error Response (404 Not Found)
```json
{
	"status": 404,
	"message": "Resource not found",
	"details": null,
	"timestamp": "2025-08-21T03:19:03+02:00"
}

```

### Creation Response (201 Created)
```json
{
  "status": 201,
  "data": {
   "id": 1,
   "name": "Jude",
		"email": "mpoyojude0@gmail.com",
   "created_at": "2025-08-21T03:19:03+02:00"
  },
  "message": "User created successfully",
  "timestamp": "2025-08-21T03:19:03+02:00"
}
```

---

## 🛡️ Error Handling
The API uses standard HTTP codes with clear error messages:
- **200 OK**: Successful request.
- **201 Created**: Resource created successfully.
- **400 Bad Request**: Invalid request.
- **401 Unauthorized**: Authentication required.
- **403 Forbidden**: Access denied.
- **404 Not Found**: Resource not found.
- **500 Internal Server Error**: Server error.

---

## 🔧 Creating New Models
To add a new model, create a file in the `models/` folder:

**Example**: `models/ArticleModel.php`
```php
  <?php
  require_once 'Model.php';

  class ArticleModel extends Model
  {
    protected $table = 'articles';
    protected $primaryKey = 'id';

    public function __construct($db)
    {
      parent::__construct($db);
    }

    
  }
```

Complete CRUD endpoints will be automatically available:
```
GET    /api/v1/articles
GET    /api/v1/articles/{id}
POST   /api/v1/articles  
PUT    /api/v1/articles/{id}
DELETE /api/v1/articles/{id}
```

---

## 🌐 CORS & Security
The API includes complete CORS configuration:
```env
# CORS configuration in .env
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Origin,Content-Type,Authorization,Accept
```

---

## 🚀 Performance
- **Automatic route generation**: No more manual configuration.
- **PDO connection pool**: Optimized database connections.
- **Model caching**: Intelligent automatic loading.
- **Light responses**: Minified JSON in production.

---



## 🤝 Contributing
Contributions are welcome! To contribute:
1. Fork the project.
2. Create a feature branch:
  ```bash
  git checkout -b feature/AmazingFeature
  ```
3. Commit changes:
  ```bash
  git commit -m 'Add AmazingFeature'
  ```
4. Push to the branch:
  ```bash
  git push origin feature/AmazingFeature
  ```
5. Open a Pull Request.

---

## 📄 License
This project is licensed under the MIT License. See the `LICENSE` file for details.

---

## 📞 Support
For any questions or issues:
- 📧 Email: mpoyojude0@gmail.com
- 🐛 Issues: [GitHub Issues](https://github.com/judempoyo/JumpApi/issues)
---

**JUMP_API** - The PHP REST API that automatically adapts to your needs! 🚀