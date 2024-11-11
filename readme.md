# JUMP_API

## Overview

This API provides endpoints for managing users and products, as well as the ability for users to create other models. It supports standard CRUD operations (Create, Read, Update, Delete) using HTTP methods.

## Features

- User management (create, read, update, delete)
- Product management (create, read, update, delete)
- Dynamic model creation by users
- Pagination support for retrieving lists of users and products
- JSON format for request and response

## Technologies Used

- PHP
- MySQL (or any other database)
- PDO for database interactions

## Getting Started

### Prerequisites

- PHP 7.0 or higher
- Composer (for dependency management, if needed)
- A web server (Apache, Nginx, etc.)
- MySQL or another database system

### Installation

1. **Clone the Repository**:

   ```bash
   git clone https://github.com/judempoyo/JumpApi.git
   cd JumpApi
    ```

2. **Set Up the Database**:

   - Create a new database in MySQL.
   - Import the SQL schema (if available) to create the necessary tables for users, products, and any other models.

3. **Configure Database Connection**:

- Open the config/Database.php file and update the database connection parameters:

    ```php
      private $host = 'localhost';
      private $db = 'your_database';
      private $user = 'your_username';
      private $pass = 'your_password';
    ```

4. **Deploy the API**:

- Place the project files in your web server's document root (e.g., htdocs for XAMPP, www for WAMP, etc.).
  
### Usage

## Base URL

  ```url
    http://JumpAi/api/index.php
  ```

### Endpoints

- ## User Model

  - `GET /JumpAi/api/index.phpmodel=user` - Get all users
  - `POST /JumpAi/api/index.phpmodel=user` - Create a new user
  - `PUT /JumpAi/api/index.phpmodel=user&id={id}`- Update a user
  - `DELETE /JumpAi/api/index.phpmodel=user&id={id}` - Delete a user
  
- ## Product Model

  - `GET /JumpAi/api/index.php?model=product` - Get all products
  - `POST /JumpAi/api/index.php?model=product` - Create a new product
  - `PUT /JumpAi/api/index.php?model=product&id={id}` - Update a product
  - `DELETE /JumpAi/api/index.php?model=product&id={id}` - Delete a product

- ## Dynamic Model Creation

  - `Endpoint`: /api/your-script.php?model={model_name}
  - `Method`: `POST`
  - `Request Body` (JSON):

    ```json
      {
          "field1": "value1",
          "field2": "value2"
      }
    ```

- ## Response

  - `201 Created`: Model created successfully.
  - `400 Bad Request`: Failed to create model.
  
- ## Example
  
    ```json
      {
          "message": "Resource created successfully"
      }
    ```

### Example Requests

## Get All Users

  ```bash
    curl -X GET "<http://your-api-domain/api/your-script.php?model=user&page=1&limit=10>"
  ```

## Create a User

  ```bash
    curl -X POST "<http://your-api-domain/api/your-script.php?model=user>" \
    -H "Content-Type: application/json" \
    -d '{"username": "newuser", "password": "password123"}'
  ```

## Create a Dynamic Model

  ```bash
    curl -X POST "<http://your-api-domain/api/your-script.php?model=customModel>" \
    -H "Content-Type: application/json" \
    -d '{"field1": "value1", "field2": "value2"}'
  ```

## Error Handling

The API returns standard HTTP status codes to indicate the success or failure of requests. Common status codes include:

- `200 OK`: Request succeeded.
- `201 Created`: Resource created successfully.
- `400 Bad Request`: Invalid request.
- `404 Not Found`: Resource not found.
- `500 Internal Server Error`: An error occurred on the server.

### Contributing

Contributions are welcome! If you have suggestions for improvements or find bugs, please open an issue or submit a pull request.

### License

This project is licensed under the MIT License. See the LICENSE file for details.
