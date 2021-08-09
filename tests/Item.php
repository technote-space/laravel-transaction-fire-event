<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Technote\TransactionFireEvent\Models\TransactionFireEventModel;

/**
 * Class Item
 * @package Technote\TransactionFireEvent\Tests
 */
class Item extends TransactionFireEventModel
{
    protected $fillable = [
        'name',
    ];

    protected static array $called = [];

    public static function boot(): void
    {
        parent::boot();
        static::setupEvents();
    }

    protected static function setupEvents(): void
    {
        self::created(function ($model) {
            $model->refresh();
            self::$called[] = ['created', $model->tags->pluck('id')];
        });
        self::updated(function ($model) {
            $model->refresh();
            self::$called[] = ['updated', $model->tags->pluck('id')];
        });
        self::saved(function ($model) {
            $model->refresh();
            self::$called[] = ['saved', $model->tags->pluck('id')];
        });
        self::deleted(function ($model) {
            $model->refresh();
            self::$called[] = ['deleted', $model->tags->pluck('id')];
        });
    }

    public static function getCalled(): array
    {
        return self::$called;
    }

    public static function getCalledEvents(): array
    {
        return array_column(self::$called, 0);
    }

    public static function clearCalled(): void
    {
        self::$called = [];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'item_tag', 'item_id');
    }
}
