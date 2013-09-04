<?php

class base_mod extends lw_object
{
    public function __construct() 
    {
		$this->output = "";
		$this->module_prefix = "psc_";
		$this->command_var = "command";

		$this->reg = lw_registry::getInstance();
		$this->reqVars  = $this->reg->getEntry("requestVars");
		$this->index = $this->reqVars['get']['index'];
		$this->auth = new psc_auth();

		$this->conf = $this->reg->getEntry("config");
		$this->conf['scheduler']['templates'] = dirname(__FILE__).'/../templates/';
		$this->conf['scheduler']['path'] = dirname(__FILE__).'/../classes/';
		
		$this->dh = new psc_datahandler();
		$this->username = $this->auth->getUserdata("name");

		$this->fGet = $this->reg->getEntry('fGet');
		$this->fPost = $this->reg->getEntry('fPost');
		$this->module = $this->filterModule($this->lwStringClean($this->fGet->getRaw($this->module_prefix."module")));
		$this->command = $this->fGet->getRaw($this->command_var);
		$this->index = $this->fGet->getRaw("index");
		$this->baseIndex = 79;
    }
    
    public function init()
    {
        $reg = lw_registry::getInstance();
		$this->fGet = $reg->getEntry('fGet');
		$this->fPost = $reg->getEntry('fPost');
		$this->conf = $reg->getEntry("config");

        $this->dh = new psc_datahandler();
		$this->auth = new psc_auth();
		$this->reqVars  = $reg->getEntry("requestVars");
		$this->index = $this->fGet->getRaw("index");
		$this->baseIndex = 79;
    }    
        
	public function getOutput()
	{
		return $this->output;
	}        
        
    public function filterModule($module)
	{
		$module = str_replace(".","_",$module);
		$module = str_replace("..","__",$module);
		$module = str_replace("/","_",$module);
		return $module;
	}
	
	public function buildURL($in, $out, $index)
	{
		if (!empty($this->index)) {
			$in['index'] = $this->index;
		} 
		else {
			$in['index'] = $this->baseIndex;
		}
		$url = parent::buildURL($in,$out);
		$base = $this->conf['url']['client'];
		$url = $base."index.php".$url;
		return $url;
	}
	
	protected function getReadableDate($date)
	{
		$year  = substr($date, 2, 2);
		$month = substr($date, 4, 2);
		$day   = substr($date, 6, 2);
		return $day.".".$month.".".$year;
	}	
	
	protected function moduleNotFoundError()
	{
		return "<h1>Error</h1><p>Module not found.</p>";
	}	
}
