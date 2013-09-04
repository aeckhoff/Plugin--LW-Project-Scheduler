<?php

class mod_output extends module
{
	public function am_show_output($template_id = false)
	{
		if ($template_id == false) {
			$tpl_id = $this->fGet->getAlNum("tpl_id");
		} 
		else {
			$tpl_id = $template_id;
		}
		
		if ($this->fGet->getAlNum("preview") == 1) {
			$this->preview = true;
		} 
		else {
			$this->preview = false;
		}
		
		if (!is_numeric($tpl_id)) {
			return "[Project Scheduler: no template]";	
		}
		
		$template = $this->dh->getTemplate($tpl_id);
		
		if (empty($template)) {
			return "[Project Scheduler: no template]";	
		}
		
		$html = stripslashes($template['html']);
		
		$tpl = new lw_te($html);
		
		$tpl->setTags("%%-","-%%");
		
		$block = $tpl->getBlock("entries");
		$b_out = "";
		
		$entries = $this->dh->getAppointmentsForTemplateID($tpl_id, $template['sorting'],$template['entrycount']);
		
		$this->oddeven = "psc_odd";
		
		foreach($entries as $entry)
		{
			$btpl = new lw_te($block);
			$btpl->setTags("%%-","-%%");
			
			$btpl->reg("nummer", $entry['nummer']);
			$btpl->reg("title", $entry['title']);
			$btpl->reg("dates", $this->getReadableTimePeriod($entry['begindate'],$entry['enddate']));
			$btpl->reg("location", $entry['location']);
			$btpl->reg("responsible", $entry['responsible']);
			
			if ($entry['descr_method'] == 0) {
			    
				if ((is_numeric($entry['description_index']))&&($entry['description_index']!=0)) {
					$url = "index.php?index=".$entry['description_index'];
				} 
				else {
					$url = "";
				}
			} 
			else {
				$url = $entry['description'];
			}
			
			$btpl->reg("url", $url);
			
			
			$categories = $this->dh->getCategoriesForAppointmentID($entry['id']);
			$cats = "";
			foreach($categories as $category) {
				$cat = $this->dh->getCategory($category['category_id']);
				$cats.=$cat['name'].", "; 
			}
			$cats = substr($cats,0,-2);
			
			$btpl->reg("psc_oddeven", $this->oddeven);
			$this->swapOddEvent();
			
			
			$btpl->reg("categories", $cats);
			$btpl->reg("id", $entry['id']);
			

			$b_out.=$btpl->parse();
		}
		$tpl->putBlock("entries", $b_out);
		
		if ($this->preview) {
			die($tpl->parse());
		} 
		else {
			return $tpl->parse();
		}
	}
	
	function swapOddEvent()
	{
		if ($this->oddeven == "psc_odd") {
			$this->oddeven = "psc_even";
		} 
		else {
			$this->oddeven = "psc_odd";
		}
	}
	
	protected function getDefaultCommand()
	{
		return "show_output";
	}
}
