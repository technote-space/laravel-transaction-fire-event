<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Technote\TransactionFireEvent\Models\TransactionFireEventModel;

/**
 * Class Tag
 * @package Technote\TransactionFireEvent\Tests
 */
class Tag extends TransactionFireEventModel
{
    protected $fillable = [
        'name',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }
}
