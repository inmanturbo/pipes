<?php

use function Inmanturbo\Pipes\halt;
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

it('can halt with halt', function () {
    $fortyFive = pipe(1);

    $count = 1;
    while ($count < 50) {
        $fortyFive->pipe(fn ($number) => $number < 45 ? ++$number : halt($number));

        $count++;
    }

    expect($fortyFive->result())->toBe(45);
    expect($fortyFive->then(fn ($number) => ++$number))->toBe(45);
    expect($fortyFive->pipe(fn ($number) => ++$number)->result())->toBe(45);
    expect($fortyFive->pipe(fn ($number) => ++$number)->halted())->toBe(true);
});

it('can halt and resume', function () {
    $fortyFive = pipe(1);

    $count = 1;
    while ($count < 50) {

        if (($number = $fortyFive->result()) >= 45) {
            $fortyFive->halt($number);
        }

        $fortyFive->pipe(fn ($number) => ++$number);

        $count++;
    }

    expect($fortyFive->result())->toBe(45);
    expect($fortyFive->then(fn ($number) => ++$number))->toBe(45);
    expect($fortyFive->pipe(fn ($number) => ++$number)->result())->toBe(45);
    expect($fortyFive->pipe(fn ($number) => ++$number)->halted())->toBe(true);

    expect($fortyFive->resume(fn ($number) => ++$number)->result())->toBe(46);
});
