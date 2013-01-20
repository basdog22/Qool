<?php

require_once('Autoloader.php');
Twig_Autoloader::register();

class Templates_Twig_Twig extends Zend_View_Abstract {

	private $_twig = false;
	var $tplObject = array();

	public function __construct($data = array()){
		parent::__construct($data);
		$dirs = Zend_Registry::get('dirs');
		$template = Zend_Registry::get('theme');
		$config = Zend_Registry::get('config');
		$qool_module = Zend_Registry::get('Qool_Module');
		// Class Constructor.
		// These automatically get set with each new instance.
		$loader = new Twig_Loader_Filesystem(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP);
		$twig = new Twig_Environment($loader, array(
		'cache' => APPL_PATH.$dirs['structure']['cache'].DIR_SEP.'twig'.DIR_SEP,
		));
		$lexer = new Twig_Lexer($twig, array(
		'tag_comment' => array('<#', '#>}'),
		'tag_block' => array('<%', '%>'),
		'tag_variable' => array('<<', '>>'),
		));
		$twig->setLexer($lexer);
		include_once(APPL_PATH.$dirs['structure']['lib'].DIR_SEP.'Qool'.DIR_SEP.'Template'.DIR_SEP.'template.php');
		if(file_exists(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'functions.php')){
			include_once(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'functions.php');
		}
		$funcs = get_defined_functions();
		foreach ($funcs['user'] as $k=>$v){
			$twig->addFunction($v, new Twig_Function_Function($v));
		}
		$this->_twig = $twig;
		$this->assign('config',$config);
		
		
		
		Zend_Registry::set('tplExt','html');
	}

	protected function _run(){
		$file = func_num_args() > 0 && file_exists(func_get_arg(0)) ? func_get_arg(0) : '';
		if ($this->_customTemplate || $file) {
			$template = $this->_customTemplate;
			if (!$template) {
				$template = $file;
			}

			$this->_twig->display($template);
		} else {
			throw new Zend_View_Exception('Cannot render view without any template being assigned or file does not exist');
		}
	}

	function display($file){
		// $this->_twig->render($file);
		$this->_twig->display($file);
	}

	public function assign($var, $value = null){
		if (is_string($var)) {
			$this->_twig->addGlobal($var, $value);
			$this->tplObject[$var] = $value;
			//$this->_twig->assign($var, $value);
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