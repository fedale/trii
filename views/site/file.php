<?php
use yii\web\JsExpression;
use app\widgets\FancyTreeWidget\FancyTreeWidget;



// Example of data.
$data = [
    ['title' => 'Node 1', 'key' => 1],
    [
        'title' => 'Folder 2', 
        'key' => '2', 
        'folder' => true, 
        'children' => [
            ['title' => 'Node 2.1', 'key' => '3'],
            ['title' => 'Node 2.2', 'key' => '4']            ]
        ]
];
?>

<?= FancyTreeWidget::widget([
    'options' =>[
        'source' =>  Yii::getAlias('@app/web/fancy'),
        'extensions' => ['dnd', 'menu'],
        'dnd' => [
            'preventVoidMoves' => true,
            'preventRecursiveMoves' => true,
            'autoExpandMS' => 400,
            'dragStart' => new JsExpression('function(node, data) {
                return true;
            }'),
            'dragEnter' => new JsExpression('function(node, data) {
                return true;
            }'),
            'dragDrop' => new JsExpression('function(node, data) {
                data.otherNode.moveTo(node, data.hitMode);
            }'),
        ],
        'menu' => [
            [
                'title' => "Edit <kbd>[F2]</kbd>",
                'cmd' => "rename",
                'uiIcon' => "ui-icon-pencil",
            ]
        ]
    ]
]) ?>