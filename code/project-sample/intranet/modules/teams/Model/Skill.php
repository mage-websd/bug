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

class Skill extends CoreModel
{
    
    use SoftDeletes;
    
    const KEY_CACHE = 'skills_skill';
    
    const TYPE_ALL = -1;
    const TYPE_PROGRAM = 1;
    const TYPE_DATABASE = 2;
    const TYPE_OS = 3;
    
    protected $table = 'skills';

    /**
     * save model skill
     * 
     * @param array $skills
     * @param int $type
     * @return array
     * @throws Exception
     */
    public static function saveItems($skills = [], $type = null)
    {
        if (! $skills) {
            return;
        }
        if (! $type) {
            $type = self::TYPE_PROGRAM;
        }
        $typeSkills = self::getAllType();
        $tblName = $typeSkills[$type];
        $skillIds = [];
        try {
            foreach ($skills as $key => $skillData) {
                if (! isset($skillData[$tblName]) || ! $skillData[$tblName]) {
                    continue;
                }
                $skillData = $skillData[$tblName];
                if (isset($skillData['id']) && $skillData['id']) {
                    if ( ($skill = self::find($skillData['id'])) &&
                        $skill->type == $type) {
                        $skillIds[$key] = $skillData['id'];
                    } else {
                        continue;
                    }
                    unset($skillData['id']);
                } elseif(isset($skillData['name']) && $skillData['name']) {
                    if ( ($skill = self::where('name', $skillData['name'])->first()) && ($skill->type == $type) ) {
                        $skillIds[$key] = $skill->id;
                    } else {
                        $skill = new self();
                    }
                } else {
                    $skill = new self();
                }
                $validator = Validator::make($skillData, [
                    'name' => 'required|max:255',
                ]);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->send();
                }
                if (isset($skillData['image_path']) && $skillData['image_path']) {
                    $skill->image = $skillData['image_path'];
                } elseif (isset($skillData['image']) && $skillData['image']) {
                    $urlEncode = preg_replace('/\//', '\/', URL::to('/'));
                    $image = preg_replace('/^' . $urlEncode . '/', '', $skillData['image']) ;
                    $image = trim($image, '/');
                    if (preg_match('/^' . Config::get('general.upload_folder') . '/', $image)) {
                        $skill->image = $image;
                    }
                }
                unset($skillData['image_path']);
                unset($skillData['image']);
                print_r($skillData);
                $skill->setData($skillData);
                $skill->type = $type;
                $skill->save();
                $skillIds[$key] = $skill->id;
            }
            CacheHelper::forget(self::KEY_CACHE);
            return $skillIds;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get all skills format json
     * 
     * @return string
     */
    public static function getAllFormatJson($type = null)
    {
        if (! $type) {
            $type = self::TYPE_PROGRAM;
        }
        if ($skills = CacheHelper::get(self::KEY_CACHE)) {
            return $skills;
        }
        $skills = self::select('id', 'name', 'image')
            ->where('type', $type)
            ->orderBy('name')
            ->get();
        if (! count($skills)) {
            return '[]';
        }
        $result = '[';
        foreach ($skills as $skill) {
            $result .= '{';
            $result .= '"id": "' . $skill->id . '",';
            $result .= '"label": "' . $skill->name . '",';
            $result .= '"image": "' . View::getLinkImage($skill->image) . '"';
            $result .= '},';
        }
        $result = trim($result, ',');
        $result .= ']';
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }
    
    /**
     * get all type skill
     * 
     * @return array
     */
    public static function getAllType()
    {
        return [
            self::TYPE_PROGRAM => 'program',
            self::TYPE_DATABASE => 'database',
            self::TYPE_OS => 'os'
        ];
    }
    
    /**
     * check skill is programming
     * 
     * @return boolean
     */
    public function isTypeProgramming()
    {
        return $this->type == self::TYPE_PROGRAM;
    }
    
    /**
     * check skill is database
     * 
     * @return boolean
     */
    public function isTypeDatabase()
    {
        return $this->type == self::TYPE_DATABASE;
    }
    
    /**
     * check skill is os
     * 
     * @return boolean
     */
    public function isTypeOs()
    {
        return $this->type == self::TYPE_OS;
    }

    /**
     * getLabel SkillType
     * @return array Description
     */
    public static function typeLabel()
    {
        return [
            self::TYPE_PROGRAM => trans('team::view.Programming Language'),
            self::TYPE_DATABASE => trans('team::view.Database'),
            self::TYPE_OS => trans('team::view.OS'),
        ];
    }
}
