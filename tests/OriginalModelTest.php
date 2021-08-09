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

    public function testInTransaction(): void
    {
        self::assertEmpty(OriginalItem::getCalledEvents());

        DB::transaction(function () {
            $item = new OriginalItem();
            $item->name = 'test';
            $item->save();

            self::assertEquals(['created', 'saved'], OriginalItem::getCalledEvents()); // この時点で saved が呼ばれることを確認

            // savepoint
            DB::transaction(function () use ($item) {
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
                self::assertEmpty(OriginalItem::getCalledEvents());

                throw new Exception();
            });
        } catch (Exception $e) {
            //
        }

        self::assertEquals(['created', 'saved'], OriginalItem::getCalledEvents()); // トランザクション内でエラーが発生しても saved が呼ばれてしまうことを確認
    }
}
