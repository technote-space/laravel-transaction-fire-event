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

    public function testNestedTransaction(): void
    {
        self::assertEmpty(Item2::getCalledEvents());

        DB::transaction(function () {
            $item = new Item2();
            $item->name = 'test';
            $item->save();

            self::assertEmpty(Item2::getCalledEvents()); // created も発火が保留されていることを確認

            DB::transaction(function () {
                // トランザクション内のエラーでは外側のトランザクション終了時に saved が呼ばれないことを確認
                try {
                    DB::transaction(function () {
                        $item = new Item2();
                        $item->name = 'test';
                        $item->save();
                        throw new Exception('test');
                    });
                } catch (Exception $e) {
                    self::assertSame('test', $e->getMessage());
                }
                self::assertEmpty(Item2::getCalledEvents());

                $item = new Item2();
                $item->name = 'test';
                $item->save();

                self::assertEmpty(Item2::getCalledEvents());
            });

            // リレーションデータの確認
            $tag = new Tag();
            $tag->name = 'tag';
            $tag->save();

            $item->tags()->sync([$tag->id]);
        });

        $called = Item2::getCalled();
        self::assertCount(4, $called);
        self::assertSame('created', $called[0][0]);
        self::assertCount(1, $called[0][1]); // トランザクション終了後に呼ばれたため created にも tags に値があることを確認
        self::assertSame('saved', $called[1][0]);
        self::assertCount(1, $called[1][1]);
        self::assertSame('created', $called[2][0]);
        self::assertEmpty($called[2][1]); // tags を sync していないので空
        self::assertSame('saved', $called[3][0]);
        self::assertEmpty($called[3][1]);
    }

    public function testMultipleTimesTransaction(): void
    {
        self::assertEmpty(Item2::getCalledEvents());

        DB::transaction(function () {
            $item = new Item2();
            $item->name = 'test';
            $item->save();

            self::assertEmpty(Item2::getCalledEvents()); // created も発火が保留されていることを確認

            // savepointも考慮
            DB::transaction(function () use ($item) {
                // リレーションデータの確認
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
        self::assertCount(1, $called[0][1]); // トランザクション終了後に呼ばれたため created にも tags に値があることを確認
        self::assertSame('saved', $called[1][0]);
        self::assertCount(1, $called[1][1]);

        // 一度 transaction を抜けた後に再度 transaction
        DB::transaction(function () {
            Item2::first()->delete();
            self::assertEquals(['created', 'saved'], Item2::getCalledEvents()); // この時点で deleted が呼ばれないことを確認
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

                throw new Exception('test');
            });
        } catch (Exception $e) {
            self::assertSame('test', $e->getMessage());
        }

        self::assertEmpty(Item2::getCalledEvents());
    }
}
