<?php
class ProfilesController extends Qool_Frontend_Action{


	function indexAction(){
		$this->setupCache('default');
		$this->level = 500;
		$this->requirePriviledges();
		
		$this->toTpl('theInclude','profile');
		
		$data = $this->_request->getParams();
		$this->toTpl('module_title',$this->t('Profile of')." ".$data['profile']);
		//check if the user is trying to view his profile
		$edit = false;
		if($data['profile']=='me' || !$data['profile'] || $data['profile']=='index'){
			$profile = $_SESSION['user']['username'];
			$edit = true;
		}else{
			$profile = $data['profile'];
			$edit = false;
		}
		//try to get the user...
		$user = $this->getUserByName($profile);
		//now we need to get the data for this user
		$user['data'] = $this->getUserData($user['id']);
		//now if the user is viewing his profile, we need to create a nice form in order for him to edit the data
		if($edit){
			//get the fields
			$fields = $this->getUserProfileFields();
			$form = new Zend_Form;
			$form->setView($this->tpl);
			$form->setAttrib('class', 'form');
			$form->removeDecorator('dl');
			$form->setAction($this->config->host->folder.'/profiles/update')->setMethod('post');
			
			foreach ($fields as $k=>$v){
				switch ($v['field_type']){
					case "selectbox":
						$values = explode(",",$v['default_value']);
						break;
					default:
						$values = $v['default_value'];
						break;
				}
				$form->addElement(
					$this->getFormElement(array('name'=>$v['name'],'value'=>$v['field_type'],'title'=>$this->t(ucfirst($v['name'])),'use_pool'=>'valuesAsKeys','pool_type'=>$values,'novalue'=>true),$user['data'][$v['name']]));
			}
			
			$form->addElement($this->getFormElement(array('name'=>'uid','value'=>'hidden'),$user['id']));
			$submit = new Zend_Form_Element_Submit('save');
			$submit->setLabel($this->t("Update"));
			$submit->setAttrib('class',"btn btn-primary");
			$form->addElement($submit);
			$form = $this->doQoolHook('pre_assign_user_profile_form',$form);
			$this->toTpl('formTitle',$this->t("Update your profile"));
			$this->toTpl('theForm',$form);
			$user = $this->doQoolHook('pre_assign_profiles_own_user_data',$user);
		}else{
			$user = $this->doQoolHook('pre_assign_profiles_user_data',$user);
		}
		
		$this->doQoolHook('pre_load_profiles_tpl');
		$this->toTpl('user',$user);
	}
	
	public function updateAction(){
		$data = $this->_request->getParams();
		if ($this->_request->isPost()) {
			$user = $this->getUserByName($_SESSION['user']['username']);
			if($user['id']==$data['uid']){
				$t = $this->getDbTables();
				//clean up the $data
				unset($data['module']);
				unset($data['controller']);
				unset($data['action']);
				unset($data['save']);
				unset($data['uid']);
				//remove all data for this user
				$this->delete($t['user_data'],$user['id'],'uid');
				//now loop
				foreach ($data as $k=>$v){
					$this->save($t['user_data'],array('name'=>$k,'value'=>$v,'uid'=>$user['id']));
				}
				$params = array('profile'=>'me',"message"=>$this->t("Profile Updated"),"msgtype"=>'success');
				$this->_helper->redirector('index', 'profiles','default',$params);
			}else{
				$params = array('profile'=>'me',"message"=>$this->t("Invalid Request"),"msgtype"=>'error');
				$this->_helper->redirector('index', 'profiles','default',$params);
			}
		}else{
			$params = array('profile'=>'me',"message"=>$this->t("Invalid Request"),"msgtype"=>'error');
			$this->_helper->redirector('index', 'profiles','default',$params);
		}
	}


}
?>