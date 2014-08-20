<?php
class Blog_AdminController extends Qool_Backend_Action{


	private function _useCache(){
		$this->prefix = "Blog_";
		$this->setupCache('blog');
	}

	public function indexAction(){
		$this->_useCache();
		Zend_Registry::set('module',$this->t('Blog'));
	}


	public function postAction(){
		$mytypes = $this->can_handle;
		foreach ($mytypes as $k=>$v){
			//accept only blog posts ;)
			if(preg_match('#blog#',strtolower($v['title']))){
				$cid = $v['id'];
			}
		}
		$params = array('id'=>$cid);
		$this->_helper->redirector('contentnew', 'index','admin',$params);
	}

	public function helpAction(){
		$dirs = $this->dirs;
		$this->addToBreadcrumb(array('blog',$this->t('Blog')));
		$this->addToBreadcrumb(array('blog/help',$this->t("Help")));
		$this->totpl('theInclude','general');
		Zend_Registry::set('module','Blog Help');
		$file = file($dirs['structure']['addons']."/blog/templates/help.php");
		$file = implode("",$file);
		$this->toTpl('html',$file);
		
	}


}
?>