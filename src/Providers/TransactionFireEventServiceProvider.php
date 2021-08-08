<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Support\Facades\Event;
use Technote\TransactionFireEvent\Services\TransactionService;

class TransactionFireEventServiceProvider extends ServiceProvider
{
    protected bool $defer = true;

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(TransactionService::class, function () {
            return new TransactionService();
        });
    }

    public function provides(): array
    {
        return [TransactionService::class];
    }

    public function boot(): void
    {
        Event::listen(TransactionCommitted::class, function () {
            $this->app->make(TransactionService::class)->transactionCommitted();
        });
    }
}
