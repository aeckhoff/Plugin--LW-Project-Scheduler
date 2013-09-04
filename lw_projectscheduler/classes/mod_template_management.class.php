<?php

class mod_template_management extends module
{
	public function am_show_templates()
	{
		$templates = $this->dh->getTemplates();
		$tpl = $this->autotemplate();
		$block = $tpl->getBlock("templates");
		$b_out = "";
		$i=0;
		foreach($templates as $template)
		{
			$btpl = new lw_te($block);
			$btpl->reg("title", $template['title']);
			$btpl->reg("id", $template['id']);
			$editurl = $this->buildURL(array("psc_module"=>"template_management","command"=>"edit_template", "tpl_id"=>$template['id']), false, "index.php");
			$btpl->reg("editurl", $editurl);
			$deleteurl = $this->buildURL(array("psc_module"=>"template_management","command"=>"delete_template","tpl_id"=>$template['id']), false, "index.php");
			$btpl->reg("deleteurl", $deleteurl);
			$previewurl = $this->buildURL(array("psc_module"=>"output","command"=>"show_output","tpl_id"=>$template['id'],"preview"=>"1"), false, "index.php");
			$btpl->reg("previewurl", $previewurl);
			$btpl->reg("rowclass", $this->getRowClass($i));
			$i++;
			$b_out.=$btpl->parse();
		}
		$tpl->putBlock("templates", $b_out);
		$addurl = $this->buildURL(array("psc_module"=>"template_management","command"=>"add_template"), false, "index.php");
		$tpl->reg("addurl", $addurl);
		return $tpl->parse();
	}
	
	public function am_add_template()
	{
		return $this->am_edit_template(true);
	}
	
	public function am_edit_template($new = false)
	{
		if ($new === true) {
			$tpl_id = -1;
		} 
		else {
			$tpl_id = $this->fGet->getAlNum("tpl_id");
		}
		
		$tpl = $this->getTemplate("template_management_edit_template");
		
		if ($tpl_id == -1) {
			$template = array();
			$template['id'] = "-1";
			$template['title'] = "untitled";
			$template['entrycount'] = "10";
			$template['sorting'] = "begindate:ASC";
			$template['html'] = "";
			
		} 
		else {
			$template = $this->dh->getTemplate($tpl_id);
		}
		
		$sorting = explode(":",$template['sorting']);
		
		$tpl->reg("title",$template['title']);
		$tpl->reg("id",$template['id']);
		$tpl->reg("html",htmlentities(stripslashes($template['html'])));
		
		$dd = "";
		for ($i=0;$i<30;$i++) {
			
			if ($template['entrycount'] == $i) {$selected="selected='selected'";} else {$selected="";}
			$dd.= "<option value='$i' $selected >$i</option>";
		}
		$tpl->reg("entrycount",$dd);
		
		$sortby = array("nummer"=>"Number","begindate"=>"Begin Date", "enddate"=>"End Date", "loc"=>"Location","responsible"=>"Responsible","title"=>"Title");
		
		$sb = "";
		foreach($sortby as $key=>$value) {
			if ($key == $sorting[0]) {$selected="selected='selected'";} else {$selected="";}
			$sb.="<option value='$key' $selected >$value</option>"; 
		}
		$tpl->reg("sortby",$sb);
		
		
		$sd = "";
		$as = "";
		$ds = "";
		if ($sorting[1] == "ASC") $as = "selected='selected'";
		if ($sorting[1] == "DESC") $ds = "selected='selected'";
		
		$sd = "<option value='ASC' $as >Ascending</option><option value='DESC' $ds >Descending</option>";
		$tpl->reg("sortdir",$sd);
		
		$actionurl = $this->buildURL(array("psc_module"=>"template_management","command"=>"save_template"), false, "index.php");
		$tpl->reg("actionurl",$actionurl);
		
		$cancelurl = $this->buildURL(array("psc_module"=>"template_management","command"=>"show_templates"), array("tpl_id"), "index.php");
		$tpl->reg("cancelurl",$cancelurl);
		
		$categories = $this->dh->getCategories();
		$selected_categories = $this->dh->getCategoriesForTemplateID($tpl_id);
		
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
		return $tpl->parse();
	}
	
	public function am_delete_template()
	{
		$tpl_id = $this->fGet->getRaw("tpl_id");
		$this->dh->deleteTemplate($tpl_id);
		$reloadurl = $this->buildURL(array("psc_module"=>"template_management","command"=>"show_templates"), array("tpl_id"), "index.php");
		$this->pageReload($reloadurl);
	}
	
	public function am_save_template()
	{
		$mqgpc = ini_get("magic_quotes_gpc");
		$mqgruntime = ini_get("magic_quotes_runtime");
		
		$id = 					$this->fPost->getRaw("tpl_id");
		$title = 				$this->lwStringClean($this->fPost->getRaw("title"));
		$entrycount = 			$this->fPost->getRaw("entrycount");
		$sortby = 				$this->fPost->getRaw("sortby");
		$sortdir = 				$this->fPost->getRaw("sortdir");
		
		if (($mqgruntime==1)||($mqgpc==1)) {
			$html = 				$this->fPost->getRaw("html");
		} 
		else {
			$html = 				addslashes($this->fPost->getRaw("html"));
		}
	
		$categories	 = 			$this->fPost->getRaw("categories");
		$sorting = $sortby.":".$sortdir;
		
		$this->dh->saveTemplate($id, $title, $entrycount, $sorting, $html, $categories);
		
		$backurl = $this->buildURL(array("psc_module"=>"template_management","command"=>"show_templates"), array("tpl_id"), "index.php");
		
		$this->pageReload($backurl);
	}
	
	protected function getDefaultCommand()
	{
		return "show_templates";
	}
}
