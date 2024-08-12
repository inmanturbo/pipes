## Pipes for php with a simple api based on functional composition

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inmanturbo/pipes.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/pipes)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/pipes/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inmanturbo/pipes/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/pipes/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inmanturbo/pipes/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inmanturbo/pipes.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/pipes)


# Usage

Very Simply put, it takes the output of the last one and pipes it to the next one in a chain. sorta like bash  `cat ./file | grep -e 'waldo'`

```php

use function Inmanturbo\Pipes\pipe;

$add = fn ($number = 0) => $number + 1;

$five = pipe($add) // 1
    ->pipe(fn ($number) => $number + 1) // 2
    ->pipe($add) // 3
    ->pipe($add) // 4
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

$six = pipe($add, 1)
    ->pipe($add)
    ->pipe($add)
    ->pipe($add)
    ->then(fn ($number) => ++$number);

$five = pipe($six)
    ->pipe(Subtract::class)
    ->thenReturn();

$three = pipe(new Subtract, $five)
    ->pipe(new Subtract)
    ->thenReturn();

```

Returning results

results can be returned three ways:

`then()` or `thenReturn()` both a take final callback and return the result, or `result()`, which simply returns the result.

```php
$six = pipe($add, 1)
    ->pipe($add)
    ->pipe($add)
    ->pipe($add)
    ->then(fn ($number) => ++$number);

$sixAgain = pipe($add, 1)
    ->pipe($add)
    ->pipe($add)
    ->pipe($add)
    ->thenReturn(fn ($number) => ++$number);

$five = pipe($add, 1)
    ->pipe($add)
    ->pipe($add)
    ->pipe($add)
    ->result();
```


