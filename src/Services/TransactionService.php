<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Service;

use Illuminate\Support\Facades\DB;

class TransactionService
{
    private array $callbacks = [];

    public function transactionCommitted(): void
    {
        if (DB::transactionLevel() === 0) {
            $callbacks = $this->callbacks;
            $this->callbacks = [];
            foreach ($callbacks as $callback) {
                $callback();
            }
        }
    }

    /**
     * @param \Closure $callback
     * @return mixed|void
     */
    public function call(\Closure $callback)
    {
        if (DB::transactionLevel() === 0) {
            return $callback();
        }

        $this->callbacks[] = $callback;
    }
}
