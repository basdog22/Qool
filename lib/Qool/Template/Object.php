<?php
class Qool_Template_Object{
	
	
	public function show($file='index'){
		$theme = Zend_Registry::get('theme');
		$module = Zend_Registry::get('Qool_Module');
		
		$this->tpl = Zend_Registry::get('tpl');
		$dirs = Zend_Registry::get("dirs");
		//include("template.php");
		
		
		if(Zend_Registry::get('tplOverride')=='default'){
			$this->tpl->display($file.".".Zend_Registry::get('tplExt'));
		}else{
			$this->tpl->display(Zend_Registry::get('tplOverride').".".Zend_Registry::get('tplExt'));
		}
	}
}

?>