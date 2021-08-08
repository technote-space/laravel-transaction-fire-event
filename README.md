# Laravel Event Control Library

[![CI Status](https://github.com/technote-space/laravel-transaction-fire-event/workflows/CI/badge.svg)](https://github.com/technote-space/laravel-transaction-fire-event/actions)
[![codecov](https://codecov.io/gh/technote-space/laravel-transaction-fire-event/branch/master/graph/badge.svg)](https://codecov.io/gh/technote-space/laravel-transaction-fire-event)
[![CodeFactor](https://www.codefactor.io/repository/github/technote-space/laravel-transaction-fire-event/badge)](https://www.codefactor.io/repository/github/technote-space/laravel-transaction-fire-event)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://github.com/technote-space/laravel-transaction-fire-event/blob/master/LICENSE)
[![PHP: >=7.4](https://img.shields.io/badge/PHP-%3E%3D7.4-orange.svg)](http://php.net/)

*Read this in other languages: [English](README.md), [日本語](README.ja.md).*

Controlling events that occur in a transaction.

[Packagist](https://packagist.org/packages/technote/laravel-transaction-fire-event)

## Table of Contents
<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
<details>
<summary>Details</summary>

- [Install](#install)
- [Usage](#usage)
  - [Change the event to hold fire](#change-the-event-to-hold-fire)
- [Author](#author)

</details>
<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Install
```
composer require technote/laravel-transaction-fire-event
```

## Usage
1. In the model where you want to control the firing of events, extend it with `TransactionFireEventModel` instead of `Model`.

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

2. If used within a transaction, `saved` and `deleted` events will be held until the end of the transaction.

   ```php
   DB::transaction(function () {
       $item = new Item();
       $item->name = 'test';
       $item->save();
       // The `saved` event will not be fired here yet.
   
       $item->tags()->sync([1, 2, 3]);
   }

   // The `saved` event is called at the end of the transaction,
   // so you can get the synchronized tags with `$model->tags()->sync`.
   ```

### Change the event to hold fire
The target events are `saved` and `deleted` by default.    
To change it, override `getTargetEvents`.

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
