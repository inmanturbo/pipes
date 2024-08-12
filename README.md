## Pipes for php with a simple api based on functional composition

[![Latest Version on Packagist](https://img.shields.io/packagist/v/inmanturbo/pipes.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/pipes)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/pipes/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/inmanturbo/pipes/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/inmanturbo/pipes/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/inmanturbo/pipes/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/inmanturbo/pipes.svg?style=flat-square)](https://packagist.org/packages/inmanturbo/pipes)


# Usage

Very Simply put, it takes the output of the last one and pipes it to the next one in a chain. sorta like bash  `cat ./file | grep -e 'waldo'`

```php
$add = fn ($number = 0) => $number + 1;

$five = pipe($add) // 1
    ->pipe(fn ($number) => $number + 1) // 2
    ->pipe($add) // 3
    ->pipe($add) // 4
    ->thenReturn(); // 4
     
```


