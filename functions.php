<?php

namespace Inmanturbo\Pipes;

use InvalidArgumentException;

function pipe(mixed $input = null, ...$args)
{
    return new Pipe($input, ...$args);
}

function halt(mixed $result = null) {
    return new Halt($result);
}

function hop(mixed $callback, mixed $middleware = null)
{
    $callback = resolveCallback($callback);

    if ($middleware) {
        $middleware = resolveCallback($middleware);
    }

    return function ($passable, $next) use ($callback, $middleware) {
        if ($middleware) {
            return $middleware($callback($passable), $next);
        }

        return $next($callback($passable));
    };
}

function resolveCallback(mixed $callback)
{
    if (is_string($callback) && class_exists($callback)) {
        $instance = function_exists('app') ? app($callback) : new $callback;

        if (! is_callable($instance)) {
            throw new InvalidArgumentException("Class {$callback} is not invokable.");
        }

        return $instance;
    }

    return $callback;
}

class Halt
{
    public function __construct(public mixed $result = null) {}
}

class Pipe
{
    private $result;

    private bool $halted = false;

    public function halted()
    {
        return $this->halted;
    }

    public function halt(mixed $result = null)
    {
        $this->halted = true;

        return new Halt($result);
    }

    public function __construct(mixed $input = null, ...$args)
    {
        $this->result = is_callable($this->resolveInput($input))
            ? $this->resolveInput($input)(...$args)
            : $input;
    }

    public function pipe(mixed $callback): self
    {
        if ($this->result instanceof Halt) {
            $this->halted = true;
        }

        if (! $this->halted) {
            $this->result = $this->resolveInput($callback)($this->result);
        }

        return $this;
    }

    public function result(): mixed
    {
        return $this->halted ? $this->result->result : $this->result;
    }

    public function then(?callable $callback = null): mixed
    {
        return $this->thenReturn($callback);
    }

    public function thenReturn(?callable $callback = null): mixed
    {
        if (! $callback) {
            return $this->result();
        }

        return $this->pipe($callback)
            ->result();
    }

    private function resolveInput(mixed $input)
    {
        if (is_string($input) && class_exists($input)) {
            $instance = new $input;

            if (! is_callable($instance)) {
                throw new InvalidArgumentException("Class {$input} is not invokable.");
            }

            return $instance;
        }

        return $input;
    }
}
