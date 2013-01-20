<?php
class Qool_Frontend_Action extends Zend_Controller_Action{

	var $config = array();
	var $applications = array();
	var $language = array();
	var $db = array();
	//the cache switch
	var $hasCache = true;
	//the cache object
	var $cache = array();
	//the controller prefix. We need this for cache and more
	var $prefix = 'Qool_Frontend_';
	//the controllers array. Just a quick hack
	var $controllers = array();
	//the directory structure
	var $dirs = array();
	//our template engine
	var $tplEngine = 'php';
	//the current template
	var $theme = 'default';
	//the template data
	var $tpl = array();
	//the modules
	var $modules = array();
	//the http of the cms
	var $http_location = '';
	//the content types the module can handle
	var $can_handle = array();
	//the addon settings. used only when not on the default module
	var $addonSettings = array();
	//the request data
	var $data = array();
	//the scripts quee
	var $queeScript = array();
	//the styles quee
	var $queeStyle = array();

	public function init(){
		Zend_Registry::set('Qool_Module','frontend');
		Zend_Registry::set('tplOverride','default');
		Zend_Registry::set('module','main');
		$this->data = $data = $this->_request->getParams();
		$this->level = 8000;
		//set some values needed by Qool
		$this->config = Zend_Registry::get('config');
		$this->applications = Zend_Registry::get('addons');
		$this->modules = Zend_Registry::get('modules');

		$this->widgets = Zend_Registry::get('widgets');
		$this->addons = Zend_Registry::get('controllers');
		$this->dirs = Zend_Registry::get('dirs');
		$this->language = $this->buildLanguage();
		$this->tplEngine = $this->config->template->frontend->engine;
		$this->theme = $this->config->template->frontend->title;
		$this->http_location = $this->config->host->http.$this->config->host->subdomain.$this->config->host->domain.$this->config->host->folder.
		//collect hooks
		$this->collectHooks();

		Zend_Registry::set('theme',$this->theme);




		//connect to database

		$this->connectDB();

		//get associated content
		$this->associateContent($data);
		//set up our template engine
		$this->setupTemplate();

		$this->toTpl('qoolrequest',$data);
		$this->toTpl('qool',$this);
		$this->requirePriviledges();
		//set up the cache
		$this->setupCache('Qool');
		Zend_Registry::set('tpl',$this->tpl);
		//get all actions that addons support

		if($_SESSION['user']['level']==1){
			$this->collectAddonCreationActions();
			$this->collectAddonMenuActions();
			$this->collectAvailableContent();
			$this->loadAdminMenus();
		}
		//create shortcuts,tasks and other qool things ;)
		$this->gatherGeneralData(array('tasks','shortcuts'));
		//if the module is other than default, we need to load the addon settings
		if($data['module']!='default'){
			$this->loadAddonSettings($data['module']);
			$this->loadAddonTemplates($data['module']);
		}
		$this->loadModules();

		$this->loadMenus();
		$this->loadTemplateSettings();
		if($_SESSION['message']){
			$data = $_SESSION['message'];
			$data = $this->doQoolHook('front_pre_assign_action_message',$data);
			$this->toTpl('message',array("message"=>$data['message'],"type"=>$data['msgtype']));
		}

	}

	function postDispatch(){
		$this->loadWidgets();
	}

	function getUserObject($uname){
		$t = $this->getDbTables();
		$uname = $this->quote($uname);
		$u = $t['users'];
		$u2g = $t['user_to_groups'];
		$g = $t['user_groups'];
		$sql = "SELECT $u.username,$g.level FROM $u,$g,$u2g WHERE
			$u.username=$uname AND $u2g.uid=$u.id AND $u2g.gid=$g.id";
		$u = $this->selectRow($sql);
		return $u;
	}

	function addMessage($data){
		$_SESSION['message'] = $data;
	}

	function sendMail($user,$data){
		$mail = new Zend_Mail('UTF-8');
		$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
		$mail->setBodyHtml($data['message']);
		$mail->setFrom($data['from']);
		$mail->addTo($data['to'], $user['username']);
		$mail->setSubject($data['subject']);
		$mail->send();
	}

	function consumeFeed($feed){
		$feed = $this->doQoolHook('front_pre_consume_feed',$feed);
		try{
			$name = $feed;
			if(!$widget = $this->loadCache('Feed_'.md5($name))){
				$feed = Zend_Feed_Reader::import($feed);

				$widget['title'] = $feed->getTitle();

				$content = '<ul class="nav">';
				foreach ($feed as $v){
					$content .= "<li><a class='pop' data-original-title='".$v->getTitle()."' data-content='".$v->getDescription()."' target='_blank' href='".$v->getLink()."'>".$v->getTitle()."</a></li>";
				}
				$content .= '</ul>';
				$widget['content'] = $content;
				$this->cacheData($widget,'Feed_'.md5($name));
			}
			$widget = $this->doQoolHook('front_feed_widget',$widget);
			return $widget;
		}catch (Exception $e){
			return false;
		}
	}

	function associateContent($data){
		//get all content that can be shown by the module
		$types = $this->getContentTypeByLib($data['module']);
		$types = $this->doQoolHook('front_pre_associate_content',$types);
		$this->can_handle = $types;
	}

	function collectAddonCreationActions(){
		$addons = $this->addons;
		$apps = $this->applications->toArray();
		$dirs = $this->dirs;
		$actions = array();
		foreach ($apps['addon'] as $k=>$v){
			$levels[$v['name']] = $v['adminlevel'];
		}
		foreach ($addons as $k=>$v){
			if($levels[$k]<=$this->level){
				$addon = new Zend_Config_Xml(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$k.DIR_SEP.'addon.xml');
				$addonActions = $addon->actions->backend->creation->toArray();
				foreach ($addonActions as $ki=>$vi){
					$ki = str_replace("_","/",$ki);
					$normal[$ki] = $vi;
				}
				$actions = array_merge($actions,$normal);
			}
		}
		$actions = $this->doQoolHook('front_post_collectaddon_creation_actions',$actions);
		$this->tpl->assign('addonCreationActions',$actions);
	}

	function collectAddonMenuActions(){
		$addons = $this->addons;
		$apps = $this->applications->toArray();
		$dirs = $this->dirs;
		$actions = array();
		foreach ($apps['addon'] as $k=>$v){
			$levels[$v['name']] = $v['adminlevel'];
		}
		foreach ($addons as $k=>$v){
			if($levels[$k]<=$this->level){
				$addon = new Zend_Config_Xml(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$k.DIR_SEP.'addon.xml');
				$addonActions = $addon->actions->backend->general->toArray();
				foreach ($addonActions as $ki=>$vi){
					$ki = str_replace("_","/",$ki);
					$normal[$ki] = $vi;
				}
				$actions[$k] = $normal;

			}
		}
		$actions = $this->doQoolHook('front_post_collectaddon_menu_actions',$actions);
		$this->tpl->assign('addonMenuActions',$actions);
	}

	function loadAdminMenus(){
		$menus['content'] = array(
		'contentlist'	=>	'Content Types List',
		'datafields'	=>	'Data Fields',
		'taxonomies'	=>	'Taxonomies',
		'menus'			=>	'Menus',
		'filemanager'	=>	'File Manager'
		);

		$menus['system'] = array(
		'languagelist'	=>	'Languages',
		'addonslist'	=>	'Addons',
		'host'			=>	'Host Settings',
		'db'			=>	'Database Settings',
		'site'			=>	'Site Settings',
		'cache'			=>	'Cache Settings',
		'theme'			=>	'Layout Settings',
		'thirdparty'	=>	'Third Party Tools',
		'users'			=>	'Users Administration'
		);

		$menus = $this->doQoolHook('front_post_admin_menus_creation',$menus);
		$this->toTpl('adminmenus',$menus);
	}

	function getDbTables(){
		return Zend_Registry::get('database');
	}

	function buildLanguage(){
		$config = $this->config;
		Zend_Registry::set('currentlang',$config->languages->frontend->language);
		//also set the language shortcode needed by some libs
		Zend_Registry::set('langcode',$config->languages->frontend->shortname);
		$dirs = $this->dirs;

		//read the system language and the user language available
		$systemLang = readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$config->languages->frontend->language.DIR_SEP.'language.xml');
		$userLang =  readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$config->languages->frontend->language.DIR_SEP.'user.xml');
		$language = buildLanguage($systemLang,$userLang);
		$language = $this->doQoolHook('front_pre_language_build',$language);
		Zend_Registry::set('language',$language);
		return $language;
	}

	function selectAll($sql){
		$c = $this->db->fetchAll($sql);
		return $c;
	}

	function selectAllPaged($sql,$records=20){
		//set the current page
		$data = $this->_request->getParams();
		if($data['page'] && $data['page']>1){
			$this->curPage = ((int) $data['page'])-1;
		}else{
			$this->curPage = 0;
		}
		$this->records_per_page = $records;
		$this->toTpl('curpage',$this->curPage);
		$from = $this->curPage*$records;

		$p = $this->db->fetchAll($sql);
		$this->pager = $this->paginate(count($p));

		$this->toTpl('pager',$this->pager);

		$c = $this->db->fetchAll($sql." LIMIT {$from},{$records}");
		return $c;
	}



	function delete($table,$id,$field='id'){
		$this->db->delete($table,"$field=$id");
	}

	function update($table,$data,$id,$field='id',$extrasql=''){
		$this->db->update($table,$data,"`{$field}`=".$id.$extrasql);
	}

	function save($table,$data){
		$this->db->insert($table,$data);
		return $this->db->lastInsertId();
	}

	function replace($table,$data,$id,$field='id'){
		$this->delete($table,$this->quote($id),$field);
		$this->save($table,$data);
	}

	function quote($val){
		return $this->db->quote($val);
	}
	function selectRow($sql){
		try{
			$c = $this->db->fetchRow($sql);
		}catch (Exception $e){
			//echo $sql."<br>";
		}
		return $c;
	}

	function gatherGeneralData(){

		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['general_data']}";
		$r = $this->selectAll($sql);
		foreach ($r as $k=>$v){
			$general[$v['data_type']][$v['id']] = unserialize($v['data_value']);
		}
		$general = $this->doQoolHook('front_post_gather_general_data',$general);

		$this->toTpl('general_data',$general);
	}

	public function cacheData($data,$name){

		if($this->hasCache){
			//lets do some prefixing...
			$cacheId = $this->prefix.$name."_".md5($name);
			//here we load data if they exist in the cache or we save data if not
			$this->cache->save($data,$cacheId);

		}
		return $data;
	}

	public function loadCache($name){

		if($this->hasCache){
			$cacheId = $this->prefix.$name."_".md5($name);
			if($data = $this->cache->load($cacheId)){

				return $data;
			}
			return false;
		}
		return false;
	}

	function collectAvailableContent(){
		$t = $this->getDbTables();
		$dg = $t['content_types'];
		$sql = "SELECT * FROM $dg";
		$sel = $this->selectAll($sql);
		$sel = $this->doQoolHook('front_post_collect_available_content',$sel);
		$this->tpl->assign('contentAvailable',$sel);
	}

	function doQoolHook($a,$data=false) {
		$this->keepHooksLog($a);
		$hooks = $this->getRegisteredHooks();

		foreach ($hooks as $hooki){
			foreach ($hooki as $hook)	{

				if ($hook['name'] == $a) {

					try{
						include_once($hook['caller_file']);
						if(is_array($hook['function'])){
							return $data;
						}else{
							if(function_exists($hook['function'])){
								return $hook['function']($this,$data);
							}
							return $data;
						}
						return $data;
					}catch (Exception $e){

						return $data;
					}
				}

			}
			return $data;
		}
		return $data;
	}

	function collectHooks(){
		$addons = $this->addons;
		$apps = $this->applications->toArray();
		$dirs = $this->dirs;
		$actions = array();
		//get the addons hooks first
		$hooks = array();
		foreach ($addons as $k=>$v){
			if($levels[$k]<=$this->level){
				if(file_exists(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$k.DIR_SEP.'addon.xml')){
					$addon = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$k.DIR_SEP.'addon.xml');
					$addon = $this->jsonArray($addon);
					$hooks = $addon['actions']['frontend']['hooks'];

					if(count($hooks)>0 && $hooks['hook']){

						if(!$hooks['hook']['name']){
							foreach ($hooks['hook'] as $w=>$h){
								$hooks['hook'][$w]['caller_file'] = APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$k.DIR_SEP.'func.php';

							}

							$actions = array_merge($actions,$hooks);
						}else{

							$hooks['hook']['caller_file'] = APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$k.DIR_SEP.'func.php';
							$actions['hook'][] = $hooks['hook'];

						}
					}
				}
			}
		}

		$this->registerHooks($actions);
		//now get the module hooks
		$addons = $this->modules;

		$ahooks = array();
		foreach ($addons as $k=>$v){
			if(file_exists(APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$k.DIR_SEP.'addon.xml')){
				$addon = readLangFile(APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$k.DIR_SEP.'addon.xml');
				$addon = $this->jsonArray($addon);

				$ahooks = $addon['actions']['frontend']['hooks'];


				if(count($ahooks)>0 && $ahooks['hook']){
					if(!$ahooks['hook']['name']){
						foreach ($ahooks['hook'] as $w=>$h){
							$ahooks['hook'][$w]['caller_file'] = APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$k.DIR_SEP.'func.php';

						}
						$actions = array_merge($actions,$ahooks);
					}else{

						$ahooks['hook']['caller_file'] = APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$k.DIR_SEP.'func.php';
						$actions['hook'][] = $ahooks['hook'];
					}
				}
			}
		}
		$this->registerHooks($actions);

		//now get the widgets hooks
		$addons = $this->widgets;
		$bhooks = array();
		foreach ($addons as $k=>$v){
			if(file_exists(APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$k.DIR_SEP.'addons.xml')){
				$addon = readLangFile(APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$k.DIR_SEP.'addons.xml');
				$addon = $this->jsonArray($addon);
				$bhooks = $addon['actions']['frontend']['hooks'];
				if(count($bhooks)>0 && $bhooks['hook']){
					if(!$bhooks['hook']['name']){
						foreach ($bhooks['hook'] as $w=>$h){
							$bhooks['hook'][$w]['caller_file'] = APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$k.DIR_SEP.'func.php';

						}
						$actions = array_merge($actions,$bhooks);
					}else{

						$bhooks['hook']['caller_file'] = APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$k.DIR_SEP.'func.php';
						$actions['hook'][] = $bhooks['hook'];
					}
				}
			}
		}

		$this->registerHooks($actions);

	}

	function getUserByEmail($email){
		$t = $this->getDbTables();
		$email = $this->quote($email);
		$sql = "SELECT * FROM {$t['users']} WHERE `email`=$email";
		return $this->selectRow($sql);
	}

	public function addUser($data){
		$t = $this->getDbTables();
		//create the user
		$uid = $this->save($t['users'],array('username'=>$data['username'],'email'=>$data['email'],'password'=>md5($data['password'])));
		//and add the user to the group
		$gid = $this->getUserGroupIdByLevel(6000);
		$this->save($t['user_to_groups'],array('uid'=>$uid,'gid'=>$gid));
		$params = array("message"=>$this->t("User Created"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('index', 'profiles','profiles');
	}

	function getUserGroupIdByLevel($level){
		$t = $this->getDbTables();
		$sql = "SELECT id FROM {$t['user_groups']} WHERE `level`=$level";
		$r = $this->selectRow($sql);
		return $r['id'];
	}

	function registerHooks($hooks){
		foreach ($hooks['hook'] as $k=>$hook){
			$this->hooks['hook'][] = $hook;
		}

	}

	function getRegisteredHooks(){
		return $this->hooks;
	}

	function queeScript($script,$name=''){
		$this->scriptQuee[$name] = $script;
	}
	function queeStyle($style,$name=''){
		$this->styleQuee[$name] = $style;
	}

	function keepHooksLog($hook){
		//only available during development
		return ;
		$xml = readLangFile(APPL_PATH.'config'.DIR_SEP."hooksdb.xml");
		//check if the value already exists...
		$i = 0;
		foreach ($xml as $k=>$v){
			$v=json_encode($v);
			$v = json_decode($v,1);
			if($v[$i]==$hook){
				return ;
			}
		}

		$node = $xml->addChild('hook',$hook);

		$xml->asXML(APPL_PATH.'config'.DIR_SEP."hooksdb.xml");
	}

	public function jsonArray($ob){
		$ob = json_encode($ob);
		$ob = json_decode($ob,1);
		return $ob;
	}

	private function connectDB(){

		try {
			$dbconfig = array(
			'host'=>$this->config->database->host,
			'username'=>$this->config->database->username,
			'password' =>$this->config->database->password,
			'dbname'=>$this->config->database->db
			);
			$dbconfig = $this->doQoolHook('front_pre_connectdb',$dbconfig);
			//here we check for database type
			switch ($this->config->database->type){
				case "mysql":
					$db = new Zend_Db_Adapter_Pdo_Mysql($dbconfig);
					$db->getConnection();
					$db->setFetchMode(Zend_Db::FETCH_ASSOC);
					$smt = $db->query("SET NAMES 'utf8'");
					$smt->execute();
					break;
				case "sqlite":
					
					$db = new Zend_Db_Adapter_Pdo_Sqlite($dbconfig);
					$db->getConnection();
					$db->setFetchMode(Zend_Db::FETCH_ASSOC);


					break;
				default:
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
		$db = $this->doQoolHook('front_after_connectdb',$db);
		$this->db = $db;
	}
	
	function getDataField($title,$type){
		$title = $this->quote($title);
		$t = $this->getDbTables();
		$field = $this->selectRow("SELECT id FROM {$t['data']} WHERE `name`=$title AND `group_id`=$type");
		return $field['id'];
	}

	function t($value,$echo=false){
		$lang = $this->language;
		//a simple way to keep track of strings that need translation
		if(!$lang[$value]){
			keepTranslationStrings($value,$this->dirs);
		}else{
			cleanTranslationStrings($value,$this->dirs);
		}
		if($lang[$value]){
			if($echo){
				if($lang[$value]){
					echo $lang[$value];
					return ;
				}
				echo $value;
			}else{
				if($lang[$value]){
					return  $lang[$value];
				}
				return $value;
			}
		}
		return $value;
	}

	private function setupTemplate(){
		$tpl = $this->tplEngine;
		$tpl = ucfirst($tpl);
		$theme = Zend_Registry::get('theme');
		$module = Zend_Registry::get('Qool_Module');
		$dirs = Zend_Registry::get("dirs");
		$tpl = $this->doQoolHook('front_pre_setuptemplate',$tpl);
		$class = "Templates_".$tpl."_".$tpl;
		Zend_Registry::set('customView',$class);
		$this->tpl = new $class();

		$this->tpl = $this->doQoolHook('front_post_setuptemplate',$this->tpl);
		$this->toTpl('tplpath',$dirs['structure']['templates'].DIR_SEP.$module.DIR_SEP.$theme);
		$this->tplPath = $dirs['structure']['templates'].DIR_SEP.$module.DIR_SEP.$theme;
		$this->toTpl('dirs',$this->dirs);
		$this->toTpl('config',$this->config);
	}

	public function toTpl($key,$value){
		$this->tpl->assign($key,$value);
	}

	public function setupCache($controller){



		//we will just set the cache for the frontend here. It's addon's job to set cache for themselves.
		//this is why we go on one switch case
		switch ($controller){
			case "Qool":
				$frontendOptions = array(
				'lifetime' => $this->config->cache->rules->overallcache->time,
				'automatic_serialization' => true
				);
				break;
			default:
				foreach ($this->applications as $k=>$v){
					if($v->name==$controller){
						$cachetime = $v->cachetime;
					}
				}
				$frontendOptions = array(
				'lifetime' => $cachetime,
				'automatic_serialization' => true
				);
		}
		//check if the cache dir of this controller exists. if not, we must create it or a fatal will come :)
		$dir = APPL_PATH.$this->dirs['structure']['cache'].DIR_SEP."frontend".DIR_SEP.$controller;
		$this->dirCheckCreate($dir);

		$backendOptions = array(
		'cache_dir' => $dir.DIR_SEP // Directory where to put the cache files
		);
		$opts = $this->doQoolHook('front_pre_setupcache',array($frontendOptions,$backendOptions));
		$frontendOptions = $opts[0];
		$backendOptions = $opts[1];
		$this->cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);
		$this->cache = $this->doQoolHook('front_post_setupcache',$this->cache);
	}

	function requirePriviledges(){
		$level = $this->level;
		$data = $this->_request->getParams();

		$loc = urlencode($this->http_location.'/'.$data['module'].'/'.$data['controller'].'/'.$data['action'].'/');
		if($_SESSION['user']){
			if($_SESSION['user']['level']>$level){
				Zend_Registry::set('tplOverride','login');

				$params = array("message"=>$this->t("You need to login to use this feature"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('login', 'index','default',array("redirect"=>$loc));
			}
		}else{
			Zend_Registry::set('tplOverride','login');

			$params = array("message"=>$this->t("You need to login to use this feature"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('login', 'index','default',array("redirect"=>$loc));
		}
	}

	function dirCheckCreate($dir){
		if(file_exists($dir)){
			return ;
		}
		//the dir does not exist... try to create it.
		mkdir($dir);
		//also chmod this shit
		chmod($dir,0777);
		return ;
	}



	function getTextWidgetContents($id){
		$id = $this->quote($id);
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['general_data']} WHERE `data_type`=$id";
		$r = $this->selectRow($sql);
		return unserialize($r['data_value']);
	}

	function getUserByName($name){
		$t = $this->getDbTables();
		$id = $this->quote($name);
		$sql = "SELECT * FROM {$t['users']} WHERE `username`=$id";

		return $this->selectRow($sql);
	}

	function getLoginForm($redirect){
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form-inline');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/dologin')->setMethod('post');
		if($redirect){
			$redir = new Zend_Form_Element_Hidden('redirect');
			$redir->setValue($redirect);
			$redir->setLabel($this->t('This action requires you login'));
			$form->addElement($redir);
		}
		$username = new Zend_Form_Element_Text('username');
		$username->setDecorators(array("ViewHelper"));
		$username->setAttrib('class','input-medium');
		$username->setAttrib('placeholder',$this->language['Username']);
		$username->addValidator('regex', false, array('/^[a-z]/i'));
		$username->setLabel($this->language['Username']);
		$username->setRequired(true);
		$username->addFilter('StringtoLower');
		$password = $form->createElement('password', 'password');
		$password->setDecorators(array("ViewHelper"));
		$password->setAttrib('class','input-medium');
		$username->setLabel($this->language['Password']);
		$password->addValidator('StringLength', false, array(6))->setRequired(true);
		$submit = new Zend_Form_Element_Submit('login');
		$submit->setAttrib('class','btn');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t('Login'));
		$form->addElement($username)->addElement($password)->addElement($submit);

		return $form;
	}

	function getUserById($id){
		$t = $this->getDbTables();
		$id = (int) $id;
		$sql = "SELECT * FROM {$t['users']} WHERE `id`=$id";

		return $this->selectRow($sql);
	}

	function getUserData($id){
		$t = $this->getDbTables();

		//get the user profile fields assigned by the admin
		$fields = $this->getUserProfileFields();
		foreach ($fields as $k=>$v){
			$sql = "SELECT `value` FROM {$t['user_data']} WHERE `uid`=$id AND `name`='{$v['name']}'";
			$data = $this->selectRow($sql);
			$userdata[$v['name']] = $data['value'];
		}

		return $userdata;
	}

	function getUserProfileFields(){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['user_profile_fields']}";
		return $this->selectAll($sql);
	}

	function getNormalWidgetContents($id){
		$widgets = $this->widgets;
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		//check first in the widgets array
		foreach ($widgets as $k=>$v){
			if($id==$k){

				//we got it... we now need to include the file and run it
				include $v.DIR_SEP."func.php";
				return $k($this);
			}
		}
		//we couldn't manage to get the widget yet... lets try on the modules
		$modules = $this->modules;

		//get the xml for any module
		foreach ($modules as $k=>$v){
			if(file_exists(APPL_PATH.$v.DIR_SEP."addon.xml")){
				$xml = readLangFile(APPL_PATH.$v.DIR_SEP."addon.xml");
				foreach ($xml->widgets as $widget){
					$widget = $this->jsonArray($widget);
					if($widget['widget']['@attributes']['id']==$id){
						include $v.DIR_SEP.$id.".php";
						$func = $widget['widget']['@attributes']['id'];
						return $func($this);
					}
				}
			}
		}
		//still nothing found here...
		//lets have a look at the addons for this widget
		$addons = $this->addons;
		//lets loop

		foreach ($addons as $k=>$v){
			//in here we have to do some hacking...
			$v = str_replace("controllers","",$v);
			//read the xml
			if(file_exists(APPL_PATH.$v.DIR_SEP."addon.xml")){
				$xml = readLangFile(APPL_PATH.$v.DIR_SEP."addon.xml");
				foreach ($xml->widgets->widget as $widget){

					$widget = $this->jsonArray($widget);

					if($widget['@attributes']['id']==$id){
						$func = $widget['@attributes']['id'];
						require_once APPL_PATH.$v.$id.".php";
						return $func($this);
					}
				}
			}
		}

	}

	function paginate($numResults=0){

		$records = $this->records_per_page;

		if($numResults>$records){
			$pages = ceil($numResults/$records);
		}
		$current = $this->curPage;
		for ($i=0;$i<$pages;$i++){
			if($i==$current || $i==$current-1 || $i==$current-2 || $i==$current-3  || $i==$current+1){
				$pager['pager'][] =$i+1;
			}
		}
		$pager['pages'] = $pages;
		return $pager;
	}

	function getContent($type,$id,$mode=false){
		if(!$content = $this->loadCache('object_'.$this->cachify(str_replace(" ",'',$type)).$id)){
			$t = $this->getDbTables();
			$d = $t['data'];
			if($mode){
				$typeid = array('id'=>$type);
			}else{
				$typeid = $this->getContentTypeByName($type);
			}
			$sql = "SELECT * FROM $d WHERE `group_id`=".(int)$typeid['id']." ORDER BY `order` ASC";
			$sel = $this->selectAll($sql);
			foreach ($sel as $k=>$v){
				if($v['is_taxonomy']){
					$sql = "SELECT {$t['taxonomies']}.* FROM {$t['taxonomies']},{$t['object_to_taxonomy']}
				WHERE {$t['taxonomies']}.id={$t['object_to_taxonomy']}.taxonomy_id AND {$t['object_to_taxonomy']}.`data_id`={$v['id']} AND {$t['object_to_taxonomy']}.`object_id`=".$id;
					$r = $this->selectAll($sql);
					if($v['value']=='treeselectbox' || $v['value']=='selectbox'){
						if($prev = $this->getPreviousTaxonomies($r[0]['id'])){
							$r[0]['previous'] = $prev;
						}
						$content[$v['name']] = $r[0];
					}else{
						$content[$v['name']] = $r;
					}
				}elseif($v['name']=='slug'){
					$sql = "SELECT `slug` FROM {$t['objects']} WHERE `id`=".$id;
					$r = $this->selectRow($sql);
					$content[$v['name']] = $r['slug'];
				}else{

					$sql = "SELECT `value` FROM {$t['object_data']} WHERE `object_id`=".$id." AND `name`=".$this->quote($v['name']);
					$r = $this->selectRow($sql);

					$content[$v['name']] = $r['value'];

				}
				if(!$content['slug']){
					$sql = "SELECT `slug` FROM {$t['objects']} WHERE `id`=".$id;
					$r = $this->selectRow($sql);
					$content['slug'] = $r['slug'];
				}
				if(!$content['creation']){
					$sql = "SELECT `datestr` FROM {$t['objects']} WHERE `id`=".$id;
					$r = $this->selectRow($sql);
					$content['datestr'] = $r['datestr'];
				}

			}
			$this->cacheData($content,'object_'.$this->cachify(str_replace(" ",'',$type)).$id);
		}
		$content = $this->doQoolHook('front_got_content',$content);
		return $content;
	}

	function slugify($str){
		$str = trim($str);
		//first replace greek-letters with latin ones:
		$str = deGreek($str);
		//replace all non letters and digits with -
		$text = preg_replace('/\W+/u', '-', $str);

		// trim and lowercase

		$text = strtolower(trim($text, '-'));
		return $text;
	}

	function cachify($str){
		$str = trim($str);
		//first replace greek-letters with latin ones:
		$str = deGreek($str);
		//replace all non letters and digits with _
		$text = preg_replace('/\W+/u', '_', $str);

		// trim and lowercase

		$text = strtolower(trim($text, '_'));
		return $text;
	}

	function getRecent($type,$num=10,$start=0,$mode=false,$ispaged=false){
		$hook_type = strtolower($type);
		if($mode){
			//this is when type is the id. we need to reverse it
			//get the content type
			$type = $this->getContentType($type);
			$type = $type['title'];
		}
		$contents = array();
		//select $num contents from the objects table
		$t = $this->getDbTables();
		$id = $this->getContentTypeByName($type);
		if($ispaged){
			$sql = "SELECT `id` FROM {$t['objects']} WHERE type_id={$id['id']} ORDER BY `datestr` DESC";
			$sel = $this->selectAllPaged($sql,$num);
		}else{
			$sql = "SELECT `id` FROM {$t['objects']} WHERE type_id={$id['id']} ORDER BY `datestr` DESC LIMIT $start,$num";
			$sel = $this->selectAll($sql);
		}

		foreach ($sel as $k=>$v){
			if($mode){
				$contents[$v['id']] = $this->getContent($type,$v['id']);
			}else{
				$contents[] = $this->getContent($type,$v['id']);
			}
		}
		$contents = $this->doQoolHook('front_pre_assign_recent_'.$hook_type,$contents);
		return $contents;
	}

	function getContentList($num=10,$start=0,$ispaged=false){
		$contents = array();
		//select $num contents from the objects table
		$t = $this->getDbTables();
		if($ispaged){
			$sql = "SELECT * FROM {$t['objects']} ORDER BY `datestr` DESC";
			$sel = $this->selectAllPaged($sql,$num);
		}else{
			$sql = "SELECT * FROM {$t['objects']} ORDER BY `datestr` DESC LIMIT $start,$num";
			$sel = $this->selectAll($sql);
		}

		foreach ($sel as $k=>$v){
			$type = $this->getContentType($v['type_id']);
			$contents[] = $this->getContent($type['title'],$v['id']);

		}

		$contents = $this->doQoolHook('front_pre_assign_contentlist',$contents);
		return $contents;
	}

	function getContentByDate($type,$date,$num,$start,$ispaged=false){
		$contents = array();
		//select $num contents from the objects table
		$t = $this->getDbTables();
		//create the format


		$check = explode("-",$date);
		$date = strtotime($date);
		if(count($check)==3){
			$format = "%Y-%m-%d";
			$date = date("Y-m-d",$date);
		}elseif (count($check)==2){
			$format = "%Y-%m";
			$date = date("Y-m",$date);
		}else{
			$date = date("Y",$date);
			$format = "%Y";
		}
		$date = $this->quote($date);
		$id = $this->getContentTypeByName($type);
		if($ispaged){
			$sql = "SELECT `id`,FROM_UNIXTIME( `datestr`,'$format' ) AS datestr FROM {$t['objects']} WHERE
		type_id={$id['id']} AND 
		FROM_UNIXTIME( `datestr`,'$format' )=$date
		ORDER BY `id` DESC";
			$sel = $this->selectAllPaged($sql,$num);
		}else{
			$sql = "SELECT `id`,FROM_UNIXTIME( `datestr`,'$format' ) AS datestr FROM {$t['objects']} WHERE
		type_id={$id['id']} AND 
		FROM_UNIXTIME( `datestr`,'$format' )=$date
		ORDER BY `id` DESC LIMIT $start,$num";
			$sel = $this->selectAll($sql);
		}
		foreach ($sel as $k=>$v){
			if($mode){
				$contents[$v['id']] = $this->getContent($type,$v['id']);
			}else{
				$contents[] = $this->getContent($type,$v['id']);
			}
		}

		return $contents;
	}

	function getRandom($type,$num=10,$start=0,$mode=false){
		if($mode){
			//this is when type is the id. we need to reverse it
			//get the content type
			$type = $this->getContentType($type);
			$type = $type['title'];
		}
		$contents = array();
		//select $num contents from the objects table
		$t = $this->getDbTables();
		$id = $this->getContentTypeByName($type);
		$sql = "SELECT `id` FROM {$t['objects']} WHERE type_id={$id['id']} ORDER BY RAND() LIMIT $start,$num";

		$sel = $this->selectAll($sql);
		foreach ($sel as $k=>$v){
			if($mode){
				$contents[$v['id']] = $this->getContent($type,$v['id']);
			}else{
				$contents[] = $this->getContent($type,$v['id']);
			}
		}

		return $contents;
	}

	function getRecentByTaxonomy($taxonomy,$type,$num=10,$start=0){
		$hook_type = $taxonomy;
		$contents = array();
		//select $num contents from the objects table
		$t = $this->getDbTables();
		$taxonomy = $this->quote($taxonomy);
		$id = $this->getContentTypeByName($type);
		$sql = "SELECT {$t['objects']}.`id` FROM {$t['objects']},{$t['object_to_taxonomy']} WHERE
		{$t['objects']}.type_id={$id['id']} AND 
		{$t['objects']}.id={$t['object_to_taxonomy']}.object_id AND 
		{$t['object_to_taxonomy']}.taxonomy_id=$taxonomy
		ORDER BY {$t['objects']}.`datestr` DESC LIMIT $start,$num";

		$sel = $this->selectAll($sql);
		foreach ($sel as $k=>$v){
			$contents[] = $this->getContent($type,$v['id']);
		}
		$contents = $this->doQoolHook('front_pre_assign_recent_taxonomy',$contents);
		return $contents;
	}

	function getContentByTaxonomy($id){
		$contents = array();
		//select $num contents from the objects table
		$t = $this->getDbTables();
		$taxonomy = $this->quote($id);
		$sql = "SELECT {$t['objects']}.* FROM {$t['objects']},{$t['object_to_taxonomy']} WHERE
		{$t['objects']}.id={$t['object_to_taxonomy']}.object_id AND 
		{$t['object_to_taxonomy']}.taxonomy_id=$taxonomy
		ORDER BY {$t['objects']}.`datestr` DESC";

		$sel = $this->selectAllPaged($sql);
		foreach ($sel as $k=>$v){
			$contents[] = $this->getContent($v['type_id'],$v['id'],1);
		}

		return $contents;
	}

	function getIdsByTaxonomy($id){
		$contents = array();
		//select $num contents from the objects table
		$t = $this->getDbTables();
		$taxonomy = $this->quote($id);
		$sql = "SELECT {$t['objects']}.id FROM {$t['objects']},{$t['object_to_taxonomy']} WHERE
		{$t['objects']}.id={$t['object_to_taxonomy']}.object_id AND 
		{$t['object_to_taxonomy']}.taxonomy_id=$taxonomy
		ORDER BY {$t['objects']}.`datestr` DESC";

		$sel = $this->selectAllPaged($sql);
		return $sel;
	}

	function getSlugById($id){
		$id = (int) $id;
		$t = $this->getDbTables();
		$sql = "SELECT `slug` FROM {$t['objects']} WHERE id=$id";
		$sel = $this->selectRow($sql);
		return $sel['slug'];
	}

	function getAllById($id){
		$id = (int) $id;
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['objects']} WHERE id=$id";
		$sel = $this->selectRow($sql);
		return $sel;
	}

	function getFullSlugById($id){
		$t = $this->getDbTables();
		$id = (int) $id;
		$sql = "SELECT * FROM {$t['objects']} WHERE id=$id";
		$sel = $this->selectRow($sql);
		$slug = $sel['slug'];
		//now get the type
		$type = $this->getContentType($sel['type_id']);
		return $type['lib'].'/'.$slug;
	}

	function getIdBySlug($slug){
		$t = $this->getDbTables();
		$slug = $this->quote($slug);
		$sql = "SELECT `id` FROM {$t['objects']} WHERE `slug`=$slug";
		$sel = $this->selectRow($sql);
		return $sel['id'];
	}

	function getAllBySlug($slug){
		$t = $this->getDbTables();
		$slug = $this->quote($slug);
		$sql = "SELECT * FROM {$t['objects']} WHERE `slug`=$slug";
		$sel = $this->selectRow($sql);
		return $sel;
	}

	function getContentType($id){
		$id = (int) $id;
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['content_types']} WHERE id=$id";
		return $this->selectRow($sql);
	}

	function getContentTypeByName($id){
		$t = $this->getDbTables();
		$id = $this->quote($id);
		$sql = "SELECT * FROM {$t['content_types']} WHERE title=$id";
		return $this->selectRow($sql);
	}

	function getContentTypeByLib($lib){
		$t = $this->getDbTables();
		$id = $this->quote($lib);
		$sql = "SELECT * FROM {$t['content_types']} WHERE `lib`=$id";
		return $this->selectAll($sql);
	}

	function getPreviousTaxonomies($id,$array=false){
		$prev = $this->getTaxonomy($id);
		$array[] = $prev;
		if($prev['parent']==0){
			return array_reverse($array);
		}else{
			return $this->getPreviousTaxonomies($prev['parent'],$array);
		}
	}

	function getTaxonomyByName($name){
		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$id = $this->quote($name);
		$sql = "SELECT * FROM $d WHERE `title`=$id";
		return $this->selectRow($sql);
	}

	function getTaxonomy($id){
		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$id = $this->quote($id);
		$sql = "SELECT * FROM $d WHERE `id`=$id";
		return $this->selectRow($sql);
	}

	function loadMenus(){
		$config = $this->config;
		$t = $this->getDbTables();
		//first we get the menus one by one
		$sel = $this->selectAll("SELECT * FROM {$t['menus']}");
		foreach ($sel as $k=>$v){
			$menuitems = array();
			//if the menu is taxonomy based, we need to do some magic
			if($v['taxonomy']>0){

				$menuitems = $this->getTaxonomiesByType($v['taxonomy']);
				foreach ($menuitems as $a=>$b){

					$menuitems[$a]['items'] = $this->getTaxonomyKids($b['id']);
				}
			}else{
				//else we need to get all items for this menu that are roots. That is parent=0
				$menuitems = $this->selectAll("SELECT * FROM {$t['menu_items']} WHERE menu_id={$v['id']} AND `parent`=0");
				//now we need to do the last loop to gather all kids
				foreach ($menuitems as $a=>$b){
					if($b['is_special']){
						$items = $this->$b['special']($b['special_object'],5,0,1);
						foreach ($items as $w=>$s){
							$kids[] = array('title'=>$s['title'],'objectlink'=>$w,'link_title'=>$s['title']);
						}
						$menuitems[$a]['items'] = $kids;
					}else{
						$menuitems[$a]['items'] = $this->getMenuItemKids($b['id']);
					}
				}
			}
			//lets create the menus array
			$menus[$v['title']] = $v;
			//we now assign the menuitems for this menu
			$menus[$v['title']]['items'] = $menuitems;
		}

		//assign to the template
		//d($menus);
		$this->toTpl('menus',$menus);
	}

	function getMenuItemKids($id,$kids=array()){
		$tax = $this->getMenuItemKid($id);

		if(count($tax)>0){

			foreach ($tax as $k=>$v){
				$kids[$k] = $v;
				$kids[$k]['items'] = $this->getMenuItemKids($v['id']);
			}

		}else{

		}
		return $kids;
	}

	function getMenuItemKid($id){
		$t = $this->getDbTables();
		$d = $t['menu_items'];
		$id = $this->quote($id);
		$type = $this->pool_type;
		$sql = "SELECT * FROM $d WHERE `parent`=$id";
		return $this->selectAll($sql);
	}

	function getTaxonomiesByType($id){
		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$id = $this->quote($id);
		$sql = "SELECT * FROM $d WHERE `taxonomy_type`=$id AND parent=0";

		return $this->selectAll($sql);
	}

	function getTaxonomyKids($id,$kids=array()){
		$tax = $this->getTaxonomyKid($id);

		if(count($tax)>0){

			foreach ($tax as $k=>$v){
				$kids[$k] = $v;
				$kids[$k]['items'] = $this->getTaxonomyKids($v['id']);
			}

		}else{

		}
		return $kids;
	}

	function getTaxonomyKid($id){
		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$id = $this->quote($id);
		$sql = "SELECT * FROM $d WHERE `parent`=$id";
		return $this->selectAll($sql);
	}

	function getTaxonomyType($id){
		$t = $this->getDbTables();
		$d = $t['taxonomy_types'];
		$id = $this->quote($id);
		$sql = "SELECT `title` FROM $d WHERE `id`=$id";
		$r = $this->selectRow($sql);
		return $r['title'];
	}

	function getMenu($id){
		$id = (int) $id;
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['menus']} WHERE id=$id ORDER BY `id` ASC";
		$list = $this->selectRow($sql);
		return $list;
	}

	function loadTemplateSettings(){
		if(!$settings = $this->loadCache('template_settings')){
			$config = $this->config;
			$dirs = $this->dirs;
			$xml = readLangFile(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.'frontend'.DIR_SEP.$config->template->frontend->title.DIR_SEP."template.xml");
			foreach ($xml->settings->item as $k=>$v){
				$v = $this->jsonArray($v);
				$settings[$v['@attributes']['id']] = $v['default_value'];
			}
			//$this->cacheData($settings,'template_settings');
		}
		$settings = $this->doQoolHook('front_template_settings',$settings);
		$this->toTpl('theme_settings',$settings);
	}

	function loadAddonSettings($addon){
		$dirs = $this->dirs;
		$settings = array();
		//get the level for this addon
		$addons = $this->applications;
		foreach ($addons->addon as $k=>$v){
			if($v->name==$addon){
				$this->level = $v->level;
			}
		}
		$xml = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$addon.DIR_SEP."addon.xml");
		foreach ($xml->settings->item as $v){
			$v = $this->jsonArray($v);
			$settings[$v['@attributes']['id']] = $v['default_value'];
		}
		$settings = $this->doQoolHook('front_addon_settings',$settings);
		$this->addonSettings = $settings;
	}

	function loadAddonTemplates($addon){
		$dirs = $this->dirs;
		$templates = array();
		//get the level for this addon
		$addons = $this->applications;
		foreach ($addons->addon as $k=>$v){
			if($v->name==$addon){
				$this->level = $v->level;
			}
		}
		$i = 0;
		$xml = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$addon.DIR_SEP."addon.xml");
		foreach ($xml->templates->css as $v){
			$v = $this->jsonArray($v);
			if(is_array($v['file'])){
				foreach ($v['file'] as $file) {
					$i++;
					$this->queeStyle("<link href=".$this->http_location."/".$dirs['structure']['addons'].'/'.$addon.'/templates'.$file." rel='stylesheet' />",$addon.$i);
				}
			}else{
				$this->queeStyle("<link href=".$this->http_location."/".$dirs['structure']['addons'].'/'.$addon.'/templates'.$v['file']." rel='stylesheet' />",$addon.$i);
			}
		}

		foreach ($xml->templates->js as $v){

			$v = $this->jsonArray($v);
			if(is_array($v['file'])){
				foreach ($v['file'] as $file) {
					$i++;
					$this->queeScript("<script type='text/javascript' src=".$this->http_location."/".$dirs['structure']['addons'].'/'.$addon.'/templates'.$file."></script>",$addon.$i);
				}
			}else{
				$this->queeScript("<script type='text/javascript' src=".$this->http_location."/".$dirs['structure']['addons'].'/'.$addon.'/templates'.$v['file']."></script>",$addon.$i);
			}
		}
		//$settings = $this->doQoolHook('front_addon_settings',$settings);
		//$this->addonSettings = $settings;
	}


	function loadWidgets(){
		//load the widgets this tpl uses...
		$config = $this->config;
		$dirs = $this->dirs;


		//echo $this->tpl->templates;
		$addons = readLangFile(APPL_PATH.'config'.DIR_SEP."addons.xml");
		$xml = readLangFile($this->tplPath.DIR_SEP."template.xml");
		$slots = $xml->slots;
		foreach ($slots->slot as $k=>$v){
			foreach ($addons->widgets->addon as $ki=>$vi){
				$vi = $this->jsonArray($vi);
				$te = $this->jsonArray($v);
				if($vi['@attributes']['name']==$te[0]){

					$level = $vi['@attributes']['level'];
				}
			}
			if($_SESSION['user']['level'] && $_SESSION['user']['level']<=$level){
				$vo = $this->jsonArray($v);
				if($vo[0]=='text'){
					$widget = $this->getTextWidgetContents($vo['@attributes']['name']);
					$widget = $this->doQoolHook('front_pre_textwidget_build',$widget);

				}elseif($vo[0]=='menu'){
					$widget = $this->getTextWidgetContents($vo['@attributes']['name']);
					$widget['type'] = 'menu';
					$name = $this->getMenu($widget['menu']);
					$widget['name'] = $name['title'];
					$widget = $this->doQoolHook('front_pre_menuwidget_build',$widget);
				}elseif($vo[0]=='feed'){
					$widget = $this->getTextWidgetContents($vo['@attributes']['name']);
					$feed = $this->consumeFeed($widget['feed']);
					$widget['contents'] = $feed['content'];
					$widget = $this->doQoolHook('front_pre_feedwidget_build',$widget);
				}else{
					//decide on the widget...
					$widget = $this->getNormalWidgetContents($v);
					$widget = $this->doQoolHook('front_pre_normalwidget_build',$widget);

				}
				$widgets[$vo['@attributes']['name']] = $widget;
			}
		}

		//if we are running an addon we need to get the addon's widgets too
		$data = $this->_request->getParams();
		if($data['module']!='default'){
			$xml = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$data['module'].DIR_SEP."addon.xml");

			$slots = $xml->templates->slots;
			foreach ($slots->slot as $k=>$v){
				foreach ($addons->widgets->addon as $ki=>$vi){
					$vi = $this->jsonArray($vi);
					$te = $this->jsonArray($v);
					if($vi['@attributes']['name']==$te[0]){

						$level = $vi['@attributes']['level'];
					}
				}

				if($_SESSION['user']['level'] && $_SESSION['user']['level']<=$level){
					$vo = $this->jsonArray($v);

					if($vo[0]=='text'){
						$widget = $this->getTextWidgetContents($data['module']."-".$vo['@attributes']['name']);
						$widget = $this->doQoolHook('front_pre_textwidget_build',$widget);

					}elseif($vo[0]=='menu'){
						$widget = $this->getTextWidgetContents($data['module']."-".$vo['@attributes']['name']);
						$widget['type'] = 'menu';
						$name = $this->getMenu($widget['menu']);
						$widget['name'] = $name['title'];
						$widget = $this->doQoolHook('front_pre_menuwidget_build',$widget);
					}else{

						//decide on the widget...
						$widget = $this->getNormalWidgetContents($v);
						$widget = $this->doQoolHook('front_pre_normalwidget_build',$widget);

					}
					$widgets[$data['module']."-".$vo['@attributes']['name']] = $widget;
				}
			}
		}


		$widgets = $this->doQoolHook('front_post_widgets_create',$widgets);
		$this->toTpl('builtwidgets',$widgets);
	}

	function loadModules(){

		//load the modules installed...
		$config = $this->config;
		$dirs = $this->dirs;
		$modules = $this->modules;
		$modsettings = array();
		$addons = readLangFile(APPL_PATH.'config'.DIR_SEP."addons.xml");


		foreach ($modules as $k=>$v){
			//if the path exists then this is a standalone module and we need to do the default action

			if(!file_exists(APPL_PATH.DIR_SEP.$v.DIR_SEP."addon.xml")){
				//else this is an addon module and we need to treat it as one
				//we also need the parent addon settings.
				$this->loadAddonSettings($v);
				include_once (APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$v.DIR_SEP.$k.".php");
				$class = $k.'_Module';
				new $class($this,$this->addonSettings);
			}else{
				//read the xml and get the settings
				$xml = readLangFile(APPL_PATH.$v.DIR_SEP."addon.xml");
				foreach ($addons->modules->addon as $ko=>$vo){
					$vo = $this->jsonArray($vo);

					if($vo['@attributes']['name']==$xml->id){

						$level = $vo['@attributes']['level'];
					}
				}
				if($_SESSION['user']['level'] && $_SESSION['user']['level']<=$level){
					$settings = array();
					foreach ($xml->settings->item as $a){
						$a = $this->jsonArray($a);
						$settings[$a['@attributes']['id']] = $a['default_value'];
					}
					$modsettings = $settings;
					$this->moduleSettings = $modsettings;
					//get the func.php for this module
					include_once($v.DIR_SEP."func.php");
					$class = $k.'_Module';
					new $class($this,$settings);
				}
			}
		}
		$modsettings = $this->doQoolHook('front_module_settings',$modsettings);
		$this->toTpl('modules_settings',$modsettings);
	}

	function valuesAsKeys($values=false){
		if(!$values){
			$values = $this->pool_type;
		}
		foreach ($values as $k=>$v){
			$new[] = array('id'=>$v,'title'=>$v);
		}
		return $new;
	}

	function getFormElement($v,$value=''){
		$this->toTpl('hasForm',1);
		$v = $this->doQoolHook('front_pre_getformelement_element',$v);
		$value = $this->doQoolHook('front_pre_getformelement_value',$value);
		switch ($v['value']){
			case "editor":
				$this->loadEditorBtns();
				$this->toTpl("loadEditor",1);
				$this->toTpl("isEditor",1);
				$element = new Zend_Form_Element_Textarea($v['name']);
				$element->setAttrib('class','editor span12');

				if($value!=''){
					$element->setValue($value);
				}
				break;
			case "fileinput":
				$element = new Zend_Form_Element_File($v['name']);
				$element->setAttrib('class','input-file');
				break;
			case "captcha":
				$element = new Zend_Form_Element_Captcha($v['name'], array(
				'label' => $this->t("Please verify you are a human"),
				'captcha' => $config->site->captcha_adapter,
				'captchaOptions' => array(
				'captcha' => $config->site->captcha_adapter,
				'wordLen' => 6,
				'timeout' => 300,
				)
				));
				return $element;
				break;
			case "multifileinputs":
				$element = new Zend_Form_Element_File($v['name']);
				$element->setAttrib('class','input-file');
				$element->setMultiFile(10);
				break;
			case "multifileinput":
				$name = $v['name']."[]";
				$element = new Zend_Form_Element_File($name);
				$element->setAttrib('class','input-file');
				$element->setAttrib('multiple','multiple');
				$element->setMultiFileForQool(1);
				$this->toTpl('filelist',true);
				break;
			case "checkbox":
				$element = new Zend_Form_Element_Checkbox($v['name']);
				$element->setAttrib('class','checkbox');
				$element->setValue($value);
				break;
			case "editarea":
				$this->toTpl('editarea',1);
				$element = new Zend_Form_Element_Textarea($v['name']);
				$element->setAttrib('class','editarea span12');
				$element->setAttrib('id','editarea');
				$element->setAttrib('style','height:500px');
				if($value!=''){
					$element->setValue($value);
				}
				break;
			case "password":
				$element = new Zend_Form_Element_Password($v['name']);

				break;
			case "textarea":
				$element = new Zend_Form_Element_Textarea($v['name']);
				$element->setAttrib('class','span9');
				$element->setAttrib('style','height:80px');
				if($value!=''){
					$element->setValue($value);
				}
				break;
			case "hidden":
				$element = new Zend_Form_Element_Hidden($v['name']);
				$element->setValue($value);
				$element->setDecorators(array("ViewHelper"));
				break;
			case "textinput":
				$element = new Zend_Form_Element_Text($v['name']);

				if($value!=''){
					$element->setValue($value);
				}
				break;
			case "selectbox":
				$element = new Zend_Form_Element_Select($v['name']);

				if($v['use_pool'] && method_exists($this,$v['use_pool'])){
					if($v['pool_type']!='0'){

						$this->pool_type = $v['pool_type'];
					}
					if($v['novalue']){
						$element->addMultiOption(0,$this->t('No Selection'));
					}
					foreach ($this->$v['use_pool']() as $ko=>$vo){
						if($v['noself'] && $vo['id']==$v['noself']){

						}else{
							$element->addMultiOption($vo['id'],$vo['title']);
						}
					}
					if($value!=''){
						$element->setValue($value);
					}
				}
				break;
			case "multiselectbox":
				$element = new Zend_Form_Element_Multiselect($v['name']);
				if($v['use_pool'] && method_exists($this,$v['use_pool'])){
					if($v['pool_type']!='0'){
						$this->pool_type = $v['pool_type'];
					}
					if($v['novalue']){
						$element->addMultiOption(0,$this->t('No Selection'));
					}
					foreach ($this->$v['use_pool']() as $ko=>$vo){
						if($v['noself'] && $vo['id']==$v['noself']){

						}else{
							$element->addMultiOption($vo['id'],$vo['title']);
						}
					}
					if(is_array($value)){

						foreach ($value as $a){
							$vals[] = $a['selected_value'];

						}
						$element->setValue($vals);

					}

				}
				break;
			case "treeselectbox":
				$element = new Zend_Form_Element_Select($v['name']);

				if($v['use_pool'] && method_exists($this,$v['use_pool'])){
					if($v['pool_type']!='0'){
						$this->pool_type = $v['pool_type'];
					}
					if($v['novalue']){
						$element->addMultiOption(0,$this->t('No Selection'));
					}
					if($value!=''){
						$element->setValue($value);
					}
					//x10 times nested support. Needs fix
					foreach ($this->$v['use_pool']() as $vo){
						$element->addMultiOption($vo['id'],$vo['title']);
						foreach ($vo['kids'] as $a){
							$element->addMultiOption($a['id'],"|_".$a['title']);
							foreach ($a['kids'] as $b){
								$element->addMultiOption($b['id'],"|__".$b['title']);
								foreach ($b['kids'] as $c){
									$element->addMultiOption($c['id'],"|___".$c['title']);
									foreach ($c['kids'] as $d){
										$element->addMultiOption($d['id'],"|____".$d['title']);
										foreach ($d['kids'] as $e){
											$element->addMultiOption($e['id'],"|_____".$e['title']);
											foreach ($e['kids'] as $f){
												$element->addMultiOption($f['id'],"|______".$f['title']);
												foreach ($f['kids'] as $g){
													$element->addMultiOption($g['id'],"|______".$g['title']);
													foreach ($g['kids'] as $h){
														$element->addMultiOption($h['id'],"|_______".$h['title']);
														foreach ($h['kids'] as $i){
															$element->addMultiOption($i['id'],"|________".$i['title']);
															foreach ($i['kids'] as $j){
																$element->addMultiOption($j['id'],"|_________".$j['title']);
																foreach ($j['kids'] as $l){
																	$element->addMultiOption($l['id'],"|__________".$l['title']);
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				break;
		}
		if($v['required']){
			$element->setRequired(true);
		}
		if($v['title']){
			$element->setLabel($v['title']);
		}else{
			$element->setLabel($this->t(ucfirst(str_replace("_"," ",$v['name']))));
		}
		if($v['attributes']){
			foreach ($v['attributes'] as $k=>$r){
				$element->setAttrib($k,$r);
			}
		}
		//$element->setDecorators(array("ViewHelper"));
		$element = $this->doQoolHook('front_post_getformelement_object',$element);
		return $element;
	}


}
?>