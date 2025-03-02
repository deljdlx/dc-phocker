<?php
namespace Phocker;

class Route implements \ArrayAccess {
    public $methods;
    public $uri;
    public $callback;
    public $name;

    public function __construct(array $methods, string $uri, callable $callback, string $name = null) {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->callback = $callback;
        $this->name = $name;
    }

    public function offsetExists($offset): bool
    {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value): void
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->$offset);
    }
}
