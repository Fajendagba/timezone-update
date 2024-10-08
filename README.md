# Timezone Update

## Overview
This Timezone Update is built using Laravel, providing a robust API for updating user details.

## Installation

1. **Clone the repository:**
    ```sh
    git clone https://github.com/Fajendagba/timezone-update.git
    cd timezone-update
    ```

2. **Install dependencies:**
    ```sh
    composer install
    ```

3. **Create a `.env` file:**
    ```sh
    cp .env.example .env
    ```

4. **Generate application key:**
    ```sh
    php artisan key:generate
    ```

5. **Set up your database credentials in the `.env` file:**
    ```dotenv
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

6. **Run database migrations:**
    ```sh
    php artisan migrate
    ```

7. **Start the development server:**
    ```sh
    php artisan serve
    ```
    
8. **Seed data:**
    ```sh
    php artisan db:seed
    ```

9. **Update users data command:**
    Run this command to update user's firstname, lastname, and timezone to new random ones
    ```sh
    php artisan users:update-user-data
    ```

## Testing
To run the tests, use the following command:
```sh
php artisan test
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
