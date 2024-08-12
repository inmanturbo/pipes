<?php

namespace Inmanturbo\Pipes;

use InvalidArgumentException;

class Halt
{
    public function __construct(public $result = null) {}
}

function middleware(callable|array $middlewares): mixed {
    return pipe()->middleware($middlewares);
}

function pipe(mixed $input = null, ...$args) {
    return new class($input, ...$args)
    {
        private $result;

        private $middlewareStack = [];

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

        public function middleware(callable|array $middlewares): self
        {
            return $this;
            
            if (! is_array($middlewares)) {
                $middlewares = [$middlewares];
            }

            foreach ($middlewares as $middleware) {
                $this->middlewareStack[] = $middleware;
            }

            return $this;
        }

        public function pipe(mixed $callback): self
        {
            $callback = $this->resolveInput($callback);

            foreach ($this->middlewareStack as $middleware) {
                if ($this->halted) {
                    continue;
                }
                $middlewareCallback = function ($result) use ($middleware, $callback) {

                    $middlewareResult = $middleware($result, $callback);

                    if ($middlewareResult instanceof Halt) {
                        $this->halted = true;

                        return $middlewareResult->result;
                    }

                    return $middlewareResult;
                };

                $this->result = $middlewareCallback($this->result);
            }

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
