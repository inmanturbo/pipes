<?php

use function Inmanturbo\Pipes\pipe;

class Subtract
{
    public function __invoke($number)
    {
        return $number - 1;
    }
}

it('can pipe', function () {

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
});