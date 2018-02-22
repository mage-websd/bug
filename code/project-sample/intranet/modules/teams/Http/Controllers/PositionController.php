<?php

namespace Rikkei\Team\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Lang;
use Validator;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Breadcrumb;
use URL;
use Rikkei\Team\Model\Role;
use Rikkei\Core\View\Menu;

class PositionController extends \Rikkei\Core\Http\Controllers\Controller
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
     * view team position
     * 
     * @param int $id
     */
    public function view($id)
    {
        $model = Role::find($id);
        if (! $model || $model->special_flg != Role::FLAG_POSITION) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        Form::setData($model, 'position');
        return view('team::setting.index');
    }
    
    /**
     * save team positon
     */
    public function save()
    {
        $id = Input::get('position.id');
        $dataItem = Input::get('position');
        if ($id) {
            $model = Role::find($id);
            if (! $model || $model->special_flg != Role::FLAG_POSITION) {
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Please choose team to do this action'));
            }
        } else {
            $model = new Role();
        }
        
        $validator = Validator::make($dataItem, [
            'role' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            Form::setData($dataItem);
            Form::setData();
            if ($model->id) {
                return redirect()->route('team::setting.team.position.view', [
                        'id' => $model->id
                    ])->withErrors($validator);
            }
            return redirect()->route('team::setting.team.index')->withErrors($validator);
        }
        
        //calculate position level
        if (! $id) { //position new
            $positionLast = Role::select('sort_order')
                ->where('special_flg', Role::FLAG_POSITION)
                ->orderBy('sort_order', 'desc')
                ->first();
            if (count($positionLast)) {
                $dataItem['sort_order'] = $positionLast->sort_order + 1;
            } else {
                $dataItem['sort_order'] = 1;
            }
        }
        
        try {
            $model->setData($dataItem);
            $model->special_flg = Role::FLAG_POSITION;
            $result = $model->save();
            if (! $result) {
                return redirect()->route('team::setting.team.index')
                    ->withErrors(Lang::get('team::messages.Error save data, please try again!'));
            }
            return redirect()->route('team::setting.team.position.view', [
                    'id' => $model->id
                ])->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Save data success!')
                    ]
                ]);
        } catch (Exception $ex) {
            return redirect()->route('team::setting.team.index')
                    ->withErrors($ex);
        }
    }
    
    /**
     * Delete team position
     * @return type
     */
    public function delete()
    {
        $id = Input::get('id');
        if (!$id) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $model = Role::find($id);
        if (! $model || ! $model->isPosition()) {
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
            return redirect()->route('team::setting.team.position.view', [
                    'id' => $id
                ])->withErrors($ex);
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
        $model = Role::find($id);
        if (!$model || $model->special_flg != Role::FLAG_POSITION) {
            return redirect()->route('team::setting.team.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        try {
            if (Input::get('move_up')) {
                $model->move(true);
            } else {
                $model->move(false);
            }

            return redirect()->route('team::setting.team.position.view', [
                    'id' => $id
                ])->with('messages', [
                    'success' => [
                        Lang::get('team::messages.Move item success!')
                    ]
                ]);
        } catch (Exception $ex) {
            return redirect()->route('team::setting.team.position.view', [
                    'id' => $id
                ])->withErrors($ex);
        }
    }
}
