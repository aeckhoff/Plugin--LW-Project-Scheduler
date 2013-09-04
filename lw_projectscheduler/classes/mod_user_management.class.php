<?php

class mod_user_management extends module
{
	public function am_show_all_users()
	{
		if(!$this->isAdmin) return $this->am_show_user();
		
		$users = $this->dh->getAllUsers();
		$tpl = $this->autotemplate();
		$block = $tpl->getBlock("users");
		$b_out = "";
		$i=0;
		foreach($users as $user)
		{
			$btpl = new lw_te($block);
			
			$btpl->reg("username", $user['name']);
			$btpl->reg("status", $user['status']);
			
			$url = $this->buildURL(array("psc_module"=>"user_management","command"=>"show_user","user_id"=>$user['id']), false, "index.php");
			
			$btpl->reg("userlink", $url);
			
			$btpl->reg("rowclass", $this->getRowClass($i));
        
			$b_out.=$btpl->parse();
			$i++;
		}
		$tpl->putBlock("users", $b_out);	
		return $tpl->parse();
	}
	
	public function am_show_user()
	{
		if ($this->isAdmin) {
			$user_id = $this->fGet->getAlNum("user_id");
		} 
		else {
			$user_id = $this->auth->getUserdata("id");
		}
		
		$tpl = $this->getTemplate("user_management_show_user");
		$user = $this->dh->getUserData($user_id);
		
		if (empty($user['name'])) return $this->getErrorMessage("No user");
		$tpl->reg("username", $user['name']);
	
		if ($this->isAdmin) {
			$tpl->setIfVar("admin");
			if ($user['status'] == "active") {
				$tpl->reg("active", "checked='checked'");
			} 
			else {
				$tpl->reg("active", "");
				
			}
			$tpl->reg("countryoptions", $this->getCountryOptions($user['country']));
		} 
		else {
			$tpl->setIfVar("user");
			if (empty($user['country'])) $user['country'] = "&nbsp;";
			$tpl->reg("country", $user['country']);
		}
		
		if (empty($user['organisation'])) $user['organisation'] = "&nbsp;";
		$tpl->reg("organisation", $user['organisation']);
		
		$employees = $this->dh->getEmployees($user_id);
		
		$block = $tpl->getBlock("employees");
		$b_out = "";
		foreach($employees as $employee)
		{
			$editurl = $this->buildURL(array("psc_module"=>"user_management","command"=>"edit_employee","user_id"=>$user['id'], "emp_id"=>$employee['id']), false, "index.php");
			
			$btpl = new lw_te($block);
			
			$btpl->reg("employee", $employee['firstname']." ".$employee['lastname']);
			$btpl->reg("editurl",$editurl);
			
			$deleteurl = $this->buildURL(array("psc_module"=>"user_management","command"=>"delete_employee","user_id"=>$user['id'], "emp_id"=>$employee['id']), false, "index.php");
			$btpl->reg("deleteurl",$deleteurl);

			$b_out.=$btpl->parse();
		}
		$tpl->putBlock("employees", $b_out);
		
		$actionurl = $this->buildURL(array("psc_module"=>"user_management","command"=>"save_user","user_id"=>$user_id), array("emp_id"), "index.php");
		$tpl->reg("actionurl",$actionurl);
		$tpl->reg("user_id",$user_id);
		
		$addurl = $this->buildURL(array("psc_module"=>"user_management","command"=>"add_employee","user_id"=>$user['id']), false, "index.php");
		$tpl->reg("addurl",$addurl);
		
		$backurl = $this->buildURL(array("psc_module"=>"user_management"), array("command","user_id","emp_id"), "index.php");
		$tpl->reg("backurl",$backurl);
		
		return $tpl->parse();
	}
	
	public function am_save_user()
	{
		if ($this->isAdmin) {
			$user_id = $this->fGet->getAlNum("user_id");
		} 
		else {
			$user_id = $this->auth->getUserdata("id");
		}
		
		$organisation = $this->lwStringClean($this->fPost->getRaw("organisation"));
		$country = $this->fPost->getRaw("country");
		$status = $this->fPost->getRaw("status");
		
		if ($this->isAdmin) {
			if ($status != "active") $status = "inactive";
		}
		
		$ok = $this->dh->saveUser($user_id, $organisation, $country, $status,$this->isAdmin);
		$reloadurl = $this->buildURL(array("psc_module"=>"user_management"), array("command","user_id","emp_id"), "index.php");
		$this->pageReload($reloadurl);
	}
	
	public function am_add_employee()
	{
		return $this->am_edit_employee(true);
	}
	
	public function am_save_employee()
	{
		if ($this->isAdmin) {
			$user_id = $this->fGet->getAlNum("user_id");
		} 
		else {
			$user_id = $this->auth->getUserdata("id");
		}
		
		$emp_id = $this->fPost->getRaw("emp_id");
		
		$firstname = $this->lwStringClean($this->fPost->getRaw("firstname"));
		$lastname = $this->lwStringClean($this->fPost->getRaw("lastname"));
		
		$this->dh->saveEmployee($user_id, $emp_id, $firstname, $lastname);
		
		$reloadurl = $this->buildURL(array("psc_module"=>"user_management","command"=>"show_user","user_id"=>$user_id), array("emp_id"), "index.php");
		$this->pageReload($reloadurl);
	}
	
	public function am_edit_employee($new = false)
	{
		if ($this->isAdmin) {
			$user_id = $this->fGet->getAlNum("user_id");
		} 
		else {
			$user_id = $this->auth->getUserdata("id");
		}
		
		if ($new === true) {
			$emp_id = -1;
		} 
		else {
			$emp_id = $this->fGet->getAlNum("emp_id");
		}
		
		$tpl = $this->getTemplate("user_management_edit_employee");
		
		if ($emp_id == -1) {
			$employee = array();
			$employee['firstname'] = "";
			$employee['lastname'] = "";
			$employee['user_id'] = $user_id;
		} 
		else {
			$employee = $this->dh->getEmployee($emp_id);
		}
		
		$tpl->reg("firstname",$employee['firstname']);
		$tpl->reg("lastname",$employee['lastname']);
		$tpl->reg("user_id",$employee['user_id']);
		$tpl->reg("emp_id",$emp_id);
		
		$actionurl = $this->buildURL(array("psc_module"=>"user_management","command"=>"save_employee","user_id"=>$user_id, "emp_id"=>$emp_id), false, "index.php");
		$tpl->reg("actionurl",$actionurl);
		
		$cancelurl = $this->buildURL(array("psc_module"=>"user_management","command"=>"show_user","user_id"=>$user_id), array("emp_id"), "index.php");
		$tpl->reg("cancelurl",$cancelurl);
		
		return $tpl->parse();
	}
	
	public function am_delete_employee()
	{
		if ($this->isAdmin) {
			$user_id = $this->fGet->getAlNum("user_id");
		} 
		else {
			$user_id = $this->auth->getUserdata("id");
		}
		
		$emp_id =  $this->fGet->getAlNum("emp_id");
		$this->dh->deleteEmployee($user_id, $emp_id);
		$url = 	$this->buildURL(array("psc_module"=>"user_management","command"=>"show_user","user_id"=>$user_id), array("emp_id"), "index.php");
		$this->pageReload($url);
	}
	
	protected function getDefaultCommand()
	{
		return "show_all_users";
	}
}
