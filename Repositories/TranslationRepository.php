<?php
namespace Modules\Extend\Repositories;

//---base
use Modules\Xot\Repositories\XotBaseRepository;

class TranslationRepository extends XotBaseRepository{
    /**
     * Specify Model class name
     *
     * @return string
     */
    protected $model = 'Modules\Extend\Models\Translation';
}