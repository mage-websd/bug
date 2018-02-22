<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;
use Rikkei\Core\View\View;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class WorkExperience extends CoreModel
{
    
    use SoftDeletes;
    
    const KEY_CACHE = 'work_experience';

    protected $table = 'work_experiences';
    
    /**
     * type of work experience
     * [
     *   0 => TYPE_NORMAL
     *   1 => TYPE_JAPAN
     * ]
     */
    const TYPE_NORMAL = 0;
    const TYPE_JAPAN = 1;

    /**
     * save model work experience
     * 
     * @param int $employeeId
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
                if (! isset($experienceData['work_experience']) || ! $experienceData['work_experience']) {
                    continue;
                }
                $experienceData = $experienceData['work_experience'];
                
                if (isset($experienceData['id']) && $experienceData['id']) {
                    if ( ! $experience = self::find($experienceData['id'])) {
                        $experience = new self();
                    }
                    unset($experienceData['id']);
                } else {
                    $experience = new self();
                }
                $validator = Validator::make($experienceData, [
                    'company' => 'required|max:255',
                    'start_at' => 'required|max:255',
                    'position' => 'required|max:255',
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
                $experience->setData($experienceData);
                $experience->employee_id = $employeeId;
                $oldId = $experience->id;
                $experience->save();
                //update work_experience_id update experience
                if ($oldId !== $experience->id && $experience->id) {
                    ProjectExperience::where(['work_experience_id' => $oldId])->update(['work_experience_id' => $experience->id]);
                }
                $idExperienceIdsAdded[] = $experience->id;
            }
            //delete experience 
            self::where('employee_id', $employeeId)
                ->whereNotIn('id', $idExperienceIdsAdded)
                ->delete();
            CacheHelper::forget(self::KEY_CACHE);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get all school format json
     * 
     * @return string json
     */
    public static function getAllFormatJson()
    {
        if ($experiences = CacheHelper::get(self::KEY_CACHE)) {
            return $experiences;
        }
        $experiences = self::select('id', 'company', 'image')
            ->orderBy('company')->get();
        if (! count($experiences)) {
            return '[]';
        }
        $result = '[';
        foreach ($experiences as $experience) {
            $result .= '{';
            $result .= '"id": "' . $experience->id . '",';
            $result .= '"label": "' . $experience->company . '",';
            $result .= '"image": "' . View::getLinkImage($experience->image) . '"';
            $result .= '},';
        }
        $result = trim($result, ',');
        $result .= ']';
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
    
    /**
     * get work experience follow employee
     * 
     * @param type $employeeId
     * @return object model
     */
    public static function getItemsFollowEmployee($employeeId)
    {
        return self::select('company', 'position', 'start_at', 'end_at', 'id',
                'image', 'type' ,'address')
            ->where('employee_id', $employeeId)
            ->orderBy('company')
            ->get();
    }
    
    /**
     * check if item type is japan work
     * @return boolean
     */
    public function isJapan()
    {
        return $this->type == self::TYPE_JAPAN;
    }
}
