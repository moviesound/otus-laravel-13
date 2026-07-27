<?php

namespace Packages\QueryLogging\Services;

class LastCreatedModelStore
{
    private ?int $id = null;

    public function set(int $id): void
    {
        $this->id = $id;
    }

    public function get(): ?int
    {
        return $this->id;
    }
}
