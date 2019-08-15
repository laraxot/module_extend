<?php
namespace Modules\Extend\Services;

use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;


class FullTextSearchEngine extends Engine{

	/**
     * Update the given model in the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function update($models)
    {
        //
    }

    /**
     * Remove the given model from the index.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function delete($models)
    {
        //
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @return mixed
     */
    public function search(Builder $builder)
    {
       	$result = [];
       	/*
       	if ($this->shouldNotRun($builder)) {
            $result['results'] = Collection::make();
            $result['count'] = 0;
            return $result;
        }
        */
        //https://arianacosta.com/php/laravel/tutorial-full-text-search-laravel-5/
        //// Full Text Index
    	//DB::statement('ALTER TABLE users ADD FULLTEXT fulltext_index (first_name, last_name, email)');
        $model=$builder->model;
        //$columns = implode(',',$model->toSearchableArray());
        $columns = \implode(', ', \array_keys($model->toSearchableArray())); // da scout
        //if($columns==''){
        //	$columns = \implode(', ',$model->getFillable()); 
        	// ricerco sui campi che posso inserire prob fare "intersezione con campi reali"
        	// tnt fa la ricerca sopra e poi passa a * percio' tengo commentato ma prob non mi serve
        //}
        if($columns==''){ // se vuoto cerco ovunque
        	$columns=$model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
        	$columns=\implode(', ',$columns);
        }

        $term=$builder->query;
        //ddd($columns);
        $query = $model::whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)" , $this->fullTextWildcards($term));
        //--- qui si potrebbe creare l'indice fulltext_index per accellerare

        if($builder->callback){
            $query = call_user_func($builder->callback, $query, $this);
        }
        $result['count'] = $query->count();
        if (property_exists($builder, 'orders') && !empty($builder->orders)) {
            foreach ($builder->orders as $order) {
                $query->orderBy($order['column'], $order['direction']);
            }
        }
        if ($builder->limit) {
            $query = $query->take($builder->limit);
        }
        if (property_exists($builder, 'offset') && $builder->offset) {
            $query = $query->skip($builder->offset);
        }
        $result['results'] = $query->get();
        return $result;
    }

    /**
     * Replaces spaces with full text search wildcards
     *
     * @param string $term
     * @return string
     */
    protected function fullTextWildcards($term)
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '<', '>', '@', '(', ')', '~'];
        $term = str_replace($reservedSymbols, '', $term);
 
        $words = explode(' ', $term);
 
        foreach($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if(strlen($word) >= 3) {
                $words[$key] = '+' . $word . '*';
            }
        }
 
        $searchTerm = implode( ' ', $words);
 
        return $searchTerm;
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  int  $perPage
     * @param  int  $page
     * @return mixed
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $builder->limit = $perPage;
        $builder->offset = ($perPage * $page) - $perPage;
        return $this->search($builder);
    }

    /**
     * Pluck and return the primary keys of the given results.
     *
     * @param  mixed  $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['results'])->map(function ($result) {
            return $result->getKey();
        });
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  \Laravel\Scout\Builder  $builder
     * @param  mixed  $results
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function map(Builder $builder, $results, $model)
    {
        //return Collection::make();
        return $results['results'];
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed  $results
     * @return int
     */
    public function getTotalCount($results)
    {
        //return count($results);
        return $results['count'];
    }

    /**
     * Flush all of the model's records from the engine.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function flush($model)
    {
        //$index = $this->algolia->initIndex($model->searchableAs());
        //$index->clearObjects();
    }

}//end 