<?php

namespace Packages\QueryLogging\Contracts;

interface LastCreatedModelStoreInterface
{
    public function set(
        int $id
    ): void;

    public function get(): ?int;
}
