<?php

declare(strict_types=1);

namespace Common\Form\Element\Attribute;

/**
 * A class list object for interacting with collections of class names.
 *
 * @see \CommonTest\Form\Element\Attribute\ClassListTest
 */
class ClassList implements \Stringable
{
    /**
     * @var array
     */
    private array $items = [];

    /**
     * @param string|string[] $classes
     */
    final public function __construct($classes = [])
    {
        $this->add($classes);
    }

    /**
     * @param string|string[] $classes
     */
    public function has($classes): bool
    {
        if ($classes instanceof ClassList) {
            $classes = $classes->toArray();
        }

        if (! is_array($classes)) {
            $classes = [$classes];
        }

        foreach ($classes as $class) {
            if (! array_key_exists($class, $this->items)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|string[] $classes
     * @return $this
     */
    public function add($classes): self
    {
        if ($classes instanceof ClassList) {
            $classes = $classes->toArray();
        }

        if (! is_array($classes)) {
            $classes = [$classes];
        }

        foreach ($classes as $class) {
            assert(is_string($class));
            $this->items[$class] = null;
        }

        return $this;
    }

    /**
     * @param string|string[] $classes
     * @return $this
     */
    public function remove($classes): self
    {
        if (is_string($classes)) {
            $classes = static::fromString($classes);
        }

        if ($classes instanceof ClassList) {
            $classes = $classes->toArray();
        }

        foreach ($classes as $class) {
            assert(is_string($class));
            unset($this->items[$class]);
        }

        return $this;
    }

    #[\Override]
    public function __toString(): string
    {
        return implode(' ', $this->toArray());
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return array_keys($this->items);
    }

    /**
     * @return static
     */
    public static function fromString(string $str): self
    {
        return new static(explode(' ', $str));
    }
}
