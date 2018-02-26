<?php

namespace Rikkei\Core\Model;

use Illuminate\Support\Facades\Config;
use Rikkei\Core\View\CacheHelper;
use Exception;
use Lang;

class CoreConfigData  extends CoreModel
{
    
    const KEY_CACHE = 'core_config_data';
    const HR_KEY = 'hr.account_to_email';
    const AUTO_APPROVE_KEY = 'auto_approve';
    const AUTO_APPROVE_COMMNENT_KEY = 'auto_approve_comment';
    const AUTO_APPROVE = 1;
    const MANUAL_APPROVE = 2;
    
    protected $table = 'core_config_datas';
    
    /**
     * get config data
     * 
     * @param string $key
     * @return string
     */
    public static function get($key)
    {
        return Config::get($key);
    }
    
    /**
     * get config from db
     * 
     * @param string $key
     * @return string
     */
    public static function getValueDb($key)
    {
        if ($item = CacheHelper::get(self::KEY_CACHE, $key)) {
            return $item;
        }
        $item = self::select('value')
            ->where('key', $key)
            ->first();
        if ($item) {
            $item = $item->value;
            $item = trim($item);
        }
        CacheHelper::put(self::KEY_CACHE, $item, $key);
        return $item;
    }
    
    /**
     * get config from db
     * 
     * @param string $key
     * @return \self
     */
    public static function getItem($key)
    {
        $item = self::where('key', $key)
            ->first();
        if ($item) {
            return $item;
        }
        $item = new self;
        $item->key = $key;
        return $item;
    }
    
    /**
     * get time report on time
     * 
     * @return array
     */
    public static function getProjectReportYesTime()
    {
        $timeReport = self::get('project.report_yes');
        if (!$timeReport) {
            return null;
        }
        list($timeReportDay, $timeReportTime) = explode('_', $timeReport);
        if (!$timeReportDay || !$timeReportTime) {
            return null;
        }
        $timeReportDay = date('N', strtotime($timeReportDay));
        $timeReportTime  = date('H:i:s', strtotime($timeReportTime));
        return [$timeReportDay, $timeReportTime];
    }
    
    /**
     * get time report on time
     * 
     * @return array
     */
    public static function getProjectCheckReportInWeek()
    {
        $timeReport = self::get('project.check_report_in_week');
        if (!$timeReport) {
            return null;
        }
        list($timeReportDay, $timeReportTime) = explode('_', $timeReport);
        if (!$timeReportDay || !$timeReportTime) {
            return null;
        }
        $timeReportDay = date('N', strtotime($timeReportDay));
        $timeReportTime  = date('H:i:s', strtotime($timeReportTime));
        return [$timeReportDay, $timeReportTime];
    }
    
    /**
     * get qa account from file .env
     * 
     * @return string|null
     */
    public static function getQAAccount($type = 1)
    {
        $qa = self::getValueDb('project.account_qa');
        switch ($type) {
            case 2:
                return $qa;
            default:
                if (!$qa) {
                    return [];
                }
                $item = preg_replace('/\ /', '', $qa);
                $item = preg_split('/\r\n|\n|\r/', $item);
                return (array) $item;
        }
    }
    
    /**
     * get coo account from file .env
     * 
     * @return string|null
     */
    public static function getCOOAccount($type = 1)
    {
        $coo = self::getValueDb('project.account_coo');
        switch ($type) {
            case 2:
                return $coo;
            default:
                if (!$coo) {
                    return [];
                }
                $item = preg_replace('/\ /', '', $coo);
                $item = preg_split('/\r\n|\n|\r/', $item);
                return (array) $item;
        }
    }
    
    /**
     * get bod email
     * 
     * @return string|null
     */
    public static function getBodEmail()
    {
        return self::getValueDb('bod_email');
    }
    
    /**
     * get qa account from file .env
     * 
     * @return string|null
     */
    public static function getSQA($type = 1)
    {
        $qa = self::getValueDb('project.account_sqa');
        switch ($type) {
            case 2:
                return $qa;
            default:
                if (!$qa) {
                    return [];
                }
                $item = preg_replace('/\ /', '', $qa);
                $item = preg_split('/\r\n|\n|\r/', $item);
                return (array) $item;
        }
    }
    
    /**
     * get email address
     * @return array
     */
    public static function getEmailAddress()
    {
        return [
            'email' => self::get('mail.username'),
            'name' => self::get('mail.name')
        ];
    }
    
    /**
     * get remine api information
     * 
     * @return array
     */
    public static function getRemineApi()
    {
        $url = self::getValueDb('project.redmine_api_url');
        $key = self::getValueDb('project.redmine_api_key');
        return [
            'url' => $url,
            'key' => $key,
            'project_url' => self::getValueDb('project.redmine_api_project_url'),
            'issue_type_rejected' => 
                self::get('project.redmine_api.issue_type_rejected'),
            'issue_title_leakage' => 
                self::get('project.redmine_api.issue_title_leakage'),
            'issue_title_feature' => 
                self::get('project.redmine_api.issue_task_title_feature'),
            'issue_title_defect_reward' => 
                (array) self::get('project.redmine_api.issue_title_defect_reward'),
            'issue_title_bug_flag' => 
                self::get('project.redmine_api.issue_title_bug_flag')
        ];
    }
    
    /**
     * get number md = 1 mm
     * 
     * @return int
     */
    public static function getMMToMD()
    {
        return self::get('project.mm');
    }
    
    /**
     * get gitlab api information
     * 
     * @return array
     */
    public static function getGitlabApi()
    {
        $url = self::getValueDb('project.gitlab_api_url');
        $key = self::getValueDb('project.gitlab_api_token');
        $url = trim($url, '\/'); // url add api/v3
        $url .= '/';
        return [
            'url' => $url,
            'token' => $key,
            'project_url' => self::getValueDb('project.gitlab_api_project_url'),
        ];
    }
    
    /**
     * get holiday
     * 
     * @param string $string
     * @return array
     */
    protected static function getHoliday($string, $type = 1)
    {
        $result = [];
        $item = preg_split('/\;|\r\n|\n|\r/', $string);
        if (!$item) {
            return $result;
        }
        switch ($type) {
            case 2: //MM_DD
                $pattern = '[0-9]{2}-[0-9]{2}';
                break;
            default: // YYYY-MM-DD
                $pattern = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
                break;
        }
        foreach ($item as $value) {
            $value = trim($value);
            if (preg_match('/^' . $pattern .'$/', $value)) {
                $result[] = $value;
            }
        }
        return $result;
    }


    /**
     * get annual holidays
     * 
     * @return array
     */
    public static function getAnnualHolidays($type = 1)
    {
        switch ($type) {
            case 1: //type string
                return self::getValueDb('project.annual_holidays');
            default: //type array
               return self::getHoliday(self::getValueDb('project.annual_holidays'), 2) ;
        }
    }
    
    /**
     * get annua holidays
     * 
     * @return array
     */
    public static function getSpecialHolidays($type = 1)
    {
        switch ($type) {
            case 1: //type string
                return self::getValueDb('project.special_holidays');
            default: //type array
               return self::getHoliday(self::getValueDb('project.special_holidays'), 1) ;
        }
    }
    
    /**
     * rewrite save model
     * 
     * @param array $options
     * @return type
     * @throws Exception
     */
    public function save(array $options = array()) {
        try {
            CacheHelper::forget(self::KEY_CACHE, $this->key);
            $result = parent::save($options);
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get days to check get css after last deliver
     * 
     * @return int
     */
    public static function getCssAfterDeliver()
    {
        return self::get('project.css_after_deliver');
    }
    
    /**
     * get account to email
     *      ex: dungpt => dung.phan@rikeisoft.com
     * @return array
     */
    public static function getAccountToEmail($type = 1, $value = 'project.account_to_email')
    {
        $result = self::getValueDb($value);
        switch ($type) {
            case 1: 
                return $result;
            case 2:
                $items = preg_split('/\r\n|\n|\r/', $result);
                if (!$items || !count($items)) {
                    return [];
                }
                $result = [];
                foreach ($items as $item) {
                    if (!$item) {
                        continue;
                    }
                    $accountEmail = preg_split('/\=\>/', $item);
                    if (!$accountEmail || count($accountEmail) != 2) {
                        continue;
                    }
                    list($account, $email) = $accountEmail;
                    $account = strtolower(trim($account));
                    $email = strtolower(trim($email));
                    if ($account && $email) {
                        $result[$account] = $email;
                    }
                }
                return $result;
            default:
                return $result;
        }
    }
    
    /**
     * get account to email
     *      ex: dungpt => dung.phan@rikeisoft.com
     * @return array
     */
    public static function getCssMail($type = 1)
    {
        $result = self::getValueDb('cssmail');
        switch ($type) {
            case 1: 
                return $result;
            case 2:
                return explode(',', $result);
            default:
                return $result;
        }
    }
    
    /**
     * get layout email account
     */
    public static function getEmailLayout()
    {
        return (int) self::getValueDb('core.email.layout');
    }
    
    /**
     * get emails sent email custom
     */
    public static function getEmailSentCustom($type = 1)
    {
        $result = self::getValueDb('email_sent_custom');
        switch ($type) {
            case 2:
                $items = preg_split('/\r\n|\n|\r/', $result);
                if (!$items || !count($items)) {
                    return [];
                }
                $result = [];
                foreach ($items as $item) {
                    $item = trim($item);
                    $item = preg_split('/\,/', $item);
                    if (!$item || count($item) != 4) {
                        continue;
                    }
                    $result[$item[0]] = $item;
                }
                return $result;
            default:
                return $result;
        }
    }
    
    /**
     * option auto
     * 
     * @return array
     */
    public static function autoOptions() {
        return [
            self::AUTO_APPROVE => Lang::get('core::view.Automatic approve'),
            self::MANUAL_APPROVE => Lang::get('core::view.Manual approve'),
        ];
    }
    
    /**
     * get video default
     * 
     * @return string
     */
    public static function getVideoDefault()
    {
        return self::get('slide_show.video_default');
    }

    /**
     * get size image preview detail
     * 
     * @return string
     */
    public static function getSizeImagePreviewDetail()
    {
        return self::get('slide_show.size_image_preview_detail');
    }

    /**
     * get size image validate
     * 
     * @return string
     */
    public static function getSizeImageValidate()
    {
        return self::get('slide_show.size_image_validate');
    }
    /**
     * get size image show
     * 
     * @return string
     */
    public static function getSizeImageShow()
    {
        return self::get('slide_show.size_image_show');
    }
    
    /**
     * get value convert to array email
     */
    public static function getValueToArrEmail($key)
    {
        $result = self::getValueDb($key);
        if (!$result) {
            return [];
        }
        $items = explode(',', $result);
        if (!$items || !count($items)) {
            return [];
        }
        $results = [];
        foreach ($items as $item) {
            $item = trim($item);
            if ($item) {
                $results[] = $item;
            }
        }
        return $results;
    }
    
    /**
     * delete config data by key
     * @param type $key
     */
    public static function delByKey($key)
    {
        return self::where('key', $key)
                ->delete();
    }

    /**
     * save data
     * @param type $key
     * @param type $value
     */
    public static function saveItem($key, $value)
    {
        $item = self::where('key', $key)->first();
        if (!$item) {
            $item = new self();
            $item->key = $key;
        }
        $item->value = $value;
        return $item->save();
    }
}
