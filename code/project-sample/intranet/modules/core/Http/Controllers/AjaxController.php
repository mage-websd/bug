<?php

namespace Rikkei\Core\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Rikkei\Team\View\Permission;
use Illuminate\Http\Request;
use Rikkei\Team\Model\School;
use Rikkei\Team\Model\Certificate;
use Rikkei\Team\Model\Skill;
use Rikkei\Team\Model\WorkExperience;

class AjaxController extends Controller
{
    /**
     * get skill data
     * 
     * @param Request $request
     * @return type
     */
    public function skillAutocomplete(Request $request)
    {
        if(! $request->ajax()){
            return redirect('/');
        }
        if (Auth::guest()) {
            exit;
        }
        if (! Permission::getInstance()->isAllow('team::team.member.edit.skill') &&
            ! Permission::getInstance()->isAllow('team::team.member.edit.exerience')
        ) {
            exit;
        }
        $result = [];
        $result['school'] = School::getAllFormatJson();
        $result['language'] = Certificate::getAllFormatJson(Certificate::TYPE_LANGUAGE);
        $result['cetificate'] = Certificate::getAllFormatJson(Certificate::TYPE_CETIFICATE);
        $result['program'] = Skill::getAllFormatJson(Skill::TYPE_PROGRAM);
        $result['database'] = Skill::getAllFormatJson(Skill::TYPE_DATABASE);
        $result['os'] = Skill::getAllFormatJson(Skill::TYPE_OS);
        $result['work_experience'] = WorkExperience::getAllFormatJson();
        echo \GuzzleHttp\json_encode($result);
        exit;
    }
}
