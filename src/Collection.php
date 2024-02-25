<?php

declare(strict_types=1);

namespace Collection;

final readonly class Collection implements \IteratorAggregate
{
    private function __construct(protected array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public static function from(array $items): self
    {
        return new self($items);
    }

    /** @param non-empty-string $separator */
    public static function fromString(string $string, string $separator, int $limit = \PHP_INT_MAX): self
    {
        return new self(explode($separator, $string, $limit));
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function first(): mixed
    {
        return reset($this->items);
    }

    public function last(): mixed
    {
        return end($this->items);
    }

    public function isEmpty(): bool
    {
        return 0 === count($this->items);
    }

    public function isNotEmpty(): bool
    {
        return 0 < count($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function map(\Closure|callable $closure): self
    {
        return self::from(array_map($closure, $this->items));
    }

    public function filter(\Closure|callable $closure, bool $preserveKeys = true): self
    {
        if ($preserveKeys) {
            return self::from(array_filter($this->items, $closure));
        }

        return self::from(array_values(array_filter($this->items, $closure)));
    }

    public function sort(\Closure|callable $closure = null, bool $preserveKeys = true): self
    {
        $items = $this->items;

        if (null === $closure) {
            sort($items);
        } elseif ($preserveKeys) {
            uasort($items, $closure);
        } else {
            usort($items, $closure);
        }

        return self::from($items);
    }

    public function slice(int $offset, int $length = null, bool $preserveKeys = false): self
    {
        return self::from(\array_slice($this->items, $offset, $length, $preserveKeys));
    }

    public function flatten(): self
    {
        return self::from(array_merge(...$this->items));
    }
}
