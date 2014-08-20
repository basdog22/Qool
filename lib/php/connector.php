<?php
session_start();
error_reporting(0); // Set E_ALL for debuging

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderConnector.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinder.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php';
// Required for MySQL storage connector
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
// Required for FTP connector support
//include_once dirname(__FILE__).'/elFinderVolumeDropbox.class.php';

//define('ELFINDER_DROPBOX_CONSUMERKEY', $_SESSION['ELFINDER_DROPBOX_CONSUMERKEY']);
//define('ELFINDER_DROPBOX_CONSUMERSECRET', $_SESSION['ELFINDER_DROPBOX_CONSUMERSECRET']);
/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}
$folder = ($_SESSION['QOOL_FOLDER'])?$_SESSION['QOOL_FOLDER']:'/';
$opts = array(
//	 'debug' => true,
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
			'path'          => "../../uploads/",         // path to files (REQUIRED)
			'URL'           => $_SESSION['SITE_URL'].'/uploads/', // URL to files (REQUIRED)
			'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
			'alias'			=>	'Home',
			'uploadAllow' 	=> array('image', 'application/x-shockwave-flash'), # allow png and flash
			'uploadDeny' => array('text') 
		)
	)
);


// run elFinder
$el = new elFinder($opts);
$connector = new elFinderConnector($el);
//echo "<pre>";print_r($el);
$connector->run();

