<?php

namespace App\Repositories;

use App\Models\PasswordReset;
use App\Support\Traits\ListQueryTrait;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BaseRepository
{
    use ListQueryTrait;

    protected $model;
    protected $primaryKey;
    protected $query;
    protected $filters;
    protected $requiredRelations;
    protected $requiredRelationsCount;

    public function setModel($modelClass)
    {
        $this->model = new $modelClass();

        $this->primaryKey = $this->model->getKeyName();

        $this->checkPrimaryKey();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function first($options, $with = [])
    {
        $options = $this->prepareOptions($options);

        return $this->model->where($options)->with($with)->first();
    }

    public function update($options, array $data): void
    {
        $options = $this->prepareOptions($options);

        $this->model->where($options)->update($data);
    }

    public function delete($options): void
    {
        $options = $this->prepareOptions($options);

        $this->model->where($options)->delete();
    }

    public function updateOrCreate(array $options, array $data)
    {
        return $this->model->updateOrCreate($options, $data);
    }

    public function exists($options)
    {
        $options = $this->prepareOptions($options);

        return $this->model->where($options)->exists();
    }

    public function listQuery($filters)
    {
        $this->query = $this->model->query();

        if (Arr::has($filters, 'with')) {
            $this->query->with($this->requiredRelations);
        }

        if (Arr::has($filters, 'with_count')) {
            $this->query->withCount($this->requiredRelations);
        }

        $this->filters = $filters;

        return $this;
    }

    public function filterBy($field, $filterName = null)
    {
        $filterName = $filterName ?? $field;

        if (Arr::has($this->filters, $filterName)) {
            $this->addWhere($this->query, $field, $this->filters[$filterName]);
        }

        return $this;
    }

    public function filterByQuery(array $fields)
    {
        if (Arr::has($this->filters,'query')) {
            $this->query->where(function ($query) use ($fields) {
                foreach ($fields as $field) {
                    $this->addOrWhereByQuery($query, $field);
                }
            });
        }

        return $this;
    }

    public function getResults()
    {
        $this->orderBy();

        if (Arr::has($this->filters, 'all')) {
            return $this->query->get();
        }

        return $this->paginate();
    }

    protected function prepareOptions($options): array
    {
        return (is_array($options)) ? $options : [$this->primaryKey => $options];
    }

    protected function checkPrimaryKey()
    {
        if (is_null($this->primaryKey)) {
            $modelClass = get_class($this->model);

            throw new Exception("Model {$modelClass} must have primary key.");
        }
    }

    public function paginate()
    {
        $perPage = Arr::get($this->filters, 'per_page', 15);
        $page = Arr::get($this->filters, 'page', 1);

        return $this->query->paginate($perPage, ['*'], 'page', $page);
    }
}
