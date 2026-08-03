<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

/**
 * A row stand-in that satisfies almost any table definition without knowing its shape.
 *
 * Table definitions reach into arbitrary nested data — $data['licence']['organisation']['name'] —
 * so hand-writing a fixture per table would mean 233 fixtures. Instead this returns itself for any
 * key, to any depth, and stringifies to an XSS marker. Whatever a definition reaches for, it gets
 * the marker, and the harness then asks whether the marker survived into the output unescaped.
 *
 * Both interfaces are templated, and psalm requires the parameters to be named. They describe what
 * this actually does: any offset is accepted and answered with the probe itself, and iterating
 * yields the probe under an integer key.
 *
 * @implements \ArrayAccess<mixed, self>
 * @implements \IteratorAggregate<int, self>
 */
final class RecursiveProbe implements \ArrayAccess, \Countable, \IteratorAggregate, \Stringable, \JsonSerializable
{
    public function __construct(private readonly string $marker)
    {
    }

    #[\Override]
    public function offsetExists(mixed $offset): bool
    {
        // Always "present", so isset()/empty() guards take their populated branch and the column
        // actually renders. A probe that reported absent would silently skip the code under test.
        return true;
    }

    #[\Override]
    public function offsetGet(mixed $offset): mixed
    {
        return $this;
    }

    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
    }

    #[\Override]
    public function count(): int
    {
        return 1;
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator([$this]);
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->marker;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->marker;
    }

    /**
     * Formatters commonly call methods on row values; answer them with the probe rather than
     * throwing, so the render gets as far as it can.
     */
    public function __call(string $name, array $arguments): self
    {
        return $this;
    }

    public function __get(string $name): self
    {
        return $this;
    }

    public function __isset(string $name): bool
    {
        return true;
    }
}
