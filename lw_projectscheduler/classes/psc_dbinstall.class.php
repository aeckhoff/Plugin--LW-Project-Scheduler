<?php

class psc_dbinstall extends lw_object 
{

	function __construct()
	{
		parent::__construct();
    	$this->conf	= lw_registry::getInstance()->getEntry("config");
    	$this->db 	= lw_registry::getInstance()->getEntry("db");
	}
	
	function install()
	{
		die("!!ausgeschaltet!!");
		$sql 		= array();
    	$prefix 	= "m38_";
		$sql 		= array_merge($sql, $this->buildTablesSQL($prefix));
		foreach($sql as $singlesql)
		{
			//$ok = $this->db->dbquery($singlesql);
			echo "<p>erstellt (".$ok."): ".$singlesql."</p>";
		}
		exit();
	}
	
	function buildTablesSQL($prefix)
	{
		$sql 		= array();
		
		/************************** PSC_APP **************************/
    	$builder = new lw_dbbuilder("psc_app");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addPrimaryField();
		$builder->addNumberField("employee_id", "20");
		$builder->addNumberField("app_id", "20");
		$builder->addTextField("dates", "255");
		$builder->addNumberField("status", "11");
		$builder->addNumberField("user_id", "20");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	
		
		/************************** PSC_APP_AGR **************************/
    	$builder = new lw_dbbuilder("psc_app_agr");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addPrimaryField();
		$builder->addTextField("title", "255");
		$builder->addNumberField("begin_date", "8");
		$builder->addNumberField("enddate", "8");
		$builder->addNumberField("finaldate", "8");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_APPOINTMENTS **************************/
    	$builder = new lw_dbbuilder("psc_appointments");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addPrimaryField();
		$builder->addTextField("nummer", "255");
		$builder->addTextField("title", "255");
		$builder->addNumberField("begindate", "8");
		$builder->addNumberField("enddate", "8");
		$builder->addTextField("location", "255");
		$builder->addTextField("responsible", "255");
		$builder->addTextField("description", "255");
		$builder->addNumberField("description_index", "11");
		$builder->addNumberField("descr_method", "1");
		$builder->addNumberField("status", "1");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_ARCHIVE **************************/
    	$builder = new lw_dbbuilder("psc_archive");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addPrimaryField();
		$builder->addTextField("nummer", "255");
		$builder->addTextField("title", "255");
		$builder->addNumberField("begindate", "8");
		$builder->addNumberField("enddate", "8");
		$builder->addTextField("location", "255");
		$builder->addTextField("responsible", "255");
		$builder->addTextField("description", "255");
		$builder->addNumberField("description_index", "11");
		$builder->addTextField("categories", "255");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_CATEGORIES **************************/
    	$builder = new lw_dbbuilder("psc_categories");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addPrimaryField();
		$builder->addTextField("name", "255");
		$builder->addTextField("status", "255");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_CATEGORY_AGR **************************/
    	$builder = new lw_dbbuilder("psc_category_agr");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addNumberField("category_id", "11");
		$builder->addNumberField("agr_id", "11");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_CATEGORY_APP **************************/
    	$builder = new lw_dbbuilder("psc_category_app");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addNumberField("category_id", "11");
		$builder->addNumberField("appointment_id", "11");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_CATEGORY_TPL **************************/
    	$builder = new lw_dbbuilder("psc_category_tpl");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addNumberField("category_id", "11");
		$builder->addNumberField("template_id", "11");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_EMPLOYEES **************************/
    	$builder = new lw_dbbuilder("psc_employees");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addPrimaryField();
		$builder->addNumberField("user_id", "20");
		$builder->addTextField("firstname", "255");
		$builder->addTextField("lastname", "255");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_TEMPLATES **************************/
    	$builder = new lw_dbbuilder("psc_templates");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addPrimaryField();
		$builder->addTextField("title", "255");
		$builder->addNumberField("entrycount", "11");
		$builder->addTextField("sorting", "255");
		$builder->addTextField("html", "3999");
		$builder->addNumberField("status", "11");
		$builder->addTextField("misc", "255");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		/************************** PSC_USERS **************************/
    	$builder = new lw_dbbuilder("psc_users");
		if ($prefix) { $builder->setPrefix($prefix); }
		$builder->addPrimaryField();
		$builder->addTextField("organisation", "255");
		$builder->addTextField("country", "255");
		$builder->addTextField("status", "255");
		$builder->addNumberField("user_id", "20");
		$sql = array_merge($sql, $builder->buildOracleTableDDL());	

		return $sql;
	}
}
