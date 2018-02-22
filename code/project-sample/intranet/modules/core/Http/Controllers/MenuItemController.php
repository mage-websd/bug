<?php

namespace Rikkei\Core\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use URL;
use Rikkei\Core\View\Form;
use Illuminate\Support\Facades\Input;
use Lang;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Model\MenuItem;
use Illuminate\Support\Facades\Validator;

class MenuItemController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Menu item', URL::route('core::setting.menu.item.index'));
        Menu::setActive(null, null, 'setting');
    }
    
    /**
     * list menu item
     */
    public function index()
    {
        return view('core::menu.item.index', [
            'collectionModel' => MenuItem::getGridData()
        ]);
    }
    
    /**
     * view/edit menu item
     * 
     * @param int $id
     */
    public function edit($id)
    {
        $model = MenuItem::find($id);
        if (! $model) {
            return redirect()->route('core::setting.menu.item.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        Form::setData($model, 'menuitem');
        return view('core::menu.item.edit');
    }
    
    /**
     * save menu item
     */
    public function save()
    {
        //if submit is delete action
        if (Input::get('submit_delete')) {
            return $this->delete();
        }
        
        $id = Input::get('id');
        if (! $id) {
            $model = new MenuItem();
        } else {
            $model = MenuItem::find($id);
            if (! $model) {
                return redirect()->route('core::setting.menu.item.index')->withErrors(Lang::get('team::messages.Not found item.'));
            }
        }
        $dataItem = Input::get('item');
        $validator = Validator::make($dataItem, [
            'name' => 'required|max:50',
        ]);
        if ($validator->fails()) {
            if ($id) {
                return redirect()->route('core::setting.menu.item.edit', [
                        'id' => $id
                    ])->withErrors($validator);
            }
            return redirect()->route('core::setting.menu.item.index')->withErrors($validator);
        }
        $model->setData($dataItem);
        $model->save();
        $messages = [
                'success'=> [
                    Lang::get('team::messages.Save data success!'),
                ]
        ];
        return redirect()->route('core::setting.menu.item.edit', ['id' => $model->id])->with('messages', $messages);
    }
    
    /**
     * create menu item
     */
    public function create()
    {
        return view('core::menu.item.edit');
    }
    
    /**
     * delete menu item
     */
    public function delete()
    {
        $id = Input::get('id');
        $model = MenuItem::find($id);
        if (! $model) {
            return redirect()->route('core::setting.menu.item.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $model->delete();
        $messages = [
                'success'=> [
                    Lang::get('team::messages.Delete item success!'),
                ]
        ];
        return redirect()->route('core::setting.menu.item.index')->with('messages', $messages);
    }
}
