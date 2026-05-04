<?php

namespace App\Objects;

use InvalidArgumentException;

final class SysTextSearchObject
{
    private function __construct(
        private readonly ?string $alias = null,
        private readonly int $perPage = 20,
        private readonly string $lang
    )
    {
        $this->validateAlias($alias);
        $this->validatePerPage($perPage);
    }

    /* VALIDATION */
    private function validatePerPage(int $perPage): void
    {
        if ($perPage < 1) {
            throw new InvalidArgumentException('perPage не может быть меньше 1');
        }

        if ($perPage > 100) {
            throw new InvalidArgumentException('perPage не может быть больше 100');
        }
    }

    private function validateAlias(?string $alias): void
    {
        if (!isset($alias)) return;

        if (!preg_match('/^[a-z][a-z0-9_]*$/', $alias)) {
            throw new InvalidArgumentException(
                'alias должен начинаться с латинской буквы,
                содержать только латинские буквы в нижнем регистре,
                цифры и нижнее подчеркивание'
            );
        }
    }

    /* GETTERS */
    public function alias(): ?string
    {
        return $this->alias;
    }

    public function lang(): ?string
    {
        return $this->lang;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    /* FACTORY */

    public static function create(array $data, array $langs): self
    {
        $lang = $data['lang'] ?? ($langs[0] ?? throw new InvalidArgumentException(
            'Не указан язык или список языков пуст'
        ));

        $alias = $data['alias'] ?? null;

        $perPage = $data['perPage'] ?? 20;

        return new self(
            alias: $alias,
            perPage: $perPage,
            lang: $lang
        );
    }
}
