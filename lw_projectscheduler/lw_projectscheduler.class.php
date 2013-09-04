<?php

class lw_projectscheduler extends lw_object
{
    public function __construct($pid=false)
	{
    	$reg 	 		= lw_registry::getInstance();
    	$this->config 	= $reg->getEntry("config");
    	$this->reqVars  = $reg->getEntry("requestVars");
    	$this->db  		= $reg->getEntry("db");
    	$this->auth		= $reg->getEntry("auth");
		$this->pid 		= $pid;		

		$base = dirname(__FILE__)."/classes/";
		
		$this->errorTemplate = dirname(__FILE__)."/templates/plugin_error.tpl.html";
		require_once($base."base_mod.class.php");
		require_once($base."module.class.php");
		require_once($base."datahandler.class.php");
		require_once($base."mod_appointment_management.class.php");
		require_once($base."mod_category_management.class.php");
		require_once($base."mod_output.class.php");
		require_once($base."mod_template_management.class.php");
		require_once($base."mod_user_management.class.php");
		require_once($base."psc_auth.class.php");
		require_once($base."scheduler.class.php");
	}
	
	public function setParameter($param)
	{
		$parts = explode("&", $param);
		foreach($parts as $part)
		{
			$sub = explode("=", $part);
			$this->params[$sub[0]] = $sub[1];
		}
	}
		
	public function buildPageOutput()
	{
		try {
		    $mode = $this->params['mode'];
		    $css = true;
		    if ($this->params['css'] == "no") {
			    $css = false;
		    }
		    if ($mode != "admin" && $mode != "install") $mode="partner";
		    $psc = new scheduler($mode);
		    $psc->execute();
		    return $psc->getOutput($css);
		} 
		catch (Exception $e) {
			return $this->buildPluginError($e);
		}
	}
	
	function buildPluginError($exception)
	{
		$tpl = new lw_te($this->loadFile($this->errorTemplate));
		
		if ($this->auth->isGodmode()) {
			$tpl->setIfVar("admin");
		}
		$tpl->reg("plugin_error",$exception->getMessage());
		$tpl->reg("stacktrace",$this->buildStackTrace($exception->getTrace()));
		return $tpl->parse();
	}
	
	function buildStackTrace($trace)
	{
		$str="<p>An exception was thrown in:</p>";
		$str.="<ul>";
		foreach($trace as $t) {
			$file = $t['file'];
			$line = $t['line'];
			$function = $t['function'];
			$class = $t['class'];
			$lastslash = strrpos ($file , "/");
			$file = substr($file, $lastslash+1,strlen($file));
			$str.="<li>File <span class='lw_general_plugin_error_file'>$file</span> on line <span class='lw_general_plugin_error_line'>$line</span> in function <span class='lw_general_plugin_error_function'>$function</span></li>";
		}
		$str.="</ul>";
		return $str;
	}		
}
