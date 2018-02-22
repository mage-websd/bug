<?php

namespace Rikkei\Core\Model;

use Illuminate\Contracts\Auth\Authenticatable;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;
use Illuminate\Support\Facades\Config;
use Exception;

class User extends CoreModel implements Authenticatable
{
    
    use SoftDeletes;
    
    /*
     * const avatar key store session
     */
    const AVATAR = 'account.logged.avatar';

    /*
     * primary key
     */
    protected $primaryKey = 'employee_id';

    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_id', 'google_id', 'name', 'email', 'avatar_url', 'token'
    ];

    /*
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'token',
    ];
    
    protected static $employee;

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return null;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->{$this->getRememberTokenName()};
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->{$this->getRememberTokenName()} = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'token';
    }
    
    /**
     * get Employee
     * 
     * @return model
     */
    public function getEmployee()
    {
        return Employee::where('email', $this->email)
                ->first();
    }
    
    /**
     * get employee of user logged
     * 
     * @return model
     */
    public static function getEmployeeLogged()
    {
        if (! self::$employee) {
            self::$employee = Employee::where('email', Auth::user()->email)
                ->first();
        }
        return self::$employee;
    }
    
    /**
     * get avatar of user logged
     * 
     * @return string
     */
    public static function getAvatar()
    {
        if (Auth::user()) {
            return Auth::user()->avatar_url;
        }
        return null;
    }
    
    /**
     * get nickname of user logged
     * 
     * @return string
     */
    public static function getNickName()
    {
        $email = Permission::getInstance()->getEmployee()->email;
        return preg_replace('/@.*/', '', $email);
    }

    /**
     * save email for user if changing and delete that user session
     * 
     * @return object
     */
    public static function saveEmail($employee)
    {
        $user = self::where('employee_id', $employee->id)->first();
        if($user && $user->email !== $employee->email){
            $user->email = $employee->email;
            $user->save();
            $user->destroyLastSession();
            return true;
        }
        return false;
    }

    /**
     * save lastest user session
     * 
     * @return object
     */
    public function saveLastSession()
    {
        $new_sessid   = Session::getId(); 
        $last_session = Session::getHandler()->read($this->last_sessid);
        if ($last_session) {
            Session::getHandler()->destroy($this->last_sessid);
        }
        $this->last_sessid = $new_sessid;
        $this->save();
    }

    /**
     * destroy lastest user session
     * 
     * @return object
     */
    public function destroyLastSession()
    {
        $last_session = Session::getHandler()->read($this->last_sessid);
        if ($last_session) {
            Session::getHandler()->destroy($this->last_sessid);
        }
        $this->last_sessid = '';
        $this->save();
    }

    /**
     * force user log out when they change their employee email
     */
    public static function forceLogOut()
    {
        Auth::logout();
        return redirect('/');
    }

    /**
     * upload Avatar of user
     *
     * @param object $upload FileUpload Object
     * @param object $employee
     * @return string
     */
    public static function uploadAvatar($upload, $employee)
    {
        $response = [];
        if (!$upload) {
            $response['status'] = 1;
            $response['filePath'] = null;
            return $response;
        }
        $user = self::where('employee_id', $employee->id)
            ->first();
        if (!$user) {
            $user = new self();
            $user->employee_id = $employee->id;
            $user->email = $employee->email;
        }
        try {
            $fileName = CoreView::uploadFile(
                $upload,
                Config::get('general.upload_storage_public_folder') .
                    '/' . Employee::AVATAR_FOLDER . $employee->id,
                Config::get('services.file.image_allow'),
                Config::get('services.file.image_max'),
                false
            );
            $filePath = Config::get('general.upload_folder') .  '/'
                . Employee::AVATAR_FOLDER .$employee->id
                . '/' . $fileName;
            $user->avatar_url = asset($filePath);
            $user->save();
            $response['status'] = 1;
            $response['filePath'] = $filePath;
            return $response;
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            return $response;
        }
    }
}
