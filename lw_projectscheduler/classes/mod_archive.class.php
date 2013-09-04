<?php

class mod_archive extends module
{
	public function am_csv_download()
	{
		if(!$this->isAdmin) {
		    die();
		}
		
		$archivedAppointments = $this->dh->getArchivedAppointments();
		$csv = "";
		
		foreach($archivedAppointments as $appointment)
		{
			$appointment['description'] = str_replace("&#45;","-",$appointment['description']);
			$line = "";
			$line.= "\"".$appointment['title']."\",";
			$line.= "\"".$this->getReadableTimePeriod($appointment['begindate'],$appointment['enddate'])."\",";
			$line.= "\"".$appointment['nummer']."\",";
			$line.= "\"".$this->getCategories($appointment['id'])."\",";
			$line.= "\"".$appointment['location']."\",";
			$line.= "\"".$appointment['responsible']."\",";
			$line.= "\"".$appointment['description']."\"";
			$csv.=$line."\n";
		}
		$filename = "PSC_CSV_".date("Y-m-d").".csv";
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-disposition: attachment; filename=\"".$filename."\"");
		header("Content-type: application/csv-file"); 
		header("Content-length: ".strlen($csv)); 
		header("Expires: 0"); 
		die($csv);
	}
	
	public function am_show_archive()
	{
		if(!$this->isAdmin) {
		    return "";
		}
		
		$tpl = $this->autotemplate();
		$archivedAppointments = $this->dh->getArchivedAppointments();
		
		$block = $tpl->getBlock("appointments");
		$b_out = "";
		$i=0;
		foreach($archivedAppointments as $appointment)
		{
			$btpl = new lw_te($block);
			$btpl->reg("id", $appointment['id']);
			if ($appointment['status'] == 1) {
				$btpl->reg("title", "<i>".$appointment['title']."</i>");
			} 
			else {
				$btpl->reg("title", $appointment['title']);
			}
			$btpl->reg("dates", $this->getReadableTimePeriod($appointment['begindate'],$appointment['enddate']));
			$btpl->reg("number", $appointment['nummer']);
			$btpl->reg("categories", $this->getCategories($appointment['id']));
			$btpl->reg("location", $appointment['location']);
			$btpl->reg("responsible", $appointment['responsible']);
			$btpl->reg("description", $appointment['description']);
			$btpl->reg("rowclass", $this->getRowClass($i));
			$i++;
			$b_out.=$btpl->parse();
		}
		$tpl->putBlock("appointments", $b_out);
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
		if (!empty($cs)) $cs = substr($cs,0,-2);
		return $cs;
	}
	
	
	protected function getDefaultCommand()
	{
		return "csv_download";
	}
}
