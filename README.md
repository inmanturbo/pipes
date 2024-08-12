## Pipes for php with a simple api based on functional composition

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inmanturbo/pipes.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/pipes)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/pipes/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inmanturbo/pipes/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/pipes/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inmanturbo/pipes/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inmanturbo/pipes.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/pipes)


# Usage

```php
    $add = fn ($number = 0) => $number + 1;

    $five = pipe($add, 1)
        ->pipe(fn ($number) => $number + 1)
        ->pipe($add)
        ->pipe($add)
        ->thenReturn();

    expect($five)->toBe(5);

    $four = pipe($add)
        ->pipe(fn ($number) => $number + 1)
        ->pipe($add)
        ->pipe($add)
        ->thenReturn();

    expect($four)->toBe(4);

    $six = pipe($add, 1)
        ->pipe($add)
        ->pipe($add)
        ->pipe($add)
        ->then(fn ($number) => ++$number);

    expect($six)->toBe(6);

    $fiveAgain = pipe($six)
        ->pipe(Subtract::class)
        ->thenReturn();

    expect($fiveAgain)->toBe(5);

    $three = pipe(new Subtract, $fiveAgain)
        ->pipe(new Subtract)
        ->thenReturn();

    expect($three)->toBe(3);

    $fifty = pipe(1);

    while ($fifty->thenReturn() < 50) {
        $fifty->pipe(fn ($number) => ++$number);
    }

    expect($fifty->thenReturn())->toBe(50);
```
