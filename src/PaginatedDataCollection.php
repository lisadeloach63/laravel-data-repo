<?php

namespace Spatie\LaravelData;

use ArrayIterator;
use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;
use Spatie\LaravelData\Concerns\BaseDataCollectable;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\PaginatedCollectionIsAlwaysWrapped;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataCollectableTransformer;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements  DataCollectable<TKey, TValue>
 */
class PaginatedDataCollection implements DataCollectable
{
    use ResponsableData;
    use IncludeableData;
    use WrappableData;
    use TransformableData;
    use BaseDataCollectable;

    private Paginator $items;

    /**
     * @param class-string<TValue> $dataClass
     * @param Paginator $items
     */
    public function __construct(
        public readonly string $dataClass,
        Paginator $items
    ) {
        $this->items = $items->through(
            fn($item) => $item instanceof $this->dataClass ? $item : $this->dataClass::from($item)
        );
    }

    /**
     * @param Closure(TValue, TKey): TValue $through
     *
     * @return static
     */
    public function through(Closure $through): static
    {
        $clone = clone $this;

        $clone->items = $clone->items->through($through);

        return $clone;
    }

    public function items(): Paginator
    {
        return $this->items;
    }

    public static function castUsing(array $arguments)
    {
        if (count($arguments) !== 1) {
            throw CannotCastData::dataCollectionTypeRequired();
        }

        return new DataCollectionEloquentCast(current($arguments));
    }

    public function withoutWrapping(): static
    {
        throw PaginatedCollectionIsAlwaysWrapped::create();
    }
}
