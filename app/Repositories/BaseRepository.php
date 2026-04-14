<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct($model)
    {
        $this->model = new $model();
    }

    public function findAll(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findBy(string $field, int|string $value): ?Collection
    {
        return $this->model->newQuery()->where($field, $value)->get();
    }

    public function create(array $data): ?Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(string|int $id, array $data): bool
    {
        return (bool)$this->model->newQuery()->find($id)->update($data);
    }

    public function findAllWithPaginate(int $page, ?int $count = null): Paginator
    {
        return $this->model->newQuery()->paginate(perPage: $count, page: $page);
    }

    public function remove(int|string $id): bool
    {
        return (bool)$this->model->newQuery()->find($id)?->delete();
    }

}
