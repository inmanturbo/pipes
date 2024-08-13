## Pipes for php with a simple api based on functional composition

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inmanturbo/pipes.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/pipes)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/pipes/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inmanturbo/pipes/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/pipes/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inmanturbo/pipes/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inmanturbo/pipes.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/pipes)

Simply put, it takes the output of the last one and pipes it to the next one. Sorta like bash  `cat ./file | grep -e 'waldo'`

## Installation

You can install the package via composer:

```bash
composer require inmanturbo/pipes
```

Or just copy or download the [`functions.php`](https://github.com/inmanturbo/pipes/blob/main/functions.php) file from this repository.

# Usage

## pipe()

```php

require __DIR__.'/../vendor/autoload.php';

use function Inmanturbo\Pipes\pipe;

$addOne = fn ($number = 0) => $number + 1;

$five = pipe($addOne) // 1
    ->pipe(fn ($number) => $number + 1) // 2
    ->pipe($addOne) // 3
    ->pipe($addOne) // 4
    ->thenReturn(); // 4
```

It doesn't delay execution

```php
$fifty = pipe(1);

    while ($fifty->result() < 50) {
        $fifty->pipe(fn ($number) => ++$number);
    }

echo $fifty->result();
// 50
     
```

You can also pass a class or a class string

```php

use function Inmanturbo\Pipes\pipe;

class Subtract
{
    public function __invoke($number)
    {
        return $number - 1;
    }
}

$addOne = fn ($number = 0) => $number + 1;

$six = pipe($addOne, 1)
    ->pipe($addOne)
    ->pipe($addOne)
    ->pipe($addOne)
    ->then(fn ($number) => ++$number);

$five = pipe($six)
    ->pipe(Subtract::class)
    ->thenReturn();

$three = pipe(new Subtract, $five)
    ->pipe(new Subtract)
    ->thenReturn();

```

## Returning results

results can be returned three ways:

`then()` or `thenReturn()` both a take final callback and return the result, or `result()`, which simply returns the result.

```php
$addOne = fn ($number = 0) => $number + 1;

$six = pipe($addOne, 1)
    ->pipe($addOne)
    ->pipe($addOne)
    ->pipe($addOne)
    ->then(fn ($number) => ++$number);

$sixAgain = pipe($addOne, 1)
    ->pipe($addOne)
    ->pipe($addOne)
    ->pipe($addOne)
    ->thenReturn(fn ($number) => ++$number);

$five = pipe($addOne, 1)
    ->pipe($addOne)
    ->pipe($addOne)
    ->pipe($addOne)
    ->result();
```

## Halting the pipeline

You can return an instance of `Inmanturbo\Pipes\Halt` from a callback to halt the chain. `Halt` takes an optional result in its constructor which you can pass as the final `result()` of the chain. Subsequent calls to `->pipe()` will not affect the final outcome.

```php

    $fortyFive = pipe(1);

    $count = 1;
    while ($count < 50) {
        $fortyFive->pipe(fn ($number) => $number < 45 ? ++$number : new Halt($number));

        $count ++;
    }

    echo $fortyFive->result();

    // 45

    echo $fortyFive->pipe(fn ($number) => ++$number)->result();

    // 45

```

## hop() and Laravel

This package doesn't require laravel to use pipe or `hop()`, but `hop()` (higher-order-pipe) is a higher order function intended for working with Laravel's [Pipeline](https://laravel.com/docs/11.x/helpers#pipeline) helper. This higher-order-function takes a callback which takes a single argument, and wraps the `$callback` for you in a closure which implements `function($next, $passable)`.

```php

use Illuminate\Pipeline\Pipeline;

use function Inmanturbo\Pipes\hop;

class Add {
    public function add($number)
    {
        return $number +1;
    }
}
class InvokeAdd {
    public function __invoke($number)
    {
        return $number +1;
    }
}

$five = (new Pipeline)->send(1)
    ->pipe(hop(fn($number) => $number +1))
    ->pipe(hop(new InvokeAdd))
    ->pipe(hop(InvokeAdd::class))
    ->pipe(hop(fn($number) => (new Add)->add($number)))
->thenReturn();

// 5

```

You can optionally pass a single `middleware` as a second argument to `hop()`, and it will get called before the first argument, which allows you to determine if the pipeline should halt before the `$callback` ever gets executed.

```php

$limitThreeMiddleware = function ($number, $next) {
    if($number >= 3) {
        Log::info('Limit hit');
        return $number;
    }

    return $next($number);
};

$five = (new Pipeline)->send(1)
    ->pipe(hop(fn($number) => $number +1, $limitThreeMiddleware))
    ->pipe(hop(new InvokeAdd, $limitThreeMiddleware))
    // Limit hit
    ->pipe(hop(InvokeAdd::class, $limitThreeMiddleware))
    ->pipe(hop(fn($number) => (new Add)->add($number), $limitThreeMiddleware))
->thenReturn();

// 3
```
