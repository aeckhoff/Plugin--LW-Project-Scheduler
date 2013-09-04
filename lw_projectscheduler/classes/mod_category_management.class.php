<?php

class mod_category_management extends module
{
	public function am_show_categories()
	{
		$categories = $this->dh->getCategories();
	
		$tpl = $this->autotemplate();
		$block = $tpl->getBlock("categories");
		$b_out = "";
		$i=0;
		foreach($categories as $category)
		{
			$btpl = new lw_te($block);
			$btpl->reg("category", $category['name']);
			$editurl = $this->buildURL(array("psc_module"=>"category_management","command"=>"edit_category", "cat_id"=>$category['id']), false, "index.php");
			$btpl->reg("editurl", $editurl);
			$used = $this->dh->categoryIsUsed($category['id']);
			
			if (!$used) {
				$btpl->setIfVar("candelete");
				$deleteurl = $this->buildURL(array("psc_module"=>"category_management","command"=>"delete_category","cat_id"=>$category['id']), false, "index.php");
			
				$btpl->reg("deleteurl", $deleteurl);
			} 
			else {
				$btpl->setIfVar("used");
			}
        	$btpl->reg("rowclass", $this->getRowClass($i));
			$b_out.=$btpl->parse();
			$i++;
		}
		
		$tpl->putBlock("categories", $b_out);	
		
		$addurl = $this->buildURL(array("psc_module"=>"category_management","command"=>"add_category"), false, "index.php");
		
		$tpl->reg("addurl", $addurl);
		
		return $tpl->parse();
	}
	
	public function am_add_category()
	{
		$this->am_edit_category(true);
	}
	
	public function am_edit_category($new = false)
	{
		if ($new === true) {
			$cat_id = -1;
		} 
		else {
			$cat_id = $this->fGet->getAlNum("cat_id");
		}
		
		$tpl = $this->getTemplate("category_management_edit_category");
		
		if ($cat_id == -1) {
			$category = array();
			$category['id'] = "-1";
			$category['name'] = "";
		} 
		else {
			$category = $this->dh->getCategory($cat_id);
		}
		$tpl->reg("name",$category['name']);
		$tpl->reg("cat_id",$category['id']);
		$actionurl = $this->buildURL(array("psc_module"=>"category_management","command"=>"save_category"), false, "index.php");
		$tpl->reg("actionurl",$actionurl);
		die($tpl->parse());
	}
	
	public function am_save_category()
	{
		$name = $this->lwStringClean($this->fPost->getRaw("name"));
		$cat_id = $this->fPost->getRaw("cat_id");
	
		$this->dh->saveCategory($cat_id,$name);
		$reloadurl = $this->buildURL(array("psc_module"=>"category_management","command"=>"show_categories"), array("emp_id"), "index.php");
		$tpl = $this->getTemplate("popup_close");
		$tpl->reg("reloadurl",$reloadurl."&time=".date("YmdHis"));
		die($tpl->parse());
	}
	
	public function am_delete_category()
	{
		$cat_id = $this->fGet->getRaw("cat_id");
		$this->dh->deleteCategory($cat_id);
		$reloadurl = $this->buildURL(array("psc_module"=>"category_management","command"=>"show_categories"), array("emp_id"), "index.php");
		$this->pageReload($reloadurl);
	}
	
	protected function getDefaultCommand()
	{
		return "show_categories";
	}
}
