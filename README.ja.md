# Laravel CRUD Helper

[![CI Status](https://github.com/technote-space/laravel-crud-helper/workflows/CI/badge.svg)](https://github.com/technote-space/laravel-crud-helper/actions)
[![codecov](https://codecov.io/gh/technote-space/laravel-crud-helper/branch/master/graph/badge.svg)](https://codecov.io/gh/technote-space/laravel-crud-helper)
[![CodeFactor](https://www.codefactor.io/repository/github/technote-space/laravel-crud-helper/badge)](https://www.codefactor.io/repository/github/technote-space/laravel-crud-helper)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://github.com/technote-space/laravel-crud-helper/blob/master/LICENSE)
[![PHP: >=7.4](https://img.shields.io/badge/PHP-%3E%3D7.4-orange.svg)](http://php.net/)

*Read this in other languages: [English](README.md), [日本語](README.ja.md).*

Laravel用CRUDヘルパー

[Packagist](https://packagist.org/packages/technote/laravel-crud-helper)

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
composer require technote/laravel-crud-helper
```

## 使用方法
1. `Crudable Contract` と `Crudable Trait` を実装

   ```php
   <?php
   namespace App\Models;
   
   use Eloquent;
   use Illuminate\Database\Eloquent\Model;
   use Technote\CrudHelper\Models\Contracts\Crudable as CrudableContract;
   use Technote\CrudHelper\Models\Traits\Crudable;
    
   /**
    * Class Item
    * @mixin Eloquent
    */
   class Item extends Model implements CrudableContract
   {
       use Crudable;
   
       /**
        * @var array
        */
       protected $guarded = [
           'id',
       ];
   }
   ```

## Routes
CRUD routes は自動で設定されます。
```shell script
> php artisan route:clear
> php artisan route:list
+--------+-----------+------------------+---------------+-----------------------------------------------------------------+------------+
| Domain | Method    | URI              | Name          | Action                                                          | Middleware |
+--------+-----------+------------------+---------------+-----------------------------------------------------------------+------------+
|        | GET|HEAD  | api/items        | items.index   | Technote\CrudHelper\Http\Controllers\Api\CrudController@index   | api        |
|        | POST      | api/items        | items.store   | Technote\CrudHelper\Http\Controllers\Api\CrudController@store   | api        |
|        | GET|HEAD  | api/items/{item} | items.show    | Technote\CrudHelper\Http\Controllers\Api\CrudController@show    | api        |
|        | PUT|PATCH | api/items/{item} | items.update  | Technote\CrudHelper\Http\Controllers\Api\CrudController@update  | api        |
|        | DELETE    | api/items/{item} | items.destroy | Technote\CrudHelper\Http\Controllers\Api\CrudController@destroy | api        |
+--------+-----------+------------------+---------------+-----------------------------------------------------------------+------------+
```

## 詳細
### バリデーション
いくつかのバリデーションルールはカラム設定から自動で生成されます。
- Type
  - integer
  - boolean
  - numeric
  - date
  - time
  - string
- Length
- Unsigned
- Nullable
- by column name
  - email
  - url
  - phone

### モデル名
使用されるモデル名はAPI名によって決まります。
#### 例：`test_items`
1. to singular: `test_item`
1. to studly: `TestItem`

=> `TestItem`

### Config
#### Namespace
- `'App\\Models'`  
  - このライブラリは再帰的に検索しません。
#### Prefix
- `'api'`
#### Middleware
- `['api']`
##### 変更方法
1. `config/crud-helper.php` を生成するためのコマンドを実行

   ```
   php artisan vendor:publish --provider="Technote\CrudHelper\Providers\CrudHelperServiceProvider" --tag=config
   ```
1. 設定を変更

   ```php
   'namespace'  => 'App\\Models\\Crud',
   'prefix'     => 'api/v1',
   'middleware' => [
       'api',
       'auth',
   ],
   ``` 

## 検索機能
`Searchable` が実装されている場合、検索機能が追加されます。
### [Laravel Search Helper](https://github.com/technote-space/laravel-search-helper)
```
api/items?s=keyword
```

## Author
[GitHub (Technote)](https://github.com/technote-space)  
[Blog](https://technote.space)
