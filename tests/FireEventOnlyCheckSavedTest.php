<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * saved イベントのみを登録しているときに $model->refresh しなくても正しい値が取得できることを確認
 *
 * Class FireEventOnlyCheckSavedTest
 * @package Technote\TransactionFireEvent\Tests
 */
class FireEventOnlyCheckSavedTest extends TestCase
{
    public function testNotInTransaction(): void
    {
        self::assertEmpty(Item3::getCalledEvents());

        $item = new Item3();
        $item->name = 'test';
        $item->save();

        self::assertEquals(['saved'], Item3::getCalledEvents());

        Item3::find($item->id)->delete();

        self::assertEquals(['saved'], Item3::getCalledEvents());
    }

    public function testInTransaction(): void
    {
        self::assertEmpty(Item3::getCalledEvents());

        DB::transaction(function () {
            $item = new Item3();
            $item->name = 'test';
            $item->save();

            self::assertEmpty(Item3::getCalledEvents());

            // savepoint
            DB::transaction(function () use ($item) {
                $tag = new Tag();
                $tag->name = 'tag';
                $tag->save();

                $item->tags()->sync([$tag->id]);

                self::assertEmpty(Item3::getCalledEvents());
            });
        });

        $called = Item3::getCalled();
        self::assertCount(1, $called);
        self::assertSame('saved', $called[0][0]);
        self::assertCount(1, $called[0][1]); // refresh を呼ばなくても tags に値があることを確認
    }

    public function testNotCallEventIfFailedInTransaction(): void
    {
        self::assertEmpty(Item3::getCalledEvents());

        try {
            DB::transaction(function () {
                $item = new Item3();
                $item->name = 'test';
                $item->save();

                self::assertEmpty(Item3::getCalledEvents());

                throw new Exception('test');
            });
        } catch (Exception $e) {
            self::assertSame('test', $e->getMessage());
        }

        self::assertEmpty(Item3::getCalledEvents());
    }
}
