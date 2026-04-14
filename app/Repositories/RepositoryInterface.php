<?php

namespace App\Repositories;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


interface RepositoryInterface
{
    public function findAll(): Collection;

    public function find(string|int $id): ?Model;

    public function findBy(string $field, string|int $value): ?Collection;

    public function create(array $data): ?Model;

    public function update(string|int $id, array $data): bool;

    public function findAllWithPaginate(int $page, ?int $count = null): Paginator;

    public function remove(string|int $id): bool;

}
