<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Models;

use Illuminate\Database\Eloquent\Model;
use Technote\TransactionFireEvent\Services\TransactionService;

/**
 * @mixin Model
 */
trait DelayFireEvent
{
    protected function getDelayTargetEvents(): array
    {
        return [
            'saved',
            'deleted',
        ];
    }

    protected function fireModelEvent($event, $halt = true)
    {
        if (!in_array($event, $this->getDelayTargetEvents(), true)) {
            return parent::fireModelEvent($event, $halt);
        }

        return resolve(TransactionService::class)->call(fn() => parent::fireModelEvent($event, $halt));
    }
}
