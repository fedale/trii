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

class SiteController extends Controller
{
    public function actionScanajax() {
        $post = Yii::$app->getRequest()->post();
        $post['folder'] = filter_var($post['folder'], FILTER_VALIDATE_BOOLEAN);
        
        switch( $action = $post['action'] ) {
            case "move":
            default:
            if ( $post['folder']) { // It is a folder
                FileHelper::copyDirectory($post['from'], $post['to'] . '/' . $post['filename']);
                FileHelper::removeDirectory($post['from']);
            } else {
                copy($post['from'], $post['to'] . '/' . $post['filename']);
                unlink($post['from']);
            }
        }

        return "OK";
    }

    public function actionRead() {
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

    public function actionDownload() {
        $post = Yii::$app->getRequest()->post();
        return $this->redirect(['/site/download2', 'path' => $post['path'], 'name' => $post['name']]);
    }

    public function actionDownload2($path, $name) {
        $response = Yii::$app->getResponse();
        $response->format = yii\web\Response::FORMAT_RAW;
        return $response->sendFile($path, $name, ['inline' => false])->send();
    }

    public function actionScandir() {
        $dir = Yii::getAlias('@app/web/fancy');
        $data = $this->listFolders($dir);
        
        return $this->render('scandir', [
            'data' => $data
        ]);
    }

    /**
     * @param int => $size = valor em bytes a ser formatado
    */
    private function format_bytes(int $size){
        $base = \log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');  
        return \round(\pow(1024, $base-\floor($base)), 2).''.$suffixes[\floor($base)];
    }

    private function listFolders($dir, $key = null) {
        $dh = scandir($dir);
        $return = [];

        foreach ($dh as $folder) {
            if ($folder != '.' && $folder != '..') {
                if (is_dir($dir . '/' . $folder)) {
                    $return[] = array(
                        'title' => $folder,
                        'folder' => true,
                        'expanded'=> true,
                        'data-content' => 'Here_Full_Path',
                        'children' => $this->listFolders($dir . '/' . $folder, $key)
                    );
                } else {
                    $return[] = [
                        'title' => $folder,
                        'data-content' => 'Here_Full_Path',
                    ];
                }
            }
        }
        return $return;
}


    public function actionFile()
    {
        return $this->render('file');
    }

    private function listDirs($dir) {
        return FileHelper::findDirectories($dir, [ 'recursive' => false ]);
    }

    private function listFiles($dir) {
        return FileHelper::findFiles($dir, [ 'recursive' => false ]);
    }

    public function actionFileBak()
    {
        // Example of data.
        $data = [
            [
                'title' => 'Node 1', 'key' => 1
            ],
            [
                'title' => 'Folder 2', 
                'key' => '2', 
                'folder' => true, 
                'children' => [
                    ['title' => 'Node 2.1', 'key' => '3'],
                    ['title' => 'Node 2.2', 'key' => '4']            ]
                ]
        ];

        $dataArray = [];
        $initialDirectory  = Yii::getAlias('@app/web/fancy');
        $directories = FileHelper::findDirectories($initialDirectory);
        $files = FileHelper::findFiles($initialDirectory);

        $k = 0;
        foreach ($directories as $directory) {
            $k++;
            $dir = str_replace($initialDirectory . '/', '', $directory);
            $titleArray = explode('/', $dir);
            $dataArray[$k]['title'] = end($titleArray);
            $dataArray[$k]['path'] = $dir;
            $dataArray[$k]['key'] = (string) $k;
            $dataArray[$k]['folder'] = true;
        }
        
        foreach ( $files as $k => $file ) {
            $files[$k] = str_replace($initialDirectory . '/', '', $file);
        }

        var_dump($dataArray, json_encode(array_values($dataArray)), $directories, $files);die();
        $k = 0;
        foreach ($directories as $directory) {
            $k++;
            $dir = str_replace($initialDirectory . '/', '', $directory);
            $dataArray[$k]['title'] = strrchr($dir, '/'); 
            
            $dataArray[$k]['key'] = (string) $k;
            $dataArray[$k]['folder'] = true;
        }

        var_dump($dataArray);die();
        $files = FileHelper::findFiles($initialDirectory);
        // var_dump($dataArray, $directories, $files);
        die();
        return $this->renderAjax('file');
    }


    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    

    

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
