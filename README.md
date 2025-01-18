# test-coding-paketur
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

### **5. Having problem with aunthentication ?**
1. First clear cache with command:
   ```bash
   php artisan cache:clear
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

### **Validation**
For the implementing validation, i use Form Requests validation because it's more great for implementing clean code and make controller more simple, the normal cases of validation are derived directly from the application's requirements. Below is a detailed explanation of how I approached validation based on reading and analyzing the requirements.

## Normal Cases of Validation

### 1. **Required Fields**
Ensures that mandatory fields are not left empty. Missing fields may lead to incomplete data and unexpected errors.
```php
'name' => 'required',
'email' => 'required|email|unique',
'password' => 'required|min:8',
```

### 2. **Data Type Validation**
Enforces that fields contain values of the expected data type.
```php
'email' => 'required|email'
```

### 3. **Length and Size Validation**
Prevents excessively short or long inputs to align with business rules and database constraints.
```php
'username' => 'required|string|min:8',
'password' => 'required|string|min:8',
```

### 4. **Uniqueness Check**
Validates that certain fields, such as `email` or `username`, are unique to avoid conflicts in the database.
```php
'email' => 'required|email|unique:users,email',
```

### 5. **Value Range Validation**
Ensures that numeric or enum-like values fall within acceptable ranges.
```php
'role_id' => 'required|in:1,2,3',
```

---

## Why These Validations Were Chosen

### 1. **Data Integrity**
Validations prevent invalid or incomplete data from being stored in the database.

### 2. **User Experience**
By catching errors early in the form submission process, users receive immediate feedback on their input.

### 3. **Security**
Protects the application from harmful inputs such as SQL injection, malicious file uploads, or other attack vectors.

### 4. **Business Logic**
Ensures the submitted data adheres to business rules, avoiding logical inconsistencies.

---

## Example Form Request Validation in Laravel

### Creating the Form Request
Use the Artisan command to create a Form Request:
```bash
php artisan make:request UserRequest
```

### Defining Validation Rules
In the `rules` method of the `UserRequest` class, define the validation logic:
```php
public function rules()
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'role_id' => 'required|string|in:1,2,3',
    ];
}
```

### Applying the Form Request
In the controller, use the Form Request as a type-hinted parameter:
```php
public function store(UserRequest $request)
{
    // Validation is automatically applied here
    $validatedData = $request->validated();

    // Process the validated data
    User::create($validatedData);
}
```
---

## **Conclusion**
Follow the steps above to set up and explore the application. If you encounter any issues, please send me an email to nasrulmuhammad676@gmail.com.