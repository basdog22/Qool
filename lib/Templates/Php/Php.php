<?php
class Templates_Php_Php extends Zend_View_Abstract {

	var $tplObject = array();
	var $templates = '';
	public function __construct($data = array()){
		$dirs = Zend_Registry::get('dirs');
   		$template = Zend_Registry::get('theme');
   		$qool_module = Zend_Registry::get('Qool_Module');
		$this->templates = $dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template;
		Zend_Registry::set('tplExt','php');
		
		
		if(file_exists(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'functions.php')){
			
			include_once(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'functions.php');
		}
		include_once(APPL_PATH.$dirs['structure']['lib'].DIR_SEP.'Qool'.DIR_SEP.'Template'.DIR_SEP.'template.php');
	}

	protected function _run(){
		$file = func_num_args() > 0 && file_exists(func_get_arg(0)) ? func_get_arg(0) : '';
		if ($this->_customTemplate || $file) {
			$template = $this->_customTemplate;
			if (!$template) {
				$template = $file;
			}

			include(APPL_PATH.$this->templates.DIR_SEP.$file);
		} else {
			throw new Zend_View_Exception('Cannot render view without any template being assigned or file does not exist');
		}
	}

	function display($file){
		include(APPL_PATH.$this->templates.DIR_SEP.$file);
	}

	public function assign($var, $value = null){
		if (is_string($var)) {
			$this->tplObject[$var] = $value;
			
		} elseif (is_array($var)) {
			foreach ($var as $key => $value) {
				$this->assign($key, $value);
			}
		} else {
			throw new Zend_View_Exception('assign() expects a string or array, got '.gettype($var));
		}
		return $this;
	}
}
?>