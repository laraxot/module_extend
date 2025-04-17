<?php
namespace Modules\Extend\Repositories;

//---base
use Modules\Xot\Repositories\XotBaseRepository;

class ExtendRepository extends XotBaseRepository{
    /**
     * Specify Model class name
     *
     * @return string
     */
    protected $model = 'Modules\Extend\Models\Extend';
}