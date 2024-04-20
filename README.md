# ShipSaaS - Laravel Priority Queue Driver

[![Latest Version](http://poser.pugx.org/shipsaas/laravel-priority-queue/v)](https://packagist.org/packages/shipsaas/laravel-priority-queue)
[![Total Downloads](http://poser.pugx.org/shipsaas/laravel-priority-queue/downloads)](https://packagist.org/packages/shipsaas/laravel-priority-queue)
[![codecov](https://codecov.io/gh/shipsaas/laravel-priority-queue/branch/main/graph/badge.svg?token=V3HOOR12HA)](https://codecov.io/gh/shipsaas/laravel-priority-queue)
[![Build & Test](https://github.com/shipsaas/laravel-priority-queue/actions/workflows/build.yml/badge.svg)](https://github.com/shipsaas/laravel-priority-queue/actions/workflows/build.yml)
[![Build & Test (Laravel 10, 11)](https://github.com/shipsaas/laravel-priority-queue/actions/workflows/build-laravel.yml/badge.svg)](https://github.com/shipsaas/laravel-priority-queue/actions/workflows/build-laravel.yml)

A simple Priority Queue Driver for your Laravel Applications to serve your priority messages and 
makes users happy 🔋.

With the famous Repository Pattern of Laravel, Priority Queue Driver is easily get injected into
Laravel's Lifecycle without any hassle/hurdle.

We can use built-in artisan command `php artisan queue:work` 😎.

## Supports
- Laravel 11 
- Laravel 10 (supports until Laravel drops the bug fixes at [August 6th, 2024](https://laravel.com/docs/11.x/releases))
- PHP 8.2 & 8.3
- Any database that Laravel supported.

## Architecture Diagram

![Seth Phat - Laravel Priority Queue](https://i.imgur.com/H8OEMhQ.png)

### Why Priority Queue Driver use Database?

- Everybody knows Database (MySQL, PgSQL, etc) 👀.
- Easy and simple to implement ❤️.
- Utilize the `ORDER BY` and `INDEX` for fast queue msgs pop process. Faster than any other stuff 🔥.
- Highest visibility (you can view the jobs and their data in DB) ⭐️.
- Highest flexibility (you can change the weight directly in DB to unblock important msgs) 💰.
- No extra tool involved. Just Laravel 🥰.

## Install Laravel Priority Queue

```bash
composer require shipsaas/laravel-priority-queue
```

### One-Time Setup

Export and run the migration (one-time):

```bash
# if you don't need to adjust the migration, don't have to export it
php artisan vendor:publish --tag=priority-queue-migrations
php artisan migrate
```

Open `config/queue.php` and add this into the `connections` array:

```php
'connections' => [
    // ... a lot of connections above
    // then our lovely guy here
    'database-priority' => [
        'driver' => 'database-priority',
        'connection' => 'mysql',
        'table' => 'priority_jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

## Scale/Reliability Consideration

It is recommended using a different database connection (eg `mysql_secondary`) to avoid the worker processes ramming your 
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
        return $this->user->isUsingProPlan()
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

Nothing different from the Laravel Documentation 😎. Just need to include the `database-priority` driver.

```bash
php artisan queue:work database-priority

# Extra win, priority on topic
php artisan queue:work database-priority --queue=custom
```

## Testing

Run `composer test` 😆

Available Tests:

- Unit Testing
- Integration Testing against MySQL and `queue:work` command

## Contributors
- Seth Phat

## Contributions & Support the Project

Feel free to submit any PR, please follow PSR-1/PSR-12 coding conventions and unit test is a must.

If this package is helpful, please give it a ⭐️⭐️⭐️. Thank you!

## License
MIT License
