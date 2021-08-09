<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Tag
 * @package Technote\TransactionFireEvent\Tests
 */
class Tag extends Model
{
    protected $fillable = [
        'name',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class);
    }
}
