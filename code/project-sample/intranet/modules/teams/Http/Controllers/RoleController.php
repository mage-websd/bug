<?php

namespace Rikkei\Team\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use URL;
use Rikkei\Team\Model\Role;
use Rikkei\Core\View\Form;
use Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Rikkei\Team\Model\Permission;
use Rikkei\Core\View\Menu;

class RoleController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Setting');
        Breadcrumb::add('Role');
        Menu::setActive(null, null, 'setting');
    }
    
    /**
     * view/edit role
     * 
     * @param int $id
     */
    public function view($id)
    {
        $model = Role::find($id);
        if (! $model || ! $model->isRole()) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        Breadcrumb::add($model->role, URL::route('team::setting.role.view', ['id' => $id]));
        Form::setData($model, 'role');
        $rolePermissions = Permission::getRolePermission($id);
        return view('team::setting.index', [
            'rolePermissions' => $rolePermissions,
        ]);
    }
    
    /**
     * save role
     */
    public function save()
    {
        $id = Input::get('role.id');
        $dataItem = Input::get('role');
        if ($id) {
            $model = Role::find($id);
            if (! $model) {
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Not found item.'));
            }
        } else {
            $model = new Role();
        }
        
        $validator = Validator::make($dataItem, [
            'role' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            Form::setData($dataItem);
            if ($model->id) {
                return redirect()->route('team::setting.role.view', [
                        'id' => $model->id
                    ])->withErrors($validator);
            }
            return redirect()->route('team::setting.team.index')->withErrors($validator);
        }
        $model->setData($dataItem);
        $model->special_flg = Role::FLAG_ROLE;
        try {
            $result = $model->save();
            if (! $result) {
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Error save data, please try again!'));
            }
            $messages = [
                    'success'=> [
                        Lang::get('team::messages.Save data success!'),
                    ]
            ];
            return redirect()->route('team::setting.role.view', ['id' => $model->id])->with('messages', $messages);
        } catch (Exception $ex) {
            return redirect()->route('team::setting.team.index')->withErrors($ex);
        }
    }
    
    /**
     * delete role
     */
    public function delete()
    {
        $id = Input::get('id');
        $model = Role::find($id);
        if (! $model) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        try {
            $model->delete();
            $messages = [
                    'success'=> [
                        Lang::get('team::messages.Delete item success!'),
                    ]
            ];
            return redirect()->route('team::setting.team.index')->with('messages', $messages);
        } catch (Exception $ex) {
            return redirect()->route('team::setting.team.index')->withErrors($ex);
        }
    }
}

