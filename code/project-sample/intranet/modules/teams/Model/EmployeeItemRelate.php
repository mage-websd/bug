<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\View\CacheHelper;

class EmployeeItemRelate extends CoreModel
{
    use SoftDeletes;

    protected $primaryKey = 'employee_id';

    /**
     * rewrite method save model
     *
     * @param array $options
     * @return type
     */
    public function save(array $options = [])
    {
        CacheHelper::forget(Employee::KEY_CACHE, $this->id);
        return parent::save($options);
    }

    /**
     * override delete method
     */
    public function delete()
    {
        CacheHelper::forget(Employee::KEY_CACHE, $this->id);
        parent::delete();
    }
}
