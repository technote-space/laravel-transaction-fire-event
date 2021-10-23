# Laravel Event Control Library

[![CI Status](https://github.com/technote-space/laravel-transaction-fire-event/workflows/CI/badge.svg)](https://github.com/technote-space/laravel-transaction-fire-event/actions)
[![codecov](https://codecov.io/gh/technote-space/laravel-transaction-fire-event/branch/main/graph/badge.svg?token=3yIzMhmFBS)](https://codecov.io/gh/technote-space/laravel-transaction-fire-event)
[![CodeFactor](https://www.codefactor.io/repository/github/technote-space/laravel-transaction-fire-event/badge)](https://www.codefactor.io/repository/github/technote-space/laravel-transaction-fire-event)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://github.com/technote-space/laravel-transaction-fire-event/blob/main/LICENSE)
[![PHP: >=7.4](https://img.shields.io/badge/PHP-%3E%3D7.4-orange.svg)](http://php.net/)

*Read this in other languages: [English](README.md), [日本語](README.ja.md).*

トランザクション内で発生したイベントを制御するLaravelライブラリ

[Packagist](https://packagist.org/packages/technote/laravel-transaction-fire-event)

## Table of Contents
<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
<details>
<summary>Details</summary>

- [インストール](#%E3%82%A4%E3%83%B3%E3%82%B9%E3%83%88%E3%83%BC%E3%83%AB)
- [使用方法](#%E4%BD%BF%E7%94%A8%E6%96%B9%E6%B3%95)
  - [発火を保留するイベントを変更](#%E7%99%BA%E7%81%AB%E3%82%92%E4%BF%9D%E7%95%99%E3%81%99%E3%82%8B%E3%82%A4%E3%83%99%E3%83%B3%E3%83%88%E3%82%92%E5%A4%89%E6%9B%B4)
- [動機](#%E5%8B%95%E6%A9%9F)
- [Author](#author)

</details>
<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## インストール
```
composer require technote/laravel-transaction-fire-event
```

## 使用方法
1. イベントの発火を制御したいモデルで `DelayFireEvent` トレイトを使用

   ```php
   <?php
   namespace App\Models;
   
   use Illuminate\Database\Eloquent\Model;
   use Technote\TransactionFireEvent\Models\DelayFireEvent;
   
   class Item extends Model
   {
       use DelayFireEvent;
   
       public static function boot()
       {
           parent::boot();
   
           self::saved(function ($model) {
               //
           });
       }

       // relation example
       public function tags(): BelongsToMany
       {
           return $this->belongsToMany(Tag::class);
       }
   }
   ```

2. トランザクション内で使用した場合、トランザクション終了時まで `saved`, `deleted` イベントの発火が保留される

   ```php
   DB::transaction(function () {
       $item = new Item();
       $item->name = 'test';
       $item->save();
       // saved イベントはまだ発火されない
   
       $item->tags()->sync([1, 2, 3]);
   }

   // トランザクション終了時に saved イベントが呼ばれるため
   // $model->tags()->sync で同期した tags が取得できる
   ```

### 発火を保留するイベントを変更
対象のイベントはデフォルトで `saved`, `deleted` です。  
変更するには `getDelayTargetEvents` をオーバーライドしてください。

```php
protected function getDelayTargetEvents(): array
{
    return [
        'created',
        'updated',
        'saved',
        'deleted',
    ];
}
```

## 動機

saved イベントで他のサービスとデータを連携する際に、以下のような実装の場合にうまく行かなかったため。

```php
function save(Article $article, $entity) {
  $article->title = $entity->getTitle();
  // ...
  $article->save(); // saved イベント発火（まだ tags は同期されていない）
  
  $article->tags()->sync($entity->getTags());
}

function create($entity) {
  save(new Article(), $entity);
}

function update($entity) {
  save(Article::findOrFail($entity->getId()), $entity);
}
```

https://github.com/fntneves/laravel-transactional-events

がやりたいことに近かったが、 Model で trait を use するだけでやりたかった。

## Author
[GitHub (Technote)](https://github.com/technote-space)  
[Blog](https://technote.space)
