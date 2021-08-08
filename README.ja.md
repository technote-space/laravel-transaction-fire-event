# Laravel Event Control Library

[![CI Status](https://github.com/technote-space/laravel-transaction-fire-event/workflows/CI/badge.svg)](https://github.com/technote-space/laravel-transaction-fire-event/actions)
[![codecov](https://codecov.io/gh/technote-space/laravel-transaction-fire-event/branch/master/graph/badge.svg)](https://codecov.io/gh/technote-space/laravel-transaction-fire-event)
[![CodeFactor](https://www.codefactor.io/repository/github/technote-space/laravel-transaction-fire-event/badge)](https://www.codefactor.io/repository/github/technote-space/laravel-transaction-fire-event)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://github.com/technote-space/laravel-transaction-fire-event/blob/master/LICENSE)
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
- [Routes](#routes)
- [詳細](#%E8%A9%B3%E7%B4%B0)
  - [バリデーション](#%E3%83%90%E3%83%AA%E3%83%87%E3%83%BC%E3%82%B7%E3%83%A7%E3%83%B3)
  - [モデル名](#%E3%83%A2%E3%83%87%E3%83%AB%E5%90%8D)
  - [Config](#config)
- [検索機能](#%E6%A4%9C%E7%B4%A2%E6%A9%9F%E8%83%BD)
  - [Laravel Search Helper](#laravel-search-helper)
- [Author](#author)

</details>
<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## インストール
```
composer require technote/laravel-transaction-fire-event
```

## 使用方法
1. イベントの発行を制御したいモデルにて `Model` の代わりに `TransactionFireEventModel` で拡張

   ```php
   <?php
   namespace App\Models;
   
   use Technote\TransactionFireEvent\Models\TransactionFireEventModel;
   
   class Item extends TransactionFireEventModel
   {
       public static function boot()
       {
           parent::boot();
   
           self::saved(function ($model) {
               //
           });
       }

       // example relation
       public function tags(): BelongsToMany
       {
           return $this->belongsToMany(Tag::class);
       }
   }
   ```

2. トランザクション内で使用した場合、トランザクション終了時まで `saved`, `deleted` イベントの発行が保留される

   ```php
   DB::transaction(function () {
       $item = new Item();
       $item->name = 'test';
       $item->save();
       // saved イベントはまだ発行されない
   
       $item->tags()->sync([1, 2, 3]);
   }

   // トランザクション終了時に saved イベントが呼ばれるため
   // $model->tags()->sync で同期した tags が取得できる
   ```

### 発行を保留するイベントを変更
対象のイベントはデフォルトで `saved`, `deleted` です。  
変更するには `getTargetEvents` をオーバーライドしてください。

```php
protected function getTargetEvents(): array
{
    return [
        'created',
        'updated',
        'saved',
        'deleted',
    ];
}
```

## Author
[GitHub (Technote)](https://github.com/technote-space)  
[Blog](https://technote.space)
