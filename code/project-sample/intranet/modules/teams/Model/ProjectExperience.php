<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class ProjectExperience extends CoreModel
{
    
    use SoftDeletes;
    
    const KEY_CACHE = 'project_experience';

    protected $table = 'proj_experiences';
    
    /**
     * save model school
     * 
     * @param array $employeeId
     * @param array $experiences
     * @return array
     * @throws Exception
     */
    public static function saveItems($employeeId, $experiences = []) 
    {
        if (! $employeeId) {
            return;
        }
        try {
            $idExperienceIdsAdded = [];
            foreach ($experiences as $experienceData) {
                if (! isset($experienceData['project_experience']) || ! $experienceData['project_experience']) {
                    continue;
                }
                $experienceData = $experienceData['project_experience'];
                
                if (isset($experienceData['id']) && $experienceData['id']) {
                    if ( ! $experience = self::find($experienceData['id'])) {
                        $experience = new self();
                    }
                    unset($experienceData['id']);
                } else {
                    $experience = new self();
                }
                $validator = Validator::make($experienceData, [
                    'name' => 'required|max:255',
                    'enviroment_language' => 'required|max:255',
                    'enviroment_enviroment' => 'required|max:255',
                    'enviroment_os' => 'required|max:255',
                    'responsible' => 'required',
                    'start_at' => 'required|max:255',
                    'end_at' => 'required|max:255',
                    
                ]);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->send();
                }
                if (isset($experienceData['image_path']) && $experienceData['image_path']) {
                        $experience->image = $experienceData['image_path'];
                } else if (isset($experienceData['image']) && $experienceData['image']) {
                    $urlEncode = preg_replace('/\//', '\/', URL::to('/'));
                    $image = preg_replace('/^' . $urlEncode . '/', '', $experienceData['image']) ;
                    $image = trim($image, '/');
                    if (preg_match('/^' . Config::get('general.upload_folder') . '/', $image)) {
                        $experience->image = $image;
                    }
                }
                unset($experienceData['image_path']);
                unset($experienceData['image']);
                
                //get environment group
                $experienceEnvironment = self::getEnvironmentGroupData($experienceData);
                $experienceEnvironment = serialize($experienceEnvironment);
                $experience->setData($experienceData);
                $experience->employee_id = $employeeId;
                $experience->enviroment = $experienceEnvironment;
                $experience->save();
                $idExperienceIdsAdded[] = $experience->id;
            }
            //delete experience 
            $delete = self::where('employee_id', $employeeId)
                ->whereNotIn('id', $idExperienceIdsAdded);
            $delete->delete();
            CacheHelper::forget(self::KEY_CACHE);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get work experience follow employee
     * 
     * @param type $employeeId
     * @return object model
     */
    public static function getItemsFollowEmployee($employeeId)
    {
        $thisTbl = self::getTableName();
        
        return self::select("{$thisTbl}.id", "{$thisTbl}.name", "{$thisTbl}.start_at", "{$thisTbl}.end_at", "{$thisTbl}.enviroment",
                "{$thisTbl}.image", "{$thisTbl}.responsible", "{$thisTbl}.customer_name", "{$thisTbl}.description", 
                "{$thisTbl}.poisition", "{$thisTbl}.no_member", "{$thisTbl}.other_tech", "{$thisTbl}.work_experience_id")
            ->where('employee_id', $employeeId)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * get environment group data
     * 
     * @param type $experienceData
     * @return array
     */
    protected static function getEnvironmentGroupData(&$experienceData = [])
    {
        $result = [];
        if (! $experienceData) {
            return $result;
        }
        $flag = 'enviroment';
        foreach ($experienceData as $key => $value) {
            if (preg_match('/^' . $flag . '_/', $key)) {
                $result[preg_replace('/^' . $flag . '_/', '', $key)] = $value;
                unset($experienceData[$key]);
            }
        }
        return $result;
    }
    
    /**
     * return environment data
     * 
     * @param string|null $key
     * @return array|string
     */
    public function getEnvironment($key = null)
    {
        if (! ($enviroment = $this->enviroment)) {
            return;
        }
        $enviroment = unserialize($enviroment);
        if (! $key) {
            return $enviroment;
        }
        if (isset($enviroment[$key])) {
            return $enviroment[$key];
        }
        return ;
    }
    
    /**
     * 
     * @param int $employeeId
     * @param int $experienceId
     * @return Collection
     */
    public static function getItemsFollowEmployeeAndExperience($employeeId, $workId)
    {
        $thisTbl = self::getTableName();
        
        return self::select("{$thisTbl}.id", "{$thisTbl}.name", "{$thisTbl}.start_at", "{$thisTbl}.end_at", "{$thisTbl}.enviroment",
                "{$thisTbl}.image", "{$thisTbl}.responsible", "{$thisTbl}.customer_name", "{$thisTbl}.description", 
                "{$thisTbl}.poisition", "{$thisTbl}.no_member", "{$thisTbl}.other_tech", "{$thisTbl}.work_experience_id")
                ->where(["{$thisTbl}.employee_id" => $employeeId, "{$thisTbl}.work_experience_id" => $workId])
                ->orderby("{$thisTbl}.name")
                ->get();
    }
    
    /**
     * get list list item follow employeeId and array workExperienceIds
     * @param int $employeeId
     * @param array $workIds
     * @return Collection
     */
    public static function getItemsFollowExperience($employeeId, $workIds = array())
    {
        $thisTbl = self::getTableName();
        
        return self::select("{$thisTbl}.id", "{$thisTbl}.name", "{$thisTbl}.start_at", "{$thisTbl}.end_at", "{$thisTbl}.enviroment",
                "{$thisTbl}.image", "{$thisTbl}.responsible", "{$thisTbl}.customer_name", "{$thisTbl}.description", 
                "{$thisTbl}.poisition", "{$thisTbl}.no_member", "{$thisTbl}.other_tech", "{$thisTbl}.work_experience_id")
                ->where(["{$thisTbl}.employee_id" => $employeeId])
                ->whereIn("{$thisTbl}.work_experience_id", $workIds)
                ->orderBy("{$thisTbl}.work_experience_id")
                ->orderby("{$thisTbl}.name")
                ->get();
    }
    
    /**
     * get nice string technicals
     * @return string
     */
    public function getTechnical()
    {
        $key = [
            'language' =>  trans('team::view.Language'),
            'enviroment' => trans('team::view.Environment'),
            'os'    => trans('team::view.OS'),
        ];
        $str = '';
        foreach($key as $i => $v) {
            $str .= "- " . $v . " : ";
            $str .= $this->getEnvironment($i) . "<br>";
        }
        $str .= "- " . $this->other_tech;
        return $str;
    }
}
