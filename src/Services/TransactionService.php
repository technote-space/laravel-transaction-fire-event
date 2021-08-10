<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Services;

use Illuminate\Support\Facades\DB;

class TransactionService
{
    private array $callbacks = [0 => []];

    public function transactionBeginning(): void
    {
        $this->callbacks[DB::transactionLevel()] = [];
    }

    public function transactionCommitted(): void
    {
        $level = DB::transactionLevel();
        array_push($this->callbacks[$level], ...($this->callbacks[$level + 1] ?? []));

        if ($level === 0) {
            $callbacks = $this->callbacks;
            $this->callbacks = [0 => []];
            foreach ($callbacks[0] as $callback) {
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
        $level = DB::transactionLevel();
        if ($level === 0) {
            return $callback();
        }

        $this->callbacks[$level][] = $callback;
    }
}
