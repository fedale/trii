<?php

namespace app\widgets\FancyTreeWidget;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Yii 2 wrapper for the https://github.com/mar10/fancytree
 */
class FancyTreeWidget extends Widget
{
    /**
     * @var array
     */
    public $options = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerAssets();
    }

    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        FancyTreeAsset::register($view);
        $id = 'fancytree_' . $this->id;

        if (isset($this->options['id'])) {
            $id = $this->options['id'];
            unset($this->options['id']);
        } else {
           echo Html::tag('div', '', ['id' => $id]);
        }
        if (isset($this->options['path'])) {
            $path = $this->options['path'];
            unset($this->options['path']);
            $this->options['source'] = $this->getTree($path);
        } 

        $options = Json::encode($this->options);
        $view->registerJs("\n\$(\"#{$id}\").fancytree({$options});\n");
    }

    /**
     * Scan dirs and files from initial directory
     */
    private function getTree($path) {
        $tree = [];
        $directories = \scandir($path);

        foreach ( $directories as $k => $directory ) {
            if ($directory != '.' && $directory != '..') {
                $p = $path . '/' . $directory;
                if (\is_dir($p)) {
                    
                    $tree[] = array(
                        'title' => $directory,
                        'folder' => true,
                        'expanded' => true,
                        'data-path' => $p,
                        'data-name' => $directory,
                        'children' => $this->getTree($p)
                    );
                } else {
                    $tree[] = array(
                        'title' => $directory,
                        'data-path' => $p,
                        'data-name' => $directory,
                    );
                }
            }
        }

        return $tree;
    }
}