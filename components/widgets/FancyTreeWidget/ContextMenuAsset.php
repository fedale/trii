<?php

namespace app\components\widgets\FancyTreeWidget;

use Yii;

/**
 * Asset bundle for ContextMenu
 */
class ContextMenuAsset extends \yii\web\AssetBundle
{
    public $sourcePath = __DIR__ ;

    /**
     * @inheritdoc
     */
    public $depends = [
        'app\widgets\FancyTreeWidget\FancyTreeAsset',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'dist/modules/jquery.contextMenu.js',
        // 'dist/modules/jquery.fancytree.contextMenu.js',
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'dist/modules/jquery.contextMenu.min.css',
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
    //    $this->setupAssets('css', [$this->css]);
        $this->setupAssets('js', $this->js);
        parent::init();
    }

}