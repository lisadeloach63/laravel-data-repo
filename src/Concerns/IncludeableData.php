<?php

namespace Spatie\LaravelData\Concerns;

use Closure;
use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\PartialDefinition;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\PartialTrees;

trait IncludeableData
{
    protected ?PartialTrees $partialTrees = null;

    /** @var array<string, bool|Closure> */
    protected array $includes = [];

    /** @var array<string, bool|Closure> */
    protected array $excludes = [];

    /** @var array<string, bool|Closure> */
    protected array $only = [];

    /** @var array<string, bool|Closure> */
    protected array $except = [];

    public function withPartialTrees(PartialTrees $partialTrees): static
    {
        $this->partialTrees = $partialTrees;

        return $this;
    }

    public function include(string ...$includes): static
    {
        foreach ($includes as $include) {
            $this->includes[$include] = true;
        }

        return $this;
    }

    public function exclude(string ...$excludes): static
    {
        foreach ($excludes as $exclude) {
            $this->excludes[$exclude] = true;
        }

        return $this;
    }

    public function only(string ...$only): static
    {
        foreach ($only as $onlyDefinition) {
            $this->only[$onlyDefinition] = true;
        }

        return $this;
    }

    public function except(string ...$except): static
    {
        foreach ($except as $exceptDefinition) {
            $this->except[$exceptDefinition] = true;
        }

        return $this;
    }

    public function includeWhen(string $include, bool|Closure $condition): static
    {
        $this->includes[$include] = $condition;

        return $this;
    }

    public function excludeWhen(string $exclude, bool|Closure $condition): static
    {
        $this->excludes[$exclude] = $condition;

        return $this;
    }

    public function onlyWhen(string $only, bool|Closure $condition): static
    {
        $this->only[$only] = $condition;

        return $this;
    }

    public function exceptWhen(string $except, bool|Closure $condition): static
    {
        $this->except[$except] = $condition;

        return $this;
    }

    protected function includes(): array
    {
        return [];
    }

    protected function excludes(): array
    {
        return [];
    }


    protected function onlys(): array
    {
        return [];
    }

    protected function excepts(): array
    {
        return [];
    }

    public function getPartialTrees(): PartialTrees
    {
        if ($this->partialTrees) {
            return $this->partialTrees;
        }

        $filter = fn(bool|null|Closure $condition, string $definition) => match (true) {
            is_bool($condition) => $condition,
            $condition === null => false,
            is_callable($condition) => $condition($this),
        };

        $includes = collect($this->includes)->merge($this->includes())->filter($filter)->keys()->all();
        $excludes = collect($this->excludes)->merge($this->excludes())->filter($filter)->keys()->all();
        $only = collect($this->only)->merge($this->onlys())->filter($filter)->keys()->all();
        $except = collect($this->except)->merge($this->excepts())->filter($filter)->keys()->all();

        return new PartialTrees(
            (new PartialsParser())->execute($includes),
            (new PartialsParser())->execute($excludes),
            (new PartialsParser())->execute($only),
            (new PartialsParser())->execute($except),
        );
    }
}
