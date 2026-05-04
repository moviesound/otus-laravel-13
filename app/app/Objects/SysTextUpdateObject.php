<?php

namespace App\Objects;

use InvalidArgumentException;

final class SysTextUpdateObject
{
    private function __construct(
        private readonly int $id,
        private readonly string $alias,
        private readonly string $context,
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
        if (trim($context) === '') {
            throw new InvalidArgumentException('Вы не заполнили текст');
        }
    }

    /* GETTERS */

    public function id(): int
    {
        return $this->id;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function context(): string
    {
        return $this->context;
    }

    /* FACTORY */

    public static function create(array $data): self
    {
        $id = $data['id'] ?? throw new InvalidArgumentException('Не передан id записи');

        $alias = $data['alias'] ?? throw new InvalidArgumentException('alias обязателен');

        $context = $data['context'] ?? throw new InvalidArgumentException('context обязателен');

        return new self(
            id: $id,
            alias: $alias,
            context: $context,
        );
    }
}
