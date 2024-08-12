<?php

test('it can make a library', function () {
    // Testing with multiple options
    [$addOne, $addTwo] = Library::library(addOne: true, addTwo: true);
    expect($addOne(0))->toBe(1);
    expect($addTwo(0))->toBe(2);

    // Testing with a single option
    $addOne = Library::library(addOne: true);
    expect($addOne(0))->toBe(1);

    // Testing with no options (should return all available options)
    [$addOne, $addTwo] = Library::library();
    expect($addOne(0))->toBe(1);
    expect($addTwo(0))->toBe(2);
});

class Library {

    public static function options(array $options)
    {
        $closures = static::closures();
        $result = [];

        foreach ($options as $key => $value) {
            if ($value && isset($closures[$key])) {
                $result[$key] = $closures[$key];
            }
        }

        return $result;
    }

    public static function closures()
    {
        return [
            'addOne' => function($number) {
                return $number + 1;
            },
            'addTwo' => function($number) {
                return $number + 2;
            }
        ];
    }

    public static function library(bool $addOne = false, bool $addTwo = false)
    {
        // If no arguments are passed, return all options
        if (empty(func_get_args())) {
            return array_values(self::closures());
        }

        $options = compact('addOne', 'addTwo');
        $result = self::options($options);

        if (count($result) === 1) {
            return reset($result);
        }

        return array_values($result);
    }
}