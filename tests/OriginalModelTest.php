<?php
declare(strict_types=1);

namespace Technote\TransactionFireEvent\Tests;

use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 元の Model の動作テスト
 *
 * Class OriginalModelTest
 * @package Technote\TransactionFireEvent\Tests
 */
class OriginalModelTest extends TestCase
{
    public function testNotInTransaction(): void
    {
        self::assertEmpty(OriginalItem::getCalledEvents());

        $item = new OriginalItem();
        $item->name = 'test';
        $item->save();

        self::assertEquals(['created', 'saved'], OriginalItem::getCalledEvents());

        OriginalItem::find($item->id)->delete();

        self::assertEquals(['created', 'saved', 'deleted'], OriginalItem::getCalledEvents());
    }

    public function testNestedTransaction(): void
    {
        self::assertEmpty(OriginalItem::getCalledEvents());

        DB::transaction(function () {
            $item = new OriginalItem();
            $item->name = 'test';
            $item->save();

            self::assertEquals(['created', 'saved'], OriginalItem::getCalledEvents()); // この時点で saved が呼ばれることを確認

            DB::transaction(function () {
                try {
                    DB::transaction(function () {
                        $item = new OriginalItem();
                        $item->name = 'test';
                        $item->save();
                        throw new Exception('test');
                    });
                } catch (Exception $e) {
                    self::assertSame('test', $e->getMessage());
                }
                self::assertEquals(['created', 'saved', 'created', 'saved'], OriginalItem::getCalledEvents()); // トランザクション内でのエラー時もイベントが呼ばれることを確認

                $item = new OriginalItem();
                $item->name = 'test';
                $item->save();

                self::assertEquals(['created', 'saved', 'created', 'saved', 'created', 'saved'], OriginalItem::getCalledEvents());
            });

            // リレーションデータの確認
            $tag = new Tag();
            $tag->name = 'tag';
            $tag->save();

            $item->tags()->sync([$tag->id]);
        });

        $called = OriginalItem::getCalled();
        self::assertCount(6, $called);
        self::assertSame('created', $called[0][0]);
        self::assertEmpty($called[0][1]);
        self::assertSame('saved', $called[1][0]);
        self::assertEmpty($called[1][1]); // sync 前に呼ばれたため tags は空であることを確認
        self::assertSame('created', $called[2][0]);
        self::assertEmpty($called[2][1]);
        self::assertSame('saved', $called[3][0]);
        self::assertEmpty($called[3][1]);
    }

    public function testMultipleTimesTransaction(): void
    {
        self::assertEmpty(OriginalItem::getCalledEvents());

        DB::transaction(function () {
            $item = new OriginalItem();
            $item->name = 'test';
            $item->save();

            self::assertEquals(['created', 'saved'], OriginalItem::getCalledEvents()); // この時点で saved が呼ばれることを確認

            // savepointも考慮
            DB::transaction(function () use ($item) {
                // リレーションデータの確認
                $tag = new Tag();
                $tag->name = 'tag';
                $tag->save();

                $item->tags()->sync([$tag->id]);

                self::assertEquals(['created', 'saved'], OriginalItem::getCalledEvents());
            });
        });

        $called = OriginalItem::getCalled();
        self::assertCount(2, $called);
        self::assertSame('created', $called[0][0]);
        self::assertEmpty($called[0][1]);
        self::assertSame('saved', $called[1][0]);
        self::assertEmpty($called[1][1]); // sync 前に呼ばれたため tags は空であることを確認

        // 一度 transaction を抜けた後に再度 transaction
        DB::transaction(function () {
            OriginalItem::first()->delete();
            self::assertEquals(['created', 'saved', 'deleted'], OriginalItem::getCalledEvents()); // この時点で deleted が呼ばれることを確認
        });
        self::assertEquals(['created', 'saved', 'deleted'], OriginalItem::getCalledEvents());
    }

    public function testNotCallEventIfFailedInTransaction(): void
    {
        self::assertEmpty(OriginalItem::getCalledEvents());

        try {
            DB::transaction(function () {
                $item = new OriginalItem();
                $item->name = 'test';
                $item->save();

                self::assertEquals(['created', 'saved'], OriginalItem::getCalledEvents());

                throw new Exception('test');
            });
        } catch (Exception $e) {
            self::assertSame('test', $e->getMessage());
        }

        self::assertEquals(['created', 'saved'], OriginalItem::getCalledEvents()); // トランザクション内でエラーが発生しても saved が呼ばれてしまうことを確認
    }
}
