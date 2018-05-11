<?php
namespace JeffBeltran\Bob;

use Illuminate\Http\Request;
use app;

class TheBuilder
{
    private $request;
    private $model;
    private $resource_id;
    private $queryBuilder;
    private $isEloquent;
    private $result;
    private $filtersApplied = [];

    public function __construct($modelClassName, $resource_id = false)
    {
        $this->request = request();
        $this->model = new $modelClassName;
        $this->isEloquent = false;
        $this->resource_id = $resource_id;
        $this->setQueryBuilderInstance();
        $this->applyDecorators();
        $this->saveQueryBuilder();
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

    public function getAPIResource()
    {
        $api_resource = [
            'data' => $this->result
        ];
        if ($this->request->has('meta')) {
            if ($this->request->get('meta')) {
                $api_resource['meta'] = [$this->getModelName() . "-filters" => $this->filtersApplied];
            }
        }
        return $api_resource;
    }

    public function getFilters()
    {
        return $this->filtersApplied;
    }

    public function getModelName()
    {
        return kebab_case(class_basename($this->model));
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
    private function applyDecorators()
    {
        $className = class_basename($this->model);
        // apply each filter to query builder
        foreach ($this->request->all() as $filterName => $filterValue) {
            $decorator = "App\\Blueprints" . '\\' . $className . '\\' . studly_case($filterName);
            if (class_exists($decorator)) {
                $this->queryBuilder = $decorator::apply($this->queryBuilder, $filterValue);
                $this->filtersApplied[$filterName] = explode(',', $filterValue);
            }
        }
    }

    /**
     * Sets the result value for the query
     */
    private function saveQueryBuilder()
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

            if ($this->request->has('with')) {
                $withArray = explode(',', $this->request->query('with'));
                $resultCollection->load($withArray);
                $this->result = $resultCollection;
            } else {
                $this->result = $resultCollection;
            }
        }
    }
}
