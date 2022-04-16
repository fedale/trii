<?php

namespace app\widgets\FancyTreeWidget;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Yii 2 wrapper for the https://github.com/swisnl/jQuery-contextMenu
 */
class ContextMenuWidget extends Widget
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
        ContextMenuAsset::register($view);
        $id = 'fancytree_' . $this->id;

        if (isset($this->options['id'])) {
            $id = $this->options['id'];
            unset($this->options['id']);
        } else {
           echo Html::tag('div', '', ['id' => $id]);
        }

        $options = Json::encode($this->options);
        $view->registerJs("\n\$.contextMenu({$options});\n");
    }
}