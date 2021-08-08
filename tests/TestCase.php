<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Technote\TransactionFireEvent\Providers\TransactionFireEventServiceProvider;

/**
 * Class TestCase
 * @package Technote\TransactionFireEvent\Tests
 */
class TestCase extends BaseTestCase
{
    /**
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'test');
        $app['config']->set('database.connections.test', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    /**
     * @param Application $app
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getPackageProviders($app): array
    {
        return [
            TransactionFireEventServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 128)->nullable(false);
            $table->timestamps();
        });
        Schema::create('tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 64)->nullable(false);
            $table->timestamps();
        });
        Schema::create('item_tag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tag_id');

            $table->foreign('item_id')->on('items')->references('id');
            $table->foreign('tag_id')->on('tags')->references('id');
        });

        Item::clearCalled();
        Item2::clearCalled();
    }
}
