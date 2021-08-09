<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

/**
 * Class Item2
 * @package Technote\TransactionFireEvent\Tests
 */
class Item2 extends Item
{
    protected $table = 'items';

    protected function getDelayTargetEvents(): array
    {
        return [
            'created',
            'updated',
            'saved',
            'deleted',
        ];
    }
}
