<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class FireEventTest
 * @package Technote\TransactionFireEvent\Tests
 */
class FireEventTest extends TestCase
{
    public function testNotInTransaction(): void
    {
        self::assertEmpty(Item::getCalledEvents());

        $item = new Item();
        $item->name = 'test';
        $item->save();

        self::assertEquals(['created', 'saved'], Item::getCalledEvents());

        Item::find($item->id)->delete();

        self::assertEquals(['created', 'saved', 'deleted'], Item::getCalledEvents());
    }

    public function testNestedTransaction(): void
    {
        self::assertEmpty(Item::getCalledEvents());

        DB::transaction(function () {
            $item = new Item();
            $item->name = 'test';
            $item->save();

            self::assertEquals(['created'], Item::getCalledEvents()); // この時点で saved が呼ばれないことを確認

            DB::transaction(function () {
                // トランザクション内のエラーでは外側のトランザクション終了時に saved が呼ばれないことを確認
                try {
                    DB::transaction(function () {
                        $item = new Item();
                        $item->name = 'test';
                        $item->save();
                        throw new Exception('test');
                    });
                } catch (Exception $e) {
                    self::assertSame('test', $e->getMessage());
                }
                self::assertEquals(['created', 'created'], Item::getCalledEvents());

                $item = new Item();
                $item->name = 'test';
                $item->save();

                self::assertEquals(['created', 'created', 'created'], Item::getCalledEvents()); // この時点で saved が呼ばれないことを確認
            });

            // リレーションデータの確認
            $tag = new Tag();
            $tag->name = 'tag';
            $tag->save();

            $item->tags()->sync([$tag->id]);
        });

        $called = Item::getCalled();
        self::assertCount(5, $called);
        self::assertSame('created', $called[0][0]);
        self::assertEmpty($called[0][1]);
        self::assertSame('created', $called[1][0]);
        self::assertEmpty($called[1][1]);
        self::assertSame('created', $called[2][0]);
        self::assertEmpty($called[2][1]);
        self::assertSame('saved', $called[3][0]);
        self::assertCount(1, $called[3][1]); // トランザクション終了後に呼ばれたため tags に値があることを確認
        self::assertSame('saved', $called[4][0]);
        self::assertEmpty($called[4][1]); // tags を sync していないので空
    }

    public function testMultipleTimesTransaction(): void
    {
        self::assertEmpty(Item::getCalledEvents());

        DB::transaction(function () {
            $item = new Item();
            $item->name = 'test';
            $item->save();

            self::assertEquals(['created'], Item::getCalledEvents()); // この時点で saved が呼ばれないことを確認

            // savepointも考慮
            DB::transaction(function () use ($item) {
                // リレーションデータの確認
                $tag = new Tag();
                $tag->name = 'tag';
                $tag->save();

                $item->tags()->sync([$tag->id]);

                self::assertEquals(['created'], Item::getCalledEvents());
            });
        });

        $called = Item::getCalled();
        self::assertCount(2, $called);
        self::assertSame('created', $called[0][0]);
        self::assertEmpty($called[0][1]);
        self::assertSame('saved', $called[1][0]);
        self::assertCount(1, $called[1][1]); // トランザクション終了後に呼ばれたため tags に値があることを確認

        // 一度 transaction を抜けた後に再度 transaction
        DB::transaction(function () {
            Item::first()->delete();
            self::assertEquals(['created', 'saved'], Item::getCalledEvents()); // この時点で deleted が呼ばれないことを確認
        });
        self::assertEquals(['created', 'saved', 'deleted'], Item::getCalledEvents());
    }

    public function testNotCallEventIfFailedInTransaction(): void
    {
        self::assertEmpty(Item::getCalledEvents());

        try {
            DB::transaction(function () {
                $item = new Item();
                $item->name = 'test';
                $item->save();

                self::assertEquals(['created'], Item::getCalledEvents());

                throw new Exception('test');
            });
        } catch (Exception $e) {
            self::assertSame('test', $e->getMessage());
        }

        self::assertEquals(['created'], Item::getCalledEvents()); // トランザクション内でエラーが発生した場合に saved が呼ばれないことを確認
    }
}
