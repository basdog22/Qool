<?php
class Hooks_HooksController extends Qool_Frontend_Action{

	public function indexAction(){
	
		$this->requirePriviledges();

		//content types awareness ;)
		$mytypes = $this->can_handle;
		
		$data = $this->_request->getParams();
		
		$settings = $this->addonSettings;
		

		$this->toTpl('module_title','Hooks Documentation');
		$this->prefix = "Hooks_";
		$this->setupCache('hooks');
		$this->toTpl('current_href',$this->http_location.'/hooks');
		$this->totpl('theInclude','list');
		Zend_Registry::set('controller','hooks');
		
		//if(!$content = $this->loadCache('blog_index'.$data['page'])){

			$content = array();
			//lets get the content for
			foreach ($mytypes as $k=>$v){
				//accept only blog posts ;)
				if(preg_match('#hooks#',strtolower($v['title']))){
				
					$content = $this->getRecent($v['title'],30,0,0,true);
				}
			}
			

			//$this->cacheData($content,'blog_index'.$data['page']);
		//}
		//echo $this->curPage;
		$this->toTpl('content',$content);

	}

	public function readAction(){
		$mytypes = $this->can_handle;
		$data = $this->_request->getParams();
		$settings = $this->addonSettings;
		$this->prefix = "Hooks_";
		$this->setupCache('hooks');
		$this->toTpl('current_href',$this->http_location.'/hooks');
		$this->totpl('theInclude','view');
		Zend_Registry::set('controller','hooks');
		//get the content type
		//now get the id of the content
		$id = $this->getIdBySlug($data['slug']);
		$content = $this->getContent('Hooks Documentation',$id);
		//d($content);
		$this->toTpl('single',$content);
	}

	public function archiveAction(){
		//content types awareness ;)
		$mytypes = $this->can_handle;
		$data = $this->_request->getParams();
		$settings = $this->addonSettings;
		$this->toTpl('module_title',$this->t("Archive for ").$data['year-:month-:date']);
		$this->prefix = "Blog_";
		$this->setupCache('blog');
		$this->toTpl('current_href',$this->http_location.'/blog/archive/'.$data['year-:month-:date']);
		$this->totpl('theInclude','list');
		Zend_Registry::set('controller','blog');
		$from = $data['page'] * $settings['posts_per_page'];

		//if(!$content = $this->loadCache('blog_archive_'.str_replace("-","",$data['year-:month-:date']))){

			$content = array();
			//lets get the content for this date
			foreach ($mytypes as $k=>$v){
				//accept only blog posts ;)
				if(preg_match('#blog#',strtolower($v['title']))){
					$content = $this->getContentByDate($v['title'],$data['year-:month-:date'],$settings['posts_per_page'],0,true);
				}
			}

			//$this->cacheData($content,'blog_archive_'.str_replace("-","",$data['year-:month-:date']));
		//}
		
		$this->toTpl('content',$content);
	}
}
?>