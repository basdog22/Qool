<?php
class Templates_Wordpress_Wordpress extends Zend_View_Abstract {

	var $tplObject = array();
	var $templates = '';
	public function __construct($data = array()){
		$dirs = Zend_Registry::get('dirs');
		$template = Zend_Registry::get('theme');
		$qool_module = Zend_Registry::get('Qool_Module');
		$this->templates = $dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template;
		Zend_Registry::set('tplExt','php');
		//we need an xml db to store options for wordpress ;)
		if(!file_exists(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'wp.xml')){
			$this->createXML(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'wp.xml');
		}
		Zend_Registry::set('optionsxml',APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'wp.xml');
		define('TEMPLATE_ABSOLUTE_DIR',$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template);
		define('TEMPLATEPATH',$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template);
		
		//Some wp themes make use of some wp classes we need to include...
		include_once APPL_PATH.$dirs['structure']['lib'].DIR_SEP.'Templates'.DIR_SEP.'Wordpress'.DIR_SEP.'wp.php';
		include_once(APPL_PATH.$dirs['structure']['lib'].DIR_SEP.'Qool'.DIR_SEP.'Template'.DIR_SEP.'template.php');
		//also include the WordPress template layer
		include_once(APPL_PATH.$dirs['structure']['lib'].DIR_SEP.'Templates'.DIR_SEP.'Wordpress'.DIR_SEP.'layer.php');
		if(file_exists(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'functions.php')){
			try{
				include_once(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'functions.php');
			}catch (Exception $e){

			}
		}


	}

	protected function createXML($file){
		$contents = '<?xml version="1.0" encoding="utf-8"?>
		<options></options>';
		$handle = fopen($file,'w');
		fwrite($handle,$contents);
		fclose($handle);
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
		global $wp_styles;
		wp_load_alloptions();
		do_action('after_setup_theme');

		$_SESSION['wp_init'] = false;
		do_action('init');
		try{
			include(APPL_PATH.$this->templates.DIR_SEP.$file);
		}catch (Exception $e){
			
		}
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