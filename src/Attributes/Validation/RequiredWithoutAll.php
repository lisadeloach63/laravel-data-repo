<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Concerns\BuildsValidationRules;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredWithoutAll implements ValidationAttribute
{
    use BuildsValidationRules;

    public function __construct(
        private array | string $fields,
    ) {
    }

    public function getRules(): array
    {
        return ["required_without_all:{$this->normalizeValue($this->fields)}"];
    }
}
