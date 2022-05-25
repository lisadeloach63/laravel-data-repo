<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Present extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'present';
    }

    public function parameters(): array
    {
        return [];
    }
}
