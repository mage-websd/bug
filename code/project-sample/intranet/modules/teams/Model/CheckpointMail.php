<?php

namespace Rikkei\Team\Model;

use Illuminate\Database\Eloquent\Model;
use Rikkei\Core\View\CacheHelper;
use DB;
use Rikkei\Team\View\Permission as TeamPermission;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckpointMail extends CoreModel
{ 
    use SoftDeletes;
    
    protected $table = 'checkpoint_email';

    /**
    * check email be sended or not
    */ 
    public static function checkSended($employeeId, $checkpointId) 
    {
        return self::where('employee_id', '=', $employeeId)->where('checkpoint_id', '=', $checkpointId)->get()->count()>0;
    }

    /**
    * get all employee_id follow checkpoint_id
    */
    public static function getEmpIdfollowCheckpoint($checkpointId)
    {
        return self::where('checkpoint_id', '=', $checkpointId)->pluck('employee_id')->toArray();
    }

}
