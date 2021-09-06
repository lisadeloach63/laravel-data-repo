<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringType extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['string'];
    }
}
