<?php

use Illuminate\Pipeline\Pipeline;

use function Inmanturbo\Pipes\hop;

class Add
{
    public function add($number)
    {
        return $number + 1;
    }
}
class InvokeAdd
{
    public function __invoke($number)
    {
        return $number + 1;
    }
}

test('it can hop', function () {
    $three = (new Pipeline)->send(0)
        ->pipe(hop(fn ($number) => $number + 1))
        ->pipe(hop(new InvokeAdd))
        ->pipe(hop(fn ($number) => (new Add)->add($number)))
        ->thenReturn();

    $four = (new Pipeline)->send(1)
        ->through([
            hop(fn ($number) => $number + 1),
            hop(fn ($number) => $number + 1),
            hop(fn ($number) => $number + 1),
        ])
        ->thenReturn();

    expect($three)->toBe(3);

    expect($four)->toBe(4);
});
