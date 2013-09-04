<?php

class scheduler extends base_mod
{
	function __construct($mode)
	{
		parent::__construct();
		
		if ($mode == "admin") {
			$this->isAdmin = true;
		} 
		else {
			$this->isAdmin = false;
		}
		if ($mode == "install") {
		    $this->installDB = true;
		}

		if ($this->isAdmin) {
			if (empty($this->module)) {
			    $this->module="appointment_management";
			}
		} 
		else {
			if (empty($this->module)) {
			    $this->module="agreements";
			}
		}
	}
	
	public function execute()
	{
		if ($this->installDB == true) {
    		require_once(dirname(__FILE__).'/psc_dbinstall.class.php');
    		$installer = new psc_dbinstall();
    		$installer->install();
    		exit();
		}
		$className = "mod_".$this->module;
		$classFile = $this->conf['scheduler']['path'].$className.".class.php";
		if (is_file($classFile)) {
			require_once($classFile);
			//$module = new $className($this->module, $this->command, $this->fGet, $this->fPost, $this->dh, $this->isAdmin);
			$module = new $className($this->module, $this->command, $this->isAdmin);
			$module->execute();
			$this->output = $module->getOutput();
		} else {
			$this->output = $this->moduleNotFoundError();
		}
	}
	
	public function getOutput($css)
	{
		return $this->applyMainTemplate($this->output,$css);
	}
	
	public function applyMainTemplate($main,$css)
	{
		if ($this->isAdmin) {
			$template = $this->loadFile($this->conf['scheduler']['templates']."__main.tpl.html");
		} 
		else {
			$template = $this->loadFile($this->conf['scheduler']['templates']."__main_user.tpl.html");
		}
		$tpl = new lw_te($template);
		
		if($css) {
			$tpl->setIfVar("defaultcss");
		} 	
		
		$tpl->reg("user_management_url", $this->buildURL(array($this->module_prefix."module"=>"user_management", "index"=>$this->index), array($this->command_var), "index.php"));
		
		if ($this->isAdmin) {
			$tpl->reg("category_management_url", $this->buildURL(array($this->module_prefix."module"=>"category_management", "index"=>$this->index), array($this->command_var), "index.php"));
			$tpl->reg("appointment_management_url", $this->buildURL(array($this->module_prefix."module"=>"appointment_management", "index"=>$this->index), array($this->command_var), "index.php"));
			$tpl->reg("template_management_url", $this->buildURL(array($this->module_prefix."module"=>"template_management", "index"=>$this->index), array($this->command_var), "index.php"));
			$tpl->reg("archive_url", $this->buildURL(array($this->module_prefix."module"=>"archive", "index"=>$this->index), array($this->command_var), "index.php"));
		}
		$tpl->reg("agreement_url", $this->buildURL(array($this->module_prefix."module"=>"agreements", "index"=>$this->index), array($this->command_var), "index.php"));
		$tpl->reg("main", $main);
		$tpl->reg("username", $this->username);
		return $tpl->parse();
	}
}
