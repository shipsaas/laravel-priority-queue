# ShipSaaS - Laravel Priority Queue Driver

A simple Priority Queue Driver for your Laravel Applications.

Laravel Priority Queue Driver uses the `database` driver.

## Supports
- Laravel 9 & 10
- PHP 8.1+

## Installation

Install the library:

```bash
composer require shipsaas/laravel-priority-queue
```

Export the migration and migrate

```bash
php artisan vendor:publish --tag=priority-queue-migrations
php artisan migrate
```

### First Time Setup

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

Note: Highly recommend you to use a different database connection (eg `mysql_secondary`) to avoid the worker processes ramming your 
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
SendEmail::dispatch($user, $emailContent)
    ->onConnection('database-priority');
```

## Contributors
- Seth Phat

## Contributions & Support the Project

Feel free to submit any PR, please follow PSR-1/PSR-12 coding conventions and unit test is a must.

If this package is helpful, please give it a ⭐️⭐️⭐️. Thank you!

## License
MIT License
