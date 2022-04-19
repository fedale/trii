<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\helpers\FileHelper;
use yii\helpers\ArrayHelper;

use yii\web\JsExpression;
use app\widgets\FancyTreeWidget\FancyTreeWidget;
use app\models\UploadNode;
use yii\web\UploadedFile;

class TreeController extends Controller
{
    /*
     * Render the FancyTree
     */
    public function actionView() {
        return $this->render('view', [
        ]);
    }

    /*
     * View information about a node
     * Return an array with node/file values
     * Callable only via AJAX
     */
    public function actionRead() {
        if (!Yii::$app->request->isAjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $post = Yii::$app->getRequest()->post();
        Yii::$app->response->format = Response::FORMAT_JSON;

        [ 
            'basename' => $basename, 
            'dirname' => $dirname, 
            'extension' => $extension, 
            'filename' => $filename
        ]  = \pathinfo($post['path']);

        $stat = \stat($post['path']);
        $mime = \finfo_file(\finfo_open(FILEINFO_MIME_TYPE), $post['path']);
        $encoding = \finfo_file(\finfo_open(FILEINFO_MIME_ENCODING), $post['path']);
        $size = $stat[7];
        $size_string = $this->format_bytes($stat[7]);
        $atime = $stat[8];
        $mtime = $stat[9];
        $permission = \substr(\sprintf('%o', \fileperms($post['path'])), -4);
        $fileowner = \getenv('USERNAME');

        return [
            'basename' => $basename, 
            'dirname' => $dirname, 
            'extension' => $extension, 
            'filename' => $filename, 
            'mime' => $mime, 
            'encoding' => $encoding, 
            'size' => $size, 
            'size_string' => $size_string, 
            'atime' => $atime, 
            'mtime' => $mtime, 
            'permission' => $permission, 
            'fileowner' => $fileowner
        ];
    }

     /*
     * 
     */
    public function actionRename() {
        if (!Yii::$app->request->isAjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $post = Yii::$app->getRequest()->post();
        $post['folder'] = filter_var($post['folder'], FILTER_VALIDATE_BOOLEAN);
        
        if ( $post['folder']) { // It is a folder
            $newDirectory = str_replace('/' . $post['previousFilename'], '/' . $post['filename'], $post['path']);
            \rename($post['path'], $newDirectory);   
        } else {
            \rename($post['path']  . '/' . $post['previousFilename'], $post['path'] . '/' . $post['filename']);
        }
    }

    /*
     * 
     */
    public function actionMove() {
        if (!Yii::$app->request->isAjax) {
            throw new \yii\web\BadRequestHttpException();
        }

        $post = Yii::$app->getRequest()->post();
        $post['folder'] = filter_var($post['folder'], FILTER_VALIDATE_BOOLEAN);
        
        switch( $action = $post['action'] ) {
            case "move":
            default:
            if ( $post['folder']) { // It is a folder
                FileHelper::copyDirectory($post['from'], $post['to'] . '/' . $post['filename']);
                FileHelper::removeDirectory($post['from']);
            } else {
                var_dump($post['from'] . '/' . $post['filename']);
                var_dump($post['to'] . '/' . $post['filename']);
                copy($post['from'] . '/' . $post['filename'], $post['to'] . '/' . $post['filename']);
            //    unlink($post['from'] . '/' . $post['filename']);
            }
        }

        return "OK";
    }

    /*
     * 
     */
    public function actionUpload() {
        $post = Yii::$app->getRequest()->post();
        $model = new UploadNode();
        $model->load($post, '');
        $model->file = UploadedFile::getInstanceByName('file');

        if ($model->upload($post['destination'])) {
            // file is uploaded successfully
            return "OK";
        }
        return $model->getErrors();
    }

    /*
     * 
     */
    public function actionDownload() {
        $post = Yii::$app->getRequest()->post();
        if ($post['folder']) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return false;
        }
        return $this->redirect(['/tree/download-item', 'path' => $post['path'], 'name' => $post['name']]);
    }

    /*
     * 
     */
    public function actionDownloadItem($path, $name) {
        $response = Yii::$app->getResponse();
        $response->format = yii\web\Response::FORMAT_RAW;
        return $response->sendFile($path . '/' . $name, $name, ['inline' => false])->send();
    }

    
    /*
     * 
     */
    public function actionDelete() {
        $post = Yii::$app->getRequest()->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post['folder'] = filter_var($post['folder'], FILTER_VALIDATE_BOOLEAN);

        if ( $post['folder']) { // It is a folder
            \rmdir( $post['path']);
        } else {
            \unlink($post['path'] . '/' . $post['name']);
        }

        return [
            'status' => 'ok'
        ];
    }

    /**
     * @param int => $size 
    */
    private function format_bytes(int $size){
        $base = \log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');  
        return \round(\pow(1024, $base-\floor($base)), 2) . '' . $suffixes[\floor($base)];
    }
}
