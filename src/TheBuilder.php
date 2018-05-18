<?php
namespace JeffBeltran\Bob;

use Illuminate\Http\Request;
use app;

class TheBuilder
{
    private $model;
    private $result;
    private $request;
    private $isEloquent;
    private $resource_id;
    private $queryBuilder;
    private $filtersApplied = [];
    private $reservedBlueprints;

    public function __construct($modelClassName, $resource_id = false)
    {
        $this->request = request();
        $this->model = new $modelClassName;
        $this->resource_id = $resource_id;
        $this->isEloquent = false;
        $this->reservedBlueprints = collect([
            'with',
            'search',
            'limit'
        ]);
        $this->setQueryBuilderInstance();
        $this->orginizeBlueprints();
        $this->publishBlueprints();
    }

    /**
     * Getter for results
     *
     * @return \Illuminate\Support\Collection
     */
    public function getResults()
    {
        if ($this->request->has('session')) {
            if ($this->request->get('session') === "true") {
                $this->request->session()->put($this->getModelName() . "-filters", $this->filtersApplied);
            }
        }
        return $this->result;
    }

    /**
     * Returns list of applied filters... i was playing around with the idea of statemanement via sessions, but i'll
     * need to hank this as well i think...
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filtersApplied;
    }

    /**
     * Getter for model name used
     *
     * @return string
     */
    public function getModelName()
    {
        return kebab_case(class_basename($this->model));
    }

    /**
     * returns true if query builder is eloquent instance, if scout instance returns false
     *
     * @return boolean
     */
    public function isEloquent()
    {
        return $this->isEloquent;
    }

    /**
     * Set correct query builder instance
     */
    private function setQueryBuilderInstance()
    {
        // Laravel\Scout\Builder
        if ($this->request->has('search')) {
            $this->queryBuilder = $this->model::search($this->request->query('search'));
            $this->filtersApplied['search'] = [$this->request->query('search')];
        } else {
            // Illuminate\Database\Eloquent\Builder
            if ($this->resource_id) {
                $modelQuery = $this->model->newQuery();
                $this->queryBuilder = $modelQuery->where($this->model->getQualifiedKeyName(), '=', $this->resource_id);
            } else {
                $this->queryBuilder = $this->model->newQuery();
            }

            $this->isEloquent = true;
        }
    }

    /**
     * Applies all the filters to the query builder
     */
    private function orginizeBlueprints()
    {
        // filter out reserved blueprint names
        $requestedBluePrints = collect($this->request->all())->reject(function ($blueprintValue, $blueprint) {
            return $this->reservedBlueprints->contains($blueprint);
        });

        // apply each filter to query builder
        foreach ($requestedBluePrints as $blueprint => $blueprintValue) {
            $decorator = "App\\Blueprints" . '\\' . class_basename($this->model) . '\\' . studly_case($blueprint);
            if (class_exists($decorator)) {
                $this->queryBuilder = $decorator::apply($this->queryBuilder, $blueprintValue);
                $this->filtersApplied[$blueprint] = explode(',', $blueprintValue);
            }
        }
    }

    /**
     * Sets the result value for the query
     */
    private function publishBlueprints()
    {
        // Scout instances do not have eager loading so we much lazy load the query
        // to get relationships (if requested)
        if ($this->isEloquent) {
            if ($this->request->has('limit')) {
                $this->result = $this->queryBuilder->paginate($this->request->get('limit'));
                $this->filtersApplied['limit'] = [$this->request->get('limit')];
            } else {
                if ($this->resource_id) {
                    $this->result = $this->queryBuilder->firstOrFail();
                } else {
                    $this->result = $this->queryBuilder->get();
                }
            }
        } else {
            // apply pagination (if requested)
            if ($this->request->has('limit')) {
                $resultCollection = $this->queryBuilder->paginate($this->request->get('limit'));
                $this->filtersApplied['limit'] = [$this->request->get('limit')];
            } else {
                $resultCollection = $this->queryBuilder->get();
                $resultCollection = $resultCollection->values();
            }
            
            $this->result = $resultCollection;
        }

        // attach relationships
        if ($this->request->has('with')) {
            $this->filtersApplied['with'] = [$this->request->get('with')];
            $withArray = explode(',', $this->request->get('with'));
            $this->result->load($withArray);
        }
    }
}
