# ShipSaaS - Laravel Priority Queue Driver

[![codecov](https://codecov.io/gh/shipsaas/laravel-priority-queue/branch/main/graph/badge.svg?token=V3HOOR12HA)](https://codecov.io/gh/shipsaas/laravel-priority-queue)
[![Build & Test](https://github.com/shipsaas/laravel-priority-queue/actions/workflows/build.yml/badge.svg)](https://github.com/shipsaas/laravel-priority-queue/actions/workflows/build.yml)
[![Build & Test (Laravel 9, 10)](https://github.com/shipsaas/laravel-priority-queue/actions/workflows/build-laravel.yml/badge.svg)](https://github.com/shipsaas/laravel-priority-queue/actions/workflows/build-laravel.yml)

A simple Priority Queue Driver for your Laravel Applications.

Laravel Priority Queue Driver uses the `database` driver.

## Supports
- Laravel 10 (compatible by default)
- Laravel 9 (supports until Laravel drops the bug fixes at [August 8th, 2023](https://laravel.com/docs/10.x/releases))
- PHP 8.1+

## Architecture

![Seth Phat - Laravel Priority Queue](https://i.imgur.com/H8OEMhQ.png)

## Why `database`?

- Easy and simple to implement.
- Utilize the `ORDER BY` and `INDEX` for fast queue msgs pop process.
- Super visibility (you can view the jobs and their data in DB).
- Super flexibility (you can change the weight directly in DB to unblock important msgs).

## Installation

Install the library:

```bash
composer require shipsaas/laravel-priority-queue
```

Export and run the migration:

```bash
php artisan vendor:publish --tag=priority-queue-migrations
php artisan migrate
```

### One-Time Setup

Open `config/queue.php` and add this into the `connections` array:

```php
    'database-priority' => [
        'driver' => 'database-priority',
        'connection' => 'mysql',
        'table' => 'priority_jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
```

### Note

We highly recommend you to use a different database connection (eg `mysql_secondary`) to avoid the worker processes ramming your 
primary database.

## Usage

### The Job Weight

The default job weight is **500**.

You can define a hardcoded weight for your job by using the `$jobWeight` property.

```php
class SendEmail implements ShouldQueue
{
    public int $jobWeight = 500;
}
```

Or if you want to calculate the job weight on runtime, you can use the `UseJobPrioritization` trait:

```php
use ShipSaasPriorityQueue\Traits\UseJobPrioritization;

class SendEmail implements ShouldQueue
{
    use UseJobPrioritization;
    
    public function getJobWeight() : int
    {
        return $this->user->isPro()
            ? 1000
            : 500;
    }
}
```

### Dispatch the Queue

You can use the normal Dispatcher or Queue Facade,... to dispatch the Queue Msgs:

```php
// use Dispatcher
SendEmail::dispatch($user, $emailContent)
    ->onConnection('database-priority');

// use Queue Facade
use Illuminate\Support\Facades\Queue;

Queue::connection('database-priority')
    ->push(new SendEmail($user, $emailContent));
```

## Run The Queue Worker

Nothing different from the Laravel's Doc.

```bash
php artisan queue:work database-priority
php artisan queue:work database-priority --queue=custom
```

## Contributors
- Seth Phat

## Contributions & Support the Project

Feel free to submit any PR, please follow PSR-1/PSR-12 coding conventions and unit test is a must.

If this package is helpful, please give it a ⭐️⭐️⭐️. Thank you!

## License
MIT License
