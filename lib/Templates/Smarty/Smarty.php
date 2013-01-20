<?php

// load Smarty library
require('Smarty.class.php');

class Templates_Smarty_Smarty extends Zend_View_Abstract {

	 private $_smarty = false;
	 var $tplObject = array();
	 
   public function __construct($data = array()){
   		parent::__construct($data);
   		$dirs = Zend_Registry::get('dirs');
   		$template = Zend_Registry::get('theme');
   		$qool_module = Zend_Registry::get('Qool_Module');
   		//set Qool directories
   		$templates = APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP;
   		$templates_c = APPL_PATH.$dirs['structure']['cache'].DIR_SEP.'smarty'.DIR_SEP.'templates_c'.DIR_SEP;
   		$configs = APPL_PATH.'config'.DIR_SEP.'smarty'.DIR_SEP;
   		$cache = APPL_PATH.$dirs['structure']['cache'].DIR_SEP.'smarty'.DIR_SEP.'cache'.DIR_SEP;
        // Class Constructor.
        // These automatically get set with each new instance.
        $this->_smarty = new Smarty();
        $this->_smarty->setTemplateDir($templates);
        $this->_smarty->setCompileDir($templates_c);
        $this->_smarty->setConfigDir($configs);
        $this->_smarty->setCacheDir($cache);
        $this->_smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
        $this->_smarty->caching = false;
        
        //get the template file and register all functions in it to smarty
        include_once(APPL_PATH.$dirs['structure']['lib'].DIR_SEP.'Qool'.DIR_SEP.'Template'.DIR_SEP.'template.php');
        if(file_exists(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'functions.php')){
			include_once(APPL_PATH.$dirs['structure']['templates'].DIR_SEP.$qool_module.DIR_SEP.$template.DIR_SEP.'functions.php');
		}
        
       // $this_smarty->register_function('date_now', 'print_current_date');
        
        Zend_Registry::set('tplExt','tpl');
   }
   
     protected function _run(){
        $file = func_num_args() > 0 && file_exists(func_get_arg(0)) ? func_get_arg(0) : '';
        if ($this->_customTemplate || $file) {
            $template = $this->_customTemplate;
            if (!$template) {
                $template = $file;
            }

            $this->_smarty->display($template);
        } else {
            throw new Zend_View_Exception('Cannot render view without any template being assigned or file does not exist');
        }
    }
    
    function display($file){
    	$this->_smarty->display($file);
    }

     public function assign($var, $value = null){
        if (is_string($var)) {
            $this->_smarty->assign($var, $value);
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