# test-coding-paketur
=======
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Laravel Project

## **Overview**
This project is a CRM company to manage employee with Laravel application for completed test coding from PT. Global Inovasi Gemilang. It using third-party libraries such as **Scramble** for API documentation and **JWT-Auth** for token-based authentication. Middleware, policies, and gates are implemented to enforce security and manage roles/permissions effectively.

---

## **Getting Started**

Follow these steps to set up and run the project on your local machine.

### **1. Clone the Repository**
1. Open your terminal.
2. Clone the repository:
   ```bash
   git clone https://github.com/Nasrul676/test-coding-paketur.git
   ```
3. Navigate into the project directory:
   ```bash
   cd test-coding-paketur
   ```

---

### **2. Install Dependencies**
1. Ensure **Composer** is installed on your system.
2. Run the following command to install project dependencies:
   ```bash
   composer install
   ```

---

### **3. Configure the Environment**
1. Copy the `.env.example` file and rename it to `.env`:
   ```bash
   cp .env.example .env
   ```
2. Update your `.env` file with the necessary database configurations:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
   ```
3. Generate the application key:
   ```bash
   php artisan key:generate
   ```
4. Generate the JWT secret key:
   ```bash
   php artisan jwt:secret
   ```

---

### **4. Set Up the Database**
1. Run migrations to set up the database schema:
   ```bash
   php artisan migrate
   ```
2. (Optional) Seed the database with initial data:
   ```bash
   php artisan db:seed
   ```

---

### **5. Run the Application**
1. Start the Laravel development server:
   ```bash
   php artisan serve
   ```
2. Open your browser and navigate to:
   ```
   http://127.0.0.1:8000
   ```

---

## **Running Tests**
1. Execute the unit tests to ensure everything works as expected:
   ```bash
   php artisan test
   ```
2. The test results will be displayed in the terminal.

---

## **Third-Party Libraries**

### **1. Scramble**
- **Purpose**: Automatically generates API documentation from controller annotations.
- **Documentation URL**:
  ```
  http://127.0.0.1:8000/docs/api#/
  ```

### **2. JWT-Auth**
- **Purpose**: Handles token-based authentication.

---
## **Middleware, Policies, and Gates**

### **Middleware**
Middleware ensures only authenticated users can access certain endpoints. Example:
- `auth:api`: Ensures the user has a valid token.
- `role:admin`: Restricts access to users with the admin role.

### **Policies**
Policies manage permissions on specific models. Example:
```php
public function update(User $user, Company $company)
{
    return $user->id === $company->id;
}
```

### **Gates**
Gates define global permissions. Example:
```php
Gate::define('create.company', function (User $user) {
    return $user->role === 'super_admin';
});
```

---

## **Conclusion**
Follow the steps above to set up and explore the application. If you encounter any issues, please send me an email to nasrulmuhammad676@gmail.com.

