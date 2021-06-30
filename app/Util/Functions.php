<?php

use Illuminate\Support\Str;

if (! function_exists('call')) {
    /**
     * Call a callback with the arguments.
     *
     * @param mixed $callback
     * @return null|mixed
     */
    function call($callback, array $args = [])
    {
        $result = null;
        if ($callback instanceof \Closure) {
            $result = $callback(...$args);
        } elseif (is_object($callback) || (is_string($callback) && function_exists($callback))) {
            $result = $callback(...$args);
        } elseif (is_array($callback)) {
            [$object, $method] = $callback;
            $result = is_object($object) ? $object->{$method}(...$args) : $object::$method(...$args);
        } else {
            $result = call_user_func_array($callback, $args);
        }
        return $result;
    }
}

if (! function_exists('setter')) {
    /**
     * Create a setter string.
     */
    function setter(string $property): string
    {
        return 'set' . Str::studly($property);
    }
}

if (! function_exists('getter')) {
    /**
     * Create a getter string.
     */
    function getter(string $property): string
    {
        return 'get' . Str::studly($property);
    }
}
