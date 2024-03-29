<?php

namespace app\components\widgets\FancyTreeWidget;

use Yii;

/**
 * Asset bundle for fancytree Widget
 */
class FancytreeAsset extends \yii\web\AssetBundle
{
    public $sourcePath = __DIR__ ;

    public $skin = 'dist/skin-lion/ui.fancytree';

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset'
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'dist/jquery.fancytree-all-deps.min.js',
        'dist/modules/jquery.fancytree.dnd.js',
        'dist/modules/jquery.edit.js',
    ];

     /**
     * @inheritdoc
     */
    public $css = [
        'dist/skin-lion/ui.fancytree.css',
    ];

    /**
     * Set up CSS and JS asset arrays based on the base-file names
     * @param string $type whether 'css' or 'js'
     * @param array $files the list of 'css' or 'js' basefile names
     */
    protected function setupAssets($type, $files = [])
    {
        $srcFiles = [];
        $minFiles = [];
        foreach ($files as $file) {
            $srcFiles[] = "{$file}.{$type}";
            $minFiles[] = "{$file}.min.{$type}";
        }
        if (empty($this->$type)) {
            $this->$type = YII_DEBUG ? $srcFiles : $minFiles;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setupAssets('css', [$this->skin]);
        $this->setupAssets('js', $this->js);
        parent::init();
    }

}