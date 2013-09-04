<?php

class lw_projectscheduleroutput extends lw_object
{
    public function __construct()
	{
		$base = dirname(__FILE__)."/../lw_projectscheduler/classes/";
		
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
		$template_id = $this->params['id'];
		if (!is_numeric($template_id)) {
		    return "invalid template id";
		}
		$psc_output = new mod_output("output", "show_output", false);
		return $psc_output->am_show_output($template_id);
	}
}
