<?php 
use ivankff\yii2ModalAjax\ModalAjax; // https://github.com/ivankff/yii2-modal-ajax
use yii\helpers\Url;
?>

<?= ModalAjax::widget([
    'id' => $id,
    'bootstrapVersion' => ModalAjax::BOOTSTRAP_VERSION_4,
    'header' => Yii::t('app', $headerText),
    'toggleButton' => [
        'label' => $labelClass . Yii::t('app',  $labelText),
        'class' => $cssClass, 
    ],
    'url' => Url::to([$url]), 
    'size' => 'modal-xl', // or '' (default) or 'modal-lg' (large) or 'modal-sm' (small)
    'ajaxSubmit' => true, // Submit the contained form as ajax, true by default
    'events'=>[
       /* ModalAjax::EVENT_MODAL_SHOW => new \yii\web\JsExpression("
            function(event, data, status, xhr, selector) {
                $(\"#assettype-name\").trigger('focus');
            }
       "),*/
    ],
]);
?>