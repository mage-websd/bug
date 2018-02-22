<?php

namespace App\Models\User;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\General;
use Illuminate\Support\Facades\Input;

class AccountRoles extends BaseModel
{
    use SoftDeletes;

    const R_MEMBER = 6;
    const R_AD = 4;
    const R_SR = 2;
    const R_ADMIN = 0;

    protected $table = 'm_account_roles';
    /**
     * get all role of account with label
     *
     * @return aray
     */
    public static function rolesLabel()
    {
        return [
            self::R_ADMIN => 'Admin',
            self::R_SR => 'SR manage',
            self::R_AD => 'AD',
            self::R_MEMBER => 'Member'
        ];
    }

    /**
     * roles name with translate language
     *
     * @return array
     */
    public static function rolesTrans()
    {
        return [
            self::R_ADMIN => __('user.Admin'),
            self::R_SR => __('user.SR manage'),
            self::R_AD => __('user.AD'),
            self::R_MEMBER => __('user.Member')
        ];
    }

    /**
     * get list of user
     *
     * @param array $dataFilter
     * @return collection
     */
    public static function getListUser()
    {
        $tblUser = User::getTableName();
        $tblSr = Showroom::getTableName();
        $params = General::getParamPager(['limit']);
        $collection = User::select([$tblUser . '.id',
            $tblUser . '.user_family_name', $tblUser . '.user_first_name',
            $tblUser . '.login_id', $tblUser . '.status',
            $tblUser . '.account_role_id', 't_sr.sr_name'])
            ->leftJoin($tblSr . ' AS t_sr', 't_sr.id', '=', $tblUser . '.sr_id')
            ->whereNull('t_sr.deleted_at');
        self::filter(
            $collection,
            [
                'status' => $tblUser . '.status',
                'login_id' => $tblUser . '.login_id',
                'sr' => 't_sr.id'
            ],
            [
                't_sr.id' => '=',
                $tblUser . '.status' => '='
            ]
        );
        $filterName =  trim(Input::get('s-name'));

        /** fix - full name search  **/
        $filterArr = explode(' ', $filterName);
        if (sizeof($filterArr) == 1) {
            // Normal filter
            $filterName = '%' . $filterName . '%';
            $collection->where(function ($query) use ($filterName, $tblUser) {
                $query->orWhere($tblUser.'.user_family_name', 'LIKE', $filterName)
                    ->orWhere($tblUser.'.user_first_name', 'LIKE', $filterName);
            });
        } elseif (isset($filterArr[1])) {
            // Full name filter
            $collection->where($tblUser.'.user_first_name', '=', $filterArr[1]);
            $collection->where($tblUser.'.user_family_name', '=', $filterArr[0]);
        } else {
            return self::pager($collection, $params['limit'], $params['page']);
        }
        /** end fix **/
        return self::pager($collection, $params['limit'], $params['page']);
    }
}
