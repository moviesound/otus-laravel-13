<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedLang implements ValidationRule
{
    public function __construct(
        private array $langs
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, $this->langs, true)) {
            $fail('язык должен быть из списка: ' . implode(', ', $this->langs));
        }
    }
}
