# `ArrayRequest` - Experimental multidimensional input array support in Laravel

Playing around with how the Laravel framework itself can facilitate multidimension
 arrays as inputs better than it does now. This is the result of that using
 the `Collective\Form` helper and some middlewares. Some examples:

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

