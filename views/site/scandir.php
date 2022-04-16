<?php
use yii\web\JsExpression;
use app\widgets\FancyTreeWidget\FancyTreeWidget;
use app\widgets\FancyTreeWidget\ContextMenuWidget;

// Example of data.
$data = [
    ['title' => 'Node 1', 'key' => 1],
    [
        'title' => 'Folder 2', 
        'key' => '2', 
        'folder' => true, 
        'children' => [
            ['title' => 'Node 2.1', 'key' => '3'],
            ['title' => 'Node 2.2', 'key' => '4']
        ]
    ]
];

?>

<div class="row">
<div id="treewidget" style="width:33%">    
<?= FancyTreeWidget::widget([
    'options' =>[
        'paths' => [
            [
                'name' => Yii::getAlias('@app/web/fancy'), 
                'permissions' => ['view', 'rename', 'delete', 'move', 'download', 'upload']
            ],
            [
                'name' => Yii::getAlias('@app/web/fancy2'), 
                'permissions' => ['view']
            ],
        ],
        'source' => $data,
        'extensions' => ['dnd5'],
        // click callback to view node
        'click' => new JsExpression('
            

            function(event, data) {
                const path = data.node.data["data-path"];
                const filename = data.node.data["data-name"];
                const folder = data.node.folder || false;
                const permissions = data.node.getParentList()[0]["data"]["data-permissions"];

                function updateItemInfo(data) {
                    console.log(data);
                    data["filename"] ? $("#filename").html(data["filename"]) : $("#filename").html("");
                    data["basename"] ? $("#basename").html(data["basename"]) : $("#basename").html("");
                    data["dirname"] ? $("#dirname").html(data["dirname"]) : $("#dirname").html("");
                    data["extension"] ? $("#extension").html(data["extension"]) : $("#extension").html("");
                    data["filename"] ? $("#filename").html(data["filename"]) : $("#filename").html("");
                    data["mime"] ? $("#mime").html(data["mime"]) : $("#mime").html("");
                    data["encoding"] ? $("#encoding").html(data["encoding"]) : $("#encoding").html("");
                    data["size"] ? $("#size").html(data["size"]) : $("#size").html("");
                    data["size_string"] ? $("#size_string").html(data["size_string"]) : $("#size_string").html("");
                    data["atime"] ? $("#atime").html(data["atime"]) : $("#atime").html("");
                    data["mtime"] ? $("#mtime").html(data["mtime"]) : $("#mtime").html("");
                    data["permission"] ? $("#permission").html(data["permission"]) : $("#permission").html("");
                    data["fileowner"] ? $("#fileowner").html(data["fileowner"]) : $("#fileowner").html("");
                };

                if (!Object.values(permissions).includes("view")) {
                    let filename = "Non è possibile leggere info su questo file";
                    updateItemInfo({filename: filename});
                    return false;
                }

                $.ajax({
                    url: "/site/read",
                    type: "POST",
                    data: {path : path, filename: filename, folder: folder},
                    dataType : "json",
                    success:function(result){
                        console.log(result);
                        updateItemInfo(result);
                    }
                 }
                );
            }
        '),
        'dnd5' => [
            'preventVoidMoves' => true,
            'preventRecursion' => true,
            'autoExpandMS' => 400,
            'dragStart' => new JsExpression('function(node, data) {
                const permissions = data.node.getParentList()[0]["data"]["data-permissions"];
                    if (!Object.values(permissions).includes("move")) {
                        console.log("NOT DND");
                        return false;
                    }
                return true;
            }'),
            'dragEnter' => new JsExpression('function(node, data) {
                return true;
            }'),
            'dragDrop' => new JsExpression('function(node, data) {
                
                    const from = data.otherNode.data["data-path"];
                    const filename = data.otherNode.data["data-name"];
                    const folder = data.otherNode.folder || false;
                    const to = node.data["data-path"];
                    
                    data.otherNode.moveTo(node, data.hitMode);
                    
                /*    $.ajax({
                        url: "/site/scanajax",
                        type: "POST",
                        data: {from : from, to: to, filename: filename, folder: folder, action: "move"},
                        dataType : "json",
                        success:function(result){
                            console.log("success");
                            console.log(result);                            
                        }
                    });
                    */

                    /* This function MUST be defined to enable dropping of items on
                    * the tree.
                    */
                    let newNode,
                    transfer = data.dataTransfer,
                    sourceNodes = data.otherNodeList,
                    mode = data.dropEffect;

                    if( data.hitMode === "after" ){
                        sourceNodes.reverse();
                    }

                    // Drop from another node
                    if (data.otherNode) {
                        console.log("data.OtherNode");
                        data.otherNode.moveTo(node, data.hitMode);
                       
                        $.ajax({
                            url: "/tree/move",
                            type: "POST",
                            data: {from : from, to: to, filename: filename, folder: folder, action: "move"},
                            dataType : "json",
                            success:function(result){
                                console.log("success");
                                console.log(result);                            
                            }
                        });

                        if (mode === "move") {
                            data.otherNode.moveTo(node, data.hitMode);
                        } else {
                            newNode = data.otherNode.copyTo(node, data.hitMode);
                            if (mode === "link") {
                            newNode.setTitle("Link to " + newNode.title);
                            } else {
                            newNode.setTitle("Copy of " + newNode.title);
                            }
                        }
                    } else if (data.otherNodeData) {
                        console.log("from different frame or window, so we only have");
                        // Drop Fancytree node from different frame or window, so we only have
                        // JSON representation available
                        node.addChild(data.otherNodeData, data.hitMode);
                    } else if (data.files.length) {
                        console.log("data.files.length");
                        // Drop files
                        for(var i=0; i<data.files.length; i++) {
                            var file = data.files[i];
                            node.addNode( { title: file.name }, data.hitMode );
                            // var url = "https://example.com/upload",
                            //     formData = new FormData();

                            // formData.append("file", transfer.files[0])
                            // fetch(url, {
                            //   method: "POST",
                            //   body: formData
                            // }).then(function() { /* Done. Inform the user */ })
                            // .catch(function() { /* Error. Inform the user */ });
                        }
                    } else {
                        console.log("// Drop a non-node");
                        // Drop a non-node
                        node.addNode( { title: transfer.getData("text") }, data.hitMode );
                    }
                    
                    node.setExpanded();
                }
            '),
        ],
    ]
    ]);  
?>
<?php ContextMenuWidget::widget([
        'options' => [
            'selector' => '#fancytree_w0 span.fancytree-node span.fancytree-title',
            'items' => [
                /*'rename' =>  [
                    'name' => 'Rinomina', 
                    'icon' => 'rename',
                    'disabled' => new JsExpression('function(key, opt) {
                        const node = $.ui.fancytree.getNode(opt.$trigger);
                        const permissions = node.getParentList()[0]["data"]["data-permissions"];
                        
                        if (!Object.values(permissions).includes("rename")) {
                            return true;
                        }
                    }
                    '),
                    'callback' => new JsExpression('function(key, opt) {
                        return false;
                        const node = $.ui.fancytree.getNode(opt.$trigger);
                        const permissions = node.getParentList()[0]["data"]["data-permissions"];
                        console.log(permissions);
                        
                        if (!Object.values(permissions).includes("rename")) {
                            console.log("NOT DND");
                            return false;
                        };
                       // node.remove();
                        console.log(node, key, opt);
                        }
                    '),
                ],*/
                'delete' =>  [
                    'name' => 'Elimina', 
                    'icon' => 'delete',
                    'disabled' => new JsExpression('function(key, opt) {
                        const node = $.ui.fancytree.getNode(opt.$trigger);
                        const permissions = node.getParentList()[0]["data"]["data-permissions"];
                        
                        if (!Object.values(permissions).includes("delete")) {
                            return true;
                        }
                    }
                    '),
                    'callback' => new JsExpression('function(key, opt) {
                        const node = $.ui.fancytree.getNode(opt.$trigger);
                        node.remove();
                        console.log(node, key, opt);
                        }
                    '),
                ], 
                'download' =>  [
                    'name' => 'Scarica', 
                    'icon' => 'file',
                    'disabled' => new JsExpression('function(key, opt) {
                        const node = $.ui.fancytree.getNode(opt.$trigger);
                        const permissions = node.getParentList()[0]["data"]["data-permissions"];
                        
                        if (!Object.values(permissions).includes("download")) {
                            return true;
                        }
                    }
                    '),
                    'callback' => new JsExpression('function(key, opt) {
                        const node = $.ui.fancytree.getNode(opt.$trigger);
                        const name = node.data["data-name"];
                        const path = node.data["data-path"];
                        const folder = node.folder || false;
                        
                        $.ajax({
                            url: "/site/download",
                            type: "POST",
                            data: {name: name, path: path, folder: folder, action: "download"},
                            dataType : "json",
                            xhrFields: { responseType: "blob" },
                            success: function(data) {
                                console.log(data);
                                var a = document.createElement("a");
                                var url = window.URL.createObjectURL(data);
                                a.href = url;
                                a.download = "myfile.pdf";
                                document.body.append(a);
                                a.click();
                                a.remove();
                                window.URL.revokeObjectURL(url);
                            }
                        });
                    }
                    '),
                ], 
            ],
        ],
    ]);  ?>  
</div>
<div style="width:66%" id="iteminfo">
    <h3 id="filename"></h3>
    <p id="basename"></p>
    <p id="dirname"></p>
    <p id="extension"></p>
    <p id="filename"></p>
    <p id="mime"></p>
    <p id="encoding"></p>
    <p id="size"></p>
    <p id="size_string"></p>
    <p id="atime"></p>
    <p id="mtime"></p>
    <p id="permission"></p>
    <p id="fileowner"></p>
</div>
</div>
