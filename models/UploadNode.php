<?php 

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadNode extends Model
{
    /**
     * @var UploadedFile
     */
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, pdf, txt'],
        ];
    }

    // https://stackoverflow.com/questions/46689698/upload-file-without-activeform-in-yii2
    public function upload($destination = null)
    {        
        if (is_null($destination)) {
            return false;
        }

        if ($this->validate()) {
            // var_dump($destination . '/' . $this->file->baseName . '.' . $this->file->extension);
            $this->file->saveAs($destination . '/' . $this->file->baseName . '.' . $this->file->extension);
            return true;
        } else {
            return false;
        }
    }
}