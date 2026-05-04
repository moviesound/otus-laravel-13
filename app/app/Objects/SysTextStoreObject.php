<?php

namespace App\Objects;

use InvalidArgumentException;

final class SysTextStoreObject
{
    private function __construct(
        private readonly string $alias,
        private readonly string $context,
        private readonly string $lang,
    ) {
        $this->validateAlias($alias);
        $this->validateContext($context);
    }

    /* VALIDATION */

    private function validateAlias(string $alias): void
    {
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $alias)) {
            throw new InvalidArgumentException(
                'alias должен начинаться с латинской буквы,
                содержать только латинские буквы в нижнем регистре,
                цифры и нижнее подчеркивание'
            );
        }
    }

    private function validateContext(string $context): void
    {
        $context = trim($context);

        if ($context === '') {
            throw new InvalidArgumentException('Вы не заполнили текст');
        }
    }

    /* GETTERS */

    public function alias(): string
    {
        return $this->alias;
    }

    public function context(): string
    {
        return $this->context;
    }

    public function lang(): string
    {
        return $this->lang;
    }

    /* FACTORY */

    public static function create(array $data, array $langs): self
    {
        $lang = $data['lang'] ?? ($langs[0] ?? throw new InvalidArgumentException(
            'Не указан язык или список языков пуст'
        ));
        if (!in_array($lang, $langs, true)) {
            throw new InvalidArgumentException(
                'lang должен быть из списка: ' . implode(', ', $langs)
            );
        }

        $alias = $data['alias'] ?? throw new InvalidArgumentException('alias обязателен');

        $context = $data['context'] ?? throw new InvalidArgumentException('context обязателен');

        return new self(
            alias: $alias,
            context: $context,
            lang: $lang,
        );
    }
}
