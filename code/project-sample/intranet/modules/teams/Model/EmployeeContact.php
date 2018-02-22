<?php

namespace Rikkei\Team\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Exception;

class EmployeeContact extends EmployeeItemRelate
{
    protected $table = 'employee_contact';

    /**
     * save employee contact follow employeeId
     * @param type $employeeId
     * @param type $data
     * @return type
     * @throws Exception
     */
    public static function saveItems($employeeId ,$data= [])
    {
        
        if(! $data ) {
            return;
        }
        try {
            $model = self::where('employee_id', $employeeId)
                    ->first();
            if(!$model){
                $model = new self;
            }
            $data['employee_id']  = $employeeId;

            $validator = Validator::make($data, [
                'employee_id' => 'required|integer',
                'other_email' => 'max:100|email',
                'personal_email' => 'max:100|email',
            ]);
            $model->setData($data);

            if ($validator->fails()) {
                return redirect()->route('team::member.profile.index')
                    ->withErrors($validator)->send();
            }

            return $model->save();
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get list relationship
     * @return array
     */
    public static function toOptionRelationship()
    {
        $relations = [
                0   => Lang::get('team::profile.Grandfather'),
                1   => Lang::get('team::profile.Grandmother'),
                2   => Lang::get('team::profile.Father'),
                3   => Lang::get('team::profile.Mother'),
                4   => Lang::get('team::profile.Wife'),
                5   => Lang::get('team::profile.Elder Brother'),
                6   => Lang::get('team::profile.Elder Sister'),
                7   => Lang::get('team::profile.Brother'),
                8   => Lang::get('team::profile.Sister'),
                9   => Lang::get('team::profile.Son'),
                10  => Lang::get('team::profile.Daughter'),
                11  => Lang::get('team::profile.Mother in law'),
                12  => Lang::get('team::profile.Other'),
        ];
        
        return $relations;
    }
    
    /**
     * get Social address (skype , yahoo)
     * @return string $str
     */
    public function getSocial()
    {
        $str = "";
        $yahoo = $this->yahoo;
        $skype = $this->skype;
        $str = $yahoo ? 'Yahoo : '. $yahoo : "";
        $str .= $skype ? "<br> Skype : " . $skype : "";        
        
        return $str;
    }
    
    /**
     * get string contact address
     * @return string $str
     */
    public function getContactAddress()
    {
        return $this->native_addr ? $this->native_addr : $this->tempo_addr;
    }
}
