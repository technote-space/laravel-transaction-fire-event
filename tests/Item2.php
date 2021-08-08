<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Technote\TransactionFireEvent\Models\TransactionFireEventModel;

/**
 * Class Item2
 * @package Technote\TransactionFireEvent\Tests
 */
class Item2 extends Item
{
    protected $table = 'items';

    protected function getTargetEvents(): array
    {
        return [
            'created',
            'updated',
            'saved',
            'deleted',
        ];
    }
}
