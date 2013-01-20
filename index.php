<?php
define('IN_QOOL',true);
error_reporting(E_ERROR);
//read the directory structure
require_once("simple_fn.php");
//check if the cms is installed
if(!$dirs = readDirFile()){
	include("install/install.php");
	$installer = new QoolInstaller();
	$installer->start();
}else{
	setIncludePath($dirs);
	//d(get_include_path());
	require_once("Zend/Session.php");

	$namespace = new Zend_Session_Namespace('Qool');
	if ($namespace->isLocked()) {
		$namespace->unLock();
	}
	//get the folder if needed
	amiInAfolder($dirs);

	//if the user is not logged in we just set some typical rights
	givemeGuestRights();

	//set the include path


	//now read configuration
	require_once("Zend/Config/Xml.php");
	$config = new Zend_Config_Xml('config/config.xml');

	//just a simple definition to make things readable
	define('DIR_SEP',$config->host->separator);
	define('APPL_PATH',$config->host->absolute_path);
	$_SESSION['SITE_URL'] = $config->host->http.$config->host->subdomain.$config->host->domain.$config->host->folder;

	//read the database table names and prefix
	$database = new Zend_Config_Xml('config'.DIR_SEP.'database.xml');
	$database = $database->toArray();
	$database = normalizeDbTables($database);
	//read the routes
	$routes = new Zend_Config_Xml('config'.DIR_SEP.'routes.xml');

	//read the application addons
	$addons = new Zend_Config_Xml('config'.DIR_SEP.'addons.xml','applications');
	$modules = new Zend_Config_Xml('config'.DIR_SEP.'addons.xml','modules');
	$widgets = new Zend_Config_Xml('config'.DIR_SEP.'addons.xml','widgets');



	//register autoloading
	require_once($dirs['structure']['lib'].DIR_SEP."Zend".DIR_SEP."Loader".DIR_SEP."Autoloader.php");
	$loader = Zend_Loader_Autoloader::getInstance();

	//register Qool
	$loader->registerNamespace('Qool_');
	//register the Templates
	$loader->registerNamespace('Templates_');

	//get the controller object
	$frontend = Zend_Controller_Front::getInstance();
	$frontend->setParam('noViewRenderer', true);
	$frontend->setParam('noErrorHandler', false);

	//lets route for the system routes
	$router = $frontend->getRouter();
	$router->addConfig($routes);

	//lets add the controllers
	$frontend->setControllerDirectory(array(
	'default' 	=> APPL_PATH.$dirs['structure']['controllers'].DIR_SEP.'frontend',
	'admin'		=> APPL_PATH.$dirs['structure']['controllers'].DIR_SEP.'backend',
	));

	//read the addons file
	$controllers = getControllers($dirs,$addons);
	$modules = getModules($dirs,$modules);
	$widgets = getWidgets($dirs,$widgets);


	//now register the controllers for these addons
	foreach ($controllers as $k=>$v){

		$frontend->addControllerDirectory(APPL_PATH.$v,$k);
	}

	//also add the routes foreach addon
	foreach ($controllers as $k=>$v){

		$addonRoutes = new Zend_Config_Xml(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$k.DIR_SEP.'routes.xml');
		$router->addConfig($addonRoutes);
	}

	//also add the routes for the admin backend
	$router->addConfig(new Zend_Config_Xml(APPL_PATH.$dirs['structure']['controllers'].DIR_SEP.'backend'.DIR_SEP.'routes.xml'));


	//attach the router to the frontend
	$frontend->setRouter($router);

	//we will be setting some registry values here
	//set the config
	Zend_Registry::set('dirs',$dirs);
	Zend_Registry::set('config',$config);
	Zend_Registry::set('home',$config->host->http.$config->host->subdomain.$config->host->domain.$config->host->folder);
	Zend_Registry::set('domain',$config->host->http.$config->host->subdomain.$config->host->domain);
	Zend_Registry::set('addons',$addons);
	Zend_Registry::set('modules',$modules);
	Zend_Registry::set('widgets',$widgets);
	Zend_Registry::set('controllers',$controllers);
	Zend_Registry::set('database',$database);
	Zend_Registry::set('controller','index');
	//dispatch
	$response = $frontend->dispatch();
//	d($frontend);
	//load the template class
	$tpl = new Qool_Template_Object();
	try{
		$tpl->show();
	}catch (Exception $e){
		
	}
}
?>