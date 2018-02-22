<?php

namespace Rikkei\Core\View;

use DateTime;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Exception;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * View ouput gender
 */
class View
{
    /**
     * get date from datetime standard
     * 
     * @param type $datetime
     * @return string
     */
    public static function getDate($datetime)
    {
        return ($datetime !== '0000-00-00 00:00:00' && $datetime) ? self::formatDateTime('Y-m-d H:i:s', 'Y-m-d', $datetime) : '';
    }

    /**
     * get date from date standard
     * 
     * @param string $date
     * @return string
     */
    public static function getOnlyDate($date)
    {
        return ($date !== '0000-00-00 00:00:00' && $date !== '0000-00-00' && $date) ? Carbon::parse($date) : '';
    }

    /**
     * format datetime
     * 
     * @param string $formatFrom
     * @param string $formatTo
     * @param string $datetime
     * @return string
     */
    public static function formatDateTime($formatFrom, $formatTo, $datetime)
    {
        if (! $datetime || ! $formatFrom || ! $formatTo) {
            return;
        }
        $date = DateTime::createFromFormat($formatFrom, $datetime);
        if (! $date) {
            $date = strtotime($datetime);
            return date($formatTo, $date);
        }
        return $date->format($formatTo);
    }
    
    /**
     * check email allow of intranet
     * 
     * @param string $email
     * @return boolean
     */
    public static function isEmailAllow($email)
    {
        //add check email allow
        $domainAllow = Config::get('domain_logged');
        if ($domainAllow && count($domainAllow)) {
            foreach ($domainAllow as $value) {
                if (preg_match('/@' . $value . '$/', $email)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * check email is root
     * 
     * @return boolean
     */
    public static function isRoot($email)
    {
        if (trim(Config('services.account_root')) == $email) {
            return true;
        }
        return false;
    }
    
    /**
     * show permission view
     */
    public static function viewErrorPermission()
    {
        Session::forget('messages');
        Session::forget('flash');
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['message' => trans('core::message.You don\'t have access')], 401);
        }
        echo view('errors.permission');
        exit;
    }
    
    /**
     * route to option
     * 
     * @return array
     */
    public static function routeListToOption()
    {
        $routeCollection = Route::getRoutes();
        $option = [];
        $option[] = [
            'value' => '#',
            'label' => '#',
        ];
        foreach ($routeCollection as $value) {
            $path = $value->getPath();
            if (preg_match('/\{.*\?\}/', $value->getPath())) {
                $path = preg_replace('/\{.*\?\}.*/', '', $path);
            } else if (preg_match('/[{}?]/', $value->getPath())) {
                continue;
            }
            if ($path != '/') {
                $path = trim($path, '/');
            }
            $option[] = [
               'value' => $path,
                'label' => $path,
            ];
        }
        return $option;
    }
    
    /**
     * get no. starter from grid data
     */
    public static function getNoStartGrid($collectionModel)
    {
        if (! $collectionModel->total()) {
            return 1;
        }
        $currentPage = $collectionModel->currentPage();
        $perPage = $collectionModel->perPage();
        return ($currentPage - 1) * $perPage + 1;
    }
    
    /**
     * upload file
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param srting $path name path after storage/app/
     * @param array $allowType
     * @param boolean $rename
     * @return string|null
     * @throws Exception
     */
    public static function uploadFile(
            $file, 
            $path, 
            $allowType = [], 
            $maxSize = null, 
            $rename = true,
            array $config = []
    ) {
        if ($file->isValid()) {
            if ($allowType) {
                $extension = $file->getClientMimeType();
                if (! in_array($extension, $allowType)) {
                    throw new Exception(Lang::get('core::message.File type dont allow'), \Rikkei\Core\Model\CoreModel::ERROR_CODE_EXCEPTION);
                }
            }
            if ($maxSize) {
                $fileSize = $file->getClientSize();
                if ($fileSize / 1000 > $maxSize) {
                    throw new Exception(Lang::get('core::message.File size is large'), \Rikkei\Core\Model\CoreModel::ERROR_CODE_EXCEPTION);
                }
            }
            if ($rename) {
                $extension = $file->getClientOriginalExtension();
                if (is_string($rename)) {
                    $fileName = $rename . '.' . $extension;
                } else {
                    $fileName = str_random(5) . '_' . time() . '.' . $extension;
                }
            } else {
                $fileName = $file->getClientOriginalName();
            }
            $fullPathOrg = $file->getRealPath();
            if ($config && isset($config['remove_exif']) && $config['remove_exif']) {
                self::removeExifImage($fullPathOrg);
            }
            if (!Storage::exists($path)) {
                Storage::makeDirectory($path, 0777);
            }
            @chmod(storage_path($path), 0777);
            Storage::put(
                $path . '/' . $fileName,
                file_get_contents($fullPathOrg)
            );
            return $fileName;
        }
        return null;
    }
    
    /**
     * remove exif info of image
     * 
     * @param string $pathFile
     * @return string
     */
    public static function removeExifImage($pathFile)
    {
        // check image jpg
        if (exif_imagetype($pathFile) != IMAGETYPE_JPEG) {
            return false;
        }
        $exif = @exif_read_data($pathFile, 'IFD0');
        // check image exif
        if (!$exif || !isset($exif['Orientation'])) {
            return ;
        }
        $source = imagecreatefromjpeg($pathFile);
        $image = false;
        switch($exif['Orientation']) {
            case 3: // 180 rotate left
                $image = imagerotate($source, 180, 0);
                break;
            case 6: // 90 rotate right
                $image = imagerotate($source, -90, 0);
                break;
            case 8:    // 90 rotate left
                $image = imagerotate($source, 90, 0);
                break;
        }
        if ($image) {
            imagejpeg($image, $pathFile, 100);
            imagedestroy($image);
        }
        imagedestroy($source);
        return $pathFile;
    }
    

    /**
     * delete file 
     * @param type $path
     */
    public static function deleteFile($path)
    {
        if (Storage::disk('local')->has($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * get link image file
     * 
     * @param string|null $path
     * @param boolean $useDefault
     * @return string|null
     */
    public static function getLinkImage($path = null, $useDefault = true)
    {
        if (! $path) {
            if ($useDefault) {
                return URL::asset('common/images/noimage.png');
            }
            return null;
        }
        if (preg_match('/^http(s)?:\/\//', $path)) {
            return $path;
        }
        if (file_exists(public_path($path))) {
            return URL::asset($path);
        }
        if ($useDefault) {
            return URL::asset('common/images/noimage.png');
        }
        return null;
    }
    
    /**
     * get language level
     * 
     * @return array
     */
    public static function getLanguageLevel()
    {
        return Config::get('general.language_level');
    }
    
    /**
     * get format json language level
     * 
     * @return string json
     */
    public static function getLanguageLevelFormatJson()
    {
        return \GuzzleHttp\json_encode(self::getLanguageLevel());
    }
    
    /**
     * to option language level
     * 
     * @param type $nullable
     * @return type
     */
    public static function toOptionLanguageLevel($nullable = true)
    {
        $options = [];
        if ($nullable) {
            $options[] = [
                'value' => '',
                'label' => Lang::get('core::view.-- Please choose --'),
            ];
        }
        $level = self::getLanguageLevel();
        if (! $level) {
            return $options;
        }
        foreach ($level as $key => $item) {
            if (! $key) {
                continue;
            }
            $options[] = [
                'value' => $key,
                'label' => $item,
            ];
        }
        return $options;
    }
    
    /**
     * get label of level language
     * 
     * @param type $key
     * @return type
     */
    public static function getLabelLanguageLevel($key)
    {
        $level = self::getLanguageLevel();
        if (! $level || ! isset($level[$key]) || ! $level[$key]) {
            return;
        }
        return $level[$key];
    }
    
    /**
     * get normal level
     * 
     * @return array
     */
    public static function getNormalLevel()
    {
        return Config::get('general.normal_level');
    }
    
    /**
     * to option normal level
     * 
     * @param type $nullable
     * @return type
     */
    public static function toOptionNormalLevel($nullable = true)
    {
        $options = [];
        if ($nullable) {
            $options[] = [
                'value' => '',
                'label' => Lang::get('core::view.-- Please choose --'),
            ];
        }
        $level = self::getNormalLevel();
        if (! $level) {
            return $options;
        }
        foreach ($level as $key => $item) {
            if (! $key) {
                continue;
            }
            $options[] = [
                'value' => $key,
                'label' => $item,
            ];
        }
        return $options;
    }
    
    /**
     * get label of level normal
     * 
     * @param type $key
     * @return type
     */
    public static function getLabelNormalLevel($key)
    {
        $level = self::getNormalLevel();
        if (! $level || ! isset($level[$key]) || ! $level[$key]) {
            return;
        }
        return $level[$key];
    }
    
    /**
     * get format json normal level
     * 
     * @return string json
     */
    public static function getNormalLevelFormatJson()
    {
        return \GuzzleHttp\json_encode(self::getNormalLevel());
    }
    
    /**
     * Get Romanic number
     * @param int $integer
     * @param boolean $upcase
     * @return romanic
     */
    public static function romanic_number($integer, $upcase = true) 
    { 
        $table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1); 
        $return = ''; 
        while($integer > 0) 
        { 
            foreach($table as $rom=>$arb) 
            { 
                if($integer >= $arb) 
                { 
                    $integer -= $arb; 
                    $return .= $rom; 
                    break; 
                } 
            } 
        } 

        return $return; 
    }
    
    /**
     * translate text follow module
     * 
     * @param string $text
     * @param string $file
     * @param string $module
     * @return string
     */
    public static function trans($text = '', $file = '', $module = '')
    {
        if (Lang::has($module.'::'. $file . '.' . $text)) {
            return Lang::get($module.'::'. $file . '.' . $text);
            Lang::get('project::view.Title');
        }
        return $text;
    }
    /*
     * custom lang
     * @parm string
     * @param string
     * @return string
     */
    public static function customLang($path, $text)
    {
        if (Lang::has($path)) {
            return Lang::get($path);
        }
        return $text;
    }

    /*
     * get status label
     * @param array
     * @param int
     * @return sring
     */
    public static function getStatusLabel($data, $statusId)
    {
        foreach ($data as $key => $value) {
            if ($key == $statusId) {
                return $value;
            }
        }
    }

    /*
     * format array team name
     * @param array
     * @return array
     */
    public static function formatArrayTeamName($array)
    {
        $result = [];
        foreach($array as $key =>  $value) {
            $result[$key+1] = $value;
        }
        return $result;
    }
    
    /**
     * nl2br note
     * 
     * @param string $note
     * @return string
     */
    public static function nl2br($note)
    {
        if ($note === null) {
            return null;
        }
        $note = e($note);
        return nl2br($note);
    }
    
    /**
     * cut text
     * 
     * @param type $note
     * @param type $length
     * @param type $default
     * @return type
     */
    public static function substr(
            $note, 
            $length = 50,
            $default = '...', 
            $joinDefault = true
    ) {
        if ($note === null || empty($note)) {
            return null;
        }
        $note = e($note);
        $note = Str::substr($note, 0, $length);
        if ($joinDefault) {
            $note .= $default;
        }
        return $note;
    }
    
    /**
     * get date from any format
     * 
     * @param string $datetime
     * @return object
     */
    public static function getDateFromAny($datetime)
    {
        $date = substr($datetime, 0, 10);
        if (preg_match('/^[0-9]{1,2}(\-|\/|\,|\.)[0-9]{1,2}(\-|\/|\,|\.)[0-9]{4}$/', $date)) {
            //format dd/mm/yyyy
            $flagSplit = preg_replace('/^([0-9]+)(\-|\/|\,|\.)(.+)/', '$2', $date);
            $date = Carbon::createFromFormat("d{$flagSplit}m{$flagSplit}Y", $date);
            return $date;
        }
        if (preg_match('/^[0-9]{4}(\-|\/|\,|\.)[0-9]{1,2}(\-|\/|\,|\.)[0-9]{1,2}$/', $date)) {
            //format yyyy-mm-dd
            $flagSplit = preg_replace('/^([0-9]+)(\-|\/|\,|\.)(.+)/', '$2', $date);
            $date = Carbon::createFromFormat("Y{$flagSplit}m{$flagSplit}d", $date);
            return $date;
        }
        return null;
    }
    
    /**
     * get label of options
     * 
     * @param string $key
     * @param array $options
     * @return string
     */
    public static function getLabelOfOptions($key, $options, $default = null)
    {
        if (array_key_exists($key, $options)) {
            return $options[$key];
        }
        if (!$default) {
            return reset($options);
        }
        return $options[$default];
    }
    
    /**
     * get key of options
     * 
     * @param string $key
     * @param array $options
     * @return string
     */
    public static function getKeyOfOptions($key, $options, $default = null)
    {
        if (array_key_exists($key, $options)) {
            return $key;
        }
        if (!$default) {
            reset($options);
            return key($options);
        }
        return $default;
    }

    /**
     * show not found view
     */
    public static function viewErrorNotFound()
    {
        Session::forget('messages');
        Session::forget('flash');
        echo view('errors.not-found');
        exit;
    }

    /**
     * get day of last week
     * 
     * @param datetime $date
     * @param int $day
     * 
     * @return datetime
     */
    public static function getDateLastWeek($date = null, $day = 1)
    {
        if (!$date) {
            $date = Carbon::now();
        }
        $weekCurrent = $date->format('W');
        $yearCurrent = $date->format('Y');
        $result = clone $date;
        $result->setISODate($yearCurrent, $weekCurrent-1, $day);
        return $result;
    }
    
    /**
     * get nick name from email
     * 
     * @param string $name
     * @return string
     */
    public static function getNickName($name)
    {
        return ucfirst(strtolower(preg_replace('/@.*/', '', $name)));
    }
    
    /**
     * Format number 
     * @param float $number
     * @param int $decimalPoint
     * @return float
     */
    public static function formatNumber($number, $decimalPoint){
        return number_format($number, $decimalPoint, ".",",");
    }
    
    /**
     * convert standard email
     * 
     * @param string $email
     * @return string
     */
    public static function standardEmail($email)
    {
        $emailAtPosition = stripos($email, '@');
        if ($emailAtPosition) {
            $emailName = preg_replace('/\s/', '', 
                Str::ascii(substr($email, 0, $emailAtPosition)));
            $emailDomain = preg_replace('/\s/', '', 
                Str::ascii(substr($email, $emailAtPosition+1)));
            return $emailName . '@' . $emailDomain;
        }
        return preg_replace('/\s/', '', Str::ascii($email));
    }
    
    /**
     * list array days in weeks
     * @return array
     */
    public static function daysInWeek() {
        return [
            Carbon::SUNDAY => trans('core::view.Sunday'),
            Carbon::MONDAY => trans('core::view.Monday'),
            Carbon::TUESDAY => trans('core::view.Tuesday'),
            Carbon::WEDNESDAY => trans('core::view.Wednesday'),
            Carbon::THURSDAY => trans('core::view.Thursday'),
            Carbon::FRIDAY => trans('core::view.Friday'),
            Carbon::SATURDAY => trans('core::view.Saturday'),
        ];
    }
}
