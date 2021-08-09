<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 対象イベントに created を追加した場合の動作を確認
 *
 * Class FireEventWithCustomTargetEventTest
 * @package Technote\TransactionFireEvent\Tests
 */
class FireEventWithCustomTargetEventTest extends TestCase
{
    public function testNotInTransaction(): void
    {
        self::assertEmpty(Item2::getCalledEvents());

        $item = new Item2();
        $item->name = 'test';
        $item->save();

        self::assertEquals(['created', 'saved'], Item2::getCalledEvents());

        Item2::find($item->id)->delete();

        self::assertEquals(['created', 'saved', 'deleted'], Item2::getCalledEvents());
    }

    public function testInTransaction(): void
    {
        self::assertEmpty(Item2::getCalledEvents());

        DB::transaction(function () {
            $item = new Item2();
            $item->name = 'test';
            $item->save();

            self::assertEmpty(Item2::getCalledEvents()); // created も発火が保留されていることを確認

            // savepoint
            DB::transaction(function () use ($item) {
                $tag = new Tag();
                $tag->name = 'tag';
                $tag->save();

                $item->tags()->sync([$tag->id]);

                self::assertEmpty(Item2::getCalledEvents());
            });
        });

        $called = Item2::getCalled();
        self::assertCount(2, $called);
        self::assertSame('created', $called[0][0]);
        self::assertCount(1, $called[0][1]); // トランザクション後に呼ばれたため tags に値があることを確認
        self::assertSame('saved', $called[1][0]);
        self::assertCount(1, $called[1][1]);

        DB::transaction(function () {
            Item2::first()->delete();
            self::assertEquals(['created', 'saved'], Item2::getCalledEvents());
        });
        self::assertEquals(['created', 'saved', 'deleted'], Item2::getCalledEvents());
    }

    public function testNotCallEventIfFailedInTransaction(): void
    {
        self::assertEmpty(Item2::getCalledEvents());

        try {
            DB::transaction(function () {
                $item = new Item2();
                $item->name = 'test';
                $item->save();
                self::assertEmpty(Item2::getCalledEvents());

                throw new Exception();
            });
        } catch (Exception $e) {
            //
        }

        self::assertEmpty(Item2::getCalledEvents());
    }
}
