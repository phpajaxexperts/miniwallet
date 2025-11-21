# Mini Wallet Application

A secure, scalable, and real-time mini wallet application built with Laravel 12 and Vue.js 3.

## Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Node.js and npm
-   MySQL or MariaDB

## Installation

1.  **Clone the repository**
    ```bash
    git clone <repository-url>
    cd miniwallet
    ```

2.  **Install Backend Dependencies**
    ```bash
    composer install
    ```

3.  **Install Frontend Dependencies**
    ```bash
    npm install
    ```

4.  **Environment Setup**
    Copy the example environment file and configure it:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Database Configuration**
    Update the `.env` file with your database credentials:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=miniwallet
    DB_USERNAME=root
    DB_PASSWORD=
    ```

6.  **Real-time Configuration (Pusher)**
    Update the `.env` file with your Pusher credentials (or use Reverb):
    ```env
    BROADCAST_CONNECTION=pusher
    PUSHER_APP_ID=your_app_id
    PUSHER_APP_KEY=your_app_key
    PUSHER_APP_SECRET=your_app_secret
    PUSHER_APP_CLUSTER=your_cluster
    
    VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
    VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
    ```

7.  **Run Migrations**
    Create the database tables:
    ```bash
    php artisan migrate
    ```

## Running the Application

1.  **Start the Backend Server**
    ```bash
    php artisan serve
    ```

2.  **Start the Frontend Development Server**
    ```bash
    npm run dev
    ```

3.  **Access the Application**
    Open your browser and navigate to `http://localhost:8000`. Register two different users in separate browsers (or incognito window) to test the transfer functionality.

## Running Tests

The application includes a comprehensive test suite covering authentication, transactions, and concurrency.

```bash
php artisan test
```

## Security & Scalability

-   **Atomic Transactions**: Uses `DB::transaction` to ensure data integrity.
-   **Pessimistic Locking**: Uses `lockForUpdate()` to handle high concurrency and prevent double-spending.
-   **Validation**: Strict server-side validation for all inputs.
-   **Optimized Queries**: Eager loading is used to prevent N+1 query issues.
