<?php

namespace ChrisLentz\LaravelApiSorting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

trait ApiSortTrait
{
    protected $allowed_sort_keys;

    public function scopeApiSort(Builder $query, array $allowed_sort_keys = null, string $default_sort_key = null)
    {
        $this->allowed_sort_keys = $allowed_sort_keys;

        if (!is_null($this->allowed_sort_keys)) {
            $params_url_array = [];
            $params_default_array = [];

            if (Request::has('_sort')) {
                $params_url_array = explode(',', Request::get('_sort'));
            }
            
            if (!is_null($default_sort_key)) {
                $params_default_array = explode(',', $default_sort_key);
            }

            $sort_array = $this->convertParamsArrayToSortArray($params_url_array);

            if (empty($sort_array) && !empty($params_default_array)) {
                $sort_array = $this->convertParamsArrayToSortArray($params_default_array);
            }

            foreach ($sort_array as $sort) {
                $query->orderBy($sort['column'], $sort['order']);
            }
        }

        return $query;
    }

    protected function convertParamsArrayToSortArray($params_array) {
        $sort_array = [];

        foreach ($params_array as $param) {
            if (strrpos($param, '-') === 0) {
                $param = substr($param, 1);

                $sort_array[$param] = ['column' => $param, 'order' => 'desc'];
            }
            else {
                $sort_array[$param] = ['column' => $param, 'order' => 'asc'];
            }
        }

        foreach ($sort_array as $k => $v) {
            if (!array_key_exists($k, $this->allowed_sort_keys) && !in_array($k, $this->allowed_sort_keys)) {
                unset($sort_array[$k]);
            }

            if (array_key_exists($k, $this->allowed_sort_keys)) {
                $sort_array[$k]['column'] = $this->allowed_sort_keys[$k];
            }
        }

        return $sort_array;
    }
}
