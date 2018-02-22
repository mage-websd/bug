<?php

namespace Rikkei\Core\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\Config;
use Exception;

class UploadController extends Controller
{
    /**
     * upload image of employee skill and experience
     * 
     * @param Request $request
     * @return json
     */
    public function imageSkill(Request $request)
    {
        if(! $request->ajax()){
            return redirect('/');
        }
        if (Auth::guest()) {
            exit;
        }
        $uploadFolderImageTeam = 'team/images';
        $result = [];
        $image = array_get(Input::all(),'file');
        $type = Input::get('skill_type');
        if (! $type) {
            $type = 'general';
        }
        $pathFolder = Config::get('general.upload_folder') . '/' . $uploadFolderImageTeam . '/' . $type;
        if ($image) {
            try {
                $image = View::uploadFile(
                    $image, 
                    Config::get('general.upload_storage_public_folder') . 
                        '/' . $uploadFolderImageTeam . '/' . $type,
                    Config::get('services.file.image_allow'),
                    Config::get('services.file.image_max')
                );
                if ($image) {
                    $result['image_path'] = trim($pathFolder, '/').'/'.$image;
                    $result['image'] = View::getLinkImage($result['image_path']);
                }
            } catch (Exception $ex) {
                $result['error'] = $ex->getMessage();
            }
        }
        echo \GuzzleHttp\json_encode($result);
        exit;
    }
}