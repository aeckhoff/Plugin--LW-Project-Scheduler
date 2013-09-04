<?php

class mod_agreements extends module
{
	public function am_show_agreements()
	{
		$agreements = $this->dh->getAgreements();
		$tpl = $this->autotemplate();
		
		$block = $tpl->getBlock("agreements");
		$b_out = "";
		$i=0;
		
		if ($this->isAdmin) $tpl->setIfVar("admin");
		
		foreach($agreements as $agreement)
		{
			$fd = $agreement['finaldate'];
			$cd = date("Ymd");
			
			if (($fd >= $cd) || $this->isAdmin) {
				$btpl = new lw_te($block);
				if ($this->isAdmin) { 
					$btpl->setIfVar("admin");
				} 
				else {
					$btpl->setIfVar("user");
					$showurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"show_agreement", "agr_id"=>$agreement['id']), false, "index.php");
            	
					$btpl->reg("showurl", $showurl);
				}
				$btpl->reg("title", $agreement['title']);
				$btpl->reg("finaldate", $this->getReadableDate($agreement['finaldate']));
				$btpl->reg("timeperiod", $this->getReadableTimePeriod($agreement['begin_date'],$agreement['enddate']));
				$btpl->reg("categories", $this->getCategories($agreement['id']));
				$editurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"edit_agreement", "agr_id"=>$agreement['id']), false, "index.php");
				$btpl->reg("editurl", $editurl);
				$deleteurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"delete_agreement", "agr_id"=>$agreement['id']), false, "index.php");
				$btpl->reg("deleteurl", $deleteurl);
				$btpl->reg("rowclass", $this->getRowClass($i));
				$b_out.=$btpl->parse();
				$i++;
			}
		}
		$tpl->putBlock("agreements", $b_out);
		$addurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"add_agreement"), false, "index.php");
		$tpl->reg("addurl", $addurl);
		return $tpl->parse();
	}
	
	protected function getCategories($id)
	{
		$cats = $this->dh->getCategoriesForAgreementID($id);
		$cs = "";
		foreach($cats as $cat) {
			$c = $this->dh->getCategory($cat);
			$cs.=$c['name'].", ";
		}
		if (!empty($cs)) $cs = substr($cs,0,-2);
		return $cs;
	}
	
	public function am_delete_agreement()
	{
		$agr_id = $this->fGet->getRaw("agr_id");
		$this->dh->deleteAgreement($agr_id);
		$reloadurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"show_agreements"), array("agr_id"), "index.php");
		$this->pageReload($reloadurl);
	}
	
	public function am_add_agreement()
	{
		return $this->am_edit_agreement(true);
	}
	
	public function am_edit_agreement($new = false, $error = false, $old_data = false)
	{
		if ($new === true) {
			$agr_id = -1;
		} 
		else {
			$agr_id = $this->fGet->getAlNum("agr_id");
			if (empty($agr_id)) {
				$agr_id = $this->fPost->getAlNum("agr_id");
			}
		}
		
		$tpl = $this->getTemplate("agreements_edit_agreement");
		
		if ($old_data === false) {
			if ($agr_id == -1) {
				$agreement = array();
				$agreement['id'] = "-1";
				$agreement['title'] = "untitled";
				$agreement['begindate'] = "";
				$agreement['enddate'] = "";
				$agreement['finaldate'] = "";
				$selected_categories = array();
			} 
			else {
				$agreement = $this->dh->getAgreement($agr_id);
				$selected_categories = $this->dh->getCategoriesForAgreementID($agr_id);
			}
		} 
		else {
			$agreement = array();
			$agreement['id'] 		= $old_data['id'];
			$agreement['title'] 	= $old_data['title'];
			$agreement['begin_date'] = $old_data['begindate'];
			$agreement['enddate'] 	= $old_data['enddate'];
			$agreement['finaldate'] = $old_data['finaldate'];
			$selected_categories = $old_data['categories'];
		}
		
		if ($agreement['id'] == -1) {
			$tpl->reg("id","n/a");
		} else {
			$tpl->reg("id",$agreement['id']);
		}
		$tpl->reg("title",$agreement['title']);
		$tpl->reg("begindatechooser", "<input type='text' name='begindate' readonly='readonly' value='".$this->buildNewDate($agreement['begin_date'])."'> [<a href='#' onClick=\"cal.select(document.forms['theform'].begindate,'anchor1','dd.MM.yyyy'); return false;\" name='anchor1' id='anchor1'>Date</a>] [<a href='#' onClick=\"document.forms['theform'].begindate.value='';\">Delete Date</a>]");
		$tpl->reg("enddatechooser", "<input type='text' name='enddate' readonly='readonly' value='".$this->buildNewDate($agreement['enddate'])."'> [<a href='#' onClick=\"cal.select(document.forms['theform'].enddate,'anchor2','dd.MM.yyyy'); return false;\" name='anchor2' id='anchor2'>Date</a>] [<a href='#' onClick=\"document.forms['theform'].enddate.value='';\">Delete Date</a>]");
		$tpl->reg("finaldatechooser", "<input type='text' name='finaldate' readonly='readonly' value='".$this->buildNewDate($agreement['finaldate'])."'> [<a href='#' onClick=\"cal.select(document.forms['theform'].finaldate,'anchor3','dd.MM.yyyy'); return false;\" name='anchor3' id='anchor3'>Date</a>] [<a href='#' onClick=\"document.forms['theform'].finaldate.value='';\">Delete Date</a>]");
		$tpl->reg("agr_id",$agreement['id']);
		$actionurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"save_agreement"), false, "index.php");
		$tpl->reg("actionurl",$actionurl);
		$backurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"show_agreements"), false, "index.php");
		$tpl->reg("backurl",$backurl);
		$appointmenturl = $this->buildURL(array("psc_module"=>"agreements","command"=>"convert_to_appointment"), false, "index.php");
		$tpl->reg("appointmenturl",$appointmenturl);
		
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
			$b_out.=$btpl->parse();
		}
		
		$tpl->putBlock("categories", $b_out);
		
		if ($agr_id == -1) {
			$tpl->reg("app_disabled","disabled='disabled'");
			$tpl->reg("matrix_message","<p>&nbsp;<br/>Agreement Matrix will be shown here when the Agreement was saved the first time.<br/>&nbsp;<br/></p>");
		} 
		else {
			
			if ($agreement['finaldate']>date(Ymd)||empty($agreement['finaldate'])) {
				
			} 
			else {
				
			}
			$matrix = $this->getMatrix($agr_id,$agreement['begin_date'],$agreement['enddate']);
			
			$tpl->reg("matrix_message",$matrix);
		}
		if ($error !== false) {
			$tpl->reg("error",$this->error);
		} 
		else {
			$tpl->reg("error","");
		}
		return ($tpl->parse());
	}
	
	public function am_show_agreement()
	{
		$agr_id = $this->fGet->getAlNum("agr_id");
		$tpl = $this->getTemplate("agreements_show_agreement");
		$agreement = $this->dh->getAgreement($agr_id);
		$tpl->reg("title",$agreement['title']);
		$tpl->reg("finaldate", $this->getReadableDate($agreement['finaldate']));
		$tpl->reg("agr_id",$agreement['id']);
		$matrix = $this->getMatrix($agr_id,$agreement['begin_date'],$agreement['enddate']);
		$tpl->reg("matrix",$matrix);
		$actionurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"save_agreement"), false, "index.php");
		$tpl->reg("actionurl",$actionurl);
		$daycount = count($this->getNumberOfDays($agreement['begin_date'],$agreement['enddate']));
		$empcount = count($this->dh->getAllEmployees());
		$tpl->reg("daycount",$daycount);
		$tpl->reg("empcount",$empcount);
		$backurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"show_agreements"), false, "index.php");
		$tpl->reg("backurl",$backurl);
		return ($tpl->parse());
	}
	
	protected function getMatrix($agr_id, $begindate, $enddate)
	{
		$tpl = $this->getTemplate("matrix");
		
		if ($this->isAdmin) {
			$tpl->setIfVar("admin");
		} 
		else {
			$tpl->setIfVar("user");
		}
		$days = $this->getNumberOfDays($begindate, $enddate);
		if ($days == false) return "";
		$tpl->reg("daysc", count($days));
		
		$block = $tpl->getBlock("days");
		$b_out = "";
		foreach($days as $day)
		{
			$btpl = new lw_te($block);
			
			$btpl->reg("day", $day[0]);
			
			if ($day[1] == "Sat" || $day[1] == "Sun") {
				$btpl->reg("dayclass", "nwday");
			} 
			else {
				$btpl->reg("dayclass", "wday");
			}

			$btpl->reg("weekday", $day[1]);

			$b_out.=$btpl->parse();
		}
		$tpl->putBlock("days", $b_out);
		
		$employees = $this->dh->getAllEmployees();
		
		$empcount = count($employees);
		$daycount = count($days);
		
		$block = $tpl->getBlock("employees");
		$b_out = "";
		$ecounter = 0;
		
		$summe = array();
		
		for ($k=0;$k<count($days);$k++) {
			$summe[$k] = 0;
		}
		$user_id = $this->auth->getUserdata("id");
		
		foreach($employees as $employee)
		{
			$ecounter++;
			$dates = $this->dh->getDates($agr_id,$employee['id']);
			$da = explode(":",$dates['dates']);
			$btpl = new lw_te($block);
			$btpl->reg("organisation", $employee['organisation']);
			$btpl->reg("country", $employee['country']);
			$btpl->reg("employee", $employee['firstname']." ".$employee['lastname']);
			$btpl->reg("empid", $employee['id']);
			
			if ($employee['user_id'] == $user_id) {
				$btpl->setIfVar("caneditemp");
			} 
			$block2 = $btpl->getBlock("empdays");
			$b_out2 = "";
			$i=0;
			
			foreach($days as $day)
			{
				$btpl2 = new lw_te($block2);
				$user_id = $this->auth->getUserdata("id");
				
				if ($employee['user_id'] == $user_id) {
					$btpl2->setIfVar("canedit");
				} 
				else {
					$btpl2->setIfVar("cannotedit");
				}
				
				if ($day[1] == "Sat" || $day[1] == "Sun") {
					$btpl2->reg("dayclass", "nwday");
				} 
				else {
					$btpl2->reg("dayclass", "wday");
				}
				
				$btpl2->reg("empday", "employee_".$employee['id']."_".$i);
				$btpl2->reg("empdayid", "emp_".$ecounter."_".$i);
				$btpl2->reg("ehv", "employee_".$employee['id']."_".$i."_value");
				$btpl2->reg("ehvid", "emp_".$ecounter."_".$i."_value");
				
				if(empty($da[$i])) {
					$da[$i] = "0";
				}
				
				$btpl2->reg("ehvv", $da[$i]);
				
				if ($da[$i] == 1) {
					$btpl2->reg("class", "okay");
					$summe[$i] = $summe[$i] + 1;
				} 
				else if ($da[$i] == -1) {
					$btpl2->reg("class", "not_okay");
					$summe[$i] = $summe[$i] - 1;
				} 
				else {
					$btpl2->reg("class", "unknown");
				}
				
				$btpl2->reg("empcount", $empcount);
				$btpl2->reg("daycount", $daycount);
				
				$i++;

				$b_out2.=$btpl2->parse();
			}
			$btpl->putBlock("empdays", $b_out2);

			$b_out.=$btpl->parse();
		}
		
		$tpl->putBlock("employees", $b_out);
		
		$block = $tpl->getBlock("sumdays");
		$b_out = "";
		$i=0;
		foreach($days as $day)
		{
			$btpl = new lw_te($block);
			
			if ($day[1] == "Sat" || $day[1] == "Sun") {
				$dayclass = "nwday";
			} else {
				$dayclass = "wday";
			}
			
			$sum = $summe[$i];
			//echo $sum."<br>\n";
			$sumclass = "unknown";
			if ($sum > 0) {
			    $sumclass = "okay";
			}
			if ($sum < 0) {
			    $sumclass = "not_okay";
			}
			
			$btpl->reg("sumclass", $sumclass." ".$dayclass);
			
			$btpl->reg("sumday", "sum"."_".$i);
			$i++;

			$b_out.=$btpl->parse();
		}
		$tpl->putBlock("sumdays", $b_out);
		
		$block = $tpl->getBlock("startdays");
		$b_out = "";
		$i=0;
		foreach($days as $day)
		{
			$btpl = new lw_te($block);
			
			$btpl->reg("startday", "startday");
			$btpl->reg("value", $i);
			$i++;

			$b_out.=$btpl->parse();
		}
		$tpl->putBlock("startdays", $b_out);
		
		$block = $tpl->getBlock("enddays");
		$b_out = "";
		$i=0;
		foreach($days as $day)
		{
			$btpl = new lw_te($block);
			$btpl->reg("endday", "endday");
			$btpl->reg("value", $i);
			$i++;
			$b_out.=$btpl->parse();
		}
		$tpl->putBlock("enddays", $b_out);
		return $tpl->parse();
	}
	
	protected function getNumberOfDays($date1,$date2)
	{
		if ($date2 < $date1) {
    		return false;
		}
		if (empty($date1)||empty($date2)) {
		    return false;
		}
		if ( ($date1 == 0)||($date2 == 0)) {
		    return false;
		}
		
		$year  = substr($date1, 0, 4);
		$month = substr($date1, 4, 2);
		$day   = substr($date1, 6, 2);
		
		$days = array();
		
		$next_day = $date1;
		$d = 1;
		while(($next_day != $date2)&&($d<21)) {
			
			$year  = substr($next_day, 0, 4);
			$month = substr($next_day, 4, 2);
			$day   = substr($next_day, 6, 2);
			
			$weekday = date("D",mktime(0,0,0, $month, $day, $year));
			
			$next_day = date("Ymd",mktime(0,0,0, $month, $day+1, $year));
			
			$days[] = array($day,$weekday);
			
			$d++;
		}
		
		$year  = substr($date2, 0, 4);
		$month = substr($date2, 4, 2);
		$day   = substr($date2, 6, 2);
		$weekday = date("D",mktime(0,0,0, $month, $day, $year));
		$days[] = array($day,$weekday);
		
		return $days;
	}
	
	public function convert_to_appointment()
	{
		$error = 0;
		$id = 					$this->fPost->getRaw("agr_id");
		$agr_id = $id;
		
		$begindate = 			$this->filterDate($this->fPost->getRaw("begindate"));
		$enddate = 				$this->filterDate($this->fPost->getRaw("enddate"));
		$finaldate = 			$this->filterDate($this->fPost->getRaw("finaldate"));
		
		$startday = 			$this->fPost->getRaw("startday");
		$endday = 				$this->fPost->getRaw("endday");
	
		if(empty($startday)&&!is_numeric($startday)) {
			$error++;
			$this->error.="<p style='color:#ff0000'>Start Date is empty</p>";
		}
		
		if(empty($endday)&&!is_numeric($endday)) {
			$error++;
			$this->error.="<p style='color:#ff0000'>End Date is empty</p>";
		}
		
		$year  = substr($begindate, 0, 4);
		$month = substr($begindate, 4, 2);
		$day   = substr($begindate, 6, 2);
		
		$begin_date = date("Ymd",mktime(0,0,0, $month, $day+$startday, $year));
		$end_date = date("Ymd",mktime(0,0,0, $month, $day+$endday, $year));
		
		$title = 				$this->lwStringClean($this->fPost->getRaw("title"));
		$categories	 = 			$this->fPost->getRaw("categories");
		
		if(!$this->checkPeriod($begin_date, $end_date)) {
			$error++;
			$this->error.="<p style='color:#ff0000'>Time Period (Begin Date, End Date) is not valid</p>";
		}
				
		if ($error != 0) {
			if ($id == -1) return $this->am_edit_agreement(true, true);
			return $this->am_edit_agreement(false, true);
		}
		
		$this->dh->saveAppointment(-1, "", $title, $begin_date, $end_date, "", "", 0, "", "", $categories);
		
		$this->dh->deleteAgreement($agr_id);
		
		$backurl = $this->buildURL(array("psc_module"=>"appointment_management","command"=>"show_appointments"), false, "index.php");
		
		$this->pageReload($backurl);
	}
	
	public function am_save_agreement()
	{
		$id = $this->fPost->getRaw("agr_id");
		if (!$this->isAdmin) {
			if ($this->fPost->getRaw("matrix") == 1) $this->saveMatrix($id);
			$backurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"show_agreement", "agr_id"=>$id), false, "index.php");
			$this->pageReload($backurl);
			die();
		}
		
		if ($this->fPost->getRaw("convert") == 1) {
			return $this->convert_to_appointment();
		}
		$error = 0;
		$begindate = 			$this->filterDate($this->fPost->getRaw("begindate"));
		$enddate = 				$this->filterDate($this->fPost->getRaw("enddate"));
		$finaldate = 			$this->filterDate($this->fPost->getRaw("finaldate"));
		$title = 				$this->lwStringClean($this->fPost->getRaw("title"));
		$categories	 = 			$this->fPost->getRaw("categories");
		
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
		
		if ($error != 0) {
			if (empty($categories)) {
			    $categories = array();
			}
			$old_data = array();
			$old_data['id'] = $id;
			$old_data['title'] = $title;
			$old_data['begindate'] = $begindate;
			$old_data['enddate'] = $enddate;
			$old_data['finaldate'] = $finaldate;
			$old_data['categories'] = $categories;
			if ($id == -1) {
			    return $this->am_edit_agreement(true, true, $old_data);
			}
			return $this->am_edit_agreement(false, true, $old_data);
		}
		
		$app_id = $this->fPost->getRaw("app_id");
		$this->dh->saveAgreement($id, $title, $begindate, $enddate, $finaldate, $categories);
		if ($this->fPost->getRaw("matrix") == 1) $this->saveMatrix($id);
		$backurl = $this->buildURL(array("psc_module"=>"agreements","command"=>"show_agreements"), false, "index.php");
		$this->pageReload($backurl);
	}
	
	protected function saveMatrix($agr_id)
	{
		$employees = $this->fPost->getRaw("employeeid");
		$days = $this->fPost->getRaw("daysc");
		foreach($employees as $employee) {
			$vals = "";
			for($i=0;$i<$days;$i++) {
				$name = "employee_".$employee."_".$i."_value";
				$val = $this->fPost->getRaw($name);
				$vals.=$val.":";
			}
			$this->dh->saveDates($agr_id,$employee,$vals);
		}
	}
	
	protected function checkPeriod($date1, $date2) 
	{
		if ($date2 < $date1) {
		    return false;
		}
		
		$year  = substr($date1, 0, 4);
		$month = substr($date1, 4, 2);
		$day   = substr($date1, 6, 2);
		
		$max_day = date("Ymd",mktime(0,0,0, $month, $day+21, $year));
		
		if ($date2 > $max_day) {
	        return false;
	    }
		
		return true;
	}
	
	protected function getDefaultCommand()
	{
		return "show_agreements";
	}
}
