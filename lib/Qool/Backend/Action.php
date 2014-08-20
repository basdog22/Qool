<?php
class Qool_Backend_Action extends Zend_Controller_Action{

	/**
	 * The Qool CMS config object
	 *
	 * @var object
	 */
	var $config = array();
	/**
	 * Existing applications
	 *
	 * @var array
	 */
	var $applications = array();
	/**
	 * Existing modules
	 *
	 * @var array
	 */
	var $modules = array();
	/**
	 * Existing widgets
	 *
	 * @var array
	 */
	var $widgets = array();
	/**
	 * The addons
	 *
	 * @var object
	 */
	var $addons = array();
	/**
	 * The language array
	 *
	 * @var array
	 */
	var $language = array();
	/**
	 * The database object
	 *
	 * @var object
	 */
	var $db = array();

	/**
	 * the cache switch
	 *
	 * @var boolean
	 */
	var $hasCache = true;

	/**
	 * the cache object
	 *
	 * @var object
	 */
	var $cache = array();

	/**
	 * the controller prefix. We need this for cache and more
	 *
	 * @var string
	 */
	var $prefix = 'Qool_Backend_';
	/**
	 * the controllers array. Just a quick hack
	 *
	 * @var array
	 */
	var $controllers = array();
	/**
	 * the directory structure
	 *
	 * @var array
	 */
	var $dirs = array();

	/**
	 * our template engine
	 *
	 * @var string
	 */
	var $tplEngine = 'php';

	/**
	 * the current template
	 *
	 * @var string
	 */
	var $theme = 'default';

	/**
	 * the template data
	 *
	 * @var array
	 */
	var $tpl = array();

	/**
	 * have editor buttons loaded yet?
	 *
	 * @var boolean
	 */
	var $editorBtnsLoaded = false;

	/**
	 * the hooks array
	 *
	 * @var array
	 */
	var $hooks = array();
	/**
	 * the current page
	 *
	 * @var int
	 */
	var $curPage = 0;

	/**
	 * the breadcrumbs array
	 *
	 * @var array
	 */
	var $breadcrumbs = array();


	/**
	 * The pager array
	 *
	 * @var array
	 */
	var $pager = array();

	/**
	 * The module level
	 *
	 * @var int
	 */
	var $level;

	/**
	 * the content types the module can handle
	 *
	 * @var array
	 */
	var $can_handle = array();

	/**
	 * the current addon settings
	 *
	 * @var array
	 */
	var $addonSettings = array();

	/**
	 * Initialize the Qool Backend
	 *
	 */
	public function init(){
		Zend_Registry::set('Qool_Module','backend');
		Zend_Registry::set('tplOverride','default');
		Zend_Registry::set('module','Dashboard');
		$this->level = 1;
		$data = $this->_request->getParams();

		//set some values needed by Qool
		$this->config = Zend_Registry::get('config');
		$this->applications = Zend_Registry::get('addons');
		$this->modules = Zend_Registry::get('modules');
		$this->widgets = Zend_Registry::get('widgets');
		$this->addons = Zend_Registry::get('controllers');
		$this->dirs = Zend_Registry::get('dirs');
		$this->language = $this->buildLanguage();
		$this->tplEngine = $this->config->template->backend->engine;
		$this->theme = $this->config->template->backend->title;
		//collect hooks
		$this->collectHooks();

		Zend_Registry::set('theme',$this->theme);

		$this->connectDB();

		$this->associateContent($data);
		//$this->addIndex();
		//$this->addIndex(array('title'=>'Banner'));
		//connect to database
		//$this->addToIndex($this->getContent(1,1),1);
		//$this->addToIndex($this->getContent(1,2),1);
		//$this->addToIndex($this->getContent(2,3),2);



		//set up our template engine
		$this->setupTemplate();
		$this->toTpl('qoolrequest',$data);
		//$this->requirePriviledges();
		//set up the cache
		$this->setupCache('QoolAdmin');
		Zend_Registry::set('tpl',$this->tpl);
		//get all actions that addons support
		$this->collectAddonCreationActions();
		$this->collectAddonMenuActions();
		$this->collectAvailableContent();
		$this->loadAdminMenus();
		//create shortcuts,tasks and other qool things ;)
		$this->gatherGeneralData();

		if($data['module']!='default'){
			$this->loadAddonSettings($data['module']);
		}

		if($_SESSION['message']){
			$data = $_SESSION['message'];
			$data = $this->doQoolHook('pre_assign_action_message',$data);
			$this->toTpl('message',array("message"=>$data['message'],"type"=>$data['msgtype']));
		}
	}

	/**
	 * Loads and registeres each addon settings
	 *
	 * @param object $addon
	 */
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

	/**
	 * get all content that can be shown by the module
	 *
	 * @param array $data
	 */
	function associateContent($data){
		//get all content that can be shown by the module
		$types = $this->getContentTypeByLib($data['module']);
		$this->can_handle = $types;
	}

	/**
	 * Returns the content type[s] by library
	 *
	 * @param string $lib
	 * @return array
	 */
	function getContentTypeByLib($lib){
		$t = $this->getDbTables();
		$id = $this->quote($lib);
		$sql = "SELECT * FROM {$t['content_types']} WHERE `lib`=$id";
		$r = $this->selectAll($sql);
		foreach ($r as $k=>$v){
			$r[$k]['ping'] = unserialize($r[$k]['ping']);
		}
		return $r;
	}

	/**
	 * Loads and assigns admin menus to the template object
	 *
	 */
	function loadAdminMenus(){
		$menus['content'] = array(
		'contentlist'	=>	'Content Types List',
		'datafields'	=>	'Data Fields',
		'taxonomies'	=>	'Taxonomies',
		'menus'			=>	'Menus',
		'gallery'		=>	'Image Gallery',
		'filemanager'	=>	'File Manager',
		'calendar'		=>	'Calendar'
		);

		$menus['system'] = array(
		'languagelist'	=>	'Languages',
		'addonslist'	=>	'Addons',
		'host'			=>	'Host Settings',
		'db'			=>	'Database Settings',
		'site'			=>	'Site Settings',
		'social'		=>	'Social Settings',
		'cache'			=>	'Cache Settings',
		'theme'			=>	'Layout Settings',
		'thirdparty'	=>	'Third Party Tools',
		'users'			=>	'Users Administration'
		);

		$menus = $this->doQoolHook('post_admin_menus_creation',$menus);
		$this->toTpl('adminmenus',$menus);
	}

	/**
	 * Returns the id of the content item based on it's slug name
	 *
	 * @param string $slug
	 * @return int
	 */
	function getIdBySlug($slug){
		$t = $this->getDbTables();
		$slug = $this->quote($slug);
		$sql = "SELECT `id` FROM {$t['objects']} WHERE `slug`=$slug";
		$sel = $this->selectRow($sql);
		return $sel['id'];
	}

	/**
	 * Creates the index. TODO: multiple indexes for content types
	 *
	 */
	function addIndex(){
		$dirs = $this->dirs;
		$this->createPath(APPL_PATH.$dirs['structure']['indexes'].DIR_SEP.'objects');
		$index = new Zend_Search_Lucene(APPL_PATH.$dirs['structure']['indexes'].DIR_SEP.'objects', true);
		//we are done..
		//we now have an index for this content type
	}

	/**
	 * Adds a content item to the index. 
	 *
	 * @param array $content
	 * @param int $typeid
	 */
	function addToIndex($content,$typeid){
		$dirs = $this->dirs;
		try{
			$index = Zend_Search_Lucene::open(APPL_PATH.$dirs['structure']['indexes'].DIR_SEP.'objects');
		}catch (Exception $e){
			$this->addIndex();
			$index = Zend_Search_Lucene::open(APPL_PATH.$dirs['structure']['indexes'].DIR_SEP.'objects');
		}
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
		$doc = new Zend_Search_Lucene_Document();

		foreach ($content as $k=>$v){
			if($k=='title'){
				$field = Zend_Search_Lucene_Field::UnStored($k,strtolower($v),'utf-8');
				$doc->addField($field);
			}elseif ($k=='slug'){
				$field = Zend_Search_Lucene_Field::unIndexed($k,strtolower($v),'utf-8');
				$doc->addField($field);
			}
		}
		$field = Zend_Search_Lucene_Field::unIndexed('type_id',$typeid);
		$doc->addField($field);
		$index->addDocument($doc);
		$index->commit();
	}

	/**
	 * Gathers general data from the database
	 *
	 */
	function gatherGeneralData(){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['general_data']}";
		$r = $this->selectAll($sql);
		foreach ($r as $k=>$v){
			$general[$v['data_type']][$v['id']] = unserialize($v['data_value']);
		}
		$general = $this->doQoolHook('post_gather_general_data',$general);

		$this->toTpl('general_data',$general);
	}

	/**
	 * Returns the content item specified
	 *
	 * @param mixed $type
	 * @param int $id
	 * @return array
	 */
	function getContent($type,$id){
		$t = $this->getDbTables();
		$d = $t['data'];
		$sql = "SELECT * FROM $d WHERE `group_id`=".(int)$type." ORDER BY `order` ASC";
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

		}

		return $content;
	}

	/**
	 * Recursive. Returns a multi dimensional array of all previous taxonomies from the one specified
	 *
	 * @param int $id
	 * @param array $array
	 * @return array
	 */
	function getPreviousTaxonomies($id,$array=false){
		$prev = $this->getTaxonomy($id);
		$array[] = $prev;
		if($prev['parent']==0){
			return array_reverse($array);
		}else{
			return $this->getPreviousTaxonomies($prev['parent'],$array);
		}
	}





	/**
	 * Returns a pager array based on number or results
	 *
	 * @param int $numResults
	 * @return array
	 */
	function paginate($numResults=0){

		$records = 20;

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

	/**
	 * Collects the addons menu actions to be used by the addon menus in the admin area
	 *
	 */
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
				$normal = array();
			}
		}

		$actions = $this->doQoolHook('post_collectaddon_menu_actions',$actions);
		$this->tpl->assign('addonMenuActions',$actions);
	}

	/**
	 * Loads tinyMCE editor buttons that are registered by addons
	 *
	 */
	function loadEditorBtns(){
		if(!$this->editorBtnsLoaded){
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
					if($addon->editor_buttons){
						$addonActions = $addon->editor_buttons->toArray();
						foreach ($addonActions as $ki=>$vi){
							$normal[] = $vi;
						}
						$actions = array_merge($actions,$normal);
					}
				}
			}

			$this->editorBtnsLoaded = true;
			$actions = $this->doQoolHook('pre_assign_editor_buttons',$actions);
			$this->tpl->assign('editorBtns',$actions);
		}
	}

	/**
	 * Returns the content item slug based on checks.
	 *
	 * @param array $data
	 * @param boolean $isUpdate
	 * @return string
	 */
	function getSlug($data,$isUpdate=false){
		//seek if a 'slug' field exists...
		if($data['slug']){
			$slug = $this->doQoolHook('valid_slug_assign',$slug);
			$slug = $data['slug'];
		}elseif($data['title']){
			$slug = $this->doQoolHook('pre_slugify_assign',$slug);
			$slug = $this->slugify($data['title']);
			$slug = $this->doQoolHook('post_slugify_assign',$slug);

		}else{
			$slug =  'content-'.time();
			$slug = $this->doQoolHook('post_auto_slug_assign',$slug);
		}
		//check if this slug exists and if it does rename the new one
		if($old = $this->isObject($slug) && !$isUpdate){
			$slug = $old."-2";
			$slug = $this->doQoolHook('post_slug_was_same_assign',$slug);
		}
		Zend_Registry::set('currentslug',$slug);
		if(!$slug){
			$slug = 'content-'.time();
		}
		return $slug;

	}

	/**
	 * Check if the slug is a content item
	 *
	 * @param string $slug
	 * @return boolean
	 */
	function isObject($slug){
		$t = $this->getDbTables();
		$slug = $this->quote($slug);
		$sql = "SELECT `slug` FROM {$t['objects']} WHERE `slug`=$slug";
		$object = $this->selectRow($sql);
		if($object['slug']){
			return $object['slug'];
		}
		return false;
	}

	/**
	 * Convert a string to a slug compatible string
	 *
	 * @param string $str
	 * @return string
	 */
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

	/**
	 * Returns all content types
	 *
	 * @return array
	 */
	function getContentTypes(){
		$t = $this->getDbTables();
		$sql = "SELECT id,title FROM {$t['content_types']}";
		return $this->selectAll($sql);
	}

	/**
	 * Returns all registered and hooked listings
	 *
	 * @return array
	 */
	function getListings(){
		$types[] = array(
		'id'=>'getRecent','title'=>'Recent Objects'
		);
		$types = $this->doQoolHook('post_listings_assign',$types);
		return $types;
	}

	/**
	 * Returns the content type array
	 *
	 * @param int $id
	 * @return array
	 */
	function getContentType($id){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['content_types']} WHERE id=$id";
		$r = $this->selectRow($sql);
		$r['ping'] = unserialize($r['ping']);
		return $r;
	}

	/**
	 * Return all registered and hooked mime types
	 *
	 * @return array
	 */
	function getMimeTypes(){
		$types[] = array(
		'id'=>'text/html','title'=>'text/html'
		);
		$types = $this->doQoolHook('post_mimetypes_assign',$types);
		return $types;
	}

	/**
	 * Returns all registered and hooked captcha adapters
	 *
	 * @return array
	 */
	function getCaptchaAdapters(){
		$types[] = array('id'=>'Dumb','title'=>'String to be typed reverse');
		$types[] = array('id'=>'Figlet','title'=>'Figlet');
		$types[] = array('id'=>'Image','title'=>'Image');
		$types[] = array('id'=>'ReCaptcha','title'=>'ReCaptcha');
		$types = $this->doQoolHook('pre_captcha_adapters_assign',$types);
		return $types;
	}

	/**
	 * Returns all registered and hooked field types
	 *
	 * @return array
	 */
	function getFieldTypes(){
		$types[] = array('id'=>'textinput','title'=>'Text Input');
		$types[] = array('id'=>'selectbox','title'=>'Select Box');
		$types[] = array('id'=>'editor','title'=>'Full Visual Editor');
		$types[] = array('id'=>'rte','title'=>'Simple Visual Editor');
		$types[] = array('id'=>'editarea','title'=>'Code Editor');
		$types[] = array('id'=>'datepicker','title'=>'Date Picker');
		$types[] = array('id'=>'checkbox','title'=>'Check Box');
		$types[] = array('id'=>'textarea','title'=>'Multi Line Text');
		$types[] = array('id'=>'radiobutton','title'=>'Radio Button');
		$types[] = array('id'=>'fileinput','title'=>'Upload file input');
		$types[] = array('id'=>'dropboxchooser','title'=>'DropBox File Chooser');
		$types[] = array('id'=>'passinput','title'=>'Password Input');
		$types[] = array('id'=>'treeselectbox','title'=>'Tree Select Box');
		$types[] = array('id'=>'multiselectbox','title'=>'Multi Select Box');
		$types[] = array('id'=>'multifileinputs','title'=>'10 File Inputs');
		$types[] = array('id'=>'multifileinput','title'=>'Multiple Files Input');
		$types[] = array('id'=>'captcha','title'=>'CAPTCHA');
		$types = $this->doQoolHook('post_fieldtypes_assign',$types);
		return $types;
	}

	/**
	 * Return all registered and hooked pools
	 *
	 * @return array
	 */
	function getPools(){

		$types[] = array('id'=>'getPools','title'=>'Available Pools');
		$types[] = array('id'=>'getFieldTypes','title'=>'Available Field Types');
		$types[] = array('id'=>'getContentTypes','title'=>'Available Content Types');
		$types[] = array('id'=>'getMimeTypes','title'=>'Available Mime Types');
		$types[] = array('id'=>'getLibraries','title'=>'Available Libraries');
		$types[] = array('id'=>'getHeaderTypes','title'=>'Header Types');
		$types[] = array('id'=>'getObjectTaxonomies','title'=>'Object Taxonomies');
		$types[] = array('id'=>'getTaxonomiesTree','title'=>'Taxonomies Tree');
		$types[] = array('id'=>'getTaxonomyTypes','title'=>'Taxonomies Types');
		$types[] = array('id'=>'getMenus','title'=>'Menus');
		$types[] = array('id'=>'getObjects','title'=>'Available Objects');
		$types[] = array('id'=>'getMenuItems','title'=>'Available Menu Items');
		$types[] = array('id'=>'getMenus','title'=>'Available Menus');
		$types = $this->doQoolHook('post_pools_assign',$types);
		return $types;
	}



	/**
	 * Returns all menu items for a menu. Uses a pre specified pool type
	 *
	 * @return array
	 */
	function getMenuItems(){
		$t = $this->getDbTables();
		$type = $this->quote($this->pool_type);
		$sql = "SELECT id,title FROM {$t['menu_items']} WHERE `menu_id`=$type ORDER BY `id` ASC";
		$list = $this->selectAll($sql);
		return $list;
	}

	/**
	 * Returns a menu item
	 *
	 * @param int $id
	 * @return array
	 */
	function getMenuItem($id){
		$t = $this->getDbTables();

		$sql = "SELECT * FROM {$t['menu_items']} WHERE `id`=$id";
		$list = $this->selectRow($sql);
		return $list;
	}

	/**
	 * Returns an array with all content objects
	 *
	 * @return array
	 */
	function getObjects(){
		$t = $this->getDbTables();
		$sql = "SELECT {$t['objects']}.id,CONCAT({$t['content_types']}.title,': ',{$t['object_data']}.value) as title
		FROM {$t['objects']},{$t['object_data']},{$t['content_types']} WHERE 
		{$t['objects']}.type_id={$t['content_types']}.id AND
		{$t['objects']}.id={$t['object_data']}.object_id AND
		{$t['object_data']}.name='title'
		ORDER BY {$t['objects']}.`datestr` DESC";
		$list = $this->selectAll($sql);
		return $list;
	}

	/**
	 * Return all menus
	 *
	 * @return array
	 */
	function getMenus(){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['menus']} ORDER BY `id` ASC";
		$list = $this->selectAll($sql);
		return $list;
	}

	/**
	 * Returns the menu array
	 *
	 * @param int $id
	 * @return array
	 */
	function getMenu($id){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['menus']} WHERE id=$id ORDER BY `id` ASC";
		$list = $this->selectRow($sql);
		return $list;
	}

	/**
	 * Returns a tree of taxonomies based on a pre assigned pool type
	 *
	 * @return array
	 */
	function getTaxonomiesTree(){
		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$p = $t['taxonomy_types'];
		$type = $this->quote($this->pool_type);

		//get all taxonomies and create a tree
		$tax = $this->getObjectTaxonomies(0,0);
		foreach ($tax as $k=>$v){
			//get all kids
			$tax[$k]['kids'] = $this->getTaxonomyAnchestors($v['id']);
		}

		return $tax;

	}

	/**
	 * Returns object taxonomies based on pre assigned pool types and modes
	 * if mode and !assigned pool type -> Return objects by type
	 * if !mode and assigned pool -> Return object taxonomies based on parent var and type
	 * else -> Return all taxonomies based on parent var
	 *
	 * @param int $parent
	 * @param boolean $mode
	 * @return array
	 */
	function getObjectTaxonomies($parent=0,$mode=true){

		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$p = $t['taxonomy_types'];
		if($this->pool_type!='' && $mode){
			$type = $this->quote($this->pool_type);

			$sql = "SELECT id,title FROM $d WHERE `taxonomy_type`=$type";
		}elseif ($this->pool_type!='' && !$mode){
			$type = $this->pool_type;

			$sql = "SELECT {$d}.*,{$p}.title as type_name FROM $d,$p WHERE {$d}.`parent`=$parent AND {$d}.taxonomy_type=$type AND {$d}.taxonomy_type={$p}.id ORDER BY `taxonomy_type`";
		}else{
			$parent = $this->quote($parent);
			$sql = "SELECT {$d}.*,{$p}.title as type_name FROM $d,$p WHERE {$d}.`parent`=$parent AND {$d}.taxonomy_type={$p}.id ORDER BY `taxonomy_type`";
		}
		return $this->selectAll($sql);
	}


	/**
	 * Returns all menu item kids. Recursive.
	 *
	 * @param int $id
	 * @param array $kids
	 * @return array
	 */
	function getMenuItemKids($id,$kids=array()){
		$tax = $this->getMenuItemKid($id);

		if(count($tax)>0){

			foreach ($tax as $k=>$v){
				$kids[$k] = $v;
				$kids[$k]['kids'] = $this->getMenuItemKids($v['id']);
			}

		}else{

		}
		return $kids;
	}

	/**
	 * Returns all menu items that are kids from another menu item specified by it's id
	 *
	 * @param int $id
	 * @return array
	 */
	function getMenuItemKid($id){
		$t = $this->getDbTables();
		$d = $t['menu_items'];
		$id = $this->quote($id);
		$type = $this->pool_type;
		$sql = "SELECT * FROM $d WHERE `parent`=$id";
		return $this->selectAll($sql);
	}

	/**
	 * Returns all taxonomy anchestors. Recursive
	 *
	 * @param int $id
	 * @param array $taxonomies
	 * @return array
	 */
	function getTaxonomyAnchestors($id,$taxonomies=array()){
		$tax = $this->getTaxonomyKid($id);

		if(count($tax)>0){

			foreach ($tax as $k=>$v){
				$taxonomies[$k] = $v;
				$taxonomies[$k]['kids'] = $this->getTaxonomyAnchestors($v['id']);
			}

		}else{

		}
		return $taxonomies;
	}

	/**
	 * Returns all kids of the specified taxonomy
	 *
	 * @param int $id
	 * @return array
	 */
	function getTaxonomyKid($id){
		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$id = $this->quote($id);
		$type = $this->pool_type;
		$sql = "SELECT * FROM $d WHERE `parent`=$id AND `taxonomy_type`=$type";
		return $this->selectAll($sql);
	}

	/**
	 * Returns the taxonomy array
	 *
	 * @param int $id
	 * @return array
	 */
	function getTaxonomy($id){
		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$id = $this->quote($id);
		$sql = "SELECT * FROM $d WHERE `id`=$id";
		return $this->selectRow($sql);
	}

	/**
	 * Retrieves all object taxonomies
	 *
	 * @return array
	 */
	function getAllObjectTaxonomies(){
		$t = $this->getDbTables();
		$d = $t['taxonomies'];
		$p = $t['taxonomy_types'];
		$sql = "SELECT {$d}.*,CONCAT({$d}.title,' (',{$p}.title,')') as title FROM $d,$p WHERE {$d}.taxonomy_type={$p}.id ORDER BY `taxonomy_type`";
		return $this->selectAll($sql);
	}

	/**
	 * Returns all taxonomy types
	 *
	 * @return array
	 */
	function getTaxonomyTypes(){
		$t = $this->getDbTables();
		$dg = $t['taxonomy_types'];
		$sql = "SELECT id,title FROM $dg";
		return $this->selectAll($sql);
	}

	/**
	 * Returns all registered and hooked libraries
	 *
	 * @return array
	 */
	function getLibraries(){
		$types[] = array(
		'id'=>'default','title'=>'Default'
		);
		$types = $this->doQoolHook('post_libraries_assign',$types);
		return $types;
	}

	/**
	 * Returns all registered and hooked header types
	 *
	 * @return array
	 */
	function getHeaderTypes(){
		$types[] = array(
		'id'=>'text/html','title'=>'text/html'
		);
		$types = $this->doQoolHook('post_headertypes_assign',$types);
		return $types;
	}

	/**
	 * Returns the addons node from the config xml as an array for use by the frontend
	 *
	 * @return array
	 */
	function getApplications(){
		$types[] = array(
		'id'=>'default','title'=>'Default'
		);
		foreach ($this->addons as $k=>$v){
			$types[] = array('id'=>$k,'title'=>ucfirst($k));
		}
		$types = $this->doQoolHook('post_applications_assign',$types);
		return $types;
	}

	/**
	 * Collects available content to be used by the template
	 *
	 */
	function collectAvailableContent(){
		$t = $this->getDbTables();
		$dg = $t['content_types'];
		$sql = "SELECT * FROM $dg";
		$sel = $this->selectAll($sql);
		$sel = $this->doQoolHook('post_collect_available_content',$sel);
		$this->tpl->assign('contentAvailable',$sel);
	}

	/**
	 * Cleans the post variable
	 *
	 * @param array $data
	 * @return array
	 */
	function cleanPost($data){
		unset($data['controller']);
		unset($data['module']);
		unset($data['action']);
		unset($data['save']);
		foreach ($data as $k=>$v){
			if(trim($v)==''){
				return false;
			}
		}
		$data = $this->doQoolHook('post_clean_post_data',$data);
		return $data;
	}

	/**
	 * Cleans the $_FILES array
	 *
	 */
	function cleanFiles(){
		$i=0;
		foreach ($_FILES as $k=>$v){
			foreach ($v as $o=>$f){
				foreach ($f as $d => $u){
					echo $u;
					if($o=='error'){
						if($u>0){
							unset($_FILES[$k][$o][$d]);
						}
					}else{
						if(!$u){
							unset($_FILES[$k][$o][$d]);
						}
					}
				}
			}
			$i=0;
		}
	}

	/**
	 * Saves a widget state
	 *
	 */
	public function savewidgetstateAction(){
		$dirs = $this->dirs;
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$config = $this->config;
			//check if the slot belongs to an addon
			$check = explode("-",$data['slotname']);
			if(count($check)>1){
				//the slot belongs to addon...
				$xml = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$check[0].DIR_SEP."addon.xml");
				$slots = $xml->templates->slots;
				$i = 0;
				foreach ($slots->slot as $k=>$v){
					$vo = $this->jsonArray($v);
					if($vo['@attributes']['name']==$check[1]){
						$xml->templates->slots->slot[$i] = $data['widgetname'];
					}
					$i++;
				}
				if($xml->asXML($dirs['structure']['addons'].DIR_SEP.$check[0].DIR_SEP."addon.xml")){
					echo $this->t("Widget position saved");
					die();
				}
				echo $this->t("Error!");
				die();
			}
			$xml = readLangFile($dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$config->template->frontend->title.DIR_SEP."template.xml");
			$slots = $xml->slots;
			$i = 0;
			foreach ($slots->slot as $k=>$v){
                
				$vo = $this->jsonArray($v);
				if($vo['@attributes']['name']==$data['slotname']){
					$xml->slots->slot[$i] = $data['widgetname'];
				}
				$i++;
			}
			if($xml->asXML($dirs['structure']['templates'].DIR_SEP."frontend".DIR_SEP.$config->template->frontend->title.DIR_SEP."template.xml")){
				echo $this->t("Widget position saved");
				die();
			}
			echo $this->t("Error!");
			die();
		}
		echo $this->t("Not valid request!");
		die();
	}

	/**
	 * Deletes the specified id from the table
	 *
	 */
	public function ajaxdeleteAction(){
		$t = $this->getDbTables();
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			if($t[$data['dbtable']]){
				$this->delete($t[$data['dbtable']],(int) $data['deleteId']);
				echo $this->t("Deleted with no errors");
				die();
			}
			echo $this->t("Error!");
			die();
		}
		echo $this->t("Not valid request!");
		die();
	}

	/**
	 * Updates the content item and adds or removes the taxonomy from it.
	 *
	 */
	public function ajaxtaxonomyupdateAction(){
		$t = $this->getDbTables();
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			//we remove all relations for the object that come from this field
			$this->removeTaxRelationWhereDatafield($data['myid'],$data['objectid']);
			//we now save the new taxonomies for this project

			foreach ($data['taxonomies'] as $v){
				$this->save($t['object_to_taxonomy'],array("object_id"=>$data['objectid'],"taxonomy_id"=>$v,'data_id'=>$data['myid']));
				$htm .= "Adding ".$v."to object ".$data['objectid']." for field ".$data['myid'];
			}
			//echo $htm;
			die();
		}
		echo $this->t("Not valid request!");
		die();
	}

	/**
	 * Removes any taxonomy relation for the specified field and object 
	 *
	 * @param int $fieldid
	 * @param int $objectid
	 */
	function removeTaxRelationWhereDatafield($fieldid,$objectid){
		$t = $this->getDbTables();
		$sql = "SELECT id FROM {$t['object_to_taxonomy']} WHERE data_id={$fieldid} AND object_id={$objectid}";
		$r = $this->selectAll($sql);
		foreach ($r as $k=>$v){
			$this->delete($t['object_to_taxonomy'],$v['id']);
		}
	}

	/**
	 * Collects addons creation options to be used by the create menu in the admin area
	 *
	 */
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
		$actions = $this->doQoolHook('post_collectaddon_creation_actions',$actions);
		$this->tpl->assign('addonCreationActions',$actions);
	}



	/**
	 * Returns the database object from the registry
	 *
	 * @return array
	 */
	function getDbTables(){
		return Zend_Registry::get('database');
	}

	/**
	 * Builds the language array to be used by template and core files
	 *
	 * @return unknown
	 */
	function buildLanguage(){
		$config = $this->config;
		Zend_Registry::set('currentlang',$config->languages->backend->language);
		//also set the language shortcode needed by some libs
		Zend_Registry::set('langcode',$config->languages->backend->shortname);
		$dirs = $this->dirs;

		//read the system language and the user language available
		$systemLang = readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$config->languages->backend->language.DIR_SEP.'language.xml');
		$userLang =  readLangFile(APPL_PATH.$dirs['structure']['languages'].DIR_SEP.$config->languages->backend->language.DIR_SEP.'user.xml');
		$language = buildLanguage($systemLang,$userLang);
		$language = $this->doQoolHook('pre_language_build',$language);
		Zend_Registry::set('language',$language);
		return $language;
	}

	/**
	 * Checks if the user has the rights to access the page. 
	 *
	 */
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

	/**
	 * Returns the id of the user group based on the user group level
	 *
	 * @param int $level
	 * @return int
	 */
	function getUserGroupIdByLevel($level){
		$t = $this->getDbTables();
		$sql = "SELECT id FROM {$t['user_groups']} WHERE `level`=$level";
		$r = $this->selectRow($sql);
		return $r['id'];
	}

	/**
	 * Returns a user group based on an id
	 *
	 * @param int $id
	 * @return array
	 */
	function getUserGroupById($id){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['user_groups']} WHERE `id`=$id";
		$r = $this->selectRow($sql);
		return $r;
	}

	/**
	 * Adds a message to the session so that it will be shown to the user
	 *
	 * @param array $data
	 */
	function addMessage($data){
		$_SESSION['message'] = $data;
	}

	/**
	 * Returns a user field
	 *
	 * @param int $id
	 * @return array
	 */
	function getUserField($id){
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['user_profile_fields']} WHERE `id`=$id";
		$r = $this->selectRow($sql);
		return $r;
	}


	/**
	 * Returns a user by his id
	 *
	 * @param int $id
	 * @return array
	 */
	function getUserById($id){
		$t = $this->getDbTables();
		$id = (int) $id;
		$sql = "SELECT * FROM {$t['users']} WHERE `id`=$id";

		return $this->selectRow($sql);
	}

	/**
	 * Unzips a zip archive to the specified location
	 *
	 * @param string $file
	 * @param string $source
	 * @param string $destination
	 * @param boolean $folderByName
	 * @return boolean
	 */
	function unzip($file,$source,$destination,$folderByName=false){
		$zip = new ZipArchive();
		if ($zip->open($source.$file['name']) !== TRUE) {
			$params = array("message"=>$this->t("Could not open file. Please verify it is a valid zip file"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('uploadlang', 'index','admin');
		}
		if($folderByName){
			$file = explode(".",$file['name']);
			$file = $file[0];
			$this->dirCheckCreate($destination.$file.DIR_SEP);
			$destination .=$file.DIR_SEP;
		}
		$zip->extractTo($destination);
		for($i = 0; $i < $zip->numFiles; $i++){
			$entry = $zip->getNameIndex($i);
			chmod($destination.$entry,0777);
		}
		$zip->close();
		return true;
	}

	/**
	 * Convert an object to an array
	 *
	 * @param object $ob
	 * @return array
	 */
	public function jsonArray($ob){
		$ob = json_encode($ob);
		$ob = json_decode($ob,1);
		return $ob;
	}

	/**
	 * Reads the config file and returns a simpleXMLElement object
	 *
	 * @return object
	 */
	function readConfigFile(){
		try {
			$dirs = $this->dirs;
			$file = file(APPL_PATH."config/config.xml");
			$file = implode("",$file);
			$xml = new SimpleXMLElement($file);
			return $xml;
		}catch (Exception $e){
			$this->toTpl('message',array("message"=>$e->getMessage(),"type"=>'error'));
		}
	}

	/**
	 * Connect to the database
	 *
	 */
	private function connectDB(){

		try {
			$dbconfig = array(
			'host'=>$this->config->database->host,
			'username'=>$this->config->database->username,
			'password' =>$this->config->database->password,
			'dbname'=>$this->config->database->db
			);
			$dbconfig = $this->doQoolHook('pre_connectdb',$dbconfig);
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
		$db = $this->doQoolHook('after_connectdb',$db);
		$this->db = $db;
	}

	/**
	 * Translate a string
	 *
	 * @param string $value
	 * @param boolean $echo
	 * @return string
	 */
	function t($value,$echo=false){
		$lang = $this->language;
		//a simple way to keep track of strings that need translation
		if(!$lang[$value]){
			keepTranslationStrings($value,$this->dirs);
		}else{
			cleanTranslationStrings($value,$this->dirs);
		}
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

	/**
	 * Open and save a file
	 *
	 * @param string $filepath
	 * @param string $data
	 * @return boolean
	 */
	function savefile($filepath,$data){
		try {
			$file = fopen($filepath,'w');
			fwrite($file,$data);
			fclose($file);
			return true;
		}catch (Exception $e){
			return false;
		}
	}

	/**
	 * Scans a directory and returns it's contents as an array
	 *
	 * @param string $dir
	 * @return array
	 */
	function scanDir($dir){

		$Dir = opendir($dir);

		while($files = readdir($Dir)){
			if($files!="." AND $files!=".." AND $files!='Thumbs.db'){
				if(is_dir($dir.$files)){
					$type = 'folder';
				}else{
					$type = 'file';
				}
				$contents[] = array('title'=>$files,'id'=>$dir.$files,'type'=>$type);

			}
		}
		closedir($Dir);

		return $contents;
	}

	/**
	 * Save data to the cache. All values passed to $name will be prefixed with $this->prefix.
	 * So if you want to save with a $name = mydata and the prefix is blog_, it will actually be saved as blog_mydata_SALT, where blog_ is the 
	 * addon prefix, mydata is your data and SALT is an md5 of mydata.
	 * 
	 * You only need to reference it only as mydata though.
	 *
	 * @param string $name
	 */
	public function cacheData($data,$name){
		if($this->hasCache){
			//lets do some prefixing...
			$cacheId = $this->prefix.$name."_".md5($name);
			//here we load data if they exist in the cache or we save data if not
			$this->cache->save($data,$cacheId);
		}
		return $data;
	}

	/**
	 * Loads a cached object
	 *
	 * @param string $name
	 * @return mixed
	 */
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

	/**
	 * Sets up the cache object for the specified controller
	 *
	 * @param string $controller
	 */
	public function setupCache($controller){
		//lets see if cache is on for backend
		if($this->config->cache->rules->cacheadmin==0){
			$this->hasCache = false;
			return ;
		}

		//ok the backend seems to be using caches
		//we will just set the cache for the backend here. It's addon's job to set cache for themselves.
		//this is why we go on one switch case
		switch ($controller){
			case "QoolAdmin":
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
		$dir = APPL_PATH.$this->dirs['structure']['cache'].DIR_SEP."backend".DIR_SEP.$controller;
		$this->dirCheckCreate($dir);

		$backendOptions = array(
		'cache_dir' => $dir.DIR_SEP // Directory where to put the cache files
		);
		$opts = $this->doQoolHook('pre_setupcache',array($frontendOptions,$backendOptions));
		$frontendOptions = $opts[0];
		$backendOptions = $opts[1];
		$this->cache = Zend_Cache::factory('Core','File',$frontendOptions,$backendOptions);
		$this->cache = $this->doQoolHook('post_setupcache',$this->cache);
	}

	/**
	 * Returns the id of the field specified by the params
	 *
	 * @param string $title
	 * @param int $type
	 * @return int
	 */
	function getDataField($title,$type){
		$title = $this->quote($title);
		$t = $this->getDbTables();
		$field = $this->selectRow("SELECT id FROM {$t['data']} WHERE `name`=$title AND `group_id`=$type");
		return $field['id'];
	}

	/**
	 * Sets up the template object
	 *
	 */
	private function setupTemplate(){
		$tpl = $this->tplEngine;
		$tpl = ucfirst($tpl);
		$tpl = $this->doQoolHook('pre_setuptemplate',$tpl);
		$class = "Templates_".$tpl."_".$tpl;
		Zend_Registry::set('customView',$class);
		$this->tpl = new $class();
		$this->tpl = $this->doQoolHook('post_setuptemplate',$this->tpl);
	}

	/**
	 * Assigns data to the template
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function toTpl($key,$value){
		$this->tpl->assign($key,$value);
	}

	/**
	 * Creates the path in the file system if it doesn't exist
	 *
	 * @param unknown_type $path
	 */
	function createPath($path){

		$path = explode("/",$path);
		foreach ($path as $a){
			$b .= $a."/";
			$this->dirCheckCreate($b);
		}
	}

	/**
	 * Checks if the folder exists and creates it if not
	 *
	 * @param string $dir
	 */
	function dirCheckCreate($dir){
		if(file_exists($dir)){
			return ;
		}
		//the dir does not exist... try to create it.
		mkdir($dir,0777);
		//also chmod this shit
		chmod($dir,0777);
		return ;
	}

	/**
	 * Quotes data to be used by the db layer
	 *
	 * @param mixed $val
	 * @return mixed
	 */
	function quote($val){
		return $this->db->quote($val);
	}
	/**
	 * Run a query and return a single row
	 *
	 * @param string $sql
	 * @return array
	 */
	function selectRow($sql){
		$this->debug['queries']++;
		$this->debug['actualQueries'][] = $sql;
		$c = $this->db->fetchRow($sql);
		return $c;
	}

	/**
	 * Runs a query and returns all rows
	 *
	 * @param string $sql
	 * @return array
	 */
	function selectAll($sql){
		$c = $this->db->fetchAll($sql);
		return $c;
	}

	/**
	 * Runs a query and limits results based on predefined data
	 *
	 * @param string $sql
	 * @return array
	 */
	function selectAllPaged($sql){
		//set the current page
		$data = $this->_request->getParams();
		if($data['page'] && $data['page']>1){
			$this->curPage = ((int) $data['page'])-1;
		}else{
			$this->curPage = 0;
		}
		$this->toTpl('curpage',$this->curPage);
		$from = $this->curPage*20;

		$p = $this->db->fetchAll($sql);
		$this->pager = $this->paginate(count($p));

		$this->toTpl('pager',$this->pager);

		$c = $this->db->fetchAll($sql." LIMIT {$from},20");
		return $c;
	}



	/**
	 * Deletes from database
	 *
	 * @param string $table
	 * @param int $id
	 * @param string $field
	 */
	function delete($table,$id,$field='id'){
		$this->db->delete($table,"$field=$id");
	}

	/**
	 * Updates a table row data
	 *
	 * @param string $table
	 * @param array $data
	 * @param int $id
	 * @param string $field
	 * @param string $extrasql
	 */
	function update($table,$data,$id,$field='id',$extrasql=''){
		//remove csrf
		unset($data['csrf']);
		$this->db->update($table,$data,"`{$field}`=".$id.$extrasql);
	}

	/**
	 * Inserts data to the database
	 *
	 * @param string $table
	 * @param array $data
	 * @return int
	 */
	function save($table,$data){
		//remove csrf
		unset($data['csrf']);
		$this->db->insert($table,$data);
		return $this->db->lastInsertId();
	}

	/**
	 * A way to have the mysql replace command with the Zend db adapter
	 *
	 * @param string $table
	 * @param array $data
	 * @param int $id
	 * @param string $field
	 */
	function replace($table,$data,$id,$field='id'){
		//remove csrf
		unset($data['csrf']);
		$this->delete($table,$this->quote($id),$field);
		$this->save($table,$data);
	}

	/**
	 * Displays an error message
	 *
	 * @param string $message
	 */
	public function triggerError($message){
		echo "Error: ".$message;
	}

	/**
	 * Insert content to the database
	 *
	 * @param array $data
	 * @return array
	 */
	function insertContent($data=false){
		$t = $this->getDbTables();
		if(!$data){
			$data = $this->_request->getParams();
		}

		//what type of content are we adding here?
		$type = (int) $data['contenttype'];
		//get all needed fields
		$sql = "SELECT * FROM {$t['data']} WHERE `group_id`=".$type." ORDER BY `order` ASC";
		$sel = $this->selectAll($sql);
		foreach ($sel as $k=>$v){
			if($v['is_taxonomy']){
				$taxes[$v['name']] = $data[$v['name']];
				$dataIds[$v['name']] = $v['id'];
			}else{
				if($v['value']=='multifileinput' || $v['value']=='multifileinputs' || $v['value']=='fileinput'){
					$files[$v['name']] = $_FILES[$v['name']];
				}else{
					if($v['name']!='slug'){
						$fields[$v['name']] = $data[$v['name']];
					}
				}
			}
		}


		//create a new object and get the id
		$objID = $this->save($t['objects'],array("slug"=>$this->getSlug($data),"datestr"=>time(),"type_id"=>$type));
		//upload files if any
		if($_FILES){
			//clean the files array so that we dont have a problem uploading
			$this->cleanFiles();
			//upload files to appropriate folder and insert the object data
			$thepath = $this->uploadGeneral('contentnew');
		}
		foreach ($files as $k=>$v){

			if(is_array($v['name'])){

				$i = 0;
				foreach ($v['name'] as $ko=>$vo){
					if($v['name'][$i]!=''){
						$this->save($t['object_data'],array("object_id"=>$objID,"name"=>$k,"value"=>$thepath.$v['name'][$i]));
					}
					$i++;
				}
			}else{

				$this->save($t['object_data'],array("object_id"=>$objID,"name"=>$k,"value"=>$thepath.$v['name']));
			}
		}


		//save the taxonomy relation if any
		foreach ($taxes as $k=>$v){
			if(is_array($v)){
				foreach ($v as $ko=>$vo){
					$this->save($t['object_to_taxonomy'],array("object_id"=>$objID,"taxonomy_id"=>$vo,'data_id'=>$dataIds[$k]));
				}
			}else{
				if($v!=''){
					$this->save($t['object_to_taxonomy'],array("object_id"=>$objID,"taxonomy_id"=>$v,'data_id'=>$dataIds[$k]));
				}
			}
		}

		//save the object data
		foreach ($fields as $k=>$v){
			if(trim($v)){
				$v = $this->doQoolHook('insert_'.$k.'_object_data',$v);
				$this->save($t['object_data'],array("object_id"=>$objID,"name"=>$k,"value"=>$v));
			}
		}
		//we also need to run any pings needed by this type
		$this->pingServices($type);
		//we now have to get the content and index it...
		$content = $this->getContent($data['contenttype'],$objID);
		$this->addToIndex($content,$data['contenttype']);
		$this->doQoolHook('content_created',Zend_Registry::get('currentslug'));
		return array($objID,$type);
	}

	/**
	 * Runs any ping services assigned to the content type
	 *
	 * @param string $type
	 */
	function pingServices($type){

		$this->doQoolHook('before_ping_services',Zend_Registry::get('currentslug'));
		//get content type
		$type = $this->getContentType($type);
		$data = array();
		$data['content_type'] = $type['title'];
		$data['slug'] = Zend_Registry::get('currentslug');
		$data['title'] = $this->config->site->frontend_title;
		foreach ($type['ping'] as $k=>$v){
			if(method_exists($this,$v)){
				$this->$v($data);
			}
		}
		$this->doQoolHook('after_ping_services',Zend_Registry::get('currentslug'));
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

	/**
	 * Updates a content item
	 *
	 * @param array $data
	 * @return array
	 */
	function updateContent($data=false){
		$t = $this->getDbTables();
		if(!$data){
			$data = $this->_request->getParams();
		}
		//what type of content are we adding here?
		$type = (int) $data['contenttype'];
		//get all needed fields
		$sql = "SELECT * FROM {$t['data']} WHERE `group_id`=".$type." ORDER BY `order` ASC";
		$sel = $this->selectAll($sql);
		foreach ($sel as $k=>$v){
			//we dont want to get tags as they are updated on the fly... we need only single selects ;)
			if($v['is_taxonomy']){
				if($v['value']!='multiselectbox'){
					$taxes[$v['id']] = $data[$v['name']];
				}
			}else{
				if($v['value']=='multifileinput' || $v['value']=='multifileinputs' || $v['value']=='fileinput'){
					$files[$v['name']] = $_FILES[$v['name']];
				}else{
					if($v['name']!='slug'){
						$fields[$v['id']] = $data[$v['name']];
						$q[$v['id']] = $v;
					}
				}
			}
		}

		//create a new object and get the id
		$this->update($t['objects'],array("slug"=>$this->getSlug($data,true),"datestr"=>time(),"type_id"=>$type),(int) $data['contentid']);
		//save the taxonomy relation if any
		foreach ($taxes as $k=>$v){
			if($v!=''){
				//$this->save($t['object_to_taxonomy'],array("object_id"=>$objID,"taxonomy_id"=>$v));
				$this->update($t['object_to_taxonomy'],array("object_id"=>(int) $data['contentid'],"taxonomy_id"=>$v),(int) $data['contentid'],'object_id'," AND data_id={$k}");
			}
		}
		if($_FILES){
			//clean the files array so that we dont have a problem uploading
			$this->cleanFiles();
			//upload files to appropriate folder and insert the object data
			$thepath = $this->uploadGeneral('editcontent',$data['contentid']);
		}
		foreach ($files as $k=>$v){
			if(is_array($v['name'])){
				$i = 0;
				foreach ($v['name'] as $ko=>$vo){
					if($v['name'][$i]!=''){
						$this->save($t['object_data'],array("object_id"=>(int) $data['contentid'],"name"=>$k,"value"=>$thepath.$v['name'][$i]));
					}
					$i++;
				}
			}else{
				$this->save($t['object_data'],array("object_id"=>(int) $data['contentid'],"name"=>$k,"value"=>$thepath.$v['name']));
			}
		}
		//save the object data

		foreach ($fields as $k=>$v){
			$v = $this->doQoolHook('update_'.$q[$k]['name'].'_object_data',$v);
			$this->update($t['object_data'],array("object_id"=>(int) $data['contentid'],"value"=>$v),$data['contentid'],'object_id'," AND `name`=".$this->quote($q[$k]['name']));
		}
		$this->doQoolHook('content_updated',Zend_Registry::get('currentslug'));
		return array($data['contentid'],$type);
	}

	/**
	 * Accepts a taxonomy name and a taxonomy type and if not exists it creates it and returns it's id. 
	 *
	 * @param string $tax
	 * @param string $type
	 * @return int
	 */
	function maybeCreateTaxonomy($tax,$type){
		$tax = trim($tax);
		if($tax){

			$t = $this->getDbTables();
			$taxclean = $this->quote($tax);

			//check if a taxonomy with the same name and type exists.

			$taxonomy = $this->selectRow("SELECT id FROM {$t['taxonomies']} WHERE `title`={$taxclean} AND `taxonomy_type`=$type");
			if($taxonomy['id']){
				return $taxonomy['id'];
			}
			//no taxonomy. insert it
			return $this->save($t['taxonomies'],array("title"=>$tax,'taxonomy_type'=>$type,'parent'=>0));
		}
		return false;
	}

	/**
	 * Accepts an array with parameters and mixed values and returns a Zend Form Element to be used by a Zend Form object
	 *
	 * @param array $v
	 * @param mixed $value
	 * @return object
	 */
	function getFormElement($v,$value=''){
		$config = $this->config;
		$this->toTpl('hasForm',1);
		$v = $this->doQoolHook('pre_getformelement_element',$v);
		$value = $this->doQoolHook('pre_getformelement_value',$value);
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
			case "rte":
				$element = new Zend_Form_Element_Textarea($v['name']);
				$element->setAttrib('class','cleditor span12');
				$this->toTpl("isRTE",1);
				if($value!=''){
					$element->setValue($value);
				}
				break;

			case "fileinput":
				$element = new Zend_Form_Element_File($v['name']);
				$element->setAttrib('class','input-file');
				break;
			case "dropboxchooser":
				$element = new Zend_Form_Element_Dropbox($v['name']);
				$element->setAttrib('style','visibility:hidden');
				$element->setAttrib('data-multiselect',true);
				break;
			case "captcha":
				if($config->site->captcha_adapter=='ReCaptcha'){
					//Do whats needed for recaptcha to work with form

				}
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
			case "datepicker":
				$element = new Zend_Form_Element_Text($v['name']);
				$element->setAttrib('class','input-xlarge datepicker');
				if($value!=''){
					$element->setValue($value);
				}
				break;
			case "imageselect":
				$element = new Zend_Form_Element_Text($v['name']);
				$element->setAttrib('class','imageselector');
				$this->toTpl("hiddenEditor",1);
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
				}elseif(is_array($v['use_pool'])){
					if($v['novalue']){
						$element->addMultiOption(0,$this->t('No Selection'));
					}
					foreach ($v['use_pool'] as $ko=>$vo){
						if($v['noself'] && $vo['id']==$v['noself']){

						}else{
							$element->addMultiOption($vo['id'],$vo['title']);
						}
					}
					if($value!=''){
						$element->setValue($value);
					}
				}elseif($v['use_pool']){
					//the pools might have been assigned by an addon
					//include the file and run it.
					require_once($this->dirs['structure']['addons'].DIR_SEP.Zend_Registry::get('controller').DIR_SEP."func.php");

					if($v['novalue']){
						$element->addMultiOption(0,$this->t('No Selection'));
					}
					foreach ($v['use_pool']($this) as $ko=>$vo){
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
				$element->setAttrib('data-rel','chosen');
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
				}else{
					//the pools might have been assigned by an addon
					//include the file and run it.
					require_once($this->dirs['structure']['addons'].DIR_SEP.Zend_Registry::get('controller').DIR_SEP."func.php");
					if($v['novalue']){
						$element->addMultiOption(0,$this->t('No Selection'));
					}

					foreach ($v['use_pool']($this) as $ko=>$vo){
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
		$element = $this->doQoolHook('post_getformelement_object',$element);
		return $element;
	}

	/**
	 * Returns all registered and hooked host protocols
	 *
	 * @return array
	 */
	private function getHostProtocols(){
		$protocols[] = array('id'=>'http://','title'=>'HTTP');
		$protocols[] = array('id'=>'https://','title'=>'HTTPS');
		$protocols = $this->doQoolHook('post_get_host_protocols',$protocols);
		return $protocols;
	}

	/**
	 * Returns all registered and hooked supported databases
	 *
	 * @return array
	 */
	private function getSupportedDbs(){
		$protocols[] = array('id'=>'mysql','title'=>'MySQL');
		$protocols[] = array('id'=>'sqlite','title'=>'SQLite');
		$protocols = $this->doQoolHook('post_get_supported_dbs',$protocols);
		return $protocols;
	}

	/**
	 * Returns the user groups with their level
	 *
	 * @return array
	 */
	function getUserGroupLevel(){

		$t = $this->getDbTables();
		$d = $t['user_groups'];
		$sql = "SELECT level as id,title FROM $d";

		return $this->selectAll($sql);
	}

	/**
	 * Creates a form for email sending and assigns it to the template or displays it
	 *
	 */
	public function mailtoAction(){
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Mailto User');
		$data = $this->_request->getParams();


		$form = new Zend_Form;
		$form->setView($this->tpl);
		$form->setAttrib('class', 'form');
		$form->removeDecorator('dl');
		$form->setAction($this->config->host->folder.'/admin/mailtouser')->setMethod('post');

		$addon = new Zend_Form_Element_Hidden('fid');
		$addon->setValue($data['id']);
		$form->addElement($addon);
		$form->addElement($this->getFormElement(array("name"=>'tomail',"value"=>'textinput',"title"=>$this->t("To mail")),$data['mail']));
		$form->addElement($this->getFormElement(array("name"=>'cc',"value"=>'textinput',"title"=>$this->t("MailCC"))));
		$form->addElement($this->getFormElement(array("name"=>'subject',"value"=>'textinput',"title"=>$this->t("Subject"))));
		$form->addElement($this->getFormElement(array("name"=>'message',"value"=>'editor',"title"=>$this->t("Message"),'attributes'=>array('class'=>'editor span5','rows'=>8))));

		$form->addElement('hidden','dummy',array('required' => false,'ignore' => true,'autoInsertNotEmptyValidator' => false,'decorators' => array(array('HtmlTag', array('tag'  => 'hr','id'   => 'wmd-button-bar','class' => 'divider')))));
		$form->dummy->clearValidators();
		$submit = new Zend_Form_Element_Submit('save');
		$submit->setAttrib('class','btn btn-primary');
		$submit->setDecorators(array("ViewHelper"));
		$submit->setLabel($this->t("Send"));
		$form->addElement($submit);
		if($data['ajaxcalled']){
			echo $form;
			die();
		}
		$this->totpl('html',$form);

	}

	/**
	 * Returns a user based on an email
	 *
	 * @param string $email
	 * @return array
	 */
	function getUserByEmail($email){
		$t = $this->getDbTables();
		$email = $this->quote($email);
		$sql = "SELECT * FROM {$t['users']} WHERE `email`=$email";
		return $this->selectRow($sql);
	}

	/**
	 * Sends an email to a user
	 *
	 */
	public function mailtouserAction(){
		if ($this->_request->isPost()) {
			try{
				$config = $this->config;
				$user = $this->getUserByEmail($data['tomail']);
				$data = $this->_request->getParams();
				$mail = new Zend_Mail('UTF-8');
				$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
				$mail->setBodyHtml($data['message']);
				$mail->setFrom('admin@'.$config->host->domain);
				$mail->addTo($data['tomail'], $user['username']);
				$mail->setSubject($data['subject']);
				//see if cc
				if(trim($data['cc'])){
					$mail->addCc($data['cc']);
				}
				$mail->send();
				$params = array("message"=>$this->t("Mail Sent"),"msgtype"=>'success');
				$this->addMessage($params);
				$this->_helper->redirector('users', 'index','admin');
			}catch (Exception $e){
				$params = array("message"=>$this->t("Something went wrong"),"msgtype"=>'error');
				$this->addMessage($params);
				$this->_helper->redirector('users', 'index','admin');
			}
		}else{
			$params = array("message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->addMessage($params);
			$this->_helper->redirector('users', 'index','admin');
		}
	}

	/**
	 * Keeps a hooks log
	 *
	 * @param string $hook
	 */
	function keepHooksLog($hook){
		//only available during development

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

	/**
	 * Executes a hook. Accepts hook name, and optional data
	 *
	 * @param string $a
	 * @param mixed $data
	 * @return mixed
	 */
	function doQoolHook($a,$data=false) {
		$this->keepHooksLog($a);
		$hooks = $this->getRegisteredHooks();
		foreach ($hooks as $hooki){
			foreach ($hooki as $i=>$hook)	{

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
				return $data;
			}
			return $data;
		}
		return $data;
	}

	/**
	 * Collects hooks and registers them for use by the system
	 *
	 */
	function collectHooks(){
		$addons = $this->addons;
		$apps = $this->applications->toArray();
		$dirs = $this->dirs;
		$actions = array();
		//get the addons hooks first
		$hooks = array();
		foreach ($addons as $k=>$v){
			if($levels[$k]<=$this->level){
				$addon = readLangFile(APPL_PATH.$dirs['structure']['addons'].DIR_SEP.$k.DIR_SEP.'addon.xml');
				$addon = $this->jsonArray($addon);
				$hooks = $addon['actions']['backend']['hooks'];
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

		//now get the module hooks
		$addons = $this->modules;

		$ahooks = array();
		foreach ($addons as $k=>$v){

			$addon = readLangFile(APPL_PATH.$dirs['structure']['modules'].DIR_SEP.$k.DIR_SEP.'addon.xml');
			$addon = $this->jsonArray($addon);
			$ahooks = $addon['actions']['backend']['hooks'];
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

		//now get the widgets hooks
		$addons = $this->widgets;
		$bhooks = array();
		foreach ($addons as $k=>$v){

			$addon = readLangFile(APPL_PATH.$dirs['structure']['widgets'].DIR_SEP.$k.DIR_SEP.'addon.xml');
			$addon = $this->jsonArray($addon);
			$bhooks = $addon['actions']['backend']['hooks'];
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

		$this->registerHooks($actions);
	}

	/**
	 * Registeres hooks
	 *
	 * @param array $hooks
	 */
	function registerHooks($hooks){

		$this->hooks = $hooks;
	}

	/**
	 * Returns all registered hooks
	 *
	 * @return array
	 */
	function getRegisteredHooks(){
		return $this->hooks;
	}

	/**
	 * Returns Twitter Bootstrap Glyphicons class names to be used for display
	 *
	 * @return array
	 */
	function getGlyphIcons(){
		$dirs = $this->dirs;
		$file = file(APPL_PATH.$dirs['structure']['lib'].DIR_SEP."css".DIR_SEP."icons.txt");
		foreach ($file as $ico){
			$types[] = array('id'=>$ico,'title'=>$ico);
		}
		return $types;
	}

	/**
	 * Returns an array with all images uploaded to the system
	 *
	 * @return array
	 */
	function getImagesUploaded(){
		$dirs = $this->dirs;
		$config = $this->config;
		$host = $config->host->http.$config->host->subdomain.$config->host->domain.$config->host->folder.DIR_SEP;
		$uploads = $this->scanDir(APPL_PATH.$dirs['structure']['uploads'].DIR_SEP);
		foreach ($uploads as $v){
			if(!preg_match('#tmb#',$v['id']) && !preg_match("#quarantine#",$v['id'])){
				if($v['type']=='folder'){
					$path = $v['title']."/";
					$c = $this->scanDir($v['id'].DIR_SEP);
					foreach ($c as $a){
						if($a['type']=='folder'){
							$path1 =$path. $a['title']."/";
							$b = $this->scanDir($a['id'].DIR_SEP);
							foreach ($b as $f){
								if($f['type']=='folder'){
									$path2 =$path1. $f['title']."/";
									$g = $this->scanDir($f['id'].DIR_SEP);
									foreach ($g as $t){
										if($this->isImage($t['id'])){
											
											$images[$t['id']] = $host.$dirs['structure']['uploads'].DIR_SEP.$path2.$t['title'];
											
										}
									}
								}else{
									if($this->isImage($f['id'])){

										$images[$f['id']] = $host.$dirs['structure']['uploads'].DIR_SEP.$path1.$f['title'];
										
									}
								}
							}
						}else{
							if($this->isImage($a['id'])){
								$images[$a['id']] = $host.$dirs['structure']['uploads'].DIR_SEP.$path.$a['title'];
								
							}
						}
					}
				}else{
					if($this->isImage($v['id'])){
						$images[$v['id']] = $host.$dirs['structure']['uploads'].DIR_SEP.$v['title'];
					
					}
				}
			}
		}
		
		return $images;
	}

	/**
	 * Check if the file is an image
	 *
	 * @param string $file
	 * @return boolean
	 */
	function isImage($file){

		//see if the file is an image
		$img = explode(".",$file);
		if(strtolower(end($img))=='png' || strtolower(end($img))=='jpg' || strtolower(end($img))=='jpeg' || strtolower(end($img))=='gif'){
			return true;

		}
		return false;
	}

	/**
	 * Gets all object items from the database in a simple way.
	 *
	 * @return array
	 */
	function getAllObjectSimple(){
		$t = $this->getDbTables();
		$sql = "SELECT {$t['objects']}. * , {$t['object_data']}.value as title FROM `{$t['objects']}` , `{$t['object_data']}` WHERE {$t['object_data']}.name='title' AND {$t['objects']}.id = {$t['object_data']}.object_id GROUP BY {$t['objects']}.slug";
		$r = $this->selectAll($sql);
		return $r;
	}

	/**
	 * Adds data to the breadcrumbs array in the template.
	 *
	 * @param array $data
	 */
	function addToBreadcrumb($data){
		$this->breadcrumbs[] = $data;
		$this->toTpl('breadcrumbs',$this->breadcrumbs);
	}

	/**
	 * Returns the contents of the specified widget
	 *
	 * @param int $id
	 * @return array
	 */
	function getTextWidgetContents($id){
		$id = $this->quote($id);
		$t = $this->getDbTables();
		$sql = "SELECT * FROM {$t['general_data']} WHERE `data_type`=$id";
		$r = $this->selectRow($sql);
		return unserialize($r['data_value']);
	}

	/**
	 * Creates a file with data from a form.
	 *
	 * @param string $file
	 * @param string $filename
	 */
	function createFile($file,$filename){
		$dirs = $this->dirs;
		$file = explode('base64,',$file);
		file_put_contents($dirs['structure']['uploads'].DIR_SEP.$filename,base64_decode($file[1]));
	}

	/**
	 * Return all registered and hooked ping services
	 *
	 * @return array
	 */
	function getPingServices(){
		$types[] = array('id'=>'pingGoogleBlogSearch','title'=>'Google Blogs');
		$types[] = array('id'=>'pingomatic','title'=>'Ping-o-matic');
		$types = $this->doQoolHook('post_ping_services_assign',$types);
		return $types;
	}

	/**
	 * Pings Google blogs search
	 *
	 * @param array $data
	 * @return string
	 */
	function pingGoogleBlogSearch($data){
		$title = urlencode($this->config->site->frontend_title);
		$url = $_SESSION['SITE_URL'];
		$xml = $url.urlencode("feed/{$data['content_type']}");
		$file = file_get_contents("http://blogsearch.google.com/ping?name=$title&url=$url&changesURL=$xml");
		return $file;
	}

	/**
	 * Pings Ping-o-matic
	 *
	 * @param array $data
	 * @return array
	 */
	function pingomatic($data) {
		$url = $_SESSION['SITE_URL'];
		$url .= $data['content_type']."/".$data['slug'];
		$content='<?xml version="1.0"?>'.
		'<methodCall>'.
		' <methodName>weblogUpdates.ping</methodName>'.
		'  <params>'.
		'   <param>'.
		'    <value>'.$data['title'].'</value>'.
		'   </param>'.
		'  <param>'.
		'   <value>'.$url.'</value>'.
		'  </param>'.
		' </params>'.
		'</methodCall>';
		$headers="POST / HTTP/1.0\r\n".
		"Mozilla/5.0 (Windows NT 6.1; rv:20.0) Gecko/20100101 Firefox/20.0\r\n".
		"Host: rpc.pingomatic.com\r\n".
		"Content-Type: text/xml\r\n".
		"Content-length: ".strlen($content);
		$request=$headers."\r\n\r\n".$content;
		$response = "";
		$fs=fsockopen('rpc.pingomatic.com',80, $errno, $errstr);
		if ($fs) {
			fwrite ($fs, $request);
			while (!feof($fs)) $response .= fgets($fs);
			if ($debug) echo "<xmp>".$response."</xmp>";
			fclose ($fs);
			preg_match_all("/<(name|value|boolean|string)>(.*)<\/(name|value|boolean|string)>/U",$response,$ar, PREG_PATTERN_ORDER);
			for($i=0;$i<count($ar[2]);$i++) $ar[2][$i]= strip_tags($ar[2][$i]);
			return array('status'=> ( $ar[2][1]==1 ? 'ko' : 'ok' ), 'msg'=>$ar[2][3] );
		} else {
			return array('status'=>'ko', 'msg'=>$errstr." (".$errno.")");
		}
	}

	function loadImgEditor($img,$image_info,$fileinfo){
		$dirs = $this->dirs;
		include_once($dirs['structure']['lib'].DIR_SEP.'js'.DIR_SEP.'imgeditor'.DIR_SEP.'editor.html');
	}

	public function serverSendMessage($message){
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		echo "data: {$message}\n\n";
		flush();
	}

	function memoryGetUsage(){
		if ( substr(PHP_OS,0,3) == 'WIN'){
			if ( substr( PHP_OS, 0, 3 ) == 'WIN' ){
				$output = array();
				exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );
				
				return "(".trim(str_replace("Image Name:","",$output[1])).") Server Memory Usage: ".round(preg_replace( '/[\D]/', '', $output[5] ) /1024,2)."MB";
			}
		}else{
			$pid = getmypid();
			exec("ps -eo%mem,rss,pid | grep $pid", $output);
			$output = explode("  ", $output[0]);
			//rss is given in 1024 byte units
			return "Server Memory Usage: ".round($output[1] / 1024,2)."MB";
		}
	}

}

?>