<?php

namespace Rikkei\Team\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Team\Model\Team;
use Lang;
use Validator;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Breadcrumb;
use URL;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Permission;
use Rikkei\Core\View\Menu;

class TeamController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Setting');
        Breadcrumb::add('Team', URL::route('team::setting.team.index'));
        Menu::setActive(null, null, 'setting');
    }

    /**
     * view team
     * 
     * @param int $id
     */
    public function view($id)
    {
        $model = Team::find($id);
        if (! $model) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        Form::setData($model);
        $positions = $teamPermissions = $permissionAs = null;
        if ($model->is_function) {
            $positions = Role::getAllPosition('desc');
            $permissionAs = $model->getTeamPermissionAs();
            if (! $permissionAs) {
                $teamPermissions = Permission::getTeamPermission($id);
            }
        }
        
        return view('team::setting.index', [
            'rolesPosition' => $positions,
            'teamPermissions' => $teamPermissions,
            'permissionAs' => $permissionAs
        ]);
    }
    
    /**
     * save team
     */
    public function save()
    {
        if ($id = Input::get('item.id')) {
            $model = Team::find($id);
        } else {
            $model = new Team();
        }
        $dataItem = Input::get('item');
        if (! Input::get('item.is_function')) {
            $dataItem['is_function'] = 0;
            $dataItem['follow_team_id'] = 0;
        } elseif (! Input::get('permission_same')) {
            $dataItem['follow_team_id'] = 0;
        }
        if (! Input::get('item.is_soft_dev')) {
            $dataItem['is_soft_dev'] = null;
        }
        $validator = Validator::make($dataItem, [
            'name' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            Form::setData($dataItem);
            Form::setData();
            if ($model->id) {
                return redirect()->route('team::setting.team.view', [
                            'id' => $model->id
                        ])->withErrors($validator);
            }
            return redirect()->route('team::setting.team.index')
                ->withErrors($validator);
        }
        //calculate position
        if (! $model->id) { //team new
            $parentId = 0;
            $teamSameParent = Team::select('id', 'sort_order')
                    ->where('parent_id', $parentId)
                    ->orderBy('sort_order', 'desc')
                    ->first();
            if (count($teamSameParent)) {
                $dataItem['sort_order'] = $teamSameParent->sort_order + 1;
            } else {
                $dataItem['sort_order'] = 0;
            }
        }

        try {
            $model->setData($dataItem);
            $result = $model->save();
            if (!$result) {
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Error save data, please try again!'));
            }
            return redirect()->route('team::setting.team.view', [
                    'id' => $model->id
                ])->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Save data success!')
                    ]
                ]);
        } catch (Exception $ex) {
            return redirect()->route('team::setting.team.index')->withErrors($ex);
        }
    }
    
    /**
     * move team
     */
    public function move()
    {
        $id = Input::get('id');
        if (!$id) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $model = Team::find($id);
        if (!$model) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        try {
            if (Input::get('move_up')) {
                $model->move(true);
            } else {
                $model->move(false);
            }

            return redirect()->route('team::setting.team.view', [
                    'id' => $id
                ])->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Move item success!')
                    ]
                ]);
        } catch (Exception $ex) {
            return redirect()->route('team::setting.team.view', [
                    'id' => $id
                ])->withErrors($ex);
        }
    }
    
    /**
     * Delete team
     */
    public function delete()
    {
        $id = Input::get('id');
        if (!$id) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $model = Team::find($id);
        if (!$model) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        try {
            $model->delete();
            return redirect()->route('team::setting.team.index')
                ->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Delete item success!')
                    ]
                ]);
        } catch (Exception $ex) {
            return redirect()->route('team::setting.team.view', [
                    'id' => $id
                ])->withErrors($ex);
        }
    }
    
    /**
     * search team by ajax
     */
    public function listSearchAjax($type = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            Team::searchAjax(Input::get('q'), [
                'page' => Input::get('page'),
                'typeExclude' => $type,
            ])
        );
    }
}
