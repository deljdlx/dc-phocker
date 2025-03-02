<?php
namespace Phocker;



/**
 * @implements \ArrayAccess<string, mixed>
 */
class Route implements \ArrayAccess {

    /**
     * @var string[]
     */
    public array $methods;
    public string $uri;


    /**
     * @var callable
     */
    public mixed $callback;
    public string $name;


    /**
     * @param string[] $methods
     * @param string $uri
     * @param callable $callback
     * @param string $name
     */
    public function __construct(array $methods, string $uri, callable $callback, string $name = null) {
        $this->methods = $methods;
        $this->uri = $uri;
        $this->callback = $callback;
        $this->name = $name ?? '';
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
