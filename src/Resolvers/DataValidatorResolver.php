<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

class DataValidatorResolver
{
    public function __construct(protected DataValidationRulesResolver $dataValidationRulesResolver)
    {
    }

    /** @param class-string<\Spatie\LaravelData\Data> $dataClass */
    public function execute(string $dataClass, Arrayable|array $payload): Validator
    {
        $payload = $payload instanceof Arrayable ? $payload->toArray() : $payload;
        $rules = app(DataValidationRulesResolver::class)
            ->execute($dataClass, $payload)
            ->toArray();

        $validator = ValidatorFacade::make(
            $payload,
            $rules,
            method_exists($dataClass, 'messages') ? app()->call([$dataClass, 'messages']) : [],
            method_exists($dataClass, 'attributes') ? app()->call([$dataClass, 'attributes']) : []
        );

        $dataClass::withValidator($validator);

        return $validator;
    }
}
