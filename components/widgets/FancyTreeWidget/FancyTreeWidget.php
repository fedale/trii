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
        if (isset($this->options['paths'])) {
            $paths = $this->options['paths'];
            unset($this->options['paths']);
            if (is_array($paths)) {
                $this->options['source'] = $this->getTree($paths);
            } else {
                throw new \yii\base\InvalidArgumentException('Paths option must be an array.');
            }
            
        } 

        $options = Json::encode($this->options);
        $view->registerJs("\n\$(\"#{$id}\").fancytree({$options});\n");
    }

    private function getTree(array $paths) {
        $tree = [];
        foreach ($paths as $k => $path) {
            $directory = $path['name'];
            $permissions = $path['permissions'];

            $tree[] = array(
                'title' => $directory,
                'folder' => true, 
                'expanded' => true, 
                'data-permissions' => $permissions,
                'children' => $this->getTreeFromPath($directory)
            );
        }
        return $tree;
    }

    /**
     * Scan dirs and files from initial directory
     */
    private function getTreeFromPath($path) {
        $tree = [];
        $directories = \scandir($path);

        foreach ( $directories as $k => $item ) {
            if ($item != '.' && $item != '..') {
                $p = $path . '/' . $item;
                if (\is_dir($p)) {
                    
                    $tree[] = array(
                        'title' => $item,
                        'folder' => true,
                        'expanded' => true,
                        'data-path' => $p, // item is a dir, we need it in path
                        'data-name' => $item,
                        'children' => $this->getTreeFromPath($p)
                    );
                } else {
                    $tree[] = array(
                        'title' => $item,
                        'data-path' => $path,
                        'data-name' => $item,
                    );
                }
            }
        }

        return $tree;
    }
}