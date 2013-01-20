<?php
class indexController extends Qool_Frontend_Action
{
	public function indexAction(){
		$this->setupCache('default');
		$this->requirePriviledges();
		if(Zend_Registry::get('tplOverride')=='login'){
			$this->buildLoginForm();
		}else{
			$this->doQoolHook('pre_load_main_tpl');
			$this->toTpl('theInclude','main');
		}
		$this->toTpl('current_href',$this->http_location);

	}

	//css in one file ;)
	public function qoolcssAction(){
		if(!$html = $this->loadCache('cssfiles')){
			$config = $this->config;
			$dirs = $this->dirs;
			$html = '';


			$xml = readLangFile($dirs['structure']['templates'].DIR_SEP.'frontend'.DIR_SEP.$config->template->frontend->title.DIR_SEP."template.xml");
			foreach ($xml->css->file as $k=>$v){
				$v = $this->jsonArray($v);
				$file = file($dirs['structure']['templates'].DIR_SEP.'frontend'.DIR_SEP.$config->template->frontend->title.DIR_SEP.$v[0]);
				$html .= implode('',$file);
			}
			$this->cacheData($html,'cssfiles');
		}
		header('Content-Type: text/css');
		ob_start("ob_gzhandler");
		echo $html;
		die();
	}

	public function readAction(){
		$data = $this->_request->getParams();
		$this->prefix = "Default_";
		$this->setupCache('default');

		$this->totpl('theInclude','view');
		//Zend_Registry::set('controller','default');
		//get the content type
		//now get the id of the content
		$obj = $this->getAllBySlug($data['slug']);
		$type = $this->getContentType($obj['type_id']);
		$content = $this->getContent($type['title'],$obj['id']);
		$this->toTpl('current_href',$this->http_location.'/default/'.$obj['slug']);
		//d($content);
		$this->toTpl('single',$content);

	}

	public function taxonomyAction(){
		$data = $this->_request->getParams();
		$this->prefix = "Default_";
		$this->setupCache('default');
		$this->totpl('theInclude','list');
		if(!$objects = $this->loadCache($this->slugify($data['type'].$data['tax']))){
			//we need to get the taxonomy id for this type
			$tax = $this->getTaxonomyByName($data['tax']);
			//we now need to get all objects that belong to this taxonomy
			$objects = $this->getContentByTaxonomy($tax['id']);
			$this->cacheData($objects,$this->slugify($data['type'].$data['tax']));
		}
		$this->toTpl('objects',$objects);
	}

	public function qooljsAction(){
		$config = $this->config;
		$dirs = $this->dirs;
		$html = '';
		header('Content-Type: text/javascript');
		ob_start("ob_gzhandler");
		$xml = readLangFile($dirs['structure']['templates'].DIR_SEP.'frontend'.DIR_SEP.$config->template->frontend->title.DIR_SEP."template.xml");
		foreach ($xml->js->file as $k=>$v){
			$v = $this->jsonArray($v);
			$file = file($dirs['structure']['templates'].DIR_SEP.'frontend'.DIR_SEP.$config->template->frontend->title.DIR_SEP.$v[0]);
			$html .= implode('',$file);
		}
		echo $html;
		die();
	}

	public function feedAction(){
		$data = $this->_request->getParams();
				
		$this->prefix = "Default_";
		$config = $this->config;
		$this->setupCache('default');
		//$this->totpl('theInclude','list');
		if($data['lib']){
			$feedtype = $data['type'];
			
			if(!$out = $this->loadCache('feed_'.$data['lib']."_".$feedtype)){
				
				//we have a lib... now get the content for this lib
				$type = $this->getContentTypeByLib($data['lib']);
				//some admins think it is a good thing to add more than one content type for each lib ;S
				if($type['title']){
					$content = $this->getRecent($type['title'],10);
				}else{
					foreach ($type as $o=>$s){
						$content[] = $this->getRecent($s['title'],5);
					}
				}
				
				//get recent items
				if(count($content)>0){
					$feed = new Zend_Feed_Writer_Feed();
					
					$feed->setTitle($config->site->frontend_title." ".$data['lib']." feed");
					$feed->setLink($this->http_location);
					$feed->setCopyright($this->t($config->site->feed_copyright));
					$feed->setGenerator($config->site->feed_generator);
					$feed->setFeedLink($this->http_location."/".$data['lib'], 'atom');
					$feed->setFeedLink($this->http_location."/".$data['lib']."?type=rss", 'rss');
					$feed->addAuthor(array(
					'name'  => $config->site->feed_author_name,
					'email' => $config->site->feed_author_email,
					'uri'   => $this->http_location
					));
					$feed->setImage(array('uri'=>$config->site->feed_logo_image));
					$feed->setDateModified(time());
					$feed->addHub('http://pubsubhubbub.appspot.com/');
					//now we need to loop
					
					foreach ($content as $k=>$v){
						if($v['title']){
							$entry = $feed->createEntry();
							$entry->setTitle($v['title']);
							$entry->setLink($this->http_location."/".$data['lib']."/".$v['slug']);
							$entry->setDateCreated($v['datestr']);
							$entry->setDateModified(time());
							if($v['content']){
								$entry->setDescription(strip_tags(stripslashes(substr($v['content'],0,160))));
							}
							$feed->addEntry($entry);
						}else{
							foreach ($v as $r){
								$entry = $feed->createEntry();
								$entry->setTitle($r['title']);
								$entry->setLink($this->http_location."/".$data['lib']."/".$r['slug']);
								$entry->setDateCreated($r['datestr']);
								$entry->setDateModified(time());
								if($r['content']){
									$entry->setDescription(strip_tags(stripslashes(substr($r['content'],0,160))));
								}
								$feed->addEntry($entry);
							}
						}

					}
					$out = $feed->export($feedtype);
				}
				
			
				$this->cacheData($out,'feed_'.$data['lib']."_".$feedtype);
			}
			header("Content-type: text/xml");

			echo $out;
			die();
		}
	}

	public function loginAction(){
		$data = $this->_request->getParams();
		$this->prefix = "Default_";
		$this->setupCache('default');
		$this->buildLoginForm($data['redirect']);
		$this->totpl('theInclude','login');
	}
	
	public function dologinAction(){
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
			if($data['redirect']){
				$data['redirect'] = urldecode($data['redirect']);
				$redir = $data['redirect'];
				$redir = explode("/",$redir);
				$redir = array_reverse($redir);
				$this->_helper->redirector($redir[0], $redir[1],$redir[2]);
			}else{
				$this->_helper->redirector('index', 'index');
			}
		}else{
			$this->doQoolHook('post_login_action_error');
			$params = array("message"=>$this->t("Wrong Username or Password combination"),"msgtype"=>'error');
			$this->_helper->redirector('login', 'index','default',$params);
		}
	}
	
	public function buildLoginForm($redirect=false){
		try {
			$form = new Zend_Form;
			$form->setView($this->tpl);
			$form->setAttrib('class', 'form-inline');
			$form->removeDecorator('dl');
			$form->setAction($this->config->host->folder.'/dologin')->setMethod('post');
			if($redirect){
				$redir = new Zend_Form_Element_Hidden('redirect');
				$redir->setValue($redirect);
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
			$submit->setLabel($this->language['Login']);
			$form->addElement($username)->addElement($password)->addElement($submit);
			$form = $this->doQoolHook('post_loginform_create',$form);
			$this->toTpl('loginForm',$form);
		}catch (Exception $e){
			echo $e->getMessage();
		}

	}
}
?>