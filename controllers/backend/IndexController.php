<?php
class Admin_IndexController extends Qool_Backend_Action{

	var $user = array();
	var $pool_type= '';

	public function indexAction(){
		$this->setupCache('QoolAdmin');
		//Zend_Registry::set('module','Dashboard');
		$this->doQoolHook('pre_load_dashboard_tpl');
		$this->loadDashboard();
		$this->doQoolHook('post_load_dashboard_tpl');
		$this->tpl->assign('theInclude','dashboard');
	}


	function preDispatch(){
		$this->requirePriviledges();
		if($this->config->site->help=='on'){
			$this->getHelpDialogs();
		}
		/*
		if(Zend_Registry::get('tplOverride')=='login'){
		$this->_forward('login','index','default');
		}else{
		return ;
		}
		*/
	}

	function getHelpDialogs(){
		$data = $this->_request->getParams();
		$dialogs = $this->loadHelp();
		$this->toTpl('help',$dialogs);
	}
	
	function loadHelp(){
		$help['menu_new'] = array('title'=>$this->t('New content menu'),'content'=>$this->t('From here you can create new content. Each addon can add menu items here.'));
		$help['menu_content'] = array('title'=>$this->t('Content menu'),'content'=>$this->t('Manage your content from this menu.'));
		$help['menu_system'] = array('title'=>$this->t('System menu'),'content'=>$this->t('Manage system from here.'));
		$help['search'] = array('title'=>$this->t('Search site'),'content'=>$this->t('You can search for content from this box.'));
		$help['menu_module'] = array('title'=>$this->t('Module menu'),'content'=>$this->t('Specific module menu actions can be done with this menu'));
		$help['profile'] = array('title'=>$this->t('User menu'),'content'=>$this->t('This menu allows you to view your profile and logout.'));
		$help['side_content'] = array('title'=>$this->t('Content actions'),'content'=>$this->t('Create and list content by type'));
		$help['side_addons'] = array('title'=>$this->t('Addon actions'),'content'=>$this->t('Specific addon actions are listed here by addon'));
		$help['object_files'] = array('title'=>$this->t('Object Files'),'content'=>$this->t('Whenever you upload a file for an item it is displayed here.'));
		return $help;
	}
	
	function loadDashboard(){
		$config = $this->config;

		$widgets['system'][] = $this->consumeFeed($config->qool->newsfeed);
		$widgets['system'][] = $this->getQoolInfo();
		//we will show the user's shortcuts as a widget
		$widgets['system'][] = array('title'=>$this->t('Shortcuts'),'content'=>$this->linksToList($this->getUserShortcuts()));
		$widgets['system'][] = array('title'=>$this->t('Tasks'),'content'=>$this->tasksToList($this->getUserTasks()));
		$widgets['feeds'] = array();
		//we will allow the user to add more feeds
		foreach ($this->getUserFeeds() as $k=>$v){
			$feed = $this->consumeFeed($v['feed']);
			$feed['type'] = 'userfeed';
			$feed['id'] = $k;
			$widgets['feeds'][] = $feed;
		}

		//d($widgets);
		$this->doQoolHook('pre_boardwidgets_assign_tpl');
		$this->toTpl('boardwidgets',$widgets);
	}


	function tasksToList($links){

		$content = '<ul class="unstyled">';
		foreach ($links as $k=>$v){
			if($v['target']==1){
				$target = 'target="_blank"';
			}
			$content .= " <li><a data-original-title='Task Contents' data-content='{$v['task']}' class='inline-block poptop' href='#'><i class='{$v['icon']}'> </i> {$v['title']}</a><a href='#' class='ajaxdelete pull-right inline-block-small alert-error' title='".$this->t("Delete")."' id='{$v['id']}' rev='general_data'>&times;</a></li>";
		}
		$content .= '</ul>';
		return $content;
	}

	function linksToList($links){

		$content = '<ul class="unstyled">';
		foreach ($links as $k=>$v){
			if($v['target']==1){
				$target = 'target="_blank"';
			}
			$content .= " <li><a class='inline-block' $target href='{$v['link']}'><i class='{$v['icon']}'> </i> {$v['title']}</a><a href='#' class='ajaxdelete pull-right inline-block-small alert-error' title='".$this->t("Delete")."' id='{$v['id']}' rev='general_data'>&times;</a></li>";
		}
		$content .= '</ul>';
		return $content;
	}

	function getUserShortcuts(){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['general_data']} WHERE data_type='shortcuts'";
		$r = $this->selectAll($sql);
		foreach ($r as $k=>$v){
			$id = $v['id'];
			$v = unserialize($v['data_value']);
			if($v['username']==$_SESSION['user']['username']){
				$v['id'] = $id;
				$feeds[] = $v;
			}
		}
		return $feeds;
	}

	function getUserTasks(){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['general_data']} WHERE data_type='tasks'";
		$r = $this->selectAll($sql);
		foreach ($r as $k=>$v){
			$id = $v['id'];
			$v = unserialize($v['data_value']);
			if($v['username']==$_SESSION['user']['username']){
				$v['id'] = $id;
				$feeds[] = $v;
			}
		}
		return $feeds;
	}

	function getUserFeeds(){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['general_data']} WHERE data_type='userfeed'";
		$r = $this->selectAll($sql);
		foreach ($r as $k=>$v){
			$id = $v['id'];
			$v = unserialize($v['data_value']);
			if($v['username']==$_SESSION['user']['username']){
				$feeds[$id] = $v;
			}
		}
		return $feeds;
	}

	function consumeFeed($feed){
		$name = $feed;
		try{
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

			return $widget;
		}catch (Exception $e){

		}
	}

	function getQoolInfo(){
		$config = $this->config;
		$info = $config->qool->toArray();
		$widget['title'] = $this->t('Qool CMS Information');
		$widget['content'] = "Codename: {$info['codename']}<br/>Version: {$info['version']}";
		return $widget;
	}

	public function loginAction(){
		$data = $this->_request->getParams();
		$data = $this->doQoolHook('pre_login_action',$data);
		$t = $this->getDbTables();
		$uname = $this->quote($data['username']);
		$pass = $this->quote(md5($data['password']));
		$level = $this->quote($this->level);
		$u = $t['users'];
		$u2g = $t['user_to_groups'];
		$g = $t['user_groups'];
		$sql = "SELECT $u.username,$g.level FROM $u,$g,$u2g WHERE
			$u.username=$uname AND $u.password=$pass AND $u2g.uid=$u.id AND $u2g.gid=$g.id";


		$u = $this->selectRow($sql);
		if($u['username']){
			$_SESSION['user'] = $u;
			$_SESSION['user'] = $this->doQoolHook('post_login_action_success',$_SESSION['user']);
			$this->_helper->redirector('index', 'index');
		}else{
			$this->doQoolHook('post_login_action_error');
			$params = array("message"=>$this->t("Wrong Username or Password combination"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('index', 'index','admin');
		}

	}



	public function applicationsinfoAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Addons Information');
		$dirs = $this->dirs;
		$addon = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$data['id'].DIR_SEP."addon.xml");
		$html = "<h1>{$this->t($addon->name)}</h1>";
		$html .= "<div class='row'>";
		$html .= "<div class='span2'>";
		$html .= "{$addon->description}";
		$html .= "</div>";
		$html .= "<div class='span3 well pull-right'>";
		$html .= "{$this->t('Version')}: <span class='badge pull-right badge-info'>{$addon->version}</span><br>";
		$html .= "{$this->t('Tags')}: <span class='badge pull-right badge-info'>{$addon->tags}</span><br>";
		$html .= "{$this->t('Created')}: <span class='badge pull-right badge-info'>{$addon->creationDate}</span><br>";
		$html .= "{$this->t('Licence')}: <span class='badge pull-right badge-info'>{$addon->licence}</span><br>";
		$html .= "{$this->t('Author')}: <a class='badge pull-right badge-info' href='{$addon->author->url}' target='_blank'>{$addon->author->name}</a><br>";
		$html .= "{$this->t('Addon Url')}: <a class='badge pull-right badge-info' href='{$addon->author->addon_url}' target='_blank'>{$this->t("Visit addon site")}</a><br>";
		$html .= "</div>";

		$html .= "</div>";
		if($data['ajaxcalled']){
			die($html);
		}
		$this->totpl('html',$html);
	}



	public function modulesinfoAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Addons Information');
		$dirs = $this->dirs;
		$addon = readLangFile(APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$data['id'].DIR_SEP."addon.xml");
		$html = "<h1>{$this->t($addon->name)}</h1>";
		$html .= "<div class='row'>";
		$html .= "<div class='span2'>";
		$html .= "{$addon->description}";
		$html .= "</div>";
		$html .= "<div class='span3 well pull-right'>";
		$html .= "{$this->t('Version')}: <span class='badge pull-right badge-info'>{$addon->version}</span><br>";
		$html .= "{$this->t('Tags')}: <span class='badge pull-right badge-info'>{$addon->tags}</span><br>";
		$html .= "{$this->t('Created')}: <span class='badge pull-right badge-info'>{$addon->creationDate}</span><br>";
		$html .= "{$this->t('Licence')}: <span class='badge pull-right badge-info'>{$addon->licence}</span><br>";
		$html .= "{$this->t('Author')}: <a class='badge pull-right badge-info' href='{$addon->author->url}' target='_blank'>{$addon->author->name}</a><br>";
		$html .= "{$this->t('Addon Url')}: <a class='badge pull-right badge-info' href='{$addon->author->addon_url}' target='_blank'>{$this->t("Visit addon site")}</a><br>";
		$html .= "</div>";

		$html .= "</div>";
		if($data['ajaxcalled']){
			die($html);
		}
		$this->totpl('html',$html);
	}

	public function widgetsinfoAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Addons Information');
		$dirs = $this->dirs;
		$addon = readLangFile(APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$data['id'].DIR_SEP."addon.xml");
		$html = "<h1>{$this->t($addon->name)}</h1>";
		$html .= "<div class='row'>";
		$html .= "<div class='span2'>";
		$html .= "{$addon->description}";
		$html .= "</div>";
		$html .= "<div class='span3 well pull-right'>";
		$html .= "{$this->t('Version')}: <span class='badge pull-right badge-info'>{$addon->version}</span><br>";
		$html .= "{$this->t('Tags')}: <span class='badge pull-right badge-info'>{$addon->tags}</span><br>";
		$html .= "{$this->t('Created')}: <span class='badge pull-right badge-info'>{$addon->creationDate}</span><br>";
		$html .= "{$this->t('Licence')}: <span class='badge pull-right badge-info'>{$addon->licence}</span><br>";
		$html .= "{$this->t('Author')}: <a class='badge pull-right badge-info' href='{$addon->author->url}' target='_blank'>{$addon->author->name}</a><br>";
		$html .= "{$this->t('Addon Url')}: <a class='badge pull-right badge-info' href='{$addon->author->addon_url}' target='_blank'>{$this->t("Visit addon site")}</a><br>";
		$html .= "</div>";

		$html .= "</div>";
		if($data['ajaxcalled']){
			die($html);
		}
		$this->totpl('html',$html);
	}

	//languages
	public function languagelistAction(){
		$data = $this->_request->getParams();
		$this->addToBreadcrumb(array('languagelist',$this->t('Languages')));
		//create the module menu
		$menu = array(
		'uploadlang'	=>	'Upload new language file'
		);
		$menu = $this->doQoolHook('languages_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		$this->totpl('theInclude','langlist');
		Zend_Registry::set('module','Languages');
		$config = $this->config;

		//get the languages for the frontend and the backend.
		$langs = $config->languages->toArray();
		//d($langs);
		$this->toTpl('theList',$langs);
	}

	public function uploadlangAction(){
		$this->addToBreadcrumb(array('languagelist',$this->t('Languages')));
		$this->addToBreadcrumb(array('uploadlang',$this->t('Upload Language'),$data['id']));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Languages');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/uploadlangfile')->setMethod('post');
		$upload = new Zend_Form_Element_File('langzip');
		$upload->setLabel($this->t("Choose a language file in .zip format"));
		$form->addElement($upload);
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setLabel($this->t("Upload"));
		$submit->setAttrib('class',"btn btn-primary");
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Upload a new language"));
		$this->toTpl('theForm',$form);
	}

	public function uploadlangfileAction(){
		$dirs = $this->dirs;
		$form = new Zend_Form;
		$form->setView($this->tpl);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$upload = new Zend_File_Transfer_Adapter_Http();
				$upload->setDestination(APPL_PATH.$dirs['structure']['uploads'].DIR_SEP);
				try {
					$upload->receive();
					$file = $upload->getFileInfo();
					if($this->unzip($file['langzip'],APPL_PATH.$dirs['structure']['uploads'].DIR_SEP,APPL_PATH.$dirs['structure']['languages'].DIR_SEP,true)){
						if($this->addlanguage($file['langzip']['name'])){
							$params = array("message"=>$this->t("Language uploaded and added"),"msgtype"=>'success');
							$this->addMessage($params);
							$this->_helper->redirector('languagelist', 'index','admin');
						}else{
							$params = array("message"=>$this->t("Could not add language"),"msgtype"=>'error');
							$this->addMessage($params);
							$this->_helper->redirector('uploadlang', 'index','admin');
						}
					}else{
						$params = array("message"=>$this->t("Unknown Error"),"msgtype"=>'error');
						$this->addMessage($params);
						$this->_helper->redirector('uploadlang', 'index','admin');
					}
				} catch (Zend_File_Transfer_Exception $e) {
					$params = array("message"=>$e->getMessage(),"msgtype"=>'error');
					$this->addMessage($params);
					$this->_helper->redirector('uploadlang', 'index','admin');
				}
			}
		}
	}

	public function addlanguage($file){
		$file = explode(".",$file);
		$file = $file[0];
		try {
			$xml = $this->readConfigFile();
			$node = $xml->languages->frontend->available;
			$node->addChild('language',$file);
			$node2 = $xml->languages->backend->available;
			$node2->addChild('language',$file);
			$xml->asXML(APPL_PATH.'config/config.xml');
			return true;
		}catch (Exception $e){
			return false;
		}
	}
	//language removal
	public function delfrontendlangAction(){
		$this->tpl->assign('theInclude','langlist');
		Zend_Registry::set('module','Languages');
		$data = $this->_request->getParams();
		$xml = $this->readConfigFile();
		if($data['id']==$xml->languages->frontend->language){
			$params = array("message"=>$this->t("Cannot remove the default language. Please set another language as default and remove after."),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('languagelist', 'index','admin');
		}
		$i=0;
		foreach ($xml->languages->frontend->available->language as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo[0]==$data['id'] && $data['id']!=$xml->languages->frontend->language){
				unset($xml->languages->frontend->available->language[$i]);
			}
			$i++;
		}
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Language record removed from frontent"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('languagelist', 'index','admin');
	}

	public function delbackendlangAction(){
		$this->tpl->assign('theInclude','langlist');
		Zend_Registry::set('module','Languages');
		$data = $this->_request->getParams();
		$xml = $this->readConfigFile();
		if($data['id']==$xml->languages->backend->language){
			$params = array("message"=>$this->t("Cannot remove the default language. Please set another language as default and remove after."),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('languagelist', 'index','admin');
		}
		$i=0;
		foreach ($xml->languages->backend->available->language as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo[0]==$data['id'] && $data['id']!=$xml->languages->backend->language){
				unset($xml->languages->backend->available->language[$i]);
			}
			$i++;
		}
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Language record removed from backend"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('languagelist', 'index','admin');
	}

	public function setdefaultbackendlangAction(){
		$this->tpl->assign('theInclude','langlist');
		Zend_Registry::set('module','Languages');
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$lang = readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$data['id'].DIR_SEP."language.xml");
		$lang = $this->jsonArray($lang);

		$xml = $this->readConfigFile();
		$xml->languages->backend->language=$data['id'];
		$xml->languages->backend->shortname=$lang['@attributes']['shortname'];
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Option Saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('languagelist', 'index','admin');
	}

	public function setdefaultfrontendlangAction(){
		$this->tpl->assign('theInclude','langlist');
		Zend_Registry::set('module','Languages');
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$lang = readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$data['id'].DIR_SEP."language.xml");
		$lang = $this->jsonArray($lang);

		$xml = $this->readConfigFile();
		$xml->languages->frontend->language=$data['id'];
		$xml->languages->frontend->shortname=$lang['@attributes']['shortname'];
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Option Saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('languagelist', 'index','admin');
	}

	public function addonslistAction(){
		$this->addToBreadcrumb(array('addonslist',$this->t('Addons')));

		$this->tpl->assign('theInclude','addonlist');
		Zend_Registry::set('module','Addons');
		$dirs = $this->dirs;
		//create the module menu
		$menu = array(
		'uploadaddon'	=>	'Upload new addon',
		'uploadmodule'	=>	'Upload new module',
		'uploadwidget'	=>	'Upload new widget'
		);
		$menu = $this->doQoolHook('addonslist_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		$langs = readLangFile('config/addons.xml');
		$langs = $this->jsonArray($langs);
		//d($langs);
		$this->toTpl('theList',$langs);
	}

	public function uploadaddonAction(){
		$this->addToBreadcrumb(array('addonslist',$this->t('Addons')));
		$this->addToBreadcrumb(array('uploadaddon',$this->t('Upload Addon'),$data['id']));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Upload new Addon');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/uploadaddonfile')->setMethod('post');
		$upload = new Zend_Form_Element_File('addonzip');
		$upload->setLabel($this->t("Choose an addon in .zip format"));
		$form->addElement($upload);
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setLabel($this->t("Upload"));
		$submit->setAttrib('class',"btn btn-primary");
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Upload a new addon"));
		$this->toTpl('theForm',$form);
	}

	public function uploadmoduleAction(){
		$this->addToBreadcrumb(array('addonslist',$this->t('Addons')));
		$this->addToBreadcrumb(array('uploadmodule',$this->t('Upload Module'),$data['id']));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Upload new Module');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/uploadmodulefile')->setMethod('post');
		$upload = new Zend_Form_Element_File('modulezip');
		$upload->setLabel($this->t("Choose a module in .zip format"));
		$form->addElement($upload);
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setLabel($this->t("Upload"));
		$submit->setAttrib('class',"btn btn-primary");
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Upload a new module"));
		$this->toTpl('theForm',$form);
	}

	public function uploadwidgetAction(){
		$this->addToBreadcrumb(array('addonslist',$this->t('Addons')));
		$this->addToBreadcrumb(array('uploadwidget',$this->t('Upload Widget'),$data['id']));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Upload new Widget');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/uploadwidgetfile')->setMethod('post');
		$upload = new Zend_Form_Element_File('widgetzip');
		$upload->setLabel($this->t("Choose a widget in .zip format"));
		$form->addElement($upload);
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setLabel($this->t("Upload"));
		$submit->setAttrib('class',"btn btn-primary");
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Upload a new widget"));
		$this->toTpl('theForm',$form);
	}

	//applications activation
	function activateapplicationsAction(){
		//state="installed" level="8000" adminlevel="1" cachetime="500"
		$this->tpl->assign('theInclude','addonlist');
		Zend_Registry::set('module','Addons');

		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		foreach ($xml->applications->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['name']==$data['id']){
				$addon = $v;
				$addon->addAttribute('state','installed');
				$addon->addAttribute('level','8000');
				$addon->addAttribute('adminlevel','1');
				$addon->addAttribute('cachetime','500');
			}
		}
		//activate any modules
		foreach ($xml->modules->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['parent']==$data['id']){
				$addon = $v;
				$addon->addAttribute('state','installed');
				$addon->addAttribute('level','8000');
				$addon->addAttribute('adminlevel','1');
				$addon->addAttribute('cachetime','500');
			}
		}
		//activate any widgets
		foreach ($xml->widgets->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['parent']==$data['id']){
				$addon = $v;
				$addon->addAttribute('state','installed');
				$addon->addAttribute('level','8000');
				$addon->addAttribute('adminlevel','1');
				$addon->addAttribute('cachetime','500');
			}
		}
		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Addon Activated"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	function deactivateapplicationsAction(){
		//state="installed" level="8000" adminlevel="1" cachetime="500"
		$this->tpl->assign('theInclude','addonlist');
		Zend_Registry::set('module','Addons');

		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		$i=0;
		//deactivate the addon
		foreach ($xml->applications->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['name']==$data['id']){
				unset($xml->applications->addon[$i]['state']);
				unset($xml->applications->addon[$i]['level']);
				unset($xml->applications->addon[$i]['adminlevel']);
				unset($xml->applications->addon[$i]['cachetime']);
			}
			$i++;
		}
		//deactivate the modules that are children of this addon
		$i=0;
		foreach ($xml->modules->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['parent']==$data['id']){
				unset($xml->modules->addon[$i]['state']);
				unset($xml->modules->addon[$i]['level']);
				unset($xml->modules->addon[$i]['adminlevel']);
				unset($xml->modules->addon[$i]['cachetime']);
			}
			$i++;
		}
		//deactivate the widgets that are children of this addon
		$i=0;
		foreach ($xml->widgets->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['parent']==$data['id']){
				unset($xml->widgets->addon[$i]['state']);
				unset($xml->widgets->addon[$i]['level']);
				unset($xml->widgets->addon[$i]['adminlevel']);
				unset($xml->widgets->addon[$i]['cachetime']);
			}
			$i++;
		}

		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Addon Deactivated"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	//modules activation
	function activatemodulesAction(){
		//state="installed" level="8000" adminlevel="1" cachetime="500"
		$this->tpl->assign('theInclude','addonlist');
		Zend_Registry::set('module','Addons');

		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		foreach ($xml->modules->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['name']==$data['id']){
				$addon = $v;
				$addon->addAttribute('state','installed');
				$addon->addAttribute('level','8000');
				$addon->addAttribute('adminlevel','1');
				$addon->addAttribute('cachetime','500');
			}
		}
		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Module Activated"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	function deactivatemodulesAction(){
		//state="installed" level="8000" adminlevel="1" cachetime="500"
		$this->tpl->assign('theInclude','addonlist');
		Zend_Registry::set('module','Addons');

		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		$i=0;
		foreach ($xml->modules->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['name']==$data['id']){
				unset($xml->modules->addon[$i]['state']);
				unset($xml->modules->addon[$i]['level']);
				unset($xml->modules->addon[$i]['adminlevel']);
				unset($xml->modules->addon[$i]['cachetime']);
			}
			$i++;
		}

		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Module Deactivated"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	//widgets activation
	function activatewidgetsAction(){
		//state="installed" level="8000" adminlevel="1" cachetime="500"
		$this->tpl->assign('theInclude','addonlist');
		Zend_Registry::set('module','Addons');

		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		foreach ($xml->widgets->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['name']==$data['id']){
				$addon = $v;
				$addon->addAttribute('state','installed');
				$addon->addAttribute('level','8000');
				$addon->addAttribute('adminlevel','1');
				$addon->addAttribute('cachetime','500');
			}
		}
		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Widget Activated"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	function deactivatewidgetsAction(){
		//state="installed" level="8000" adminlevel="1" cachetime="500"
		$this->tpl->assign('theInclude','addonlist');
		Zend_Registry::set('module','Addons');

		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		$i=0;
		foreach ($xml->widgets->addon as $k=>$v){
			$vo = $this->jsonArray($v);
			if($vo['@attributes']['name']==$data['id']){
				unset($xml->widgets->addon[$i]['state']);
				unset($xml->widgets->addon[$i]['level']);
				unset($xml->widgets->addon[$i]['adminlevel']);
				unset($xml->widgets->addon[$i]['cachetime']);
			}
			$i++;
		}

		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Widget Deactivated"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	public function editlangAction(){
		$dirs = $this->dirs;
		$this->toTpl('hasForm',1);
		$data = $this->_request->getParams();
		$this->addToBreadcrumb(array('languagelist',$this->t('Languages')));
		$this->addToBreadcrumb(array('editlang',$this->t('Edit Language'),$data['id']));
		$this->addToBreadcrumb($this->t($data['id']));
		$this->tpl->assign('theInclude','form');
		Zend_Registry::set('module','Languages');
		$xml = readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$data['id'].DIR_SEP."language.xml");
		$user = readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$data['id'].DIR_SEP."user.xml");
		$lang = buildLanguage($xml,$user);
		//lets get the autotranslate feature to work
		$xml = readLangFile($dirs['structure']['languages'].DIR_SEP."autotranslate.xml");

		$autotranslate = cleanAutoTranslate($xml,$data['id']);
		$autotranslate = buildLanguage($autotranslate,array());
		$lang = array_merge($autotranslate,$lang);
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		//$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savelanguage')->setMethod('post');
		$contentid = new Zend_Form_Element_Hidden('langid');
		$contentid->setValue($data['id']);
		$form->addElement($contentid);
		foreach ($lang as $k=>$v){
			$o = $k;
			$k = $this->normalizeForForm($k,1);
			$element = new Zend_Form_Element_Textarea($k);
			$element->setValue($v);
			$element->setLabel(ucfirst($o));
			$element->setAttrib("class","span12");
			$element->setAttrib("style","height:70px");
			$form->addElement($element);
		}
		//we need something to move the buttons down...
		$form->addElement('hidden',	'dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();

		$newval = new Zend_Form_Element_Text('qoolnewval');
		$newval->setLabel($this->t("New Language Value"));
		$newtranslate =  new Zend_Form_Element_Textarea('qoolnewtranslate');
		$newtranslate->setLabel($this->t("New Language Translation"));
		$newtranslate->setAttrib("class","span12");
		$newtranslate->setAttrib("style","height:70px");
		$form->addDisplayGroup(array($newval,$newtranslate),'newval');
		$form->addElement('hidden',	'dummy1',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy1->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-large btn-primary');
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Edit Language"));
		$this->toTpl('theForm',$form);
	}

	private function normalizeForForm($k,$in=false){
		if($in){
			$k = str_replace(" ","_",$k);
			$k = str_replace(".","__",$k);
			$k = str_replace(",","___",$k);
			$k = str_replace("!","____",$k);
			$k = str_replace("?","_____",$k);
			$k = str_replace("@","______",$k);
			$k = str_replace("#","_______",$k);
			$k = str_replace("%","________",$k);
			$k = str_replace("&","_________",$k);
			$k = str_replace("*","__________",$k);
			$k = str_replace("(","___________",$k);
			$k = str_replace(")","____________",$k);
			$k = str_replace("=","_____________",$k);
			$k = str_replace("+","______________",$k);
			$k = str_replace("\$","_______________",$k);
			$k = str_replace("^","________________",$k);
			$k = str_replace("[","_________________",$k);
			$k = str_replace("]","__________________",$k);
			$k = str_replace("/","___________________",$k);
			$k = str_replace("\\","____________________",$k);
		}else{
			$k = str_replace("____________________","\\",$k);
			$k = str_replace("___________________","/",$k);
			$k = str_replace("__________________","]",$k);
			$k = str_replace("_________________","[",$k);
			$k = str_replace("________________","^",$k);
			$k = str_replace("_______________","\$",$k);
			$k = str_replace("______________","+",$k);
			$k = str_replace("_____________","=",$k);
			$k = str_replace("____________",")",$k);
			$k = str_replace("___________","(",$k);
			$k = str_replace("__________","*",$k);
			$k = str_replace("_________","&",$k);
			$k = str_replace("________","%",$k);
			$k = str_replace("_______","#",$k);
			$k = str_replace("______","@",$k);
			$k = str_replace("_____","?",$k);
			$k = str_replace("____","!",$k);
			$k = str_replace("___",",",$k);
			$k = str_replace("__",".",$k);
			$k = str_replace("_"," ",$k);
		}
		return $k;
	}

	public function savelanguageAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		$language = $data['langid'];
		unset($data['langid']);
		unset($data['module']);
		unset($data['action']);
		unset($data['controller']);
		unset($data['submit']);
		if($data['qoolnewval']){
			$data[$data['qoolnewval']] = $data['qoolnewtranslate'];
		}
		unset($data['qoolnewval']);
		unset($data['qoolnewtranslate']);
		//d($data);
		$xml = readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$language.DIR_SEP."user.xml");
		$xml = json_encode($xml);
		$xml = json_decode($xml,1);

		$file = '<?xml version="1.0" encoding="utf-8"?>';
		$file .= '<language author="'.$xml['@attributes']['author'].'">'.PHP_EOL;
		foreach ($data as $k=>$v){
			$k = $this->normalizeForForm($k);
			$file .= '	<translate value="'.$k.'">'.$v.'</translate>'.PHP_EOL;
		}
		$file .= '</language>';
		$file = new SimpleXMLElement($file);
		$file->asXML(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$language.DIR_SEP."user.xml");
		$params = array("message"=>$this->t("Language Saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('editlang', 'index','admin',array("id"=>$language));
	}


	public function datafieldsAction(){
		$this->addToBreadcrumb(array('datafields',$this->t('Data Fields')));
		Zend_Registry::set('theaction','datafield');
		$this->tpl->assign('theInclude','datalist');
		Zend_Registry::set('module','Data Fields');
		$menu = array(
		'newdatafield'	=>	'Add Data Field'
		);
		$menu = $this->doQoolHook('datafields_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		$t = $this->getDbTables();
		$sql = "SELECT {$t['data']}.*,{$t['content_types']}.title as type_name FROM {$t['data']},{$t['content_types']} WHERE
		{$t['data']}.group_id={$t['content_types']}.id ORDER BY {$t['data']}.group_id, {$t['data']}.order, {$t['data']}.id";
		$r = $this->selectAllPaged($sql);
		$this->toTpl('theList',$r);
	}

	public function addnewtypeAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			$t = $this->getDbTables();

			unset($data['action']);
			unset($data['module']);
			unset($data['controller']);
			unset($data['save']);
			//create the index for this type
			$this->addIndex($data);
			$this->save($t['content_types'],$data);
			$params = array("message"=>$this->t("Content Type Added"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('contentlist', 'index','admin');
		}
	}

	public function savetypeAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			$t = $this->getDbTables();

			unset($data['action']);
			unset($data['module']);
			unset($data['controller']);
			unset($data['save']);
			$tid = $data['tid'];
			unset($data['tid']);
			$this->update($t['content_types'],$data,$tid);
			$params = array("message"=>$this->t("Content Type Updated"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('contentlist', 'index','admin');
		}
	}

	public function addnewdatafieldAction(){
		if ($this->_request->isPost()) {
			try{
				$data = $this->_request->getParams();
				$t = $this->getDbTables();

				unset($data['action']);
				unset($data['module']);
				unset($data['controller']);
				unset($data['save']);
				$this->save($t['data'],$data);
				$params = array("message"=>$this->t("Data Field Added"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('datafields', 'index','admin');
			}catch (Exception $e){
				echo $e->getMessage();
				die();
			}
		}
	}

	public function savedatafieldAction(){
		if ($this->_request->isPost()) {
			try{
				$data = $this->_request->getParams();
				$t = $this->getDbTables();

				if($data = $this->cleanPost($data)){
					$this->update($t['data'],$data,$data['id']);
					$params = array("message"=>$this->t("Data Field Updated"),"msgtype"=>'success');
					$this->addMessage($params);
					$this->_helper->redirector('datafields', 'index','admin');
				}
			}catch (Exception $e){
				echo $e->getMessage();
				die();
			}
		}
	}

	public function newdatafieldAction(){
		$this->addToBreadcrumb(array('datafields',$this->t('Data Fields')));
		$this->addToBreadcrumb(array('newdatafield',$this->t('Add Data Field')));
		$this->toTpl('theInclude','form');
		Zend_Registry::set('module','Add Data Field');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addnewdatafield')->setMethod('post');

		$form->addElement($this->getFormElement(array("name"=>'group_id',"value"=>'selectbox',"title"=>$this->t("Content Type"),'use_pool'=>'getContentTypes')));
		$form->addElement($this->getFormElement(array("name"=>'name',"value"=>'textinput',"title"=>$this->t("Title"))));
		$form->addElement($this->getFormElement(array("name"=>'value',"value"=>'selectbox',"title"=>$this->t("Field Type"),'use_pool'=>'getFieldTypes')));
		$form->addElement($this->getFormElement(array("name"=>'use_pool',"value"=>'selectbox',"title"=>$this->t("Use Pool"),'use_pool'=>'getPools','novalue'=>true)));
		$form->addElement($this->getFormElement(array("name"=>'pool_type',"value"=>'selectbox',"title"=>$this->t("Pool"),'novalue'=>true,'use_pool'=>'getTaxonomyTypes')));
		$form->addElement($this->getFormElement(array("name"=>'order',"value"=>'textinput',"title"=>$this->t("Order"))));
		$form->addElement($this->getFormElement(array("name"=>'is_taxonomy',"value"=>'checkbox',"title"=>$this->t("Taxonomy Field")),false));

		$form->addElement('hidden',	'dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();

		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$reset = new Zend_Form_Element_Reset('reset');
		$reset->setAttrib('class','btn');
		$reset->setDecorators(array("ViewHelper"));
		$reset->setLabel($this->t("Reset"));
		$form->addElement($reset);
		$this->toTpl('formTitle',$this->t("Add Data Field"));
		$this->toTpl('theForm',$form);
	}

	function getDataField($id){
		$t = $this->getDbTables();
		$d = $t['data'];
		$id = $this->quote($id);
		$sql = "SELECT * FROM $d WHERE `id`=$id";
		return $this->selectRow($sql);
	}

	public function editdatafieldAction(){
		$this->toTpl('theInclude','form');
		Zend_Registry::set('module','Edit Data Field');
		$data = $this->_request->getParams();
		$this->addToBreadcrumb(array('datafields',$this->t('Data Fields')));
		$this->addToBreadcrumb(array('editdatafield',$this->t('Edit Data Field'),$data['id']));
		$field = $this->getDataField($data['id']);

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savedatafield')->setMethod('post');

		$form->addElement($this->getFormElement(array("name"=>'group_id',"value"=>'selectbox',"title"=>$this->t("Content Type"),'use_pool'=>'getContentTypes'),$field['group_id']));
		$form->addElement($this->getFormElement(array("name"=>'name',"value"=>'textinput',"title"=>$this->t("Title")),$field['name']));
		$form->addElement($this->getFormElement(array("name"=>'value',"value"=>'selectbox',"title"=>$this->t("Field Type"),'use_pool'=>'getFieldTypes'),$field['value']));
		$form->addElement($this->getFormElement(array("name"=>'use_pool',"value"=>'selectbox',"title"=>$this->t("Use Pool"),'use_pool'=>'getPools','novalue'=>true),$field['use_pool']));
		$form->addElement($this->getFormElement(array("name"=>'pool_type',"value"=>'selectbox',"title"=>$this->t("Pool"),'novalue'=>true,'use_pool'=>'getTaxonomyTypes'),$field['pool_type']));
		$form->addElement($this->getFormElement(array('name'=>'id','value'=>'hidden'),$data['id']));
		$form->addElement($this->getFormElement(array("name"=>'order',"value"=>'textinput',"title"=>$this->t("Order")),$field['order']));
		$form->addElement($this->getFormElement(array("name"=>'is_taxonomy',"value"=>'checkbox',"title"=>$this->t("Taxonomy Field")),$field['is_taxonomy']));
		$form->addElement('hidden',	'dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();

		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$reset = new Zend_Form_Element_Reset('reset');
		$reset->setAttrib('class','btn');
		$reset->setDecorators(array("ViewHelper"));
		$reset->setLabel($this->t("Reset"));
		$form->addElement($reset);
		$this->toTpl('formTitle',$this->t("Edit Data Field"));
		$this->toTpl('theForm',$form);
	}

	public function newtypeAction(){
		$this->toTpl('theInclude','form');
		Zend_Registry::set('module','Add Content Type');
		$this->addToBreadcrumb(array('contentlist',$this->t('Content Type List')));
		$this->addToBreadcrumb(array('newtype',$this->t('Add Content Type')));
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addnewtype')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"))));
		$form->addElement($this->getFormElement(array("name"=>'mime',"value"=>'selectbox',"title"=>$this->t("Mime"),'use_pool'=>'getMimeTypes')));
		$form->addElement($this->getFormElement(array("name"=>'lib',"value"=>'selectbox',"title"=>$this->t("Lib"),'use_pool'=>'getLibraries')));
		$form->addElement($this->getFormElement(array("name"=>'headers',"value"=>'selectbox',"title"=>$this->t("Headers"),'use_pool'=>'getHeaderTypes')));

		$form->addElement('hidden',	'dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();

		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$reset = new Zend_Form_Element_Reset('reset');
		$reset->setAttrib('class','btn');
		$reset->setDecorators(array("ViewHelper"));
		$reset->setLabel($this->t("Reset"));
		$form->addElement($reset);
		$this->toTpl('formTitle',$this->t("Add Content Type"));
		$this->toTpl('theForm',$form);
	}

	public function edittypeAction(){
		$this->toTpl('theInclude','form');
		Zend_Registry::set('module','Edit Content Type');
		$data = $this->_request->getParams();
		$type = $this->getContentType($data['id']);
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$addon = new Zend_Form_Element_Hidden('tid');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$form->setAction($this->config->host->folder.'/admin/savetype')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title")),$type['title']));
		$form->addElement($this->getFormElement(array("name"=>'mime',"value"=>'selectbox',"title"=>$this->t("Mime"),'use_pool'=>'getMimeTypes'),$type['mime']));
		$form->addElement($this->getFormElement(array("name"=>'lib',"value"=>'selectbox',"title"=>$this->t("Lib"),'use_pool'=>'getLibraries'),$type['lib']));
		$form->addElement($this->getFormElement(array("name"=>'headers',"value"=>'selectbox',"title"=>$this->t("Headers"),'use_pool'=>'getHeaderTypes'),$type['headers']));

		$form->addElement('hidden',	'dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();

		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$reset = new Zend_Form_Element_Reset('reset');
		$reset->setAttrib('class','btn');
		$reset->setDecorators(array("ViewHelper"));
		$reset->setLabel($this->t("Reset"));
		$form->addElement($reset);
		$this->toTpl('formTitle',$this->t("Edit Content Type"));
		$this->toTpl('theForm',$form);
	}

	public function contentlistAction(){
		$this->addToBreadcrumb(array('contentlist',$this->t('Content Type List')));
		Zend_Registry::set('theaction','type');
		$this->tpl->assign('theInclude','contentlist');
		Zend_Registry::set('module','Content Type List');
		$menu = array(
		'newtype'	=>	'Add Content Type'
		);
		$menu = $this->doQoolHook('contentlist_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['content_types']}";
		$r = $this->selectAllPaged($sql);
		$r = $this->doQoolHook('pre_contentlist_assign',$r);
		$this->toTpl('theList',$r);
	}

	public function itemlistAction(){

		Zend_Registry::set('theaction','content');
		$this->toTpl('theInclude','itemlist');
		Zend_Registry::set('module','Items List');
		$data = $this->_request->getParams();
		$this->addToBreadcrumb(array('itemlist',$this->t('Items List'),$data['id']));
		$t = $this->getDbTables();
		//$sql = "SELECT *,slug as title FROM {$t['objects']} WHERE type_id=".(int) $data['id'];
		$sql = "SELECT {$t['objects']}. * , {$t['object_data']}.value as title FROM `{$t['objects']}` , `{$t['object_data']}` WHERE {$t['objects']}.type_id =".(int) $data['id']." AND {$t['object_data']}.name='title' AND {$t['objects']}.id = {$t['object_data']}.object_id GROUP BY {$t['objects']}.slug ORDER BY {$t['objects']}.datestr DESC";
		$r = $this->selectAllPaged($sql);
		$r = $this->doQoolHook('pre_itemlist_assign',$r);
		$this->toTpl('theList',$r);
	}

	function uploadGeneral($action,$id=false){
		$dirs = $this->dirs;
		$form = new Zend_Form;
		$form->setView($this->tpl);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$upload = new Zend_File_Transfer_Adapter_Http();
				$this->createPath(APPL_PATH.$dirs['structure']['uploads'].DIR_SEP.date("Y").DIR_SEP.date("m").DIR_SEP.date("d"));
				$upload->setDestination(APPL_PATH.$dirs['structure']['uploads'].DIR_SEP.date("Y").DIR_SEP.date("m").DIR_SEP.date("d").DIR_SEP);
				try {
					$upload->receive();
					return $dirs['structure']['uploads'].DIR_SEP.date("Y").DIR_SEP.date("m").DIR_SEP.date("d").DIR_SEP;
				} catch (Zend_File_Transfer_Exception $e) {
					if($id){
						$params = array("message"=>$e->getMessage(),"msgtype"=>'error');
					}else{
						$params = array("message"=>$e->getMessage(),"msgtype"=>'error');
					}
					$this->addMessage($params);
					$this->_helper->redirector($action, 'index','admin',array('id'=>$id));
				}
			}
		}
	}


	public function editcontentAction(){

		$data = $this->_request->getParams();

		$data = $this->doQoolHook('pre_editcontent_params',$data);

		$this->addToBreadcrumb(array('itemlist',$this->t('Items List'),$data['type_id']));
		$this->addToBreadcrumb(array('editcontent',$this->t('Edit Content Item'),$data['id'],$data['type_id']));

		Zend_Registry::set('module','Edit Content Item');
		$this->toTpl('theInclude','form');
		$t = $this->getDbTables();
		$d = $t['data'];
		$sql = "SELECT * FROM $d WHERE `group_id`=".(int)$data['type_id']." ORDER BY `order` ASC";
		$sel = $this->selectAll($sql);
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/updatecontentitem')->setMethod('post');
		//the type id
		$contenttype = new Zend_Form_Element_Hidden('contenttype');
		$contenttype->setValue($data['type_id']);
		$form->addElement($contenttype);
		//the item id
		$contentid = new Zend_Form_Element_Hidden('contentid');
		$contentid->setValue($data['id']);
		$form->addElement($contentid);
		$files = array();
		foreach ($sel as $k=>$v){
			if($v['is_taxonomy']){
				$sql = "SELECT `taxonomy_id` as selected_value FROM {$t['object_to_taxonomy']} WHERE `data_id`={$v['id']} AND `object_id`=".$data['id'];
				$r = $this->selectAll($sql);
				if($v['value']=='treeselectbox' || $v['value']=='selectbox'){
					$form->addElement($this->getFormElement($v,$r[0]['selected_value']));
				}else{
					$v['attributes'] = array('rel'=>'taxonomy','rev'=>$data['id'],'data-myid'=>$v['id']);
					$form->addElement($this->getFormElement($v,$r));
				}
			}elseif($v['name']=='slug'){
				$sql = "SELECT `slug` FROM {$t['objects']} WHERE `id`=".$data['id'];
				$r = $this->selectRow($sql);
				$form->addElement($this->getFormElement($v,$r['slug']));
			}else{
				if($v['value']=='multifileinput' || $v['value']=='multifileinputs' || $v['value']=='fileinput'){
					$sql = "SELECT * FROM {$t['object_data']} WHERE `object_id`=".$data['id']." AND `name`=".$this->quote($v['name']);
					$files[$v['name']] = $this->selectAll($sql);
					$r = array('value');
				}else{
					$sql = "SELECT `value` FROM {$t['object_data']} WHERE `object_id`=".$data['id']." AND `name`=".$this->quote($v['name']);
					$r = $this->selectRow($sql);
					$r['value'] = $this->doQoolHook('get_'.$v['name'].'_field_value',$r['value']);
				}
				$form->addElement($this->getFormElement($v,$r['value']));
			}
		}

		//we need something to move the buttons down...
		$form->addElement('hidden',	'dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();

		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$reset = new Zend_Form_Element_Reset('reset');
		$reset->setAttrib('class','btn');
		$reset->setDecorators(array("ViewHelper"));
		$reset->setLabel($this->t("Reset"));
		$form->addElement($reset);
		if(count($files)>0){
			$this->toTpl('objectfiles',$files);
		}

		$this->toTpl('formTitle',$this->t("Edit content item"));
		$this->toTpl('theForm',$form);
	}

	public function updatecontentitemAction(){
		list($cid,$type) = $this->updateContent();
		$params = array("message"=>$this->t("Item Updated"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('editcontent', 'index','admin',array("id"=>(int) $cid,"type_id"=>$type));
	}

	public function addcontentitemAction(){
		list($objID,$type) = $this->insertContent();
		$params = array("message"=>$this->t("Item Saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('editcontent', 'index','admin',array("id"=>$objID,"type_id"=>$type));
	}

	public function contentnewAction(){
		$this->toTpl('theInclude','form');
		$data = $this->_request->getParams();
		$data = $this->doQoolHook('pre_contentnew',$data);
		$this->addToBreadcrumb(array('itemlist',$this->t('Items List'),$data['id']));
		$this->addToBreadcrumb(array('contentnew',$this->t('Add Content Item'),$data['id']));
		//get all needed fields to build a form that will manage this content type
		$t = $this->getDbTables();
		$d = $t['data'];
		$sql = "SELECT * FROM $d WHERE `group_id`=".(int)$data['id']." ORDER BY `order` ASC";
		$sel = $this->selectAll($sql);
		$sel = $this->doQoolHook('post_getdatafields',$sel);
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form-horizontal');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addcontentitem')->setMethod('post');
		$contenttype = new Zend_Form_Element_Hidden('contenttype');
		$contenttype->setValue($data['id']);
		$form->addElement($contenttype);
		foreach ($sel as $k=>$v){
			$form->addElement($this->getFormElement($v));
		}

		//we need something to move the buttons down...
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();

		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$reset = new Zend_Form_Element_Reset('reset');
		$reset->setAttrib('class','btn');
		$reset->setDecorators(array("ViewHelper"));
		$reset->setLabel($this->t("Reset"));
		$form->addElement($reset);
		$this->toTpl('formTitle',$this->t("Add new content item"));
		$form = $this->doQoolHook('post_contentnew',$form);
		$this->toTpl('theForm',$form);
	}







	public function buildLoginForm(){
		try {
			$form = new Zend_Form;
			$form->setView($this->tpl);
			$form->setAttrib('class', 'form-inline');
			$form->removeDecorator('dl');
			$form->setAction($this->config->host->folder.'/admin/login')->setMethod('post');
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
			$submit->setLabel($this->language['Login']);
			$form->addElement($username)->addElement($password)->addElement($submit);
			$form = $this->doQoolHook('post_loginform_create',$form);
			$this->toTpl('loginForm',$form);
		}catch (Exception $e){
			echo $e->getMessage();
		}

	}

	public function uploadaddonfileAction(){
		$dirs = $this->dirs;
		$form = new Zend_Form;
		$form->setView($this->tpl);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$upload = new Zend_File_Transfer_Adapter_Http();
				$upload->setDestination(APPL_PATH.$dirs['structure']['uploads'].DIR_SEP);
				try {
					$upload->receive();
					$file = $upload->getFileInfo();
					$filename = explode(".",$file['addonzip']['name']);
					$filename = $filename[0];
					if($this->unzip($file['addonzip'],APPL_PATH.$dirs['structure']['uploads'].DIR_SEP,APPL_PATH.$dirs['structure']['addons'].DIR_SEP,true)){
						if($this->addaddon($filename,readLangFile($dirs['structure']['addons'].DIR_SEP.$filename.DIR_SEP."addon.xml"))){
							$params = array("message"=>$this->t("Addon uploaded and added"),"msgtype"=>'success');
							$this->addMessage($params);
							$this->_helper->redirector('addonslist', 'index','admin');
						}else{
							$params = array("message"=>$this->t("Could not add addon"),"msgtype"=>'error');
							$this->addMessage($params);
							$this->_helper->redirector('uploadaddon', 'index','admin');
						}
					}else{
						$params = array("message"=>$this->t("Unknown Error"),"msgtype"=>'error');
						$this->addMessage($params);
						$this->_helper->redirector('uploadaddon', 'index','admin');
					}
				} catch (Zend_File_Transfer_Exception $e) {
					$params = array("message"=>$e->getMessage(),"msgtype"=>'error');
					$this->addMessage($params);
					$this->_helper->redirector('uploadaddon', 'index','admin');
				}
			}
		}
	}

	public function addaddon($file,$addon){
		//parent="blog" name="seo" state="installed" level="8000" adminlevel="1" cachetime="500"
		try {
			$this->doQoolHook('pre_add_addon_'.$addon->id);
			$xml = readLangFile(APPL_PATH."config/addons.xml");
			$node = $xml->applications;
			$newnode = $node->addChild('addon');
			$newnode->addAttribute('parent', 'none');
			$newnode->addAttribute('name', $file);
			$newnode->addAttribute('state', 'installed');
			$newnode->addAttribute('level', '8000');
			$newnode->addAttribute('adminlevel', '1');
			$newnode->addAttribute('cachetime', '500');
			$newnode->addAttribute('title',$addon->name);

			//run the install func if available
			if($func = $addon->on_install){
				$dirs = $this->dirs;

				include_once($dirs['structure']['addons'].DIR_SEP.$file.DIR_SEP."func.php");
				$func = $this->jsonArray($func);
				$func = $func[0];
				$func($this);
			}
			//create the content type for this addon
			$this->addAddonContentType($addon);

			//lets see if the addon has modules
			if($addon->modules){
				foreach ($addon->modules->module as $k=>$v){
					$v = $this->jsonArray($v);
					if($v['@attributes']){
						$this->doQoolHook('pre_add_module_'.$v['@attributes']['id']);
						$modnode = $xml->modules;
						$newmod = $modnode->addChild('addon');
						$newmod->addAttribute('parent', $file);
						$newmod->addAttribute('name', $v['@attributes']['id']);
						$newmod->addAttribute('state', 'installed');
						$newmod->addAttribute('level', '8000');
						$newmod->addAttribute('adminlevel', '1');
						$newmod->addAttribute('cachetime', '500');
						$newmod->addAttribute('title', $v['@attributes']['name']);
						$this->doQoolHook('post_add_module_'.$v['@attributes']['id']);
					}
				}
			}
			//now lets see if the addon has widgets
			if($addon->widgets){
				foreach ($addon->widgets->widget as $k=>$v){
					$v = $this->jsonArray($v);
					if($v['@attributes']){
						$this->doQoolHook('pre_add_widget_'.$v['@attributes']['id']);
						$widgetnode = $xml->widgets;
						$newwidget = $widgetnode->addChild('addon');
						$newwidget->addAttribute('parent', $file);
						$newwidget->addAttribute('name', $v['@attributes']['id']);
						$newwidget->addAttribute('state', 'installed');
						$newwidget->addAttribute('level', '8000');
						$newwidget->addAttribute('adminlevel', '1');
						$newwidget->addAttribute('cachetime', '500');
						$newwidget->addAttribute('title', $v['@attributes']['name']);
						$this->doQoolHook('post_add_widget_'.$v['@attributes']['id']);
					}
				}
			}
			$this->doQoolHook('post_add_addon_'.$addon->id);
			$xml->asXML(APPL_PATH.'config/addons.xml');
			return true;
		}catch (Exception $e){
			return false;
		}
	}

	function addAddonContentType($addon){
		if($addon->content){
			$t = $this->getDbTables();
			//insert the content type
			$type = array(
			'title'		=>	$addon->content->title,
			'mime'		=>	$addon->content->mime,
			'lib'		=>	$addon->content->lib,
			'headers'	=>	$addon->content->headers
			);
			$ctypeid = $this->save($t['content_types'],$type);
			//we must now add the fields needed
			foreach ($addon->content->data->item as $k=>$v){
				$v = $this->jsonArray($v);
				$v['group_id'] = $ctypeid;
				$this->save($t['data'],$v);
			}
		}
	}

	public function uploadmodulefileAction(){
		$dirs = $this->dirs;
		$form = new Zend_Form;
		$form->setView($this->tpl);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$upload = new Zend_File_Transfer_Adapter_Http();
				$upload->setDestination(APPL_PATH.$dirs['structure']['uploads'].DIR_SEP);
				try {
					$upload->receive();
					$file = $upload->getFileInfo();
					$filename = explode(".",$file['modulezip']['name']);
					$filename = $filename[0];
					if($this->unzip($file['modulezip'],APPL_PATH.$dirs['structure']['uploads'].DIR_SEP,APPL_PATH.$dirs['structure']['modules'].DIR_SEP,true)){
						if($this->addmodule($filename,readLangFile($dirs['structure']['modules'].DIR_SEP.$filename.DIR_SEP."addon.xml"))){
							$params = array("message"=>$this->t("Module uploaded and added"),"msgtype"=>'success');
							$this->addMessage($params);
							$this->_helper->redirector('addonslist', 'index','admin');
						}else{
							$params = array("message"=>$this->t("Could not add module"),"msgtype"=>'error');
							$this->addMessage($params);
							$this->_helper->redirector('uploadmodule', 'index','admin');
						}
					}else{
						$params = array("message"=>$this->t("Unknown Error"),"msgtype"=>'error');
						$this->addMessage($params);
						$this->_helper->redirector('uploadmodule', 'index','admin');
					}
				} catch (Zend_File_Transfer_Exception $e) {
					$params = array("message"=>$e->getMessage(),"msgtype"=>'error');
					$this->addMessage($params);
					$this->_helper->redirector('uploadmodule', 'index','admin');
				}
			}
		}
	}

	public function addmodule($file,$addon){
		//parent="blog" name="seo" state="installed" level="8000" adminlevel="1" cachetime="500"
		try {
			$this->doQoolHook('pre_add_module_'.$addon->id);
			$xml = readLangFile(APPL_PATH."config/addons.xml");
			$node = $xml->modules;
			$newnode = $node->addChild('addon');
			$newnode->addAttribute('parent', 'none');
			$newnode->addAttribute('name', $file);
			$newnode->addAttribute('state', 'installed');
			$newnode->addAttribute('level', '8000');
			$newnode->addAttribute('adminlevel', '1');
			$newnode->addAttribute('cachetime', '500');
			$newnode->addAttribute('title',$addon->name);

			//now lets see if the module has widgets
			if($addon->widgets){
				foreach ($addon->widgets as $k=>$v){
					$v = $this->jsonArray($v);
					$this->doQoolHook('pre_add_widget_'.$v['widget']['@attributes']['id']);
					$widgetnode = $xml->widgets;
					$newwidget = $widgetnode->addChild('addon');
					$newwidget->addAttribute('parent', $file);
					$newwidget->addAttribute('name', $v['widget']['@attributes']['id']);
					$newwidget->addAttribute('state', 'installed');
					$newwidget->addAttribute('level', '8000');
					$newwidget->addAttribute('adminlevel', '1');
					$newwidget->addAttribute('cachetime', '500');
					$newwidget->addAttribute('title', $v['widget']['@attributes']['name']);
					$this->doQoolHook('post_add_widget_'.$v['widget']['@attributes']['id']);
				}
			}
			$this->doQoolHook('post_add_module_'.$addon->id);
			$xml->asXML(APPL_PATH.'config/addons.xml');
			return true;
		}catch (Exception $e){
			return false;
		}
	}

	public function uploadwidgetfileAction(){
		$dirs = $this->dirs;
		$form = new Zend_Form;
		$form->setView($this->tpl);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$upload = new Zend_File_Transfer_Adapter_Http();
				$upload->setDestination(APPL_PATH.$dirs['structure']['uploads'].DIR_SEP);
				try {
					$upload->receive();
					$file = $upload->getFileInfo();
					$filename = explode(".",$file['widgetzip']['name']);
					$filename = $filename[0];
					if($this->unzip($file['widgetzip'],APPL_PATH.$dirs['structure']['uploads'].DIR_SEP,APPL_PATH.$dirs['structure']['widgets'].DIR_SEP,true)){
						if($this->addwidget($filename,readLangFile($dirs['structure']['widgets'].DIR_SEP.$filename.DIR_SEP."addon.xml"))){
							$params = array("message"=>$this->t("Widget uploaded and added"),"msgtype"=>'success');
							$this->addMessage($params);
							$this->_helper->redirector('addonslist', 'index','admin');
						}else{
							$params = array("message"=>$this->t("Could not add widget"),"msgtype"=>'error');
							$this->addMessage($params);
							$this->_helper->redirector('uploadwidget', 'index','admin');
						}
					}else{
						$params = array("message"=>$this->t("Unknown Error"),"msgtype"=>'error');
						$this->addMessage($params);
						$this->_helper->redirector('uploadwidget', 'index','admin');
					}
				} catch (Zend_File_Transfer_Exception $e) {
					$params = array("message"=>$e->getMessage(),"msgtype"=>'error');
					$this->addMessage($params);
					$this->_helper->redirector('uploadwidget', 'index','admin');
				}
			}
		}
	}

	public function addwidget($file,$addon){
		//parent="blog" name="seo" state="installed" level="8000" adminlevel="1" cachetime="500"
		try {
			$this->doQoolHook('pre_add_widget_'.$addon->id);
			$xml = readLangFile(APPL_PATH."config/addons.xml");
			$node = $xml->widgets;
			$newnode = $node->addChild('addon');
			$newnode->addAttribute('parent', 'none');
			$newnode->addAttribute('name', $file);
			$newnode->addAttribute('state', 'installed');
			$newnode->addAttribute('level', '8000');
			$newnode->addAttribute('adminlevel', '1');
			$newnode->addAttribute('cachetime', '500');
			$newnode->addAttribute('title',$addon->name);

			$xml->asXML(APPL_PATH.'config/addons.xml');
			$this->doQoolHook('post_add_widget_'.$addon->id);
			return true;
		}catch (Exception $e){
			return false;
		}
	}

	public function applicationsconfigAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Addons Configuration');
		$dirs = $this->dirs;
		$xml = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$data['id'].DIR_SEP."addon.xml");
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/saveaddonconfig')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('addon');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$i = 0;
		foreach ($xml->settings->item as $k=>$v){
			$v = $this->jsonArray($v);
			$element = $this->getFormElement(array("name"=>$v['@attributes']['id'],"value"=>$v['type'],"title"=>$v['name']),$v['default_value']);
			if($v['type']=="selectbox"){
				foreach ($v['values']['value'] as $ko=>$vo){
					$element->addMultiOption($vo,$vo);
				}
				$element->setValue($v['default_value']);
			}
			$form->addElement($element);
			$i++;
		}
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($i<1){
			$form = $this->t("No Configuration options for this addon");
		}
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function saveaddonconfigAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$data['addon'].DIR_SEP."addon.xml");
			foreach ($xml->settings->item as $k=>$v){
				$vo = $this->jsonArray($v);
				$v->default_value = $data[$vo['@attributes']['id']];
			}
			$xml->asXML(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$data['addon'].DIR_SEP."addon.xml");
			$params = array("message"=>$this->t("Addon configuration saved"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('addonslist', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Could not save configuration for this addon"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('addonslist', 'index','admin');
		}
	}

	public function modulesconfigAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Modules Configuration');
		$dirs = $this->dirs;
		$xml = readLangFile(APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$data['id'].DIR_SEP."addon.xml");
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savemoduleconfig')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('addon');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$i=0;
		foreach ($xml->settings->item as $k=>$v){
			$v = $this->jsonArray($v);
			$element = $this->getFormElement(array("name"=>$v['@attributes']['id'],"value"=>$v['type'],"title"=>$v['name']),$v['default_value']);
			if($v['type']=="selectbox"){
				foreach ($v['values']['value'] as $ko=>$vo){
					$element->addMultiOption($vo,$vo);
				}
				$element->setValue($v['default_value']);
			}
			$form->addElement($element);
			$i++;
		}
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($i<1){
			$form = $this->t("No Configuration options for this module");
		}
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function savemoduleconfigAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$data['addon'].DIR_SEP."addon.xml");

			foreach ($xml->settings->item as $k=>$v){
				$vo = $this->jsonArray($v);
				$v->default_value = $data[$vo['@attributes']['id']];
			}
			$xml->asXML(APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$data['addon'].DIR_SEP."addon.xml");
			$params = array("message"=>$this->t("Module configuration saved"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('addonslist', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Could not save configuration for this module"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('addonslist', 'index','admin');
		}
	}

	public function widgetsconfigAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Widgets Configuration');
		$dirs = $this->dirs;
		$xml = readLangFile(APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$data['id'].DIR_SEP."addon.xml");
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savewidgetconfig')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('addon');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$i= 0;
		foreach ($xml->settings->item as $k=>$v){
			$v = $this->jsonArray($v);
			$element = $this->getFormElement(array("name"=>$v['@attributes']['id'],"value"=>$v['type'],"title"=>$v['name']),$v['default_value']);
			if($v['type']=="selectbox"){
				foreach ($v['values']['value'] as $ko=>$vo){
					$element->addMultiOption($vo,$vo);
				}
				$element->setValue($v['default_value']);
			}
			$form->addElement($element);
			$i++;
		}
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($i<1){
			$form = $this->t("No Configuration options for this widget");
		}
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function savewidgetconfigAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$data['addon'].DIR_SEP."addon.xml");

			foreach ($xml->settings->item as $k=>$v){
				$vo = $this->jsonArray($v);
				$v->default_value = $data[$vo['@attributes']['id']];
			}
			$xml->asXML(APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$data['addon'].DIR_SEP."addon.xml");
			$params = array("message"=>$this->t("Widget configuration saved"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('addonslist', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Could not save configuration for this widget"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('addonslist', 'index','admin');
		}
	}


	public function applicationssettingsAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Addon Settings');
		$dirs = $this->dirs;
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/saveaddonsettings')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('addon');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$form->addElement($this->getFormElement(array("name"=>'level','value'=>'selectbox','title'=>$this->t("User Level"),'use_pool'=>'getUserGroupLevel'),$data['lvl']));
		$form->addElement($this->getFormElement(array("name"=>'adminlevel','value'=>'selectbox','title'=>$this->t("Admin Level"),'use_pool'=>'getUserGroupLevel'),$data['alvl']));
		$form->addElement($this->getFormElement(array("name"=>'cachetime','value'=>'textinput','title'=>$this->t("Cache Time")),$data['cache']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function saveaddonsettingsAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.'config/addons.xml');
			$i=0;
			foreach ($xml->applications->addon as $k=>$v){
				$vo = $this->jsonArray($v);
				if($vo['@attributes']['name']==$data['addon']){
					$xml->applications->addon[$i]['level'] = $data['level'];
					$xml->applications->addon[$i]['adminlevel'] = $data['adminlevel'];
					$xml->applications->addon[$i]['cachetime'] = $data['cachetime'];
				}
				$i++;
			}
		}
		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Addon security settings saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	public function deltypeAction(){
		$data = $this->_request->getParams();
		try {
			if($data['id']>0){
				$t = $this->getDbTables();
				$this->delete($t['content_types'],(int) $data['id']);
				//we need to delete all datafields that belong to this type too.
				$params = array("message"=>$this->t("Content Type Deleted"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('contentlist', 'index','admin');
			}
		}catch (Exception $e){
			$params = array("message"=>$this->t("Something went wrong"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('contentlist', 'index','admin');
		}
	}

	public function deldatafieldAction(){
		$data = $this->_request->getParams();
		try {
			if($data['id']>0){
				$t = $this->getDbTables();
				$this->delete($t['data'],(int) $data['id']);
				$params = array("message"=>$this->t("Data Field Deleted"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('datafields', 'index','admin');
			}
		}catch (Exception $e){
			$params = array("message"=>$this->t("Something went wrong"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('datafields', 'index','admin');
		}
	}



	public function modulessettingsAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Module Settings');
		$dirs = $this->dirs;
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savemodulesettings')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('addon');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$form->addElement($this->getFormElement(array("name"=>'level','value'=>'selectbox','title'=>$this->t("User Level"),'use_pool'=>'getUserGroupLevel'),$data['lvl']));
		$form->addElement($this->getFormElement(array("name"=>'adminlevel','value'=>'selectbox','title'=>$this->t("Admin Level"),'use_pool'=>'getUserGroupLevel'),$data['alvl']));
		$form->addElement($this->getFormElement(array("name"=>'cachetime','value'=>'textinput','title'=>$this->t("Cache Time")),$data['cache']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function savemodulesettingsAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.'config/addons.xml');
			$i=0;
			foreach ($xml->modules->addon as $k=>$v){
				$vo = $this->jsonArray($v);
				if($vo['@attributes']['name']==$data['addon']){
					$xml->modules->addon[$i]['level'] = $data['level'];
					$xml->modules->addon[$i]['adminlevel'] = $data['adminlevel'];
					$xml->modules->addon[$i]['cachetime'] = $data['cachetime'];
				}
				$i++;
			}
		}
		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Module security settings saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	public function widgetssettingsAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Widget Settings');
		$dirs = $this->dirs;
		$xml = readLangFile(APPL_PATH.'config/addons.xml');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savewidgetsettings')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('addon');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$form->addElement($this->getFormElement(array("name"=>'level','value'=>'selectbox','title'=>$this->t("User Level"),'use_pool'=>'getUserGroupLevel'),$data['lvl']));
		$form->addElement($this->getFormElement(array("name"=>'adminlevel','value'=>'selectbox','title'=>$this->t("Admin Level"),'use_pool'=>'getUserGroupLevel'),$data['alvl']));
		$form->addElement($this->getFormElement(array("name"=>'cachetime','value'=>'textinput','title'=>$this->t("Cache Time")),$data['cache']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function savewidgetsettingsAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.'config/addons.xml');
			$i=0;
			foreach ($xml->widgets->addon as $k=>$v){
				$vo = $this->jsonArray($v);
				if($vo['@attributes']['name']==$data['addon']){
					$xml->widgets->addon[$i]['level'] = $data['level'];
					$xml->widgets->addon[$i]['adminlevel'] = $data['adminlevel'];
					$xml->widgets->addon[$i]['cachetime'] = $data['cachetime'];
				}
				$i++;
			}
		}
		$xml->asXML(APPL_PATH.'config/addons.xml');
		$params = array("message"=>$this->t("Widget security settings saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('addonslist', 'index','admin');
	}

	public function hostAction(){
		$this->addToBreadcrumb(array('host',$this->t('Host Settings')));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Host Settings');
		$xml = readLangFile(APPL_PATH.'config/config.xml');

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');

		$form->setAction($this->config->host->folder.'/admin/savehost')->setMethod('post');
		$form->addElement($this->getFormElement(array('name'=>'http','value'=>'selectbox','title'=>$this->t('HTTP or HTTPS'),'use_pool'=>'getHostProtocols'),$xml->host->http));
		$form->addElement($this->getFormElement(array('name'=>'subdomain','value'=>'textinput','title'=>$this->t('Subdomain')),$xml->host->subdomain));
		$form->addElement($this->getFormElement(array('name'=>'domain','value'=>'textinput','title'=>$this->t('Domain')),$xml->host->domain));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);

		$form = $this->doQoolHook('pre_assign_hostedit_form',$form);
		$this->toTpl('formTitle',$this->t("Host Settings"));
		$this->toTpl('theForm',$form);
	}

	public function savehostAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		$data = $this->doQoolHook('pre_save_host_post_data',$data);
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.'config/config.xml');
			$xml->host->http = $data['http'];
			$xml->host->subdomain = $data['subdomain'];
			$xml->host->domain = $data['domain'];
			$xml = $this->doQoolHook('post_edit_host_xml_data',$xml);
		}
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Host Settings saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('host', 'index','admin');
	}



	public function dbAction(){
		$this->addToBreadcrumb(array('db',$this->t('Database Settings')));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Database Settings');
		$xml = readLangFile(APPL_PATH.'config/config.xml');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savedb')->setMethod('post');


		$form->addElement($this->getFormElement(array('name'=>'type','value'=>'selectbox','title'=>$this->t('Database Type'),'use_pool'=>'getSupportedDbs'),$xml->database->type));
		$form->addElement($this->getFormElement(array('name'=>'host','value'=>'textinput','title'=>$this->t('Database Host')),$xml->database->host));
		$form->addElement($this->getFormElement(array('name'=>'username','value'=>'textinput','title'=>$this->t('DB Username')),$xml->database->username));
		$form->addElement($this->getFormElement(array('name'=>'password','value'=>'textinput','title'=>$this->t('DB Password')),$xml->database->password));
		$form->addElement($this->getFormElement(array('name'=>'db','value'=>'textinput','title'=>$this->t('DB Name')),$xml->database->db));


		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$form = $this->doQoolHook('post_get_dbedit_form',$form);
		$this->toTpl('formTitle',$this->t("Database Settings"));
		$this->toTpl('theForm',$form);
	}

	public function savedbAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		$data = $this->doQoolHook('pre_savedb_post_data',$data);
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.'config/config.xml');
			$xml->database->type = $data['type'];
			$xml->database->host = $data['host'];
			$xml->database->username = $data['username'];
			$xml->database->password = $data['password'];
			$xml->database->db = $data['db'];
			$xml = $this->doQoolHook('post_edit_db_xml_data',$xml);
		}
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Database Settings saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('db', 'index','admin');
	}

	public function siteAction(){
		$this->addToBreadcrumb(array('site',$this->t('Site Settings')));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Site Settings');
		$xml = readLangFile(APPL_PATH.'config/config.xml');

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savesite')->setMethod('post');



		$form->addElement($this->getFormElement(array('name'=>'backend_title','value'=>'textinput','title'=>$this->t('Backend Title')),$xml->site->backend_title));
		$form->addElement($this->getFormElement(array('name'=>'frontend_title','value'=>'textinput','title'=>$this->t('Frontend Title')),$xml->site->frontend_title));
		$form->addElement($this->getFormElement(array('name'=>'description','value'=>'textarea','title'=>$this->t('Site Description')),$xml->site->description));
		$form->addElement($this->getFormElement(array('name'=>'feed_copyright','value'=>'textinput','title'=>$this->t('Feeds Copyright')),$xml->site->feed_copyright));
		$form->addElement($this->getFormElement(array('name'=>'feed_generator','value'=>'textinput','title'=>$this->t('Feeds Generator')),$xml->site->feed_generator));
		$form->addElement($this->getFormElement(array('name'=>'feed_author_name','value'=>'textinput','title'=>$this->t('Feeds Author')),$xml->site->feed_author_name));
		$form->addElement($this->getFormElement(array('name'=>'feed_author_email','value'=>'textinput','title'=>$this->t('Feeds Author Email')),$xml->site->feed_author_email));
		$form->addElement($this->getFormElement(array('name'=>'feed_logo_image','value'=>'imageselect','title'=>$this->t('Feeds Image Logo')),$xml->site->feed_logo_image));
		$form->addElement($this->getFormElement(array('name'=>'captcha_adapter','value'=>'selectbox','title'=>$this->t('Captcha Adapter'),'use_pool'=>'getCaptchaAdapters'),$xml->site->captcha_adapter));
		$form->addElement($this->getFormElement(array('name'=>'recaptcha_pub','value'=>'textinput','title'=>$this->t('ReCaptcha Public Key'),'attributes'=>array('placeholder'=>$this->t("If ReCaptcha chosen"))),$xml->site->recaptcha_pub));
		$form->addElement($this->getFormElement(array('name'=>'recaptcha_priv','value'=>'textinput','title'=>$this->t('ReCaptcha Private Key'),'attributes'=>array('placeholder'=>$this->t("If ReCaptcha chosen"))),$xml->site->recaptcha_priv));
		$form->addElement($this->getFormElement(array('name'=>'help','value'=>'selectbox','title'=>$this->t('Help mode'),'use_pool'=>array(array('id'=>'on','title'=>$this->t('On')),array('id'=>'off','title'=>$this->t('Off')))),$xml->site->help));
		$form->addElement($this->getFormElement(array('name'=>'default','value'=>'selectbox','title'=>$this->t('Front Page'),'use_pool'=>'getApplications'),$xml->site->default));
		
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider editor')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$form = $this->doQoolHook('post_get_site_edit_form',$form);
		$this->toTpl('formTitle',$this->t("Site Settings"));
		$this->toTpl('theForm',$form);
	}

	public function savesiteAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		$data = $this->doQoolHook('pre_savesite_post_data',$data);
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.'config/config.xml');
			$xml->site->backend_title = $data['backend_title'];
			$xml->site->frontend_title = $data['frontend_title'];
			$xml->site->description = $data['description'];
			$xml->site->feed_copyright = $data['feed_copyright'];
			$xml->site->feed_generator = $data['feed_generator'];
			$xml->site->feed_author_name = $data['feed_author_name'];
			$xml->site->feed_author_email = $data['feed_author_email'];
			$xml->site->feed_logo_image = $data['feed_logo_image'];
			$xml->site->captcha_adapter = $data['captcha_adapter'];
			$xml->site->recaptcha_pub = $data['recaptcha_pub'];
			$xml->site->recaptcha_priv = $data['recaptcha_priv'];
			$xml->site->help = $data['help'];
			$xml->site->default = $data['default'];
			
			$xml = $this->doQoolHook('post_edit_site_xml_data',$xml);
		}
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Site Settings saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('site', 'index','admin');
	}

	public function cacheAction(){
		$this->addToBreadcrumb(array('cache',$this->t('Cache Settings')));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Cache Settings');
		$xml = readLangFile(APPL_PATH.'config/config.xml');

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savecache')->setMethod('post');



		$form->addElement($this->getFormElement(array('name'=>'cacheuser','value'=>'checkbox','title'=>$this->t('Cache Frontent')),$xml->cache->rules->cacheuser));
		$form->addElement($this->getFormElement(array('name'=>'cacheadmin','value'=>'checkbox','title'=>$this->t('Cache Backend')),$xml->cache->rules->cacheadmin));


		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$form = $this->doQoolHook('post_get_cachesettings_edit_form',$form);
		$this->toTpl('formTitle',$this->t("Cache Settings"));
		$this->toTpl('theForm',$form);
	}

	public function savecacheAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		$data = $this->doQoolHook('pre_savecache_post_data',$data);
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.'config/config.xml');
			$xml->cache->rules->cacheuser = $data['cacheuser'];
			$xml->cache->rules->cacheadmin = $data['cacheadmin'];
			$xml = $this->doQoolHook('post_edit_cache_xml_data',$xml);
		}
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Cache Settings saved"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('cache', 'index','admin');
	}

	public function themeAction(){
			$this->addToBreadcrumb(array('theme',$this->t('Theme Settings')));
			$this->tpl->assign('theInclude','themelist');
			Zend_Registry::set('module','Themes');
			$dirs = $this->dirs;
			//create the module menu
			$menu = array(
			'uploadtheme'	=>	'Upload new theme',
			'widgetslots'	=>	'Widgets'
			);
			$menu = $this->doQoolHook('themeaction_menu',$menu);
			$this->totpl('moduleMenu',$menu);
			$themes = readLangFile(APPL_PATH.'config/config.xml');
			$this->toTpl('default',$themes->template->frontend->title);
			$themes = $this->jsonArray($themes->template->frontend->available);

			$this->toTpl('theList',$themes);
	}

	public function themeinfoAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Theme Information');
		$dirs = $this->dirs;
		$addon = readLangFile(APPL_PATH.$dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$data['id'].DIR_SEP."template.xml");
		$html = "<h1>{$this->t($addon->title)}</h1>";
		$html .= "<div class='row'>";
		$html .= "<div class='span2'>";
		$html .= "{$addon->description}";
		$html .= "</div>";
		$html .= "<div class='span3 well pull-right'>";
		$html .= "{$this->t('Engine')}: <span class='badge pull-right badge-info'>{$addon->engine}</span><br>";
		$html .= "{$this->t('Version')}: <span class='badge pull-right badge-info'>{$addon->version}</span><br>";
		$html .= "{$this->t('Tags')}: <span class='badge pull-right badge-info'>{$addon->tags}</span><br>";
		$html .= "{$this->t('Created')}: <span class='badge pull-right badge-info'>{$addon->creationDate}</span><br>";
		$html .= "{$this->t('Licence')}: <span class='badge pull-right badge-info'>{$addon->licence}</span><br>";
		$html .= "{$this->t('Author')}: <a class='badge pull-right badge-info' href='{$addon->author->url}' target='_blank'>{$addon->author->name}</a><br>";
		$html .= "{$this->t('Template Url')}: <a class='badge pull-right badge-info' href='{$addon->author->template_url}' target='_blank'>{$this->t("Visit theme site")}</a><br>";
		$html .= "</div>";

		$html .= "</div>";
		if($data['ajaxcalled']){
			die($html);
		}
		$this->totpl('html',$html);
	}

	public function setdefaultthemeAction(){
		$this->tpl->assign('theInclude','themelist');
		Zend_Registry::set('module','Themes');

		$data = $this->_request->getParams();
		//here we need to open the xml file.. read and make the change
		$xml = $this->readConfigFile();
		$xml->template->frontend->title=$data['id'];
		$xml->template->frontend->engine=$data['engine'];
		$xml = $this->doQoolHook('post_set_default_theme_xml_data',$xml);
		$xml->asXML(APPL_PATH.'config/config.xml');
		$params = array("message"=>$this->t("Default theme changed"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('theme', 'index','admin');
	}

	public function themeconfigAction(){
		$data = $this->_request->getParams();
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Theme Settings');
		$dirs = $this->dirs;
		$xml = readLangFile(APPL_PATH.$dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$data['id'].DIR_SEP."template.xml");
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savethemeconfig')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('addon');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		foreach ($xml->settings->item as $k=>$v){
			$v = $this->jsonArray($v);
			$element = $this->getFormElement(array("name"=>$v['@attributes']['id'],"value"=>$v['type'],"title"=>$v['name']),$v['default_value']);
			if($v['type']=="selectbox"){
				foreach ($v['values']['value'] as $ko=>$vo){
					$element->addMultiOption($vo,$vo);
				}
				$element->setValue($v['default_value']);
			}
			$form->addElement($element);
			$i++;
		}

		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function savethemeconfigAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$xml = readLangFile(APPL_PATH.$dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$data['addon'].DIR_SEP."template.xml");

			foreach ($xml->settings->item as $k=>$v){
				$vo = $this->jsonArray($v);
				$v->default_value = $data[$vo['@attributes']['id']];
			}
			$xml->asXML(APPL_PATH.$dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$data['addon'].DIR_SEP."template.xml");
			$params = array("message"=>$this->t("Theme configuration saved"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('theme', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Could not save configuration for this theme"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('theme', 'index','admin');
		}
	}

	public function editthemeAction(){
		$this->totpl('theInclude','filelist');
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		$this->addToBreadcrumb(array('theme',$this->t('Theme Settings')));
		$this->addToBreadcrumb(array('edittheme',$this->t('Edit Theme'),$data['id']));
		$this->addToBreadcrumb($data['id']);
		Zend_Registry::set('module','Edit Theme');
		Zend_Registry::set('theaction','code');
		$list = $this->scanDir($dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$data['id'].DIR_SEP);
		$this->toTpl('theList',$list);
	}

	function buildEditorBreadCrumbs($data){
		$path = $data['id'];
		$path = explode("/",$path);
		$original_path = $path[0].'/'.$path[1].'/'.$path[2].'/';
		$this->addToBreadcrumb(array('theme',$this->t('Theme Settings')));
		$this->addToBreadcrumb(array('edittheme',$this->t('Edit Theme'),$path[2]));
		unset($path[0]);//remove templates/
		unset($path[1]);//remove frontend/
		unset($path[2]);//remove theme name

		$e = array();
		foreach ($path as $k=>$v){
			$t = explode(".",$v);
			if(count($t)>1){
				$this->addToBreadcrumb(array('editfilecode',$this->t('Edit Theme File'),$original_path.implode('/',$path)));
				$this->addToBreadcrumb($v);
			}else{
				$e[] = $v;
				$this->addToBreadcrumb(array('editfoldercode',$v,$original_path.implode("/",$e)));

			}
		}

	}

	public function editfilecodeAction(){
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Edit File');
		$data = $this->_request->getParams();
		$this->buildEditorBreadCrumbs($data);
		//get the syntax to use
		$this->toTpl('syntax',pathinfo($data['id'], PATHINFO_EXTENSION));
		//build the form
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savefilecode')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('addon');
		$addon->setValue(urldecode($data['id']));
		$form->addElement($addon);
		//check if we come from a previous save
		if($data['message']){
			$data['id'] = urldecode($data['id']);
		}
		$file = file($data['id']);
		$file = implode("",$file);
		$form->addElement($this->getFormElement(array("name"=>'editarea',"value"=>'editarea',"title"=>$this->t('Edit File').":".urldecode(end(explode("/",$data['id'])))),$file));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$this->toTpl('theForm',$form);
	}

	public function editfoldercodeAction(){
		$this->totpl('theInclude','filelist');
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		$data['id'] = str_replace(array("../",".."),"",$data['id']);
		$this->buildEditorBreadCrumbs($data);
		Zend_Registry::set('module','Edit Theme');
		Zend_Registry::set('theaction','code');
		$list = $this->scanDir("./".$data['id'].DIR_SEP);
		$this->toTpl('theList',$list);
	}

	public function savefilecodeAction(){
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			if($this->savefile($data['addon'],$data['editarea'])){
				$params = array("message"=>$this->t("File saved"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('editfilecode', 'index','admin',array('id'=>urlencode($data['addon'])));
			}
			$params = array("message"=>$this->t("Could not save file. Check file rights"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('editfilecode', 'index','admin',array('id'=>urlencode($data['addon'])));
		}
		$params = array("message"=>$this->t("Something went wrong"),"msgtype"=>'error');
		$this->addMessage($params);
		$this->_helper->redirector('editfilecode', 'index','admin',array('id'=>urlencode($data['addon'])));
	}

	public function uploadthemeAction(){
		$this->addToBreadcrumb(array('theme',$this->t('Theme Settings')));
		$this->addToBreadcrumb(array('uploadtheme',$this->t('Upload Theme')));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Upload new Theme');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/uploadthemefile')->setMethod('post');
		$upload = new Zend_Form_Element_File('themezip');
		$upload->setLabel($this->t("Choose a theme in .zip format"));
		$form->addElement($upload);
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setLabel($this->t("Upload"));
		$submit->setAttrib('class',"btn btn-primary");
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Upload a new theme"));
		$this->toTpl('theForm',$form);
	}

	public function uploadthemefileAction(){
		$dirs = $this->dirs;
		$form = new Zend_Form;
		$form->setView($this->tpl);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$upload = new Zend_File_Transfer_Adapter_Http();
				$upload->setDestination(APPL_PATH.$dirs['structure']['uploads'].DIR_SEP);
				try {
					$upload->receive();
					$file = $upload->getFileInfo();
					$filename = explode(".",$file['themezip']['name']);
					$filename = $filename[0];
					if($this->unzip($file['themezip'],APPL_PATH.$dirs['structure']['uploads'].DIR_SEP,APPL_PATH.$dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP,true)){
						if($this->addtheme($filename,readLangFile(APPL_PATH.$dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$filename.DIR_SEP."template.xml"))){
							$params = array("message"=>$this->t("Theme uploaded and added"),"msgtype"=>'success');
							$this->addMessage($params);
							$this->_helper->redirector('theme', 'index','admin');
						}else{
							$params = array("message"=>$this->t("Could not add theme"),"msgtype"=>'error');
							$this->addMessage($params);
							$this->_helper->redirector('theme', 'index','admin');
						}
					}else{
						$params = array("message"=>$this->t("Unknown Error"),"msgtype"=>'error');
						$this->addMessage($params);
						$this->_helper->redirector('theme', 'index','admin');
					}
				} catch (Zend_File_Transfer_Exception $e) {
					$params = array("message"=>$e->getMessage(),"msgtype"=>'error');
					$this->addMessage($params);
					$this->_helper->redirector('theme', 'index','admin');
				}
			}
		}
	}

	public function addtheme($file,$addon){
		try {
			$xml = readLangFile(APPL_PATH."config/config.xml");
			$node = $xml->template->frontend->available;
			$newnode = $node->addChild('template');

			$newnode->addChild('title', $addon->id);
			$newnode->addChild('engine',$addon->engine);
			$xml = $this->doQoolHook('post_addtheme_xml_data',$xml);
			$xml->asXML(APPL_PATH.'config/config.xml');
			return true;
		}catch (Exception $e){
			return false;
		}
	}

	public function widgetslotsAction(){
		$this->addToBreadcrumb(array('theme',$this->t('Theme Settings')));
		$this->addToBreadcrumb(array('widgetslots',$this->t('Widgets')));
		//the usual stuff
		$dirs = $this->dirs;
		$config = $this->config;
		$this->totpl('theInclude','widgets');
		Zend_Registry::set('module','Manage Widgets');
		//we need to get all installed widgets
		$xml = readLangFile(APPL_PATH."config/addons.xml");
		$widgets = $xml->widgets;
		$widgets = $this->jsonArray($widgets);
		foreach ($widgets['addon'] as $k=>$v){
			if($v['@attributes']['state']=='installed'){
				$widgetlist[] = $v['@attributes'];
			}
		}
		//we have the widgets... now lets assign them to the template
		$this->toTpl('widgetList',$widgetlist);

		//now we need to get all slots defined in the template chosen
		$xmltpl = readLangFile(APPL_PATH.$dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$config->template->frontend->title.DIR_SEP."template.xml");
		$slots = $xmltpl->slots;
		foreach ($slots->slot as $k=>$v){
			$vo = $this->jsonArray($v);

			$slotlist[] = array('id'=>$vo['@attributes']['name'],'title'=>$vo['@attributes']['title'],'widget'=>$vo[0]);

		}
		$slotlist = $this->doQoolHook('widgets_template_slotlist_creation',$slotlist);
		//d($widgetlist);
		//we have the slots for the template... we now need to get the slots for the addons templates too
		//lets do it
		$addons = $xml->applications;
		$addons =  $this->jsonArray($addons);
		foreach ($addons['addon'] as $k=>$v){
			if($v['@attributes']){
				$xmladdon = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$v['@attributes']['name'].DIR_SEP."addon.xml");
				$addon = $xmladdon->templates->slots;
				foreach ($addon->slot as $a=>$b){
					$b = $this->jsonArray($b);
					$slotlist[] = array('id'=>$v['@attributes']['name'].'-'.$b['@attributes']['name'],'title'=>$v['@attributes']['name'].' '.$b['@attributes']['title'],'widget'=>$b[0]);
				}
			}
		}
		$slotlist = $this->doQoolHook('widgets_addons_slotlist_creation',$slotlist);
		//we have the slots... now lets assign them to the template
		$this->toTpl('slotList',$slotlist);
	}











	public function taxonomiesAction(){

		$this->totpl('theInclude','taxonomies');
		$data = $this->_request->getParams();
		Zend_Registry::set('theaction','taxonomies');
		Zend_Registry::set('module','Manage Taxonomies');
		$menu = array(
		'newtaxonomy'	=>	'New Taxonomy',
		'newtaxtype'	=>	'Add Taxonomy Type'
		);
		$menu = $this->doQoolHook('taxonomies_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		if($data['id'] && $data['id']>0){
			$this->toTpl('previous_taxonomy',$this->getPreviousTaxonomies($data['id']));
		}

		$taxonomies = $this->getObjectTaxonomies((int) $data['id']);
		$this->toTpl('theList',$taxonomies);
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

	public function newtaxonomyAction(){
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','New Taxonomy');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addtaxonomy')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'taxonomy_type',"value"=>'selectbox',"title"=>$this->t("Taxonomy Type"),'use_pool'=>'getTaxonomyTypes')));
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"),'required'=>true)));
		$form->addElement($this->getFormElement(array("name"=>'parent',"value"=>'selectbox',"title"=>$this->t("Parent"),'use_pool'=>'getAllObjectTaxonomies','novalue'=>true)));

		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setLabel($this->t("Save"));
		$submit->setAttrib('class',"btn btn-primary");
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Add a new taxonomy"));
		$this->toTpl('theForm',$form);
	}

	public function addtaxonomyAction(){
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {

			if($data = $this->cleanPost($data)){
				$t = $this->getDbTables();
				$this->save($t['taxonomies'],$data);
				$params = array("message"=>$this->t("Taxonomy Added"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('taxonomies', 'index','admin');
			}else{
				$params = array("message"=>$this->t("Please fill in all fields"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('newtaxonomy', 'index','admin');
			}
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('newtaxonomy', 'index','admin');
		}
	}

	public function newtaxtypeAction(){
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','New Taxonomy Type');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addtaxtype')->setMethod('post');

		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"),'required'=>true)));


		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setLabel($this->t("Save"));
		$submit->setAttrib('class',"btn btn-primary");
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Add a new taxonomy type"));
		$this->toTpl('theForm',$form);
	}

	public function addtaxtypeAction(){
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {

			if($data = $this->cleanPost($data)){
				$t = $this->getDbTables();
				$this->save($t['taxonomy_types'],$data);
				$params = array("message"=>$this->t("Taxonomy Type Added"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('taxonomies', 'index','admin');
			}else{
				$params = array("message"=>$this->t("Please fill in all fields"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('newtaxtype', 'index','admin');
			}
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('newtaxtype', 'index','admin');
		}
	}

	public function deltaxonomiesAction(){
		$data = $this->_request->getParams();
		$t = $this->getDbTables();
		//first make all kids orphans
		$this->update($t['taxonomies'],array("parent"=>0),$data['id'],'parent');
		$this->delete($t['taxonomies'],$data['id']);
		$params = array("message"=>$this->t("Taxonomy Deleted"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('taxonomies', 'index','admin');
	}

	public function delcontentAction(){
		$data = $this->_request->getParams();
		$t = $this->getDbTables();
		//lets kill the object ;)
		$this->delete($t['objects'],$data['id']);
		//now lets remove the object data
		$this->delete($t['object_data'],$data['id'],'object_id');
		//dont forget to remove any taxonomy relation
		$this->delete($t['object_to_taxonomy'],$data['id'],'object_id');

		$params = array("message"=>$this->t("Object Deleted"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('itemlist', 'index','admin',array('id'=>$data['type_id']));
	}

	public function edittaxonomiesAction(){
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Edit Taxonomy');
		$data = $this->_request->getParams();
		$taxonomy = $this->getTaxonomy($data['id']);
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savetaxonomy')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'taxonomy_type',"value"=>'selectbox',"title"=>$this->t("Taxonomy Type"),'use_pool'=>'getTaxonomyTypes'),$taxonomy['taxonomy_type']));
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"),'required'=>true),$taxonomy['title']));
		$form->addElement($this->getFormElement(array("name"=>'parent',"value"=>'selectbox',"title"=>$this->t("Parent"),'use_pool'=>'getAllObjectTaxonomies','novalue'=>true,'noself'=>$taxonomy['id']),$taxonomy['parent']));
		$form->addElement($this->getFormElement(array("name"=>'id',"value"=>'hidden'),$data['id']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setLabel($this->t("Save"));
		$submit->setAttrib('class',"btn btn-primary");
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Edit taxonomy").": ".$taxonomy['title']);
		$this->toTpl('theForm',$form);

	}

	public function savetaxonomyAction(){
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {

			if($data = $this->cleanPost($data)){
				$t = $this->getDbTables();
				$this->update($t['taxonomies'],$data,$data['id']);
				$params = array("message"=>$this->t("Taxonomy Updated"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('taxonomies', 'index','admin');
			}else{
				$params = array("message"=>$this->t("Please fill in all fields"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('edittaxonomies', 'index','admin',array("id"=>$data['id']));
			}
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('edittaxonomies', 'index','admin',array("id"=>$data['id']));
		}
	}

	public function usersAction(){
		$this->addToBreadcrumb(array('users',$this->t('Users Administration')));
		$t = $this->getDbTables();
		Zend_Registry::set('theaction','user');
		$this->toTpl('theInclude','userlist');
		Zend_Registry::set('module','Users List');
		$menu = array(
		'newuser'	=>	'Add a new user',
		'usergroups'=>	'User Groups',
		'userfields'=>	'User Profile Fields'
		);
		$menu = $this->doQoolHook('users_admin_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		//get the users
		$sql = $this->selectAllPaged("SELECT {$t['users']}.*,{$t['user_groups']}.title FROM {$t['users']},{$t['user_groups']},{$t['user_to_groups']}
		WHERE {$t['users']}.id={$t['user_to_groups']}.uid AND {$t['user_to_groups']}.gid={$t['user_groups']}.id ORDER BY {$t['users']}.username ASC");
		$this->toTpl('theList',$sql);
	}

	public function edituserAction(){
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','User Settings');
		//get the user...
		$data = $this->_request->getParams();
		$t = $this->getDbTables();
		$u = $t['users'];
		$uid = $data['id'];
		$u2g = $t['user_to_groups'];
		$g = $t['user_groups'];
		$sql = "SELECT $u.username,$u.email,$g.level FROM $u,$g,$u2g WHERE $u.id=$uid AND $u2g.uid=$u.id AND $u2g.gid=$g.id";
		$u = $this->selectRow($sql);

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/saveuser')->setMethod('post');
		$addon = new Zend_Form_Element_Hidden('uid');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$form->addElement($this->getFormElement(array('name'=>'username','value'=>'textinput','title'=>$this->t('Username')),$u['username']));
		$form->addElement($this->getFormElement(array('name'=>'email','value'=>'textinput','title'=>$this->t('Email')),$u['email']));
		$form->addElement($this->getFormElement(array("name"=>'level','value'=>'selectbox','title'=>$this->t("Role"),'use_pool'=>'getUserGroupLevel'),$u['level']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function newuserAction(){
		$this->addToBreadcrumb(array('users',$this->t('Users Administration')));
		$this->addToBreadcrumb(array('newuser',$this->t('New User')));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','New User');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/adduser')->setMethod('post');

		$form->addElement($this->getFormElement(array('name'=>'username','value'=>'textinput','title'=>$this->t('Username'))));
		$form->addElement($this->getFormElement(array('name'=>'email','value'=>'textinput','title'=>$this->t('Email'))));
		$form->addElement($this->getFormElement(array('name'=>'password','value'=>'password','title'=>$this->t('Password'))));
		$form->addElement($this->getFormElement(array("name"=>'level','value'=>'selectbox','title'=>$this->t("Role"),'use_pool'=>'getUserGroupLevel')));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Add a new user"));
		$this->toTpl('theForm',$form);
	}

	public function saveuserAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			$data = $this->cleanPost($data);
			$t = $this->getDbTables();

			//first lets update the user group for this user
			$gid = $this->getUserGroupIdByLevel($data['level']);
			$this->update($t['user_to_groups'],array("gid"=>$gid),$data['uid'],'uid');
			//remove the keys not needed
			unset($data['level']);
			$uid = $data['uid'];
			unset($data['uid']);

			$this->update($t['users'],$data,$uid);
			$params = array("message"=>$this->t("User Updated"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('users', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('users', 'index','admin');
		}
	}

	public function adduserAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			$data = $this->cleanPost($data);
			$t = $this->getDbTables();
			//create the user
			$uid = $this->save($t['users'],array('username'=>$data['username'],'email'=>$data['email'],'password'=>md5($data['password'])));
			//and add the user to the group
			$gid = $this->getUserGroupIdByLevel($data['level']);
			$this->save($t['user_to_groups'],array('uid'=>$uid,'gid'=>$gid));
			$params = array("message"=>$this->t("User Created"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('users', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('newuser', 'index','admin');
		}
	}


	public function usergroupsAction(){
		$this->addToBreadcrumb(array('users',$this->t('Users Administration')));
		$this->addToBreadcrumb(array('usergroups',$this->t('User Groups')));
		$t = $this->getDbTables();
		Zend_Registry::set('theaction','usergroup');
		$this->toTpl('theInclude','groupslist');
		Zend_Registry::set('module','User Groups List');
		$menu = array(
		'newgroup'	=>	'Add a new user group'
		);
		$menu = $this->doQoolHook('usergroups_admin_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		$sql = "SELECT * FROM {$t['user_groups']} ORDER BY `level` ASC";
		$list = $this->selectAllPaged($sql);
		$this->toTpl('theList',$list);
	}

	public function menusAction(){
		$this->addToBreadcrumb(array('menus',$this->t('Menus')));
		$t = $this->getDbTables();
		Zend_Registry::set('theaction','menu');
		$this->toTpl('theInclude','menuslist');
		Zend_Registry::set('module','Menus List');
		$menu = array(
		'newmenu'	=>	'Add a new menu'
		);
		$menu = $this->doQoolHook('menus_admin_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		$sql = "SELECT * FROM {$t['menus']} ORDER BY `id` ASC";
		$list = $this->selectAllPaged($sql);
		$this->toTpl('theList',$list);
	}

	public function newmenuAction(){
		$this->addToBreadcrumb(array('menus',$this->t('Menus')));
		$this->addToBreadcrumb(array('newmenu',$this->t('New Menu')));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','New Menu');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addmenu')->setMethod('post');

		$form->addElement($this->getFormElement(array('name'=>'title','value'=>'textinput','title'=>$this->t('Title'))));
		$form->addElement($this->getFormElement(array("name"=>'taxonomy','value'=>'selectbox','title'=>$this->t("Taxonomy"),'use_pool'=>'getTaxonomyTypes','novalue'=>true)));

		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Add a new menu"));
		$this->toTpl('theForm',$form);
	}

	public function editmenuAction(){
		$data = $this->_request->getParams();
		$menu = $this->getMenu((int) $data['id']);
		$this->addToBreadcrumb(array('menus',$this->t('Menus')));
		$this->addToBreadcrumb(array('editmenu?id='.(int) $data['id'],$this->t('Edit Menu')));
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Edit Menu');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savemenu')->setMethod('post');

		$form->addElement($this->getFormElement(array('name'=>'id','value'=>'hidden'),$menu['id']));
		$form->addElement($this->getFormElement(array('name'=>'title','value'=>'textinput','title'=>$this->t('Title')),$menu['title']));
		$form->addElement($this->getFormElement(array("name"=>'taxonomy','value'=>'selectbox','title'=>$this->t("Taxonomy"),'use_pool'=>'getTaxonomyTypes','novalue'=>true),$menu['taxonomy']));

		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){
			echo $form;
			die();
		}

		$this->toTpl('html',$form);
	}

	public function newmenuitemAction(){
		$data = $this->_request->getParams();
		$this->addToBreadcrumb(array('menus',$this->t('Menus')));
		$this->addToBreadcrumb(array('newmenuitem?id='.(int) $data['id'],$this->t('New Menu Item')));

		$this->totpl('theInclude','form');
		Zend_Registry::set('module','New Menu Item');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addmenuitem')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'menu_id','value'=>'hidden'),(int) $data['id']));
		$form->addElement($this->getFormElement(array('name'=>'title','value'=>'textinput','title'=>$this->t('Title'))));
		$form->addElement($this->getFormElement(array('name'=>'link','value'=>'textinput','title'=>$this->t('Link'),'attributes'=>array('class'=>'span6','placeholder'=>$this->t('Use this to link to somewhere else than a content object. Leave blank if linking to an object')))));
		$form->addElement($this->getFormElement(array("name"=>'objectlink','value'=>'selectbox','title'=>$this->t("Content Link"),'use_pool'=>'getObjects','novalue'=>true)));
		$form->addElement($this->getFormElement(array('name'=>'link_title','value'=>'textinput','title'=>$this->t('Link Title Attribute'),'attributes'=>array('placeholder'=>$this->t('This is the title of the link when a user hovers a link'),'class'=>'span6'))));
		$form->addElement($this->getFormElement(array("name"=>'link_target',"value"=>'checkbox',"title"=>$this->t("New Window")),false));
		$form->addElement($this->getFormElement(array("name"=>'content',"value"=>'editor',"title"=>$this->t("Content"))));
		$form->addElement($this->getFormElement(array("name"=>'is_special',"value"=>'checkbox',"title"=>$this->t("Special Item")),false));
		$form->addElement($this->getFormElement(array("name"=>'special','value'=>'selectbox','title'=>$this->t("Special Functionality"),'use_pool'=>'getListings','novalue'=>true)));
		$form->addElement($this->getFormElement(array("name"=>'special_object','value'=>'selectbox','title'=>$this->t("Content Type"),'use_pool'=>'getContentTypes','novalue'=>true)));

		$form->addElement($this->getFormElement(array("name"=>'parent','value'=>'selectbox','title'=>$this->t("Parent"),'use_pool'=>'getMenuItems','pool_type'=>(int) $data['id'],'novalue'=>true)));


		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Add a new menu item"));
		$this->toTpl('theForm',$form);
	}

	public function editmenuitemAction(){
		$data = $this->_request->getParams();
		$this->addToBreadcrumb(array('menus',$this->t('Menus')));
		$this->addToBreadcrumb(array('editmenuitem?id='.(int) $data['id'],$this->t('Edit Menu Item')));
		$item = $this->getMenuItem((int) $data['id']);
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','Edit Menu Item');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/savemenuitem')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'id','value'=>'hidden'),(int) $item['id']));
		$form->addElement($this->getFormElement(array("name"=>'menu_id','value'=>'hidden'),(int) $item['menu_id']));
		$form->addElement($this->getFormElement(array('name'=>'title','value'=>'textinput','title'=>$this->t('Title')),$item['title']));
		$form->addElement($this->getFormElement(array('name'=>'link','value'=>'textinput','title'=>$this->t('Link'),'attributes'=>array('class'=>'span6','placeholder'=>$this->t('Use this to link to somewhere else than a content object. Leave blank if linking to an object'))),$item['link']));
		$form->addElement($this->getFormElement(array("name"=>'objectlink','value'=>'selectbox','title'=>$this->t("Content Link"),'use_pool'=>'getObjects','novalue'=>true),$item['objectlink']));
		$form->addElement($this->getFormElement(array('name'=>'link_title','value'=>'textinput','title'=>$this->t('Link Title Attribute'),'attributes'=>array('placeholder'=>$this->t('This is the title of the link when a user hovers a link'),'class'=>'span6')),$item['link_title']));
		$form->addElement($this->getFormElement(array("name"=>'link_target',"value"=>'checkbox',"title"=>$this->t("New Window")),$item['link_target']));
		$form->addElement($this->getFormElement(array("name"=>'content',"value"=>'editor',"title"=>$this->t("Content")),$item['content']));
		$form->addElement($this->getFormElement(array("name"=>'parent','value'=>'selectbox','title'=>$this->t("Parent"),'use_pool'=>'getMenuItems','pool_type'=>(int) $item['menu_id'],'novalue'=>true,'noself'=>$item['id']),$item['parent']));
		$form->addElement($this->getFormElement(array("name"=>'is_special',"value"=>'checkbox',"title"=>$this->t("Special Item")),$item['is_special']));
		$form->addElement($this->getFormElement(array("name"=>'special','value'=>'selectbox','title'=>$this->t("Special Functionality"),'use_pool'=>'getListings','novalue'=>true),$item['special']));
		$form->addElement($this->getFormElement(array("name"=>'special_object','value'=>'selectbox','title'=>$this->t("Content Type"),'use_pool'=>'getContentTypes','novalue'=>true),$item['special_object']));

		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Edit menu item").": ".$item['title']);
		$this->toTpl('theForm',$form);
	}

	public function addmenuitemAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			if(trim(($data['link'])!='' || $data['objectlink']>0) && trim($data['title'])!=''){
				unset($data['controller']);
				unset($data['module']);
				unset($data['action']);
				unset($data['save']);
				$t = $this->getDbTables();
				$this->save($t['menu_items'],$data);
				$params = array("message"=>$this->t("Menu Item Created"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('menu', 'index','admin',array("id"=>$data['menu_id']));
			}else{
				$params = array("message"=>$this->t("Please fill in all fields"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('newmenuitem', 'index','admin',array("id"=>(int) $data['menu_id']));
			}
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('menus', 'index','admin');
		}
	}

	public function savemenuitemAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			if(trim(($data['link'])!='' || $data['objectlink']>0) && trim($data['title'])!=''){
				unset($data['controller']);
				unset($data['module']);
				unset($data['action']);
				unset($data['save']);
				$t = $this->getDbTables();
				$this->update($t['menu_items'],$data,$data['id']);
				$params = array("message"=>$this->t("Menu Item Updated"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('menu', 'index','admin',array("id"=>$data['menu_id']));
			}else{
				$params = array("message"=>$this->t("Please fill in all fields"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('newmenuitem', 'index','admin',array("id"=>(int) $data['menu_id']));
			}
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('menus', 'index','admin');
		}
	}

	public function savemenuAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			if($datas = $this->cleanPost($data)){
				unset($data['controller']);
				unset($data['module']);
				unset($data['action']);
				unset($data['save']);
				$t = $this->getDbTables();
				$this->update($t['menus'],$data,$data['id']);
				$params = array("message"=>$this->t("Menu Updated"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('menus', 'index','admin');
			}else{
				$params = array("message"=>$this->t("Please fill in all fields"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('editmenu', 'index','admin',array("id"=>$data['id']));
			}
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('newmenu', 'index','admin',array("id"=>$data['id']));
		}
	}

	public function addmenuAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			if($data = $this->cleanPost($data)){
				$t = $this->getDbTables();
				$this->save($t['menus'],$data);
				$params = array("message"=>$this->t("Menu Created"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('menus', 'index','admin');
			}else{
				$params = array("message"=>$this->t("Please fill in all fields"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('newmenu', 'index','admin');
			}
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('newmenu', 'index','admin');
		}
	}

	public function menuAction(){
		$data = $this->_request->getParams();
		$this->addToBreadcrumb(array('menus',$this->t('Menus')));
		$this->addToBreadcrumb(array('menu?id='.(int) $data['id'],$this->t('Menu Items')));
		$t = $this->getDbTables();

		Zend_Registry::set('theaction','menuitem');
		$this->toTpl('theInclude','menuitemslist');
		Zend_Registry::set('module','Menu Items List');
		$menu = array(
		"newmenuitem?id={$data['id']}"	=>	'Add a new menu item'
		);
		$menu = $this->doQoolHook('menu_items_admin_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		//get the menu requested
		$req = $this->getMenu((int) $data['id']);
		//if the menu is a taxonomy...we need to load the taxonomies page
		if($req['taxonomy']>0){
			$params = array("message"=>$this->t("Menu is based on taxonomies"),"msgtype"=>'info');
			$this->addMessage($params);
			$this->_helper->redirector('taxonomies', 'index','admin');
		}else{
			$sql = "SELECT {$t['menu_items']}.*,{$t['menus']}.title as menu,{$t['menus']}.taxonomy FROM {$t['menu_items']},{$t['menus']}
		WHERE {$t['menu_items']}.menu_id={$t['menus']}.id AND {$t['menu_items']}.menu_id={$data['id']} AND {$t['menu_items']}.parent=0 ORDER BY {$t['menus']}.`id` ASC";
			$r = $this->selectAll($sql);

			//now we will loop through the items list and get all kids for each item
			foreach ($r as $k=>$v){
				$r[$k]['kids'] = $this->getMenuItemKids($v['id']);
			}
			$this->toTpl('theList',$r);
		}
	}



	public function editusergroupAction(){
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','User Group Settings');
		//get the user group
		$data = $this->_request->getParams();
		$u = $this->getUserGroupById($data['id']);
		if($u['id']==1 || $u['id']==4){
			if($data['ajaxcalled']){
				$this->t("Not allowed to edit this user group",1);
				die();
			}else{
				$this->totpl('html',$this->t("Not allowed to edit this user group"));
			}
		}else{
			$form = new Zend_Form;
			$form->setView($this->tpl);
			$form->setAttrib('class', 'form');
			$form->removeDecorator('dl');
			$form->setAction($this->config->host->folder.'/admin/saveusergroup')->setMethod('post');
			$addon = new Zend_Form_Element_Hidden('gid');
			$addon->setValue($data['id']);
			$form->addElement($addon);
			$form->addElement($this->getFormElement(array('name'=>'title','value'=>'textinput','title'=>$this->t('Title')),$u['title']));
			$form->addElement($this->getFormElement(array('name'=>'level','value'=>'textinput','title'=>$this->t('User Level')),$u['level']));
			$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
			$form->dummy->clearValidators();
			$submit = new Zend_Form_Element_Submit('save');
			$submit->setAttrib('class','btn btn-primary');
			$submit->setDecorators(array("ViewHelper"));
			$submit->setLabel($this->t("Save"));
			$form->addElement($submit);
			if($data['ajaxcalled']){
				echo $form;
				die();
			}
			$this->totpl('html',$form);
		}
	}

	public function saveusergroupAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			$data = $this->cleanPost($data);
			$t = $this->getDbTables();
			$gid = $data['gid'];
			unset($data['gid']);
			$this->update($t['user_groups'],$data,$gid);
			$params = array("message"=>$this->t("User Group Updated"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('usergroups', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('usergroups', 'index','admin');
		}
	}

	public function newgroupAction(){
		$this->addToBreadcrumb(array('users',$this->t('Users Administration')));
		$this->addToBreadcrumb(array('usergroups',$this->t('User Groups')));
		$this->addToBreadcrumb(array('newgroup',$this->t('New User Group')));
		$this->totpl('theInclude','form');
		Zend_Registry::set('module','New User Group');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addusergroup')->setMethod('post');

		$form->addElement($this->getFormElement(array('name'=>'title','value'=>'textinput','title'=>$this->t('Title'))));
		$form->addElement($this->getFormElement(array('name'=>'level','value'=>'textinput','title'=>$this->t('User Level'))));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Add a new user group"));
		$this->toTpl('theForm',$form);
	}

	public function addusergroupAction(){
		if ($this->_request->isPost()) {
			$data = $this->_request->getParams();
			$data = $this->cleanPost($data);
			$t = $this->getDbTables();
			//create the user
			$this->save($t['user_groups'],$data);
			//and add the user to the group

			$params = array("message"=>$this->t("User Group Created"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('usergroups', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('newgroup', 'index','admin');
		}
	}

	public function deluserAction(){
		$data = $this->_request->getParams();
		$t = $this->getDbTables();
		//we must NEVER kill a user... we just mark them as simple visitors ;)
		$gid = $this->getUserGroupIdByLevel(8000);
		$this->update($t['user_to_groups'],array("gid"=>$gid),$data['id'],'uid');
		$params = array("message"=>$this->t("User Demoted"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('users', 'index','admin');
	}

	public function delmenuAction(){
		$data = $this->_request->getParams();
		$t = $this->getDbTables();
		//remove the menu and any kids
		$this->delete($t['menus'],$data['id']);
		$this->delete($t['menu_items'],$data['id'],'menu_id');
		$params = array("message"=>$this->t("Menu has been deleted. All kids also removed"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('menus', 'index','admin');
	}

	public function delmenuitemAction(){
		$data = $this->_request->getParams();
		$t = $this->getDbTables();
		$this->delete($t['menu_items'],$data['id']);
		$params = array("message"=>$this->t("Menu item has been deleted"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('menu', 'index','admin',array('id'=>$data['menu_id']));
	}

	public function delusergroupAction(){
		$data = $this->_request->getParams();
		if($data['id']!=1 && $data['id']!=4){
			$t = $this->getDbTables();
			$this->delete($t['user_groups'],$data['id']);
			$params = array("message"=>$this->t("User Group Deleted"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('usergroups', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Not allowed to delete this user group"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('usergroups', 'index','admin');
		}
	}

	public function userfieldsAction(){
		$this->addToBreadcrumb(array('users',$this->t('Users Administration')));
		$this->addToBreadcrumb(array('userfields',$this->t('User Fields')));
		$t = $this->getDbTables();
		Zend_Registry::set('theaction','userfield');
		$this->toTpl('theInclude','userfieldslist');
		Zend_Registry::set('module','User Fields List');
		$menu = array(
		'newuserfield'	=>	'Add a new user field'
		);
		$menu = $this->doQoolHook('userfields_admin_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		//get the users
		$sql = "SELECT *,name as title FROM {$t['user_profile_fields']} ORDER BY `name` ASC";
		$list = $this->selectAllPaged($sql);
		$this->toTpl('theList',$list);
	}

	public function newuserfieldAction(){
		$this->addToBreadcrumb(array('users',$this->t('Users Administration')));
		$this->addToBreadcrumb(array('userfields',$this->t('User Fields')));
		$this->addToBreadcrumb(array('newuserfield',$this->t('Add User Field')));
		$t = $this->getDbTables();
		$this->toTpl('theInclude','form');
		Zend_Registry::set('module','Add User Profile Field');
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addnewuserfield')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'name',"value"=>'textinput',"title"=>$this->t("Title"))));
		$form->addElement($this->getFormElement(array("name"=>'field_type',"value"=>'selectbox',"title"=>$this->t("Field Type"),'use_pool'=>'getFieldTypes')));
		$form->addElement($this->getFormElement(array("name"=>'default_value',"value"=>'textinput',"title"=>$this->t("Default Values"),'attributes'=>array('placeholder'=>$this->t("Fill in a default value if needed. Seperate values with comma if on a select box."),'class'=>'input-xxlarge'))));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		$this->toTpl('formTitle',$this->t("Add a new user profile field"));
		$this->toTpl('theForm',$form);
	}

	public function addnewuserfieldAction(){
		$t = $this->getDbTables();
		$data = $this->_request->getParams();
		if($data = $this->cleanPost($data)){
			$this->save($t['user_profile_fields'],$data);
			$params = array("message"=>$this->t("User profile field added"),"msgtype"=>'success');
			$this->addMessage($params);
			$this->_helper->redirector('userfields', 'index','admin');
		}else{
			$params = array("message"=>$this->t("Please fill in all fields"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('newuserfield', 'index','admin');
		}
	}

	public function saveuserfieldAction(){
		$t = $this->getDbTables();
		$data = $this->_request->getParams();

		$this->update($t['user_profile_fields'],array('name'=>$data['name'],'field_type'=>$data['field_type'],'default_value'=>$data['default_value']),$data['fid']);
		$params = array("message"=>$this->t("User profile field updated"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('userfields', 'index','admin');

	}

	public function edituserfieldAction(){
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','User Field Settings');
		$data = $this->_request->getParams();
		//get the user group
		$u = $this->getUserField($data['id']);

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/saveuserfield')->setMethod('post');

		$addon = new Zend_Form_Element_Hidden('fid');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$form->addElement($this->getFormElement(array("name"=>'name',"value"=>'textinput',"title"=>$this->t("Title")),$u['name']));
		$form->addElement($this->getFormElement(array("name"=>'field_type',"value"=>'selectbox',"title"=>$this->t("Field Type"),'use_pool'=>'getFieldTypes'),$u['field_type']));
		$form->addElement($this->getFormElement(array("name"=>'default_value',"value"=>'textinput',"title"=>$this->t("Default Values"),'attributes'=>array('placeholder'=>$this->t("Fill in a default value if needed. Seperate values with comma if on a select box."),'class'=>'input span5')),$u['default_value']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);
	}

	public function newshortcutAction(){
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Add New Shortcut');
		$data = $this->_request->getParams();

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addgeneraldata')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"),'attributes'=>array('placeholder'=>$this->t('Shortcut Title')))));
		$form->addElement($this->getFormElement(array("name"=>'link',"value"=>'textinput',"title"=>$this->t("Link"),'attributes'=>array('placeholder'=>$this->t('Shortcut Link')))));
		$form->addElement($this->getFormElement(array("name"=>'icon',"value"=>'selectbox',"title"=>$this->t("Glyph Icon"),'use_pool'=>'getGlyphIcons','novalue'=>true)));
		$form->addElement($this->getFormElement(array("name"=>'target',"value"=>'checkbox',"title"=>$this->t("New Window")),true));
		$form->addElement($this->getFormElement(array("name"=>'data_type',"value"=>'hidden'),'shortcuts'));

		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){

			echo $form;
			echo "<i id='icon-preview' class='pull-right hide'> </a>";
			die();
		}
		$this->totpl('html',$form);
		die();
	}

	public function newtaskAction(){
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Add New Shortcut');
		$data = $this->_request->getParams();

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addgeneraldata')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"),'attributes'=>array('placeholder'=>$this->t('Task Title')))));
		$form->addElement($this->getFormElement(array("name"=>'task',"value"=>'textarea',"title"=>$this->t("Content"),'attributes'=>array('placeholder'=>$this->t('Task Contents'),'class'=>'span5'))));

		$form->addElement($this->getFormElement(array("name"=>'data_type',"value"=>'hidden'),'tasks'));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){

			echo $form;
			echo "<i id='icon-preview' class='pull-right hide'> </a>";
			die();
		}
		$this->totpl('html',$form);
		die();
	}

	public function addgeneraldataAction(){
		$data = $this->_request->getParams();
		$t = $this->getDbTables();
		$data = $this->cleanPost($data);
		$type = $data['data_type'];
		$data['username'] = $_SESSION['user']['username'];
		$this->save($t['general_data'],array('data_type'=>$data['data_type'],'data_value'=>serialize($data)));
		$params = array("message"=>$this->t("Item created"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('index', 'index','admin');
	}

	public function addwidgetdataAction(){
		$data = $this->_request->getParams();

		$t = $this->getDbTables();
		$data = $this->cleanPost($data);

		$type = $data['data_type'];
		$data['username'] = $_SESSION['user']['username'];
		$this->replace($t['general_data'],array('data_type'=>$data['data_type'],'data_value'=>serialize($data)),$data['data_type'],'data_type');
		$params = array("message"=>$this->t("Item created"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('widgetslots', 'index','admin');
	}

	public function deluserfieldAction(){
		$data = $this->_request->getParams();
		$t = $this->getDbTables();
		$this->delete($t['user_profile_fields'],$data['id']);
		$params = array("message"=>$this->t("User Profile Field Deleted"),"msgtype"=>'success');
		$this->addMessage($params);
		$this->_helper->redirector('userfields', 'index','admin');

	}

	function ajaxgeteditorimagesAction(){
		$images = $this->getImagesUploaded();

		$html = 'var tinyMCEImageList = new Array(';
		$i = count($images)-1;
		$o=0;
		foreach ($images as $k=>$v){
			if($o<$i){
				$html .= '["'.$k.'", "'.$v.'"],';
			}else{
				$html .= '["'.$k.'", "'.$v.'"]';
			}
			$o++;
		}

		$html .= ');';
		echo $html;
		die();
	}

	function ajaxgeteditorlinksAction(){
		$links = $this->getAllObjectSimple();
		$config = $this->config;
		$host = $config->host->http.$config->host->subdomain.$config->host->domain.$config->host->folder.DIR_SEP;
		$html = 'var tinyMCELinkList = new Array(';
		$i = count($links)-1;
		$o=0;
		foreach ($links as $k=>$v){
			$type = $this->getContentType($v['type_id']);
			if($o<$i){
				$html .= '["'.$v['title'].'", "'.$host.$type['lib'].DIR_SEP.$v['slug'].'"],';
			}else{
				$html .= '["'.$v['title'].'", "'.$host.$type['lib'].DIR_SEP.$v['slug'].'"]';
			}
			$o++;
		}

		$html .= ');';
		echo $html;
		die();
	}

	function ajaxgettemplatecssAction(){
		$config = $this->config;
		header('Content-Type: text/css');

		$dirs = $this->dirs;
		$xml = readLangFile(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.'frontend'.DIR_SEP.$config->template->frontend->title.DIR_SEP."template.xml");
		foreach ($xml->css->file as $k=>$v){
			$v = $this->jsonArray($v);
			$file = file(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.'frontend'.DIR_SEP.$config->template->frontend->title.DIR_SEP.$v[0]);
			$file = implode('',$file);
			echo $file;
		}
		echo "body{background:#fff !important;color:#000 !important;font-size:12px !important}";
		die();
	}

	public function thirdpartyAction(){
		$this->addToBreadcrumb(array('thirdparty',$this->t('Third Party Tools')));
		$this->totpl('theInclude','simplelist');
		Zend_Registry::set('module','Third party tools and libraries');
		$menu = array(
		'hooksdb'	=>	'Available Hooks'
		);
		$menu = $this->doQoolHook('thirdparty_menu',$menu);
		$this->totpl('moduleMenu',$menu);
		$list[] = array('title'=>'TinyMCE','link'=>'http://tinymce.moxiecode.com/');
		$list[] = array('title'=>'elFinder','link'=>'http://elfinder.org/');
		$list[] = array('title'=>'editArea','link'=>'http://sourceforge.net/projects/editarea/');
		$list[] = array('title'=>'Zend Framework','link'=>'http://www.zend.com');
		$list[] = array('title'=>'Twitter Bootstrap','link'=>'https://github.com/twitter/bootstrap/');
		$list[] = array('title'=>'jQuery','link'=>'http://www.jquery.com/');
		$list[] = array('title'=>'jQuery UI','link'=>'http://www.jqueryui.com/');
		$list[] = array('title'=>'Smarty Template Engine','link'=>'http://www.smarty.net/');
		$list[] = array('title'=>'Twig Template Engine','link'=>'http://twig.sensiolabs.org/');
		$list[] = array('title'=>'Savant3 Template Engine','link'=>'http://phpsavant.com/');
		$list = $this->doQoolHook('thirdparty_list',$list);
		$this->toTpl('theList',$list);
	}

	public function hooksdbAction(){
		$this->addToBreadcrumb(array('thirdparty',$this->t('Third Party Tools')));
		$this->addToBreadcrumb(array('hooksdb',$this->t('Qool Hooks Database')));

		$this->totpl('theInclude','simplelist');
		Zend_Registry::set('module','Qool Hooks Database');
		$hooks = readLangFile(APPL_PATH.'config/hooksdb.xml');
		foreach ($hooks->hook as $k=>$v){
			$list[] = array('title'=>$v,'link'=>'http://www.qool.gr/hooks/'.str_replace("_","-",$v));
		}
		$list = $this->doQoolHook('hooks_list',$list);
		$this->toTpl('theList',$list);
	}

	public function loadtextwidgetAction(){
		//check if a widget exists with this id

		$dirs = $this->dirs;
		$config = $this->config;
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Text Widget');
		$data = $this->_request->getParams();
		$widget = $this->getTextWidgetContents($data['textid']);
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addwidgetdata')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"),'attributes'=>array('placeholder'=>$this->t('Widget Title'))),$widget['title']));
		$form->addElement($this->getFormElement(array("name"=>'contents',"value"=>'textarea',"title"=>$this->t("Content"),'attributes'=>array('placeholder'=>$this->t('Widget Contents'),'class'=>'span5')),$widget['contents']));
		$form->addElement($this->getFormElement(array("name"=>'data_type',"value"=>'hidden'),$data['textid']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		echo $form;
		die();
	}

	public function loadmenuwidgetAction(){
		//check if a widget exists with this id

		$dirs = $this->dirs;
		$config = $this->config;
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Menu Widget');
		$data = $this->_request->getParams();
		$widget = $this->getTextWidgetContents($data['textid']);
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addwidgetdata')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"),'attributes'=>array('placeholder'=>$this->t('Widget Title'))),$widget['title']));
		$form->addElement($this->getFormElement(array("name"=>'menu','value'=>'selectbox','title'=>$this->t("Menu"),'use_pool'=>'getMenus','novalue'=>true)));
		$form->addElement($this->getFormElement(array("name"=>'data_type',"value"=>'hidden'),$data['textid']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		echo $form;
		die();
	}

	public function loadfeedwidgetAction(){
		//check if a widget exists with this id

		$dirs = $this->dirs;
		$config = $this->config;
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Feed Widget');
		$data = $this->_request->getParams();
		$widget = $this->getTextWidgetContents($data['textid']);
		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addwidgetdata')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'title',"value"=>'textinput',"title"=>$this->t("Title"),'attributes'=>array('placeholder'=>$this->t('Widget Title'))),$widget['title']));
		$form->addElement($this->getFormElement(array("name"=>'feed',"value"=>'textinput',"title"=>$this->t("Feed URL"),'attributes'=>array('placeholder'=>$this->t('The feed URL'))),$widget['feed']));
		$form->addElement($this->getFormElement(array("name"=>'data_type',"value"=>'hidden'),$data['textid']));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		echo $form;
		die();
	}

	public function searchAction(){
		Zend_Registry::set('theaction','content');
		$this->toTpl('theInclude','search');
		Zend_Registry::set('module','Search Results');
		//error_reporting(E_ALL);
		$data = $this->_request->getParams();
		$dirs = $this->dirs;
		$word = strtolower($data['q']);
		$index  = new Zend_Search_Lucene(APPL_PATH.$dirs['structure']['indexes'].DIR_SEP."objects");
		$exp = explode(" ",$word);
		$query = new Zend_Search_Lucene_Search_Query_Phrase($exp);
		$query->setSlop(2);
		//get all available indexed
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
		Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
		Zend_Search_Lucene::setResultSetLimit(10);
		$result=$index->find($query);
		foreach ($result as $hit){
			$cid = $this->getIdBySlug($hit->slug);
			$content = $this->getContent($hit->type_id,$cid);
			$content['id'] = $cid;
			$content['type_id'] = $hit->type_id;
			$content['type'] = $this->getContentType($hit->type_id);
			$resu[] = $content;
		}
		$this->toTpl('theList',$resu);
	}

	public function newfeedAction(){
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Add New Feed');
		$data = $this->_request->getParams();

		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/addgeneraldata')->setMethod('post');
		$form->addElement($this->getFormElement(array("name"=>'feed',"value"=>'textinput',"title"=>$this->t("Feed URL"),'attributes'=>array('placeholder'=>$this->t('Feed URL')))));

		$form->addElement($this->getFormElement(array("name"=>'data_type',"value"=>'hidden'),'userfeed'));
		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Save"));
		$form->addElement($submit);
		if($data['ajaxcalled']){

			echo $form;
			die();
		}
		$this->totpl('html',$form);
		die();
	}

	public function filemanagerAction(){
		$this->totpl('theInclude','filemanager');
		$this->addToBreadcrumb(array('filemanager',$this->t('File Manager')));
		Zend_Registry::set('module','Filemanager');
		$this->toTpl('elfinder',true);
	}

}
?>