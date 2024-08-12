<?php

namespace Inmanturbo\Pipes;

use InvalidArgumentException;

class Halt
{
    public function __construct(public $result = null) {}
}

function pipe(mixed $input = null, ...$args)
{
    return new class($input, ...$args)
    {
        private $result;

        private bool $halted = false;

        public function halt(mixed $result = null)
        {
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
            $callback = $this->resolveInput($callback);

            if (! $this->halted) {
                $this->result = $callback($this->result);
            }

            return $this;
        }

        public function result(): mixed
        {
            return $this->result;
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
    };
}

function hop(callable $callback, ?callable $middleware = null)
{
    return function ($passable, $next) use ($callback, $middleware) {
        if ($middleware) {
            return $middleware($callback($passable), $next);
        }

        return $next($callback($passable));
    };
}
