<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Models;

use Illuminate\Database\Eloquent\Model;
use Technote\TransactionFireEvent\Services\TransactionService;

abstract class TransactionFireEventModel extends Model
{
    private TransactionService $transactionService;

    protected function getTargetEvents(): array
    {
        return [
            'created',
            'updated',
            'saved',
            'deleted',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->transactionService = resolve(TransactionService::class);
    }

    protected function fireModelEvent($event, $halt = true)
    {
        if (!in_array($event, $this->getTargetEvents(), true)) {
            return parent::fireModelEvent($event, $halt);
        }

        return $this->transactionService->call(fn() => parent::fireModelEvent($event, $halt));
    }
}
