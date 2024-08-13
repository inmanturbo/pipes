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
        return static::getLibrary($addOne, $addTwo);
    }

    public static function getLibrary(...$args)
    {
        $reflection = new \ReflectionMethod(__CLASS__, 'library');
        $parameters = $reflection->getParameters();
        $args = func_get_args();
        $options = [];

        foreach ($parameters as $index => $param) {
            if (isset($args[$index])) {
                $options[$param->getName()] = $args[$index];
            } else {
                $options[$param->getName()] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
            }
        }

        // Determine if all options are false
        $allFalse = count(array_filter($options)) == 0;

        // If all options are false or no arguments were passed, return all closures
        if ($allFalse || count($args) === 0) {
            return array_values(self::closures());
        }

        $result = self::options($options);

        if (count($result) === 1) {
            return reset($result); // Return the single callable directly
        }

        return array_values($result); // Return all matching closures
    }
}