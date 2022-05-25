<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ActiveUrl extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'active_url';
    }

    public function parameters(): array
    {
        return [];
    }
}
