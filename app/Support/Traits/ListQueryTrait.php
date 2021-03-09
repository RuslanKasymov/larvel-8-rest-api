<?php

namespace App\Support\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Prophecy\Exception\Doubler\MethodNotFoundException;

trait ListQueryTrait
{
    public function orderBy($default = null, $defaultDesc = false)
    {
        $default = $default ?? $this->primaryKey;

        $orderField = Arr::get($this->filters, 'order_by', $default);
        $isDesc = Arr::get($this->filters, 'desc', $defaultDesc);

        if (Str::contains($orderField, '.')) {
            $this->query->orderByRelated($orderField, $this->getDesc($isDesc));
        } else {
            $this->query->orderBy($orderField, $this->getDesc($isDesc));
        }

        if ($orderField != $default) {
            $this->query->orderBy($default, $this->getDesc($defaultDesc));
        }

        return $this;
    }

    protected function getQuerySearchCallback($field)
    {
        return function ($query) use ($field) {
            $loweredQuery = mb_strtolower($this->filters['query']);
            $field = DB::raw("lower({$field})");

            $query->orWhere($field, 'like', "%{$loweredQuery}%");
        };
    }

    protected function addWhere(&$query, $field, $value, $sign = '=')
    {
        if (Str::contains($field, '.')) {
            $entities = explode('.', $field);
            $shiftedRelation = array_shift($entities);
            $relations = implode('.', $entities);

            $query->whereHas($shiftedRelation, function ($q) use ($relations, $value, $sign) {
                $this->addWhere($q, $relations, $value, $sign);
            });
        } else {
            $query->where($field, $sign, $value);
        }
    }

    private function addOrWhereByQuery(&$query, $field)
    {
        if (Str::contains($field, '.')) {
            $entities = explode('.', $field);
            $fieldName = array_shift($entities);
            $relations = implode('.', $entities);

            $query->orWhereHas($relations, function ($q) use ($fieldName) {
                $this->addWhereByQuery($q, $fieldName);
            });
        } else {
            $query->orWhere(
                $this->getQuerySearchCallback($field)
            );
        }
    }

    private function addWhereByQuery(&$query, $field)
    {
        if (Str::contains($field, '.')) {
            $entities = explode('.', $field);
            $shiftedRelation = array_shift($entities);
            $relations = implode('.', $entities);

            //check has relation

            $query->whereHas($shiftedRelation, function ($q) use ($relations) {
                $q->addWhereByQuery($q, $relations);
            });
        } else {
            $query->where($this->getQuerySearchCallback($field));
        }
    }

    protected function getDesc($isDesc)
    {
        return $isDesc ? 'DESC' : 'ASC';
    }

}
