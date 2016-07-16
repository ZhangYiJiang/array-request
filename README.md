# `ArrayRequest` - Experimental multidimensional input array support in Laravel

[![Build Status](https://travis-ci.org/ZhangYiJiang/array-request.svg?branch=master)](https://travis-ci.org/ZhangYiJiang/array-request)

Playing around with how the Laravel framework itself can facilitate multidimension arrays as inputs better than it does now. This is the result of that using the [`Collective\Form` helper](laravelcollective.com/docs/5.2/html) and some middlewares.

## Motivation

Suppose you have a model with some array data


```php
[
    // Single dimensional data - array of strings
    'princesses' => [
        'Celestia',
        'Luna',
        'Cadance',
     ],

    // Multidimensional data - array of associative arrays
    'ponies' => [
        ['name' => 'Twilight', 'color' => 'purple'],
        ['name' => 'Applejack', 'color' => 'orange'],
        ['name' => 'Rarity', 'color' => 'white'],
    ],
]
```

Representing this in a form you might be tempted to write

```php
{{ Form::text('princess[]') }}

{{ Form::text('ponies[][name]') }}
{{ Form::text('ponies[][color]') }}
```

This won't work. The first line will throw a rather cryptic `InvalidArgumentsError`, because the form builder tries to pass the entire `princesses` array to `htmlentities` function. The second and third will similarly fail. To work around this you can use
explicit numbering

```php
{{ Form::text('princess[0]') }}
{{ Form::text('princess[1]') }}

{{ Form::text('ponies[0][name]') }}
{{ Form::text('ponies[0][color]') }}
```

This works, but it means having to explicitly number every input field. This means that if fields need to be added dynamically on the client side, they too have to be named properly by the JS. This seems like unnecessary complexity. In addition, while single dimension data are processed properly by PHP, multidimension data are mangled. The above will translate to the following when retrieved on the server side

```php
'ponies' => [
    [['name' => 'Twilight']],
    [['color' => 'purple']]
]
```
The solution to the above is an extension to the `FormBuilder` class which will correctly translate the form names to use the correct value when form model binding is used, and a middleware which will unmangle the form data.

## Installation

The package is currently in development, so it is not listed on packagist. Please do not use this in production environment. To install this package in its current state, follow [Composer's instructions for installing from VCS](https://getcomposer.org/doc/05-repositories.md#vcs) and add this repo's URL to the list, then add this package to your composer.json file

```
composer require zhangyijiang/array-request:dev-master
```

To use the patched form helper, replace `Collective\Html\HtmlServiceProvider` with `ZhangYiJiang\ArrayRequest\HtmlServiceProvider`

```php
// config/app.php

'providers' => [
    // ...
    ZhangYiJiang\ArrayRequest\HtmlServiceProvider::class,
    // ...
],
```

and add the middleware to the HTTP Kernel

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
    // ...
    'form.array' => \ZhangYiJiang\ArrayRequest\Middleware::class,
];
```

Then simply rememeber to use the Middleware on the routes which there are array inputs that need unmangling.