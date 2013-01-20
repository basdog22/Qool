<?php
class ErrorController extends Qool_Frontend_Action{
	
	
	public function errorAction($d){
		$data = $this->_request->getParams();
		$this->toTpl('theInclude','404');
	}
}
?>