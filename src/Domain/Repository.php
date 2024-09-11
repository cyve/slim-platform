<?php

namespace SlimPlatform\Domain;

interface Repository
{
    public function getAll(): array;

    public function get(int $id): object|null;

    public function has(int $id): bool;

    public function create(array $data): int;

    public function update(int $id, array $data): void;

    public function delete(int $id): void;
}
