# Application Setup Guide

This document outlines the initial steps taken to set up the application development environment and create the basic structure.

## Initial Setup

1.  **Step 1: Install PHP and Composer**
    * PHP Version: 8.4.6 (Larabit)
    * Composer Version: 2.6.8

2.  **Step 2: Create Laravel Project**
    * Command Run:
        ```bash
        composer create-project laravel/laravel custom-job-runner
        ```
    * Edited `php.ini`.
    * Enabled SSL.

3.  **Step 3: Create `run-job.php` in your project root**
    * The script will:
        * Take CLI arguments (`php run-job.php method`).
        * Check against allowed jobs from config.
        * Run the method on the class.
        * Log status: running, success, failure.
        * Retry on failure (with delay and limit).
        * Log exceptions separately.

## Further Steps (from previous notes)

4.  **Step 8: Created Controller**
    * A controller was created for handling application logic.

5.  **Step 9: Add Routing on `web.php`**
    * Routing was configured in the `web.php` file to map URLs to specific controller actions.
    * This allows accessing the application on `localhost`.

6.  **Step 10: Generated the key**
    * The application key was generated using the following command:
        ```bash
        php artisan key:generate
        ```

7.  **Step 11: We switch database to file**
    * The application's database configuration was switched to use a file-based database.

8.  **Step 12: Test the endpoint**
    * The application's endpoints were tested.
    * The development server was started using the command:
        ```bash
        php artisan serve
        ```

9.  **Step 13: Create the Dashboard Controller**
    * A controller specifically for the application's dashboard was created.
    * Routing for the dashboard was added with the following configuration:
        ```php
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index']);
        ```
        * This creates a route that responds to GET requests on the `/dashboard` URL and directs them to the `index` method of the `DashboardController`.

## Usage

To access the application, navigate to `localhost` in your web browser (after running `php artisan serve`).

![Cool Cat](images/Screenshot%202025-04-14%20172654.png)

To access the dashboard, navigate to `localhost` dashboard its a default page in your web browser (after running `php artisan serve`).
You should see this image:

![Cool Cat](images/Screenshot%202025-04-14%20171940.png)

To run the `run-job.php` script from the command line:
```bash
php run-job.php App\\Jobs\\TestJob run "This async !"


# 🛠️ Background Job Dispatcher – `runBackgroundJob()`

This utility provides a secure, dynamic way to dispatch Laravel jobs from anywhere — including user input, APIs, or internal triggers — while maintaining safety and control.

---

## 🚀 Features

 ✅ Dynamically dispatch background jobs
 🔐 Only runs allowed job classes and methods (whitelisted)
 🔁 Supports retry attempts and delay between retries
 📦 Clean JSON-compatible input (perfect for APIs or logs)
 🧾 Logs job execution, retries, delays, and results

---

## 🧩 Configuration

Create or edit `config/background_jobs.php`:

```php
return [
    'allowed' => [
        App\Jobs\TestJob::class => ['run'],
    ],

    // How many times to retry a failed job
    'max_attempts' => 3,

    // Delay between retries in seconds
    'retry_delay' => 5,
];


## Logs Image :

![Cool Cat](images/Screenshot%202025-04-14%20233308.png)
