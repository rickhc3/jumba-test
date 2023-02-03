<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

abstract class Filter
{
    /**
     * The request instance.
     *
     * @var Request
     */
    public $request;

    /**
     * The builder instance.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Set the table
     *
     * @var String
     */
    public $table = "";

    /**
     * Initialize a new filter instance.
     *
     * @param Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    private function stringTransformInArray(string $value): array
    {
        $array = explode(';', $value);
        $arr = [];
        if(is_array($array) AND count($array) > 0) {
            foreach ($array as $e) {
                $item = explode(':', $e);
                if(is_array($item) AND count($item) > 1) {
                    $arr[] = ['value' => $item[1],'key' => $item[0]];
                }
            }
        }
        return $arr;
    }

    private function stringTransformInArrayRelationship(string $value)
    {
        $array = explode(':', $value);
        $arr = [];
        if(is_array($array) && isset($array[0]) && isset($array[1])){
            $arr['key'] = $array[0];
            $values = explode(';', $array[1]);
            if(is_array($values)){
                foreach ($values as $e) {
                    $arr['value'][] = $e;
                }
            }
        }
        return $arr;
    }

    private function separatorRelationship(string $value): array
    {
        $ex = explode('.', $value);
        if(is_array($ex) AND count($ex) === 2) {
            $arr['relationship'] = $ex[0];
            $arr['column'] = $ex[1];
            return $arr;
        }
        return [];
    }


    /**
     * Apply the filters on the builder.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->request->all() as $name => $value) {
            if (method_exists($this, $name)) {
                call_user_func_array([$this, $name], array_filter([$value]));
            }
        }

        return $this->builder;
    }

    /**
     * inject per page in request
     *
     * @return int
     */
    public function perPage(): int
    {
        return $this->request->get('perPage') ?? 100;
    }

    /**
     * Usage: sort[by]=name&sort[order]=asc
     *
     * @param array $value
     * @return Builder
     */
    public function sort(array $value = []): Builder
    {
        if (isset($value['by']) && !Schema::hasColumn($this->table, $value['by'])) {
            return $this->builder;
        }

        return $this->builder->orderBy(
            $value['by'] ?? 'created_at', $value['order'] ?? 'desc'
        );
    }

    /**
     * Method that returns a Builder with parameters through the term "search"
     * Usage: ?search=column_name1:value;column_name2:value
     *
     * @param string $value
     * @return Builder
     */
    public function search(string $value = ""): Builder
    {
        $value = $this->stringTransformInArray($value);
        foreach ($value as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->whereHas($relationship['relationship'], function($q) use ($relationship, $item) {
                    $q->where($relationship['column'], $item['value']);
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->where($item['key'], $item['value']);
            }
        }

        return $this->builder;
    }


    /**
     * Method that returns a Builder with parameters through the term "searchOr"
     * Usage: ?search=column_name1:value;column_name2:value
     *
     * @param string $value
     * @return Builder
     */
    public function searchOr(string $value = ""): Builder
    {
        foreach ($this->stringTransformInArray($value) as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->orWhereHas($relationship['relationship'], function(Builder $q) use ($relationship, $item) {
                    $q->orWhere($relationship['column'], $item['value']);
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->orWhere($item['key'], $item['value']);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "searchLike"
     * It use "like" as operator
     * Usage: ?searchLike=column_name1:value;column_name2:value
     *
     * @param string $value
     * @return Builder
     */
    public function searchLike(string $value = ""): Builder
    {
        foreach ($this->stringTransformInArray($value) as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->whereHas($relationship['relationship'], function(Builder $q) use ($relationship, $item) {
                    $q->where($relationship['column'], 'like', "%".$item['value']."%");
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->where($item['key'], 'like', "%".$item['value']."%");
            }
        }
        return $this->builder;
    }

    public function searchInRelationship(string $value = ""): Builder
    {
        $value = $this->stringTransformInArrayRelationship($value);
        if(!empty($value)) {
            $relationship = $this->separatorRelationship($value['key']);
            if(!empty($relationship)) {
                $this->builder->whereHas($relationship['relationship'], function($q) use ($relationship, $value) {
                    $q->whereIn($relationship['column'], $value['value']);
                });
            }
        }

        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "searchLike"
     * It use "like" as operator
     * Usage: ?searchUnLike=column_name1:value;column_name2:value
     *
     * @param string $value
     * @return Builder
     */
    public function searchUnLike(string $value = ""): Builder
    {
        foreach ($this->stringTransformInArray($value) as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->whereHas($relationship['relationship'], function(Builder $q) use ($relationship, $item) {
                    $q->where($relationship['column'], '<>', $item['value']);
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->where($item['key'], '<>', $item['value']);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "searchLikeOr"
     * It use "like" as operator
     * Usage: ?searchLikeOr=column_name1:value;column_name2:value
     *
     * @param string $value
     * @return Builder
     */
    public function searchLikeOr(string $value = ""): Builder
    {
        foreach ($this->stringTransformInArray($value) as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->orWhereHas($relationship['relationship'], function(Builder $q) use ($relationship, $item) {
                    $q->orWhere($relationship['column'], 'like', "%".$item['value']."%");
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->orWhere($item['key'], 'like', "%".$item['value']."%");
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "searchLikeWithStart"
     * It use "like" as operator
     * Ex: SELECT * FROM table WHERE value like value%
     * Usage: ?searchLikeWithStart[name_column]=value
     *
     * @param string $value
     * @return Builder
     */
    public function searchLikeWithStart(string $value = ""): Builder
    {
        foreach ($this->stringTransformInArray($value) as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->whereHas($relationship['relationship'], function(Builder $q) use ($relationship, $item) {
                    $q->where($relationship['column'], 'like', $item['value']."%");
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->where($item['key'], 'like', $item['value']."%");
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "searchLikeWithStart"
     * It use "like" as operator
     * Ex: SELECT * FROM table WHERE value like value%
     * Usage: ?searchLikeWithStart[name_column]=value
     *
     * @param string $value
     * @return Builder
     */
    public function searchLikeWithStartOr(string $value = ""): Builder
    {
        foreach ($this->stringTransformInArray($value) as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->orWhereHas($relationship['relationship'], function(Builder $q) use ($relationship, $item) {
                    $q->orWhere($relationship['column'], 'like', $item['value']."%");
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->orWhere($item['key'], 'like', $item['value']."%");
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "searchLikeWithEnd"
     * It use "like" as operator
     * Ex: SELECT * FROM table WHERE value like %value
     * Usage: ?searchLikeWithEnd[name_column]=value
     *
     * @param string $value
     * @return Builder
     */
    public function searchLikeWithEnd(string $value = ""): Builder
    {
        foreach ($this->stringTransformInArray($value) as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->whereHas($relationship['relationship'], function(Builder $q) use ($relationship, $item) {
                    $q->where($relationship['column'], 'like', "%".$item['value']);
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->where($item['key'], 'like', "%".$item['value']);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "searchLikeWithEnd"
     * It use "like" as operator
     * Ex: SELECT * FROM table WHERE value like %value
     * Usage: ?searchLikeWithEnd[name_column]=value
     *
     * @param string $value
     * @return Builder
     */
    public function searchLikeWithEndOr(string $value = ""): Builder
    {
        foreach ($this->stringTransformInArray($value) as $item) {
            $relationship = $this->separatorRelationship($item['key']);
            if(!empty($relationship)) {
                $this->builder->orWhereHas($relationship['relationship'], function(Builder $q) use ($relationship, $item) {
                    $q->orWhere($relationship['column'], 'like', "%".$item['value']);
                });
            }
            if (Schema::hasColumn($this->table, $item['key'])) {
                $this->builder->orWhere($item['key'], 'like', "%".$item['value']);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "with" bringing your relationships
     * Usage: ?with[]=relationship
     *
     * @param array $value = []
     * @return Builder
     */
    public function with(array $value = []): Builder
    {
        foreach ($value as $key => $item) {
            try {
                $this->builder->getRelation(explode('.', $item)[0]);
                $this->builder->with($item);
            } catch (\Exception $e) {
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "with" bringing your relationships
     * Usage: ?withDeep[vehicle]=relationship
     *
     * @param array $value = []
     * @return Builder
     */
    public function withDeep(array $value = []): Builder
    {
        foreach ($value as $key => $item) {
            try {
                $this->builder->getRelation($item);
                $this->builder->with("$key.$item");
            } catch (\Exception $e) {
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "greaterThan" bringing values greater than
     * Usage: ?greaterThan[column_name]=value
     *
     * @param array $value = []
     * @return Builder
     */
    public function greaterThan(array $value = []): Builder
    {
        foreach ($value as $key => $item) {
            if (Schema::hasColumn($this->table, $key)) {
                $this->builder->where($key, '>', $item);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "greaterThan" bringing values less than
     * Usage: ?lessThan[column_name]=value
     *
     * @param array $value = []
     * @return Builder
     */
    public function lessThan(array $value = []): Builder
    {
        foreach ($value as $key => $item) {
            if (Schema::hasColumn($this->table, $key)) {
                $this->builder->where($key, '<', $item);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "greaterThan" bringing values less than or equal
     * Usage: ?lessThanOrEqual[column_name]=value
     *
     * @param array $value = []
     * @return Builder
     */
    public function lessThanOrEqual(array $value = []): Builder
    {
        foreach ($value as $key => $item) {
            if (Schema::hasColumn($this->table, $key)) {
                $this->builder->where($key, '<=', $item);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "greaterThan" bringing values greater than or equal
     * Usage: ?greaterThanOrEqual[column_name]=value
     *
     * @param array $value = []
     * @return Builder
     */
    public function greaterThanOrEqual(array $value = []): Builder
    {
        foreach ($value as $key => $item) {
            if (Schema::hasColumn($this->table, $key)) {
                $this->builder->where($key, '>=', $item);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "isNull" bringing values that have value null
     * Usage: ?isNull=column
     *
     * @param array $value = []
     * @return Builder
     */
    public function isNull(array $value = []): Builder
    {
        foreach ($value as $item) {
            if (Schema::hasColumn($this->table, $item)) {
                $this->builder->whereNull($item);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "isNull" bringing values that have value null
     * Usage: ?isNull=column
     *
     * @param array $value = []
     * @return Builder
     */
    public function isNullOr(array $value = []): Builder
    {
        foreach ($value as $item) {
            if (Schema::hasColumn($this->table, $item)) {
                $this->builder->orWhereNull($item);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "isNotNull" bringing values that haven't value null
     * Usage: ?isNull=column
     *
     * @param array $value = []
     * @return Builder
     */
    public function isNotNull(array $value = []): Builder
    {
        foreach ($value as $item) {
            if (Schema::hasColumn($this->table, $item)) {
                $this->builder->whereNotNull($item);
            }
        }
        return $this->builder;
    }

    /**
     * Method that returns a Builder with parameters through the term "greaterThan" bringing values between
     * Usage: ?between[column_name]=value_1,value_2
     *
     * @param array $value = []
     * @return Builder
     */
    public function between(array $value = []): Builder
    {
        foreach ($value as $key => $item) {
            if (Schema::hasColumn($this->table, $key)) {
                $this->builder->whereBetween($key, explode(',', $item));
            }
        }
        return $this->builder;
    }
}
