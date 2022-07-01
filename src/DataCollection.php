<?php

namespace Spatie\LaravelData;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Concerns\BaseDataCollectable;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\InvalidDataCollectionOperation;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataCollectionTransformer;

/**
 * @template TValue
 *
 * @implements \ArrayAccess<array-key, TValue>
 * @implements  DataCollectable<TValue>
 */
class DataCollection implements DataCollectable, ArrayAccess
{
    use BaseDataCollectable;
    use ResponsableData;
    use IncludeableData;
    use WrappableData;
    use TransformableData;

    /** @var Enumerable<array-key, TValue> */
    private Enumerable $items;

    /**
     * @param class-string<TValue> $dataClass
     * @param array|Enumerable<array-key, TValue>|DataCollection $items
     */
    public function __construct(
        public readonly string $dataClass,
        Enumerable|array|DataCollection $items
    ) {
        if (is_array($items)) {
            $items = new Collection($items);
        }

        if ($items instanceof DataCollection) {
            $items = $items->toCollection();
        }

        $this->items = $items->map(
            fn ($item) => $item instanceof $this->dataClass ? $item : $this->dataClass::from($item)
        );
    }

    /**
     * @param Closure(TValue, array-key): TValue $through
     *
     * @return static
     */
    public function through(Closure $through): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->map($through);

        return $cloned;
    }

    /**
     * @param Closure(TValue): bool $filter
     *
     * @return static
     */
    public function filter(Closure $filter): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->filter($filter);

        return $cloned;
    }

    /**
     * @return array<array-key, TValue>
     */
    public function items(): array
    {
        return $this->items->all();
    }

    /**
     * @return array<array>
     */
    public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
    ): array {
        $transformer = new DataCollectionTransformer(
            $this->dataClass,
            $transformValues,
            $wrapExecutionType,
            $this->getPartialTrees(),
            $this->items,
            $this->getWrap(),
        );

        return $transformer->transform();
    }

    public function values(): static
    {
        $this->items = $this->items->values();

        return $this;
    }

    public function toCollection(): Enumerable
    {
        return $this->items;
    }

    /**  @return \ArrayIterator<array-key, array> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->transform(
            transformValues: false,
        ));
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param array-key $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        return $this->items->offsetExists($offset);
    }

    /**
     * @param array-key $offset
     *
     * @return TValue
     */
    public function offsetGet($offset): mixed
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        return $this->items->offsetGet($offset);
    }

    /**
     * @param array-key|null $offset
     * @param TValue $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        $value = $value instanceof BaseData
            ? $value
            : $this->dataClass::from($value);

        $this->items->offsetSet($offset, $value);
    }

    /**
     * @param array-key $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        $this->items->offsetUnset($offset);
    }

    public static function castUsing(array $arguments)
    {
        if (count($arguments) !== 1) {
            throw CannotCastData::dataCollectionTypeRequired();
        }

        return new DataCollectionEloquentCast(current($arguments));
    }
}
