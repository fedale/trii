<?php 

namespace app\widgets;

use yii\base\Widget;

/**
 * 
 */
class ButtonModalWidget extends Widget
{
    public $url;

    public $headerText = 'Visualizza';

    public $labelText = 'Visualizza';

    public $cssClass = 'badge badge-success align-middle';

    public $labelClass = '<span class="fas fa-plus-square"></span> ';


    public function init()
    {
        $this->id = 'modal_' . $this->id;
        parent::init();
    }

    public function run()
    {
        return $this->render('_button_modal', [ 
            'id' => $this->id,
            'url' => $this->url,
            'headerText' => $this->headerText,
            'labelText' => $this->labelText,
            'cssClass' => $this->cssClass,
            'labelClass' => $this->labelClass,
        ]);
    }

}