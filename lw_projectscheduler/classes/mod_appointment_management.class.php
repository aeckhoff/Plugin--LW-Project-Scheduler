<?php

class mod_appointment_management extends module
{
	public function am_show_appointments()
	{
		$appointments = $this->dh->getAppointments();
	
		$tpl = $this->autotemplate();
		
		$block = $tpl->getBlock("appointments");
		$b_out = "";
		$i=0;
		foreach($appointments as $appointment)
		{
			$btpl = new lw_te($block);
			$btpl->reg("title", $appointment['title']);
			$btpl->reg("date", $this->getReadableTimePeriod($appointment['begindate'],$appointment['enddate']));
			$btpl->reg("category", $this->getCategories($appointment['id']));
			$editurl = $this->buildURL(array("psc_module"=>"appointment_management","command"=>"edit_appointment", "app_id"=>$appointment['id']), false, "index.php");
			$btpl->reg("editurl", $editurl);
			$deleteurl = $this->buildURL(array("psc_module"=>"appointment_management","command"=>"delete_appointment", "app_id"=>$appointment['id']), false, "index.php");
			$btpl->reg("deleteurl", $deleteurl);
			$btpl->reg("rowclass", $this->getRowClass($i));
			$b_out.=$btpl->parse();
			$i++;
		}
		$tpl->putBlock("appointments", $b_out);	
		$addurl = $this->buildURL(array("psc_module"=>"appointment_management","command"=>"add_appointment"), false, "index.php");
		$tpl->reg("addurl", $addurl);
		return $tpl->parse();
	}
	
	protected function getCategories($id)
	{
		$cats = $this->dh->getCategoriesForAppointmentID($id);
		
		$cs = "";
		foreach($cats as $cat) {
			
			$c = $this->dh->getCategory($cat);
			$cs.=$c['name'].", ";
		}
		if (!empty($cs)) {
		    $cs = substr($cs,0,-2);
		}
		return $cs;
	}
	
	public function am_add_appointment()
	{
		return $this->am_edit_appointment(true);
	}
	
	public function am_edit_appointment($new = false, $error = false, $old_data = false)
	{
		if ($new === true) {
			$app_id = -1;
		} 
		else {
			$app_id = $this->fGet->getAlNum("app_id");
		}
		
		$tpl = $this->getTemplate("appointment_management_edit_appointment");
		
		if ($old_data === false) {
			if ($app_id == -1) {
				$appointment = array();
				$appointment['id'] = "-1";
				$appointment['nummer'] = "";
				$appointment['title'] = "";
				$appointment['begindate'] = "";
				$appointment['enddate'] = "";
				$appointment['location'] = "";
				$appointment['responsible'] = "";
				$appointment['description'] = "";
				$appointment['description_index'] = "";
				$appointment['descr_method'] = 0;
				$selected_categories = array();
			} 
			else {
				$appointment = $this->dh->getAppointment($app_id);
				$selected_categories = $this->dh->getCategoriesForAppointmentID($app_id);
			}
		} 
		else {
			$appointment['id'] 					= $old_data['id'];
			$appointment['nummer'] 				= $old_data['number'];
			$appointment['title'] 				= $old_data['title'];
			$appointment['begindate'] 			= $old_data['begindate'];
			$appointment['enddate'] 			= $old_data['enddate'];
			$appointment['location'] 			= $old_data['location'];
			$appointment['responsible'] 		= $old_data['responsible'];
			$appointment['description'] 		= $old_data['description'];
			$appointment['description_index'] 	= $old_data['description_index'];
			$appointment['descr_method']	 	= $old_data['descr_method'];
			$selected_categories = $old_data['categories'];
		}
		if ($appointment['id'] == -1) {
			$tpl->reg("id","n/a");
		} 
		else {
			$tpl->reg("id",$appointment['id']);
		}
		$tpl->reg("number",$appointment['nummer']);
		$tpl->reg("title",$appointment['title']);
		$tpl->reg("begindate",$appointment['begindate']);
		$tpl->reg("begindatechooser", "<input type='text' name='begindate' readonly='readonly' value='".$this->buildNewDate($appointment['begindate'])."'> [<a href='#' onClick=\"cal.select(document.forms['theform'].begindate,'anchor1','dd.MM.yyyy'); return false;\" name='anchor1' id='anchor1'>Date</a>] [<a href='#' onClick=\"document.forms['theform'].begindate.value='';\">Delete Date</a>]");
		$tpl->reg("enddatechooser", "<input type='text' name='enddate' readonly='readonly' value='".$this->buildNewDate($appointment['enddate'])."'> [<a href='#' onClick=\"cal.select(document.forms['theform'].enddate,'anchor2','dd.MM.yyyy'); return false;\" name='anchor2' id='anchor2'>Date</a>] [<a href='#' onClick=\"document.forms['theform'].enddate.value='';\">Delete Date</a>]");
		$tpl->reg("enddate",$appointment['enddate']);
		$tpl->reg("location",$appointment['location']);
		$tpl->reg("description",$appointment['description']);
		$tpl->reg("responsible",$appointment['responsible']);
		$tpl->reg("description_index",$appointment['description_index']);
		$tpl->reg("app_id",$appointment['id']);
		$actionurl = $this->buildURL(array("psc_module"=>"appointment_management","command"=>"save_appointment"), false, "index.php");
		$tpl->reg("actionurl",$actionurl);
		$backurl = $this->buildURL(array("psc_module"=>"appointment_management","command"=>"show_appointments"), false, "index.php");
		$tpl->reg("backurl",$backurl);
		
		$categories = $this->dh->getCategories();
		
		$block = $tpl->getBlock("categories");
		$b_out = "";
		
		foreach($categories as $category)
		{
			$btpl = new lw_te($block);
			
			$btpl->reg("category_name", $category['name']);
			$btpl->reg("cat_id", $category['id']);
			
			if (in_array($category['id'],$selected_categories)) {
				$checked = "checked='checked'";
			} 
			else {
				$checked = "";
			}
			
			$btpl->reg("checked", $checked);
			
			if ($error !== false) {
				$tpl->reg("error",$this->error);
			} 
			else {
				$tpl->reg("error","");
			}

			$b_out.=$btpl->parse();
		}
		
		$tpl->putBlock("categories", $b_out);
		
		if ($appointment['descr_method'] == 0) {
			$tpl->reg("is", "checked='checked'");
			$tpl->reg("us", "");
		} 
		else {
			$tpl->reg("us", "checked='checked'");
			$tpl->reg("is", "");
		}
		return ($tpl->parse());
	}
	
	public function am_save_appointment()
	{
		$id = 					$this->fPost->getRaw("app_id");
		$number = 				$this->fPost->getRaw("number");
		
		$begindate = 			$this->filterDate($this->fPost->getRaw("begindate"));
		$enddate = 				$this->filterDate($this->fPost->getRaw("enddate"));
		
		$title = 				$this->lwStringClean($this->fPost->getRaw("title"));
		$location = 			$this->lwStringClean($this->fPost->getRaw("location"));
		$responsible = 			$this->lwStringClean($this->fPost->getRaw("responsible"));
		$description = 			$this->lwStringClean($this->fPost->getRaw("description"));
		$description_index = 	$this->lwStringClean($this->fPost->getRaw("description_index"));
		$categories	 = 			$this->fPost->getRaw("categories");
		$descr_method	= 		$this->fPost->getRaw("dtype");
		
		$app_id = $this->fPost->getRaw("app_id");
		
		if (empty($begindate)&&empty($enddate)) {
			// No archive, but no error!
		} 
		else {
			
			if (empty($begindate)) {
				$error++;
				$this->error.="<p style='color:#ff0000'>Begin Date is not valid</p>";
			}
		
			if (empty($enddate)) {
			    $enddate = $begindate;
			}
			
			if(!$this->checkPeriod($begindate, $enddate)) {
				$error++;
				$this->error.="<p style='color:#ff0000'>Time Period (Begin Date, End Date) is not valid</p>";
			}
		}
				
		if ($error != 0) {
			if (empty($categories)) {
			    $categories = array();
			}
			$old_data = array();
			$old_data['id'] = $id;
			$old_data['number'] = $number;
			$old_data['begindate'] = $begindate;
			$old_data['enddate'] = $enddate;
			$old_data['location'] = $location;
			$old_data['title'] = $title;
			$old_data['responsible'] = $responsible;
			$old_data['description'] = $description;
			$old_data['description_index'] = $description_index;
			$old_data['descr_method'] = $descr_method;
			$old_data['categories'] = $categories;
			if ($id == -1) return $this->am_edit_appointment(true, true, $old_data);
			return $this->am_edit_appointment(false, true, $old_data);
		}
		
		$this->dh->saveAppointment($id, $number, $title, $begindate, $enddate, $location, $responsible, $descr_method, $description, $description_index, $categories);
		
		$backurl = $this->buildURL(array("psc_module"=>"appointment_management","command"=>"show_appointments"), false, "index.php");
		
		$this->pageReload($backurl);
	}	
	
	protected function checkPeriod($date1, $date2) 
	{
		if ($date2 < $date1) return false;
		
		$year  = substr($date1, 0, 4);
		$month = substr($date1, 4, 2);
		$day   = substr($date1, 6, 2);
		
		$max_day = date("Ymd",mktime(0,0,0, $month, $day+21, $year));
		
		if ($date2 > $max_day) return false;
		
		return true;
	}
	
	public function am_delete_appointment()
	{
		$app_id = $this->fGet->getInt("app_id");
		$this->dh->deleteAppointment($app_id);
		$reloadurl = $this->buildURL(array("psc_module"=>"appointment_management","command"=>"show_appointments"), array("app_id"), "index.php");
		$this->pageReload($reloadurl);
	}
	
	protected function getDefaultCommand()
	{
		return "show_appointments";
	}
}
