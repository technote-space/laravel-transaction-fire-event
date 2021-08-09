<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

/**
 * Class Item3
 * @package Technote\TransactionFireEvent\Tests
 */
class Item3 extends Item
{
    protected $table = 'items';

    protected static function setupEvents(): void
    {
        self::saved(function ($model) {
            self::$called[] = ['saved', $model->tags->pluck('id')];
        });
    }
}
