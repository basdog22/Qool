<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Qool File Manager (elfinder)</title>
		<script type="text/javascript" src="../editor/tiny_mce_popup.js"></script>
		<!-- jQuery and jQuery UI (REQUIRED) -->
		<link rel="stylesheet" type="text/css" media="screen" href="../../css/jquery-ui.smoothness.css">
		<script type="text/javascript" src="../jquery.1.7.2.min.js"></script>
		<script type="text/javascript" src="../jquery-ui-1.8.23.custom.min.js"></script>

		<!-- elFinder CSS (REQUIRED) -->
		<link rel="stylesheet" type="text/css" media="screen" href="../../css/elfinder.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="../../css/theme.css">

		<!-- elFinder JS (REQUIRED) -->
		<script type="text/javascript" src="../elfinder/elfinder.min.js"></script>

		<!-- elFinder initialization (REQUIRED) -->
		<script type="text/javascript">
            var FileBrowserDialogue = {
                init : function () {
                    // Here goes your code for setting your custom things onLoad.
                },
                mySubmit : function (URL) {
                    
                    var win = tinyMCEPopup.getWindowArg("window");

                    // insert information now
                    win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

                    // are we an image browser
                    if (typeof(win.ImageDialog) != "undefined") {
                        // we are, so update image dimensions...
                        if (win.ImageDialog.getImageData)
                            win.ImageDialog.getImageData();

                        // ... and preview if necessary
                        if (win.ImageDialog.showPreviewImage)
                            win.ImageDialog.showPreviewImage(URL);
                    }

                    // close popup window
                    tinyMCEPopup.close();
                }
            }

            tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);
            $().ready(function() {
                var elf = $('#elfinder').elfinder({
                    url : '../../php/connector.php?type=<?php echo $_GET['type']; ?>',
                    getfile : {
                        onlyURL : true,
                        multiple : false,
                        folders : false
                    },
                    getFileCallback : function(url) {
                        path = url;
                        path = path.replace("lib/php/","");
                        path = path.replace("../../","");
                        FileBrowserDialogue.mySubmit(path);
                    }                     
                }).elfinder('instance');            
            });
        </script>
	</head>
	<body>
		<div id="elfinder"></div>
	</body>
</html>