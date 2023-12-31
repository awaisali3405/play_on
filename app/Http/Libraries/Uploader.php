<?php



namespace App\Http\Libraries;



use App\Http\Controllers\Admin\Auth\CustomAuth\Input;

use App\Http\Controllers\Admin\Auth\CustomAuth\Request;
use App\Http\Controllers\Admin\Auth\CustomAuth\Validator;



class Uploader {



    protected $maxFileSize, $inputName, $file, $fileType, $valid, $filePath, $uploaded, $message ,$filextension, $mimeType;

    public $fileName;



    function __construct($inputName = '', $maxFileSize = 26214400) {

        $this->maxFileSize = $maxFileSize; // 25 MB default size

        $this->inputName = $inputName;

        $this->valid = FALSE;

        $this->uploaded = FALSE;

        $this->message = '';

    }



    public function setInputName($inputName) {

        $this->inputName = $inputName;

        return $this;

    }



    public function setFile($file) {

        $this->file = $file;

    }



    public function isValidFile() {
        if (Request::hasFile($this->inputName) || $this->file) {

            $this->file = (!$this->file) ? Request::file($this->inputName):$this->file;

            if ($this->file->isValid()) {

                if ($this->file->getSize() <= $this->maxFileSize) {

                    $fileName = explode('.', $this->file->getClientOriginalName());

                    $fileNameChange = str_replace(' ', '_', $fileName[0]);

                    $this->fileName = $fileNameChange;

                    $this->valid = TRUE;

                    $this->fileextension = $this->file->getClientMimeType();

                }

                else {

                    $this->message = 'File size too large';

                }

            }

            else {

                $this->message = $this->file->getErrorMessage();

            }

        }

        return $this->valid;

    }



    public function hasValidDimentions($minWidth, $minHeight) {

        if ($this->valid) {

            $validator = Validator::make([$this->inputName => 'image'], [$this->inputName => $this->file]);

            if ($validator->passes()) {

                $this->fileType = 'image';

                $imageData = getimagesize($this->file->getRealPath());

                if ($imageData[0] >= $minWidth && $imageData[1] >= $minHeight) {

                    return TRUE;

                }

                else {

                    $this->message = 'Image must be at least '.$minWidth.'x'.$minHeight;

                }

            }

            else {

                $this->message = $validator->messages()->first();

            }

        }

        return false;

    }



    public function upload($uploadPath, $fileName = 'file', $temp = false, $thumb = []) {

        if ($this->valid) {

            $this->fileextension = $this->file->getClientMimeType();

            if (strpos($this->fileextension, 'image')===FALSE) {

                $this->fileextension = 'video';

            }

            $fileName = $fileName.'-'.time().'.'.$this->file->getClientOriginalExtension();

            $this->filePath = 'uploads/'.(($temp) ? 'temp':$uploadPath).'/';

            $this->file->move(env('BASE_UPLOAD_PATH', '').$this->filePath, $fileName);

            if (!empty($thumb)) {

                list($width, $height) = $thumb;

            }

            $this->filePath = $this->filePath.$fileName;

            $this->mimeType = \File::mimeType(env('BASE_UPLOAD_PATH', '').$this->filePath);

            $this->file = NULL;

            $this->valid = FALSE;

            $this->uploaded = TRUE;

        }

        else {

            $this->uploaded = FALSE;

        }

    }



    public function getUploadedPath() {

        return $this->filePath;

    }



    public function isUploaded() {

        return $this->uploaded;

    }

    public function getFileExtension(){



        return $this->fileextension;

    }



    public function getMessage() {

        return $this->message;

    }



    public function getMaxFileSize() {

        return $this->maxFileSize;

    }



    public function getMimeType() {

        return $this->mimeType;

    }





}
