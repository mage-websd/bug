<?php

namespace Rikkei\Core\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Rikkei\Team\View\Permission;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Project\Model\ProjectMember;
use Illuminate\Support\Facades\Log;
use Exception;
use Rikkei\SlideShow\View\RunBgSlide;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Rikkei\Project\View\ProjDbHelp;
use Rikkei\News\View\ManageComment;
use Rikkei\News\Model\PostComment;
use Rikkei\CallApi\Helpers\Sonar;
use Rikkei\CallApi\Helpers\Jenkins;

class SettingSystemDataController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Setting System', URL::route('core::setting.system.data.index'));
        Menu::setActive(null, null, 'setting');
    }
    
    /**
     * list system data
     */
    public function index($type = null)
    {
        $isScopeCompany = Permission::getInstance()->isScopeCompany();
        $typesView = [
            'general' => 'General',
            'project' => 'Project',
            'resource' => 'Resource'
        ];
        if ($isScopeCompany) {
            $typesViewCompany = [
                'api' => 'Api',
            ];
        } else {
            $typesViewCompany = [];
        }
        $typesView = array_merge($typesView, $typesViewCompany);
        if (!$type || !in_array($type, array_keys($typesView))) {
            $type = 'general';
        }
        return view('core::system.data.index', [
            'isScopeCompany' => $isScopeCompany,
            'typeViewMain' => $type,
            'typesView' => $typesView
        ]);
    }
    
    /**
     * save system data
     */
    public function save()
    {
        $item = Input::get('item');
        $response = [];
        if (!$item) {
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Save success');
            return response()->json($response);
        }
        $fields = $this->fieldsAccessScopeCompany();
        $isScopeCompany = Permission::getInstance()->isScopeCompany();
        foreach ($item as $key => $value) {
            if (in_array($key, $fields) && !$isScopeCompany) {
                $response['error'] = 1;
                $response['message'] = Lang::get('core::message.You don\'t have access');
                return response()->json($response);
            }
            $item = CoreConfigData::getItem($key);
            if (!$item) {
                $item = new CoreConfigData();
                $item->key = $key;
            }
            if (is_array($value)) {
                $value = serialize($value);
            }
            $item->value = $value;
            $item->save();
        }
        //Update all comment if change setting to approve auto
        if ($item['key'] == CoreConfigData::AUTO_APPROVE_COMMNENT_KEY) {
            if ($item['value'] == CoreConfigData::AUTO_APPROVE) {
                if (Permission::getInstance()->isAllow('news::manage.comment.changeStatusAll')) {
                    $listId = PostComment::getAllIdCommentNotApprove();
                    ManageComment::updateCommentNotApproveToApprove($listId);
                }
            } 
        } 
        $response['success'] = 1;
        $response['message'] = Lang::get('core::message.Save success');
        if (Input::get('holidays')) {
            try {
                ProjDbHelp::incrementDWVersion();
                ProjectMember::flatAllResource();
            } catch (Exception $ex) {
                Log::info($ex);
            }
        }
        //check if need forget cache
        if ($keyCache = Input::get('key_config_cache')) {
            CacheHelper::forget($keyCache);
        }
        return response()->json($response);
    }
    
    /**
     * check connection
     * 
     * @param string $api
     */
    public function checkConnection($api)
    {
        switch ($api) {
            case 'gitlab':
                return \Rikkei\Project\View\ProjectGitlab::getInstance()->checkConnection();
            case 'redmine':
                return \Rikkei\Project\View\ProjectRedmine::getInstance()->checkConnection();
            case 'sonar':
                $connect = Sonar::isConnect();
                if ($connect === true) {
                    $response['success'] = 1;
                    $response['message'] = 'Connect sonar success';
                } else {
                    $response['error'] = 1;
                    $response['message'] = $connect;
                }
                return $response;
            case 'jenkins':
                $connect = Jenkins::isConnect();
                if ($connect === true) {
                    $response['success'] = 1;
                    $response['message'] = 'Connect jenkins success';
                } else {
                    $response['error'] = 1;
                    $response['message'] = $connect;
                }
                return $response;
            case 'jenkinscrumb':
                return Jenkins::getCrumb();
        }
        return [
            'error' => 1,
            'message' => Lang::get('core::message.Not found api')
        ];
    }
    
    /**
     * fields need access scope company to save
     * 
     * @return array
     */
    protected function fieldsAccessScopeCompany()
    {
        return [
            'project.redmine_api_url',
            'project.redmine_api_key',
            'project.gitlab_api_url',
            'project.gitlab_api_token',
            'project.account_to_email',
        ];
    }
    
    /**
     * delete email process queue
     */
    public function deleteEmailProcessQueue()
    {
        $response = [];
        EmailQueue::deleteProcessing();
        $response['success'] = 1;
        $response['message'] = Lang::get('core::message.Delete success');
        return response()->json($response);
    }
    
    /**
     * delete email process queue
     */
    public function deleteEmailQueueData()
    {
        $response = [];
        EmailQueue::truncate();
        $response['success'] = 1;
        $response['message'] = Lang::get('core::message.Delete data success');
        return response()->json($response);
    }
    
    /**
     * delete split timekeeping and fines employee
     */
    public function deleteTimekeepingProcess()
    {
        $isScopeCompany = Permission::getInstance()->isScopeCompany();
        if (!$isScopeCompany) {
            $response = [];
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.You don\'t have access');
            return response()->json($response);
        }
        $response = [];
        \Rikkei\Project\View\TimekeepingSplit::deleteProcessSplit();
        $response['success'] = 1;
        $response['message'] = Lang::get('core::message.Delete success');
        return response()->json($response);
    }
    
    /**
     * delete process queue
     */
    public function deleteProcessQueue($type)
    {
        $response = [];
        try {
            switch ($type) {
                case 'slide':
                    RunBgSlide::deleteProcess();
                    break;
            }
        } catch (Exception $ex) {
            $response['error'] = 1;
            $response['message'] = 
                Lang::get('core::message.Error system, please try later!');
            Log::info($ex);
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['message'] = Lang::get('core::message.Delete success');
        return response()->json($response);
    }
    
    /**
     * delete acl, permission trash
     */
    public function deleteAclDraft()
    {
        try {
            DB::delete('DELETE FROM `permissions` WHERE deleted_at IS NOT NULL;');
            DB::delete('DELETE FROM `permissions` WHERE action_id IN ('
                . 'SELECT id FROM `actions` WHERE deleted_at IS NOT NULL);');
            DB::delete('DELETE FROM `actions` WHERE deleted_at IS NOT NULL;');
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Delete action draft success');
            return response()->json($response);
        } catch(Exception $ex) {
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error system, please try later!');
            Log::info($ex);
            return response()->json($response);
        }
    }

    /**
     * clear cache
     */
    public function clearCache()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Clear cache success');
            return response()->json($response);
        } catch (Exception $ex) {
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error system, please try later!');
            Log::info($ex);
            return response()->json($response);
        }
    }
    
    /**
     * refresh version migration: menu, acl
     */
    public function refreshVersionSeeder()
    {
        try {
            DB::delete('DELETE FROM `migrations` WHERE migration LIKE "seed-MenuItemsSeeder%";');
            DB::delete('DELETE FROM `migrations` WHERE migration LIKE "seed-ActionSeeder%";');
            $response['success'] = 1;
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Success');
            return response()->json($response);
        } catch (Exception $ex) {
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error system, please try later!');
            Log::info($ex);
            return response()->json($response);
        }
    }
}