<?php

use function Inmanturbo\Pipes\middleware;
use function Inmanturbo\Pipes\pipe;

class Subtractinate
{
    public function __invoke($number)
    {
        return $number - 1;
    }
}

it('can pipe', function () {

    $add = fn ($number) => $number + 1;

    $five = pipe($add, 1)
        ->pipe(fn ($number) => $number + 1)
        ->pipe($add)
        ->pipe($add)
        ->thenReturn();

    expect($five)->toBe(5);

    $six = pipe($add, 1)
        ->pipe($add)
        ->pipe($add)
        ->pipe($add)
        ->then(fn ($number) => ++$number);

    expect($six)->toBe(6);

    $fiveAgain = pipe($six)
        ->pipe(Subtractinate::class)
        ->thenReturn();

    expect($fiveAgain)->toBe(5);

    $three = pipe(new Subtractinate, $fiveAgain)
        ->pipe(new Subtractinate)
        ->thenReturn();

    expect($three)->toBe(3);

    $fifty = pipe(1);

    while ($fifty->thenReturn() < 50) {
        $fifty->pipe(fn ($number) => ++$number);
    }

    expect($fifty->thenReturn())->toBe(50);
});

it('can apply middleware', function () {
    $thirty = pipe(10)
        ->middleware(function ($number, $next) {
            return $next($number * 3);
        })
        ->pipe(fn ($number) => 10)
        ->thenReturn();
    expect($thirty)->toBe(30);

    $middlewareFourty = [
        function ($data, $next) {
            $data = 10;

            return $next($data);
        },
        function ($data, $next) {
            $data = 20;

            return $next($data);
        },
        function ($data, $next) {
            $data = 40;

            return $next($data);
        },
    ];

    $fourty = pipe(1000)
        ->middleware($middlewareFourty)
        ->pipe(fn ($data) => $data)
        ->thenReturn();

    $middlewareTimesTwoThreeTimes = [
        function ($data, $next) {
            $data = $data * 2;

            return $next($data);
        },
        function ($data, $next) {
            $data = $data * 2;

            return $next($data);
        },
        function ($data, $next) {
            $data = $data * 2;

            return $next($data);
        },
    ];

    $twoTimesTwoThreeTimes = pipe(2)
        ->middleware($middlewareTimesTwoThreeTimes)
        ->pipe(fn ($data) => $data)
        ->thenReturn();

    expect($twoTimesTwoThreeTimes)->toBe(2 * 2 * 2 * 2);
})->skip('Not yet implemented');

test('middleware can halt pipes', function () {
    $thirty = pipe(10)
        ->middleware(function ($number, $next) {
            return pipe()->halt(30);
        })
        ->pipe(fn ($number) => $number)
        ->thenReturn();
    expect($thirty)->toBe(30);

    $returnsNull = pipe(10)
        ->middleware(function ($number, $next) {
            return pipe()->halt(null);
        })
        ->pipe(fn ($number) => $number)
        ->thenReturn();
    expect($returnsNull)->toBe(null);
})->skip('Not yet implemented');
