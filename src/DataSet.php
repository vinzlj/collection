<?php

declare(strict_types=1);

namespace DataSet;

readonly class DataSet
{
    private function __construct(protected array $items)
    {
    }

    public static function empty(): self
    {
        return new static([]);
    }

    public static function from(array|self $items): self
    {
        if ($items instanceof self) {
            return new static($items->toArray());
        }

        return new static($items);
    }

    /** @param non-empty-string $separator */
    public static function fromString(string $string, string $separator, int $limit = \PHP_INT_MAX): self
    {
        return new static(explode($separator, $string, $limit));
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function jsonEncode(int $flags = 0, int $depth = 512): string
    {
        return (string) json_encode($this->items, $flags, max(1, $depth));
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function first(): mixed
    {
        return $this->items[0] ?? null;
    }

    public function last(): mixed
    {
        return $this->items[count($this->items) - 1] ?? null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    public function isNotEmpty(): bool
    {
        return 0 < $this->count();
    }

    public function addItem(mixed $value, ?string $key = null): self
    {
        if (null === $key) {
            return $this->addItems([$value]);
        }

        return $this->addItems([$key => $value]);
    }

    public function addItems(array $items): self
    {
        return self::from(array_merge($this->items, $items));
    }

    public function contains(mixed $item): bool
    {
        return \in_array($item, $this->items, true);
    }

    public function min(): int|float|null
    {
        if (empty($this->items)) {
            return null;
        }

        return min($this->items);
    }

    public function max(): int|float|null
    {
        if (empty($this->items)) {
            return null;
        }

        return max($this->items);
    }

    public function slice(int $offset, int $length = null, bool $preserveKeys = false): self
    {
        return self::from(\array_slice($this->items, $offset, $length, $preserveKeys));
    }

    public function join(string $delimiter): string
    {
        return implode($delimiter, $this->items);
    }

    public function deduplicate(int $flags = \SORT_STRING): self
    {
        return self::from(array_unique($this->items, $flags));
    }

    public function reverse(): mixed
    {
        return array_reverse($this->items);
    }

    public function map(\Closure $closure, bool $preserveKeys = true): self
    {
        if (!$preserveKeys) {
            return self::from(array_values(array_map($closure, $this->items)));
        }

        return self::from(array_map($closure, $this->items));
    }

    public function filter(\Closure|null $closure = null, bool $preserveKeys = true): self
    {
        $filtered = null === $closure ? array_filter($this->items) : array_filter($this->items, $closure);

        return self::from($preserveKeys ? $filtered : array_values($filtered));
    }

    public function column(string $columnName, bool $preserveKeys = false): self
    {
        if ($preserveKeys) {
            return self::from(array_combine(array_keys($this->items), array_column($this->items, $columnName)));
        }

        return self::from(\array_column($this->items, $columnName));
    }

    public function sort(\Closure|null $closure = null, bool $preserveKeys = true): self
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

    public function reduce(\Closure $closure, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $closure, $initial);
    }

    public function some(\Closure $closure): bool
    {
        foreach ($this->items as $item) {
            if ($closure($item)) {
                return true;
            }
        }

        return false;
    }

    public function every(\Closure $closure): bool
    {
        foreach ($this->items as $item) {
            if (!$closure($item)) {
                return false;
            }
        }

        return true;
    }

    public function flatten(): self
    {
        $flattened = [];

        foreach ($this->items as $item) {
            if (\is_array($item)) {
                foreach ($item as $key => $value) {
                    $flattened[$key] = $value;
                }
            }
        }

        return self::from($flattened);
    }
}
