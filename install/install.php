<?php 
if(!defined('IN_QOOL')){
	//sillence is gold
	die();
}

class QoolInstaller{
	function start(){
		if($_GET['trunc']){
			$this->truncate();
		}


		//here we need to check some things in order to see what to do next

		$step = 'init';

		if($_POST){
			switch ($_POST['formstep']){
				case "host":
					$xml = readLangFile('config/config.xml');
					$xml->host->http = $_POST['http'];
					//check if it ends with dot
					
					if(substr($_POST['subdomain'],-1)=="."){
						$xml->host->subdomain = $_POST['subdomain'];
					}elseif(trim($_POST['subdomain'])){
						$xml->host->subdomain = $_POST['subdomain'].".";
					}
					
					$xml->host->domain = $_POST['domain'];
					$xml->host->folder = $_POST['folder'];
					$xml->host->separator = $_POST['separator'];
					if($_POST['folder']){
						$xml->host->absolute_path = $_SERVER['DOCUMENT_ROOT'].$_POST['folder']."/";
					}else{
						$xml->host->absolute_path = $_SERVER['DOCUMENT_ROOT']."/";
					}

					$xml->asXML('config/config.xml');
					$step = 'db';
					//we will give some help to the tpl ;)
					$database = readLangFile('install/database.xml');
					//and we will assign the names passed from the post vars ;)
					$database = json_encode($database);
					$database = json_decode($database,1);
					$tables = $database['tables']['table'];

					break;
				case "db":
					$xml = readLangFile('config/config.xml');
					$xml->database->type = $_POST['type'];
					$xml->database->host = $_POST['host'];
					$xml->database->username = $_POST['username'];
					$xml->database->password = $_POST['password'];
					$xml->database->db = $_POST['db'];
					$xml->database->prefix = $_POST['prefix'];
					$_SESSION['dbprefix'] = $_POST['prefix'];
					$xml->asXML('config/config.xml');
					//we now need to create the database also.
					$this->createDatabase($_POST);
					//and after that, we need to insert some values so that the user will be able to login
					$this->addData();
					$step = 'site';
					break;
				case "site":
					$xml = readLangFile('config/config.xml');
					$xml->site->backend_title = 'Qool v2.0';
					$xml->site->frontend_title = $_POST['frontend_title'];
					$xml->site->description = 'Qool CMS is the next generation of content management';
					$xml->site->slogan = $_POST['slogan'];
					$xml->site->feed_copyright = 'This feed is powered by Qool CMS';
					$xml->site->feed_generator = 'Qool CMS v2.0';
					$xml->site->feed_author_name = 'Qool CMS v2.0';
					$xml->site->feed_author_email = 'admin@'.$xml->host->domain;
					$xml->site->feed_logo_image = 'http://www.qoolsoft.gr/feed_logo.png';
					$xml->site->help = 'on';
					$xml->asXML('config/config.xml');
					//create a salt
					$salt = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
					$saltlow = strtolower($salt);
					$saltnum = "0,1,2,3,4,5,6,7,8,9";
					$saltspecial = "!,@,#,%,^,&,*,(,)";
					$salt = $salt.",".$saltlow.",".$saltnum.",".$saltspecial;
					$salt = explode(",",$salt);
					shuffle($salt);
					$salt = implode("",$salt);
					$salt = substr($salt,1,8);
					$domain = $xml->host->domain;
					$step = 'user';
					break;
				case "user":
					//we need to add the user to the database. and give him root rights
					$this->connectDB();
					$this->addUser($_POST);

					$xml = readLangFile('config/config.xml');
					if(!is_array($xml->host->subdomain)){
						if(!is_array($xml->host->folder)){
							$domain = $xml->host->subdomain.$xml->host->domain.$xml->host->folder;
							$admin = $xml->host->subdomain.$xml->host->domain.$xml->host->folder.'/admin';
						}else{
							$domain = $xml->host->subdomain.$xml->host->domain;
							$admin = $xml->host->subdomain.$xml->host->domain.'/admin';
						}
					}else{
						if(!is_array($xml->host->folder)){
							$domain = $xml->host->domain.$xml->host->folder;
							$admin = $xml->host->domain.$xml->host->folder.'/admin';
						}else{
							$domain = $xml->host->domain;
							$admin = $xml->host->domain.'/admin';
						}
					}



					$this->insertSample($domain);
					//chmod as instructed by the directories_file.xml
					$this->doTheChmod();
					//and rename the file to directories.xml
					$this->renameDirsFile();
					$step = 'complete';
					break;
			}
		}
		include("install/tpl.php");
	}

	function truncate(){
		$dir = opendir("./");
		while ($file = readdir($dir)) {
			echo "Removing: ".$file."<br>";
			chmod($file,0777);
			$this->rrmdir($file);
			unlink($file);
		}
	}
	function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
	function insertSample($domain){
		$xml = readLangFile('config/database.xml');
		$xml = json_encode($xml);
		$xml = json_decode($xml,1);
		$xml = normalizeDbTables($xml);
		$db = $this->db;
		$query = "REPLACE INTO `{$xml['general_data']}` (`id`, `data_type`, `data_value`) VALUES
		('', 'top_box1', 'a:4:{s:5:\"title\";s:13:\"Qool CMS v2.0\";s:8:\"contents\";s:347:\"Welcome to your new powered by Qool CMS site. We have been working hard to create a CMS that will be able to do anything the user wants. <br/>\r\nYou can now go to the admin panel and start adding content, rip off these text widgets and add your own. Visit <a href=\"http://www.qool.gr/\">Qool CMS homepage</a> and find out how to do things with Qool.\";s:9:\"data_type\";s:8:\"top_box1\";s:8:\"username\";s:8:\"basdog22\";}'),
		('', 'footer_2', 'a:4:{s:5:\"title\";s:17:\"Available widgets\";s:8:\"contents\";s:287:\"There are 3 widgets available in a clean install of the system. These include the \"Text widget\" (used to create what you are reading now), the \"Menu widget\" which can be used to render Qool menus and the \"Feeds widget\" which can be used to render feeds from other sites or your own site.\";s:9:\"data_type\";s:8:\"footer_2\";s:8:\"username\";s:8:\"basdog22\";}'),
		('', 'top_box2', 'a:4:{s:5:\"title\";s:18:\"Addons independant\";s:8:\"contents\";s:315:\"Qool supports unlimited content types. Addons are extensions to Qool but you can do without them too. Qool has been built with both worlds (user and developer) in mind. <br/>\r\nYou can visit our <a href=\"http://www.modules.gr\">official addons site</a> and download addons, modules and widgets. Many of them are free.\";s:9:\"data_type\";s:8:\"top_box2\";s:8:\"username\";s:8:\"basdog22\";}'),
		('', 'top_box3', 'a:4:{s:5:\"title\";s:19:\"4 template engines!\";s:8:\"contents\";s:241:\"Qool supports 4 different template engines!!!<br/>\r\nYou can choose to use your prefered template engine to build templates. The supported template engines are:<br/>\r\n<ul>\r\n<li>PHP</li>\r\n<li>Smarty</li>\r\n<li>Twig</li>\r\n<li>Savant3</li>\r\n</ul>\";s:9:\"data_type\";s:8:\"top_box3\";s:8:\"username\";s:8:\"basdog22\";}'),
		('', 'top_box4', 'a:4:{s:5:\"title\";s:24:\"Extendable user profiles\";s:8:\"contents\";s:298:\"You can extend the user profiles by adding as many fields you want. For example you may want to create a fan site where a user would be able to choose their favorite soccer team or a social network where users need more profile fields than a typical site. With Qool CMS this is easy as 1-2-3<br/>\r\n\";s:9:\"data_type\";s:8:\"top_box4\";s:8:\"username\";s:8:\"basdog22\";}'),
		('', 'highlight', 'a:4:{s:5:\"title\";s:26:\"Do you want more features?\";s:8:\"contents\";s:110:\"<span><a href=\"http://www.qool.gr/\">Visit our site</a> to find out what else is possible with Qool v2.0</span>\";s:9:\"data_type\";s:9:\"highlight\";s:8:\"username\";s:8:\"basdog22\";}'),
		('', 'footer_3', 'a:4:{s:5:\"title\";s:17:\"Slots slots slots\";s:8:\"contents\";s:233:\"Qool templates can have reserved slots for widgets. The admin can move things around and place widgets in the slots. Widgets can be installed as standalone widgets or be installed by an addon or a module. Addons can also have modules\";s:9:\"data_type\";s:8:\"footer_3\";s:8:\"username\";s:8:\"basdog22\";}'),
		('', 'footer_1', 'a:4:{s:5:\"title\";s:12:\"The template\";s:8:\"contents\";s:225:\"This great template is named Arcana and comes from <a href=''http://html5up.net/''>HTML5up</a>. Qool comes bundled with 4 different templates (one for each template engine). You can choose to use any of them to build your site.\";s:9:\"data_type\";s:8:\"footer_1\";s:8:\"username\";s:8:\"basdog22\";}'),
		('', 'footer_4', 'a:4:{s:5:\"title\";s:13:\"A feed reader\";s:4:\"feed\";s:28:\"http://www.qool.gr/feed/blog\";s:9:\"data_type\";s:8:\"footer_4\";s:8:\"username\";s:8:\"basdog22\";}')";

		$smt = $db->query($query);

		//we also need to create the page content type
		$query = "REPLACE INTO `{$xml['content_types']}` (`id`, `title`, `mime`, `lib`, `headers`) VALUES
		('1', 'Page', 'text/html', 'default', 'text/html')";
		$db->query($query);
		//some data...
		$query = "REPLACE INTO `{$xml['data']}` (`id`, `group_id`, `is_taxonomy`, `name`, `value`, `use_pool`, `pool_type`, `order`) VALUES
		(1, 1, 0, 'content', 'editor', '0', '0', 2),
		(2, 1, 0, 'title', 'textinput', '0', '0', 1)";
		$db->query($query);
		//an object
		$query = "REPLACE INTO `{$xml['objects']}` (`id`, `slug`, `datestr`, `type_id`) VALUES
		(1, 'about', ".time().", 1)";
		$db->query($query);
		//and the object data
		$query = "REPLACE INTO `{$xml['object_data']}` (`id`, `object_id`, `name`, `value`) VALUES
		(1, 1, 'title', 'About'),
		(2, 1, 'content', '<p>This is an about page created with the default controller. You can delete it or edit it.</p>')";
		$db->query($query);
		//we also need to create the menus
		$query = "REPLACE INTO `{$xml['menus']}` (`id`, `title`, `taxonomy`) VALUES
		(1, 'Main', 0)";
		$db->query($query);
		//and add some items
		$query = "REPLACE INTO `{$xml['menu_items']}` (`id`, `menu_id`, `is_special`, `special`, `special_object`, `title`, `link`, `objectlink`, `link_title`, `link_target`, `content`, `parent`) VALUES
		(1, 1, 0, '', 0, 'Qool', 'http://{$domain}', 0, 'Welcome to Qool CMS', 0, '', 0),
		(2, 1, 0, '0', 0, 'About', '', 1, 'Sample page', 0, '', 0)";
		$db->query($query);
	}

	function doTheChmod(){
		$xmli = readLangFile('config/config.xml');

		require_once("Zend/Config/Xml.php");
		$dirs = new Zend_Config_Xml('config/directories_file.xml');
		$structure = $dirs->structure;
		foreach ($structure as $k=>$v){
			//chmod($k,$v->mode);
		}
		$xml = readLangFile('config/directories_file.xml');
		$xml->special->folder = $xmli->host->folder;
		$xml->asXML('config/directories_file.xml');
	}

	function renameDirsFile(){
		rename('config/directories_file.xml','config/directories.xml');
	}

	function addUser($post){
		$xml = readLangFile('config/database.xml');
		$xml = json_encode($xml);
		$xml = json_decode($xml,1);
		$xml = normalizeDbTables($xml);
		$db = $this->db;
		//md5 it
		$post['password'] = md5($post['password']);
		$query = "REPLACE INTO `{$xml['users']}` (`id`, `username`, `password`, `email`) VALUES
		('1', '{$post['username']}', '{$post['password']}', '{$post['email']}')";
		$smt = $db->query($query);
		//we need to add the user to the root user group
		$query = "REPLACE INTO `{$xml['user_to_groups']}` (`id`, `uid`, `gid`) VALUES
		(1, 1, 1)";
		$smt = $db->query($query);
	}

	function addData(){
		$xml = readLangFile('config/database.xml');
		$xml = json_encode($xml);
		$xml = json_decode($xml,1);
		$xml = normalizeDbTables($xml);
		$db = $this->db;
		$query = "REPLACE INTO `{$xml['user_groups']}` (`id`, `title`, `level`) VALUES
		('1', 'Root', 1),
		('2', 'Admin', 2),
		('3', 'Editor', 500),
		('4', 'Member', 6000),
		('5', 'Visitor', 8000)";
		$smt = $db->query($query);
	}

	function unzip($file,$destination,$folderByName=false){
		$zip = new ZipArchive();
		if ($zip->open($file) !== TRUE) {
			die("File does not exist!!!");
		}

		$zip->extractTo($destination);
		$zip->close();
		return true;
	}

	function createDatabase($post){
		$xml = readLangFile('config/config.xml');
		setIncludePath(array('special'=>array('folder'=>$xml->host->folder)));

		try {
			$dbconfig = array(
			'host'=>$post['host'],
			'username'=>$post['username'],
			'password' =>$post['password'],
			'dbname'=>$post['db']
			);
			//here we check for database type
			switch ($xml->database->type){
				case "mysql":
					require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
					$db = new Zend_Db_Adapter_Pdo_Mysql($dbconfig);
					$db->getConnection();
					$db->setFetchMode(Zend_Db::FETCH_ASSOC);
					$smt = $db->query("SET NAMES 'utf8'");
					$smt->execute();
					$this->db = $db;
					$this->createDBTables($post);
					break;
				default:
					require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
					$db = new Zend_Db_Adapter_Pdo_Mysql($dbconfig);
					$db->getConnection();
					$db->setFetchMode(Zend_Db::FETCH_ASSOC);
					$smt = $db->query("SET NAMES 'utf8'");
					$smt->execute();
					$this->db = $db;
					$this->createDBTables($post);
			}

		} catch (Zend_Db_Adapter_Exception $e) {
			$this->triggerError('connect_error');
		} catch (Zend_Exception $e) {
			$this->triggerError('db_factory_error');
		}
	}

	function createDBTables($post){
		$xml = readLangFile('config/config.xml');
		$prefix = $post['prefix'];
		$db = $this->db;
		//now we will open the database file
		$database = readLangFile('install/database.xml');
		//and we will assign the names passed from the post vars ;)
		$database = json_encode($database);
		$database = json_decode($database,1);
		$originaldb = $database;
		foreach ($database['tables']['table'] as $k=>$v){
			$database['tables']['table'][$k]['name'] = $post[$database['tables']['table'][$k]['name']];
		}
		//we now have the database array we need to create our db.
		//lets loop through and create our tables
		foreach ($database['tables']['table'] as $k=>$v){

			//lets create our query
			/*CREATE TABLE `qool_cms`.`1` (
			`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`menu_id` INT( 11 ) NOT NULL ,
			`title` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
			INDEX ( `menu_id` )
			) ENGINE = MYISAM ;*/
			$query = "CREATE TABLE IF NOT EXISTS `{$post['db']}`.`{$prefix}{$v['name']}` (";
			$indexes = array();
			$collation = array();
			$i = 0;
			foreach ($v['fields'] as $field=>$attr){
				if($i>0){
					$query .= ",";
				}
				$i++;
				$attributes = $attr['@attributes'];

				$attributes['type'] = strtoupper($attributes['type']);
				$query .= "`{$field}` ";
				if($attributes['type']=='INT' || $attributes['type']=='VARCHAR' || $attributes['type']=='TINYINT'){
					$query .= "{$attributes['type']}({$attributes['length']}) ";
				}else{
					$query .= "{$attributes['type']} ";
				}
				if($attributes['collation']){
					$collation = explode("_",$attributes['collation']);
					$query .= "CHARACTER SET {$collation[0]} COLLATE {$attributes['collation']} ";
				}
				if($attributes['null']){
					$query .= "NULL ";
				}else{
					$query .= "NOT NULL ";
				}
				if($attributes['ai']){
					$query .= "AUTO_INCREMENT ";
				}
				if($attributes['index']=='primary'){
					$query .= "PRIMARY KEY ";
				}


				if($attributes['index'] && $attributes['index']!='primary'){

					$indexes[] = $field;
				}
			}
			if(count($indexes)>0){
				$query .= ", INDEX ( ";
				$theindex = implode("`,`",$indexes);
				$query .= "`{$theindex}`";
				$query .= " )";
			}
			$query .= ") ENGINE = MYISAM";
			//echo $query."<br><br>";

			try{
				unset($smt);
				$smt = $db->query($query);

			}catch (Exception $e){

				echo $e->getMessage()."<br>";
			}
			/**/
		}
		//right now, we are supposed to have installed the database. We now need to create the reference file for the db
		$dbXML = '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL;
		$dbXML .= '<database>'.PHP_EOL;
		$dbXML .= '    <tables>'.PHP_EOL;
		foreach ($originaldb['tables']['table'] as $k=>$v){


			$dbXML .= "        <{$v['name']}>{$database['tables']['table'][$k]['name']}</{$v['name']}>".PHP_EOL;
		}
		$dbXML .= '    </tables>'.PHP_EOL;
		$dbXML .= '<prefix>'.$prefix.'</prefix>'.PHP_EOL;

		$dbXML .= '</database>';
		$thedbfile = new SimpleXMLElement($dbXML);
		$thedbfile->asXML('config/database.xml');
	}

	function createDirectoriesFile(){

	}

	private function connectDB(){
		$xml = readLangFile('config/config.xml');
		setIncludePath(array('special'=>array('folder'=>$xml->host->folder)));
		try {
			$dbconfig = array(
			'host'=>$xml->database->host,
			'username'=>$xml->database->username,
			'password' =>$xml->database->password,
			'dbname'=>$xml->database->db
			);

			//here we check for database type
			switch ($xml->database->type){
				case "mysql":
					require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
					$db = new Zend_Db_Adapter_Pdo_Mysql($dbconfig);
					$db->getConnection();
					$db->setFetchMode(Zend_Db::FETCH_ASSOC);
					$smt = $db->query("SET NAMES 'utf8'");
					$smt->execute();
					break;
				default:
					require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
					$db = new Zend_Db_Adapter_Pdo_Mysql($dbconfig);
					$db->getConnection();
					$db->setFetchMode(Zend_Db::FETCH_ASSOC);
					$smt = $db->query("SET NAMES 'utf8'");
					$smt->execute();
			}

		} catch (Zend_Db_Adapter_Exception $e) {
			$this->triggerError($this->language['db_connect_error']);
		} catch (Zend_Exception $e) {
			$this->triggerError($this->language['db_factory_error']);
		}
		$this->db = $db;
	}

	function triggerError($msg){
		echo $msg;
	}
}
?>