<?php

namespace Rikkei\Core\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use URL;
use Rikkei\Core\View\Form;
use Illuminate\Support\Facades\Input;
use Lang;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Model\Menu as MenuModel;
use Illuminate\Support\Facades\Validator;

class MenuGroupController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Menu', URL::route('core::setting.menu.group.index'));
        Menu::setActive(null, null, 'setting');
    }
    
    /**
     * list menu group
     */
    public function index()
    {
        return view('core::menu.group.index', [
            'collectionModel' => MenuModel::getGridData()
        ]);
    }
    
    /**
     * view/edit menu group
     * 
     * @param int $id
     */
    public function edit($id)
    {
        $model = MenuModel::find($id);
        if (! $model) {
            return redirect()->route('core::setting.menu.group.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        Form::setData($model, 'menus');
        return view('core::menu.group.edit');
    }
    
    /**
     * save menu group
     */
    public function save()
    {
        //if submit is delete action
        if (Input::get('submit_delete')) {
            return $this->delete();
        }
        
        $id = Input::get('id');
        if (! $id) {
            $model = new MenuModel();
        } else {
            $model = MenuModel::find($id);
            if (! $model) {
                return redirect()->route('core::setting.menu.group.index')->withErrors(Lang::get('team::messages.Not found item.'));
            }
        }
        $dataItem = Input::get('item');
        $validator = Validator::make($dataItem, [
            'name' => 'required|max:255',
        ]);
        if ($validator->fails()) {
            if ($id) {
                return redirect()->route('core::setting.menu.group.edit', [
                        'id' => $id
                    ])->withErrors($validator);
            }
            return redirect()->route('core::setting.menu.group.index')->withErrors($validator);
        }
        $model->setData($dataItem);
        try {
            $model->save();
        } catch (Exception $ex) {
            return redirect()->route('core::setting.menu.group.index')->withErrors($ex);
        }
        $messages = [
                'success'=> [
                    Lang::get('team::messages.Save data success!'),
                ]
        ];
        return redirect()->route('core::setting.menu.group.edit', ['id' => $model->id])->with('messages', $messages);
    }
    
    /**
     * create menu group
     */
    public function create()
    {
        return view('core::menu.group.edit');
    }
    
    /**
     * delete menu group action
     */
    public function delete()
    {
        $id = Input::get('id');
        $model = MenuModel::find($id);
        if (! $model) {
            return redirect()->route('core::setting.menu.group.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        try {
            $model->delete();
            $messages = [
                    'success'=> [
                        Lang::get('team::messages.Delete item success!'),
                    ]
            ];
            return redirect()->route('core::setting.menu.group.index')->with('messages', $messages);
        } catch (Exception $ex) {
            return redirect()->route('core::setting.menu.group.index')->withErrors($ex);
        }
    }
}
