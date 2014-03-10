<?php
require_once 'lib/Kendo/Autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_GET['type'];


    if ($type == 'save') {
        $files = $_FILES['files'];
        // Save the uploaded files
        for ($index = 0; $index < count($files['name']); $index++) {
            $file = $files['tmp_name'][$index];
            if (is_uploaded_file($file)) {
                move_uploaded_file($file, './incoming/' . $files['name'][$index]);
			}
        }
    } else if ($type == 'remove') {
        $fileNames = $_POST['fileNames'];
        // Delete uploaded files
        for ($index = 0; $index < count($fileNames); $index++) {
            unlink('./incoming/' . $fileNames[$index]);
        }
    }

    // Return an empty string to signify success
    echo '';

    exit;
}
// require_once 'include/header.php';
?>
<html>
    <head>
        <title>Home</title>
        <link href="../../content/css/web/kendo.common.min.css" rel="stylesheet" />
        <link href="../../content/css/web/kendo.rtl.min.css" rel="stylesheet" />
        <link href="../../content/css/web/kendo.default.min.css" rel="stylesheet" />
        <link href="../../content/css/dataviz/kendo.dataviz.min.css" rel="stylesheet" />
        <link href="../../content/css/dataviz/kendo.dataviz.default.min.css" rel="stylesheet" />
        <link href="../../content/shared/styles/examples-offline.css" rel="stylesheet">

        <script src="../../content/js/jquery.min.js"></script>
        <script src="../../content/js/kendo.all.min.js"></script>
        <script src="../../content/js/kendo.timezones.min.js"></script>
        <script src="../../content/shared/js/console.js"></script>
        <script src="../../content/shared/js/prettify.js"></script>
    </head>
<div style="width:45%">
<?php
$upload = new \Kendo\UI\Upload('files[]');
$upload->async(array(
        'saveUrl' => 'upload.php?type=save',
        'removeUrl' => 'upload.php?type=remove',
        'autoUpload' => false,
        'removeField' => 'fileNames[]'
       ))
	   ->upload('onUpload')
	   ->success('onSuccess')
	   ->error('onError');
	   

echo $upload->render();
?>
<script>
	function onSuccess(e){
	document.location = "parse.php?filename="+getFileInfo(e);		
		//kendoConsole.log("Success (" + e.operation + ") :: " + getFileInfo(e));
		
	}
	function onUpload(e){
		//kendoConsole.log("Uploading");
	}
	function onError(e){
		alert('Error: ' + e.operation);
	}
	function getFileInfo(e) {
        return $.map(e.files, function(file) {
            var info = file.name;
       return info;
        }).join(", ");
    }	
	</script>
</div>
<div class="console"></div>

