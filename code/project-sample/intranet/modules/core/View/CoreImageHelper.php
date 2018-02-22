<?php

namespace Rikkei\Core\View;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\URL;
use Exception;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;

class CoreImageHelper
{
    /*
     *  path/to/image in folder public
     */
    protected $image;
    
    /*
     * image info
     */
    protected $imageInfo;

    /*
     * folder storage image resize and crop
     */
    protected $cacheFolder;
    protected $cacheFolderShort;

    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    /**
     * constructor
     * 
     * @param string $image
     * @return \self
     */
    public function __construct($image = null) {
        $this->cacheFolderShort = Config::get('general.upload_folder') . '/cache' . '/';
        $this->cacheFolder = public_path($this->cacheFolderShort);
        if ($image) {
            $this->setImage($image);
        }
        return $this;
    }
    
    
    /**
     * set image
     *  path/to/image in folder public
     * @param string $image
     * @return \self
     */
    public function setImage($image)
    {
        $this->image = $image;
        $this->imageInfo = null;
        if ($image) {
            $this->initImageInfo();
        }
        return $this;
    }
    
    /**
     * get full path of image
     * 
     * @return string
     */
    public function getImageFullPath()
    {
        return public_path($this->image);
    }
    
    /**
     * init image info
     * 
     * @return \self
     */
    private function initImageInfo()
    {
        $this->imageInfo = $this->processImageInfo($this->image, 0);
        return $this;
    }
    
    /**
     * process image info
     * 
     * @param string $image path/to/image in folder public
     * @param null|int|string $pathSave store image process success
     * @return array
     */
    private function processImageInfo($image, $pathSave = 0)
    {
        $imageInfo = [];
        $fullPath = public_path($image);
        //check exists file
        if (!File::exists($fullPath) || !$image) {
            return $imageInfo;
        }
        //get full name from path/image
        $fullName = preg_split('/\/(\/{0})/', $fullPath);
        $fullName = array_reverse($fullName);
        reset($fullName);
        if (! $fullName || ! isset($fullName[0])) {
            return $imageInfo;
        }
        $fullName = $fullName[0];
        $matches = null;
        preg_match('/(.*)(\.[a-zA-Z0-9]+){1}$/', $fullName, $matches);
        if (!$matches || sizeof($matches) != 3) {
            return $imageInfo;
        }
        //get info of image
        $fileInfo = getimagesize($fullPath);
        //check type image
        $typeAllow = Config::get('image.type');
        if ($typeAllow) {
            if (!in_array($fileInfo['mime'], Config::get('image.type'))) {
                return $imageInfo;
            }
        } else {
            //not image
            if (!exif_imagetype($fullPath)) {
                return $imageInfo;
            }
        }
        //get short path, full path store image
        $fullNameReg = preg_replace('/\./', '\.', $fullName);
        $shortPath = preg_replace('/' . $fullNameReg . '$/', '', $image);
        $shortPath = trim($shortPath, '/');
        switch ($pathSave) {
            case 0: // use path default
                $fullImageCachePath = $this->cacheFolder . $shortPath . '/';
                break;
            case 1: // use path type 2, only render
                $shortPath = preg_replace('/^' . '/', '', $shortPath);
                $shortPath = 'render' . $shortPath;
                $fullImageCachePath = public_path($shortPath) . '/';
                break;
        }
        //create folder
        if (!File::exists($fullImageCachePath)) {
            try {
                if (!File::makeDirectory($fullImageCachePath, 0775, true, true)) {
                    throw new Exception('Cannot create folder image cache');
                }
                @chmod($fullImageCachePath, 0775);
            } catch (Exception $ex) {
                throw $ex;
            }
        }
        $imageInfo['full_name'] = $fullName;
        $imageInfo['name'] = $matches[1];
        $imageInfo['extension'] = $matches[2];
        $imageInfo['full_path'] = $fullPath;
        $imageInfo['width'] = $fileInfo[0];
        $imageInfo['height'] = $fileInfo[1];
        $imageInfo['short_path'] = $shortPath;
        $imageInfo['full_path_cache'] = $fullImageCachePath;
        return $imageInfo;
    }
    
    /**
     * crop image
     * 
     * @param type $width
     * @param type $height
     */
    public function crop($width = null, $height = null)
    {
        if (! $this->imageInfo) {
            return null;
        }
        list($width, $height, $x, $y) = $this->calculatorSizeCrop($width, $height);
        $name = $this->imageInfo['name'] . '-c-' . (int) $width . 
                '-' . (int) $height . $this->imageInfo['extension'];
        // check file exists
        if (File::exists($this->imageInfo['full_path_cache'] . $name)) {
            return URL::asset($this->cacheFolderShort . $this->imageInfo['short_path'] 
                . '/' . $name);
        }
        $image = Image::make($this->imageInfo['full_path']);
        $image->crop((int) $width, (int) $height, $x, $y);
        $image->save($this->imageInfo['full_path_cache'] . $name);
        return URL::asset($this->cacheFolderShort . $this->imageInfo['short_path'] 
                . '/' . $name);
    }
    
    /**
     * resize image
     * 
     * @param string $width
     * @param string $height
     * @param boolean $resizeGreater allow resize if expect greater origin
     * @return type
     */
    public function resize($width = null, $height = null, $resizeGreater = false)
    {
        if (!$this->imageInfo) {
            return null;
        }
        list($width, $height) = $this->calculatorSizeRatio($width, $height, $resizeGreater);
        $name = $this->imageInfo['name'] . '-r-' . (int) $width . 
                '-' . (int) $height . $this->imageInfo['extension'];
        // check file exists
        if (File::exists($this->imageInfo['full_path_cache'] . $name)) {
            return URL::asset($this->cacheFolderShort . $this->imageInfo['short_path'] 
                . '/' . $name);
        }
        $image = Image::make($this->imageInfo['full_path']);
        $image->resize($width, $height);
        $image->save($this->imageInfo['full_path_cache'] . $name);
        return URL::asset($this->cacheFolderShort . $this->imageInfo['short_path'] 
                . '/' . $name);
    }
    
    /**
     * resize image
     * 
     * @param string $width
     * @param string $height
     * @return type
     */
    public function fullbox($width, $height)
    {
        if (! $this->imageInfo) {
            return null;
        }
        $name = $this->imageInfo['name'] . '-fc-' . (int) $width . 
                '-' . (int) $height . $this->imageInfo['extension'];
        // check file exists
        if (File::exists($this->imageInfo['full_path_cache'] . $name)) {
            return URL::asset($this->cacheFolderShort . $this->imageInfo['short_path'] 
                . '/' . $name);
        }
        
        list($widthResize, $heightresize) = $this->calculatorSizeFullbox($width, $height);
        $image = Image::make($this->imageInfo['full_path']);
        $image->resize($widthResize, $heightresize);
        $marginX = $widthResize / 2 - $width / 2;
        $marginY = $heightresize / 2 - $height / 2;
        $image->crop((int) $width, (int) $height, (int) $marginX, (int) $marginY);
        $image->save($this->imageInfo['full_path_cache'] . $name);
        return URL::asset($this->cacheFolderShort . $this->imageInfo['short_path'] 
                . '/' . $name);
    }
    
    /**
     * caculator ratio of size iamge
     * 
     * @param float $width
     * @param float $height
     * @param boolean $resizeGreater allow resize if expect greater origin
     * @return array
     */
    private function calculatorSizeRatio(
        $width = null, 
        $height = null, 
        $resizeGreater = false,
        $imageInfo = null
    ) {
        $result = [
            $width,
            $height
        ];
        if (!$imageInfo) {
            $imageInfo = $this->imageInfo;
        }
        if ($width && $height) {
            if (!$resizeGreater) {
                //size image smaller size expect
                if ($imageInfo['width'] <= $width && 
                    $imageInfo['height'] <= $height
                ) {
                    return [
                        $imageInfo['width'],
                        $imageInfo['height']
                    ];
                }
            }
            //size image greater size expect
            if (($width / $height) >= 
                ($imageInfo['width'] / $imageInfo['height'])) {
                // keep height, ratio width
                $result[0] = $height * $imageInfo['width'] / 
                        $imageInfo['height'];
                return $result;
            }
            //keep width ratio height
            $result[1] = $width * $imageInfo['height'] / 
                    $imageInfo['width'];
            return $result;
        }
        
        if ($width) {
            if (!$resizeGreater) {
                if ($imageInfo['width'] <= $width) {
                    return [
                        $imageInfo['width'],
                        $imageInfo['height']
                    ];
                }
            }
            //keep width ratio height
            $result[1] = $width * $imageInfo['height'] / 
                    $imageInfo['width'];
            return $result;
        }
        
        if ($height) {
            if (!$resizeGreater) {
                if ($imageInfo['height'] <= $height) {
                    return [
                        $imageInfo['width'],
                        $imageInfo['height']
                    ];
                }
            }
            // keep height, ratio width
            $result[0] = $height * $imageInfo['width'] / 
                    $imageInfo['height'];
            return $result;
        }
        return [
            $imageInfo['width'],
            $imageInfo['height']
        ];
    }
    
    /**
     * caculator ratio of size image in fullbox
     * 
     * @param float $width
     * @param float $height
     * @return array
     */
    private function calculatorSizeFullbox($width, $height)
    {
        if (! $width || ! $height) {
            return [
                $this->imageInfo['width'],
                $this->imageInfo['height']
            ];
        }
        
        $result = [
            $width,
            $height
        ];
        
        //size image greater size expect
        if (($width / $height) >= 
            ($this->imageInfo['width'] / $this->imageInfo['height'])) {
            // keep height, ratio width
            $result[1] = $width * $this->imageInfo['height'] / 
                $this->imageInfo['width'];
        } else {
            //keep width ratio height
            $result[0] = $height * $this->imageInfo['width'] / 
                        $this->imageInfo['height'];
        }
        return $result;
    }
    
    /**
     * caculator size crop of size iamge
     * 
     * @param float $width
     * @param float $height
     * @return array
     */
    private function calculatorSizeCrop($width = null, $height = null) {
        if (! $width || ! $height || 
            $this->imageInfo['width'] < $width ||
            $this->imageInfo['height'] < $height
        ) {
            return [
                $this->imageInfo['width'],
                $this->imageInfo['height'],
                0,
                0
            ];
        }
        
        //crop center image
        $marginX = $this->imageInfo['width'] / 2 - $width / 2;
        $marginY = $this->imageInfo['height'] / 2 - $height / 2;
        
        return [
            $width,
            $height,
            (int) $marginX,
            (int) $marginY
        ];
    }
    
    /**
     * caculator size crop of size iamge
     * 
     * @param float $ratio width/height
     * @param float $width
     * @param float $height
     * @return array
     */
    private function calculatorSizeCropOne($ratio, $width = true, $height = false) {
        if (! $width && ! $height) {
            return [
                $this->imageInfo['width'],
                $this->imageInfo['height'],
                0,
                0
            ];
        }
        //get full width, crop height
        if ($width) {
            $heightResize = $this->imageInfo['width'] * $ratio;
            if ($heightResize > $this->imageInfo['height']) {
                $heightResize = $this->imageInfo['height'];
            }
            $marginY = $this->imageInfo['height'] / 2 - $heightResize / 2;
            return [
                $this->imageInfo['width'],
                $heightResize,
                0,
                $marginY
            ];
        }
        
        //get full height, crop width
        if ($height) {
            $widthResize = $this->imageInfo['height'] / $ratio;
            if ($widthResize > $this->imageInfo['width']) {
                $widthResize = $this->imageInfo['width'];
            }
            $marginX = $this->imageInfo['width'] / 2 - $widthResize / 2;
            return [
                $widthResize,
                $this->imageInfo['height'],
                $marginX,
                0
            ];
        }
    }
    
    /**
     * upload file
     * 
     * @param object $file
     * @param srting $path
     * @param float $capacity (MB)
     * @param array $allowType
     * @param boolean $rename
     * @return string|null
     * @throws Exception
     */
    public static function uploadFile($file, $path, $capacity = null, $allowType = [], $rename = true)
    {
        if ($file->isValid()) {
            if (! $allowType) {
                $allowType = Config::get('image.type');
            }
            if ($allowType) {
                $extension = $file->getClientMimeType();
                if (! in_array($extension, $allowType)) {
                    throw new Exception(Lang::get('frontend_error.File type dont allow upload'), CoreModel::CODE_EXCEPTION_MANUAL);
                }
            }
            if ($capacity) {
                if ($capacity < $file->getClientSize() / pow(Config::get('image.capacity_unit'),2)) {
                    throw new Exception(
                        Lang::get('frontend_error.Capacity must smaller :number MB', ['number' => $capacity]), 
                        CoreModel::CODE_EXCEPTION_MANUAL
                    );
                }
            }
            if ($rename) {
                $extension = $file->getClientOriginalExtension();
                $fileName  = time() . '.' . str_random(5) . '.' . $extension;
            } else {
                $fileName = $file->getClientOriginalName();
            }
            try {
                Storage::put(
                        $path . $fileName, 
                        file_get_contents($file->getRealPath())
                );
            } catch (Exception $ex) {
                throw $ex;
            }
            return $fileName;
        }
        return null;
    }
    
    /**
     * get string extension allow follow type
     * 
     * @param $typeFormat
     * @return string
     */
    public static function getImageExtensionAllow($typeFormat = null)
    {
        $type = Config::get('image.type');
        $extension = Config::get('image.extensions');
        if (! $type || ! $extension) {
            return '';
        }
        if (! $typeFormat) {
            //format ['image/a','image/b']
            $result = implode('","', $type);
            $result = '["' . $result . '"]';
            return $result;
        }
        $result = [];
        foreach ($type as $item) {
            if (isset($extension[$item]) && $extension[$item]) {
                if (is_array($extension[$item])) {
                    $result = array_merge($result, $extension[$item]);
                } else {
                    $result[] = $extension[$item];
                }
            }
        }
        $result = array_unique($result);
        switch ($typeFormat) {
            case 1: //format .a,.b,.c,.d
                return '.' . implode(',.', $result);
            case 2: //format a|b|c|d
                return implode('|', $result);
            case 3: //format a,b,c
                return implode(',', $result);
            case 4: //format ["a","b","c"]
                return '["' . implode('","', $result) . '"]';
            default:
                return '';
        }
    }
    
    /**
     * process crop resize bulk image
     * 
     * @param array $data have key: image, type, width, height
     * @return array
     */
    public static function processImageBulk($data)
    {
        if (! $data) {
            return null;
        }
        $dataItemDefault = [
            'type' => 'fullbox', // resize,
            'width' => null,
            'height' => null,
        ];
        
        $result = [];
        $imageHelpler = new static;
        foreach ($data as $item) {
            if (! $item || ! iisset($item['image']) || ! $item['image']) {
                continue;
            }
            $item = array_merge($dataItemDefault, $item);
            $imageHelpler->setImage($item['image']);
            switch ($data['type']) {
                case 'resize':
                    $result[] = $imageHelpler->resize($item['width'], $item['height']);
                default: //default fulbox
                    $result[] = $imageHelpler->fullbox($item['width'], $item['height']);
            }
        }
        return $result;
    }
    
    /**
     * Singleton instance
     * 
     * @return \self
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
}
