<?php

namespace SmartPay\Framework\Database\Eloquent\Relation;

use SmartPay\Framework\Database\Eloquent\Model;
use SmartPay\Framework\Database\Eloquent\ModelQueryBuilder;
use SmartPay\Framework\Database\Eloquent\ModelCollection;
use SmartPay\Framework\Database\QueryBuilder\Expression;
use SmartPay\Framework\Database\Eloquent\ModelNotFoundException;
use SmartPay\Framework\Database\Eloquent\Relation\Relation;

class HasManyThrough extends Relation
{
    /**
     * The distance parent model instance.
     *
     * @var \SmartPay\Framework\Database\Eloquent\Model
     */
    protected $farParent;

    /**
     * The near key on the relationship.
     *
     * @var string
     */
    protected $firstKey;

    /**
     * The far key on the relationship.
     *
     * @var string
     */
    protected $secondKey;

    /**
     * The local key on the relationship.
     *
     * @var string
     */
    protected $localKey;

    /**
     * Create a new has many through relationship instance.
     *
     * @param  \SmartPay\Framework\Database\Eloquent\ModelQueryBuilder  $query
     * @param  \SmartPay\Framework\Database\Eloquent\Model  $farParent
     * @param  \SmartPay\Framework\Database\Eloquent\Model  $parent
     * @param  string  $firstKey
     * @param  string  $secondKey
     * @param  string  $localKey
     * @return void
     */
    public function __construct(ModelQueryBuilder $query, Model $farParent, Model $parent, $firstKey, $secondKey, $localKey)
    {
        $this->localKey = $localKey;
        $this->firstKey = $firstKey;
        $this->secondKey = $secondKey;
        $this->farParent = $farParent;

        parent::__construct($query, $parent);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        $parentTable = $this->parent->getTable();

        $localValue = $this->farParent[$this->localKey];

        $this->setJoin();

        if (static::$constraints) {
            $this->query->where($parentTable . '.' . $this->firstKey, '=', $localValue);
        }
    }

    /**
     * Add the constraints for a relationship query.
     *
     * @param  \SmartPay\Framework\Database\Eloquent\ModelQueryBuilder  $query
     * @param  \SmartPay\Framework\Database\Eloquent\ModelQueryBuilder  $parent
     * @param  array|mixed  $columns
     * @return \SmartPay\Framework\Database\Eloquent\ModelQueryBuilder
     */
    public function getRelationQuery(ModelQueryBuilder $query, ModelQueryBuilder $parent, $columns = ['*'])
    {
        $parentTable = $this->parent->getTable();

        $this->setJoin($query);

        $query->select($columns);

        $key = $this->wrap($parentTable . '.' . $this->firstKey);

        return $query->where($this->getHasCompareKey(), '=', new Expression($key));
    }

    /**
     * Set the join clause on the query.
     *
     * @param  \SmartPay\Framework\Database\Eloquent\ModelQueryBuilder|null  $query
     * @return void
     */
    protected function setJoin(ModelQueryBuilder $query = null)
    {
        $query = $query ?: $this->query;

        $foreignKey = $this->related->getTable() . '.' . $this->secondKey;

        $query->join($this->parent->getTable(), $this->getQualifiedParentKeyName(), '=', $foreignKey);

        if ($this->parentSoftDeletes()) {
            $query->whereNull($this->parent->getQualifiedDeletedAtColumn());
        }
    }

    /**
     * Determine whether close parent of the relation uses Soft Deletes.
     *
     * @return bool
     */
    public function parentSoftDeletes()
    {
        //
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $table = $this->parent->getTable();

        $this->query->whereIn($table . '.' . $this->firstKey, $this->getKeys($models));
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \SmartPay\Framework\Database\Eloquent\ModelCollection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, ModelCollection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have the dictionary we can simply spin through the parent models to
        // link them up with their children using the keyed dictionary to make the
        // matching very convenient and easy work. Then we'll just return them.
        foreach ($models as $model) {
            $key = $model->getKey();

            if (isset($dictionary[$key])) {
                $value = $this->related->newCollection($dictionary[$key]);

                $model->setRelation($relation, $value);
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param  \SmartPay\Framework\Database\Eloquent\ModelCollection  $results
     * @return array
     */
    protected function buildDictionary(ModelCollection $results)
    {
        $dictionary = [];

        $foreign = $this->firstKey;

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $result) {
            $dictionary[$result->{$foreign}][] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->get();
    }

    /**
     * Execute the query and get the first related model.
     *
     * @param  array   $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->take(1)->get($columns);

        return count($results) > 0 ? $results->first() : null;
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array  $columns
     * @return \SmartPay\Framework\Database\Eloquent\Model|static
     *
     * @throws \SmartPay\Framework\Database\Eloquent\ModelNotFoundException
     */
    public function firstOrFail($columns = ['*'])
    {
        if (!is_null($model = $this->first($columns))) {
            return $model;
        }
	    $modelClass = get_class($this->parent);
	    $exception = new ModelNotFoundException();
	    $exception->setModel($modelClass);
		throw $exception;
    }

    /**
     * Find a related model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \SmartPay\Framework\Database\Eloquent\Model|\SmartPay\Framework\Database\Eloquent\ModelCollection|null
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id)) {
            return $this->findMany($id, $columns);
        }

        $this->where($this->getRelated()->getQualifiedKeyName(), '=', $id);

        return $this->first($columns);
    }

    /**
     * Find multiple related models by their primary keys.
     *
     * @param  mixed  $ids
     * @param  array  $columns
     * @return \SmartPay\Framework\Database\Eloquent\ModelCollection
     */
    public function findMany($ids, $columns = ['*'])
    {
        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }

        $this->whereIn($this->getRelated()->getQualifiedKeyName(), $ids);

        return $this->get($columns);
    }

    /**
     * Find a related model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \SmartPay\Framework\Database\Eloquent\Model|\SmartPay\Framework\Database\Eloquent\ModelCollection
     *
     * @throws \SmartPay\Framework\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        if (is_array($id)) {
            if (count($result) == count(array_unique($id))) {
                return $result;
            }
        } elseif (!is_null($result)) {
            return $result;
        }

		$modelClass = get_class($this->parent);
		$exception = new ModelNotFoundException();
		$exception->setModel($modelClass);
		throw $exception;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @return \SmartPay\Framework\Database\Eloquent\ModelCollection
     */
    public function get()
    {
        $models = $this->getModels();

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
        if (count($models) > 0 && $this->hasEagerLoads()) {
            $models = $this->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }

    /**
     * Set the select clause for the relation query.
     *
     * @param  array  $columns
     * @return array
     */
    protected function getSelectColumns(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        return array_merge($columns, [$this->parent->getTable() . '.' . $this->firstKey]);
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int  $page
     * @return \SmartPay\Framework\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->getSelectColumns($columns));

        return $this->query->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @return \SmartPay\Framework\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page')
    {
        $this->query->addSelect($this->getSelectColumns($columns));

        return $this->query->simplePaginate($perPage, $columns, $pageName);
    }

    /**
     * Get the key for comparing against the parent key in "has" query.
     *
     * @return string
     */
    public function getHasCompareKey()
    {
        return $this->farParent->getQualifiedKeyName();
    }

    /**
     * Get the qualified foreign key on the related model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->related->getTable() . '.' . $this->secondKey;
    }

    /**
     * Get the qualified foreign key on the "through" model.
     *
     * @return string
     */
    public function getThroughKey()
    {
        return $this->parent->getTable() . '.' . $this->firstKey;
    }
}
