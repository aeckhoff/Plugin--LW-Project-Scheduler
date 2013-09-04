<?php

class psc_datahandler extends lw_object
{
	function __construct()
	{
		$this->config = lw_registry::getInstance()->getEntry("config");
		$this->db = lw_registry::getInstance()->getEntry("db");
		$this->logPath = $this->config['path']['web_resource']."lw_logs/pscLogs/";
		
		$this->userTable = $this->db->gt('lw_in_user');
		$this->pscUserTable = $this->config['dbt']['psc_users'];
		$this->pscEmployeesTable = $this->config['dbt']['psc_employees'];
		$this->pscCategoriesTable = $this->config['dbt']['psc_categories'];
		$this->pscAppointmentTable = $this->config['dbt']['psc_appointments'];
		$this->pscCategoryAppTable = $this->config['dbt']['psc_category_app'];
		$this->pscCategoryAgrTable = $this->config['dbt']['psc_category_agr'];
		$this->pscCategoryTemplateTable = $this->config['dbt']['psc_category_template'];
		$this->pscTemplatesTable = $this->config['dbt']['psc_templates'];
		$this->pscAppAgrTable = $this->config['dbt']['psc_app_agr'];
		$this->pscAppTable = $this->config['dbt']['psc_app'];
	}
	
	function log($data)
	{
		$date = date("Ym");
		$file = $this->logPath.$date."_scheduler_log.txt";
		$data = date("Y-m-d H:i:s")." : ".$data."\n";
		$this->appendFile($file, $data);
	}
	
	function initDB() {}
	
	function getAllUsers()
	{
		$sql = "SELECT * FROM ".$this->userTable;
		$results = $this->db->select($sql);
		
		for ($i=0; $i < count($results); $i++) { 
			$id = $results[$i]['id'];
			$sql = "SELECT * FROM ".$this->pscUserTable." WHERE user_id = ".$id;
			
			$r = $this->db->select1($sql);
			if (empty($r)) {
				$results[$i]['organisation'] = "";
				$results[$i]['country'] = "";
				$results[$i]['status'] = "inactive";
			} 
			else {
				$results[$i]['organisation'] = $r['organisation'];
				$results[$i]['country'] = $r['country']; 
				$results[$i]['status'] = $r['status'];
			}
		}
		return $results;
	}
	
	public function getUserData($user_id)
	{
		$this->checkNumeric($user_id);

		$sql = "SELECT * FROM ".$this->userTable." WHERE id=".$user_id;
		$result = $this->db->select1($sql);

		$sql = "SELECT * FROM ".$this->pscUserTable." WHERE user_id = ".$user_id;
		$r = $this->db->select1($sql);
		
		if (empty($r)) {
			$result['organisation'] = "";
			$result['country'] = "";
			$result['status'] = "inactive";
		} 
		else {    
			$result['organisation'] = $r['organisation'];
			$result['country'] = $r['country']; 
			$result['status'] = $r['status'];
		}
		return $result;
	}
	
	public function getEmployees($user_id)
	{
		$this->checkNumeric($user_id);
		$sql = "SELECT * FROM ".$this->pscEmployeesTable." WHERE user_id = ".$user_id;
		return $this->db->select($sql);
	}

	public function getAllEmployees()
	{
		$sql = "SELECT ".$this->pscEmployeesTable.".*, ".$this->userTable.".name AS username, ".$this->pscUserTable.".organisation, ".$this->pscUserTable.".country FROM ".$this->pscEmployeesTable.", ".$this->userTable.", ".$this->pscUserTable." WHERE ".$this->pscEmployeesTable.".user_id = ".$this->userTable.".id AND ".$this->pscUserTable.".user_id = ".$this->userTable.".id ORDER BY ".$this->userTable.".name";
		return $this->db->select($sql);
	}
	
	public function getEmployee($emp_id)
	{
		$this->checkNumeric($emp_id);
		$sql = "SELECT * FROM ".$this->pscEmployeesTable." WHERE id = ".$emp_id;
		return $this->db->select1($sql);
	}
	
	public function saveEmployee($user_id, $emp_id, $firstname, $lastname)
	{
		$this->checkNumeric($user_id);
		$this->checkNumeric($emp_id);
		
		if ($emp_id == -1) {
			$sql = "INSERT INTO ".$this->pscEmployeesTable." (firstname,lastname, user_id) VALUES ('$firstname','$lastname', $user_id)";
			$this->log($sql);
			$id = $this->db->dbinsert($sql,$this->pscEmployeesTable);
		} 
		else {
			$sql = "UPDATE ".$this->pscEmployeesTable." SET firstname = '$firstname', lastname = '$lastname' WHERE id = ".$emp_id;
			$this->log($sql);
			$ok = $this->db->dbquery($sql);
		}
	}
	
	public function deleteEmployee($user_id, $emp_id)
	{
		$this->checkNumeric($user_id);
		$this->checkNumeric($emp_id);
		
		$sql = "DELETE FROM ".$this->pscEmployeesTable." WHERE user_id = ".$user_id." AND id = ".$emp_id;
		$ok = $this->db->dbquery($sql);
		$this->log($sql);
	}
	
	public function saveUser($user_id, $org, $country, $status,$admin)
	{
		$this->checkNumeric($user_id);
		
		$sql = "SELECT id FROM ".$this->pscUserTable." WHERE user_id = ".$user_id;
		$r = $this->db->select1($sql);
		
		if ($admin) {
			if (empty($r)) {
				$sql = "INSERT INTO ".$this->pscUserTable." (user_id,organisation, status, country) VALUES ($user_id,'$org', '$status', '$country')";
				$this->log($sql);
				return $this->db->dbinsert($sql,$this->pscUserTable);
			} 
			else {
				$sql = "UPDATE ".$this->pscUserTable." SET organisation = '$org', status = '$status', country = '$country' WHERE id = ".$r['id'];
				$this->log($sql);
				return $this->db->dbquery($sql);
			}
		} 
		else {
			if (empty($r)) {
				$sql = "INSERT INTO ".$this->pscUserTable." (user_id,organisation,country) VALUES ($user_id,'$org','$country')";
				$this->log($sql);
				return $this->db->dbinsert($sql,$this->pscUserTable);
			} 
			else {
				$sql = "UPDATE ".$this->pscUserTable." SET organisation = '$org', country = '$country' WHERE id = ".$r['id'];
				$this->log($sql);
				return $this->db->dbquery($sql);
			}
		}
	}
	
	public function getCategories()
	{
		$sql = "SELECT * FROM ".$this->pscCategoriesTable;
		return $this->db->select($sql);
	}
	
	public function getCategory($cat_id)
	{
		$this->checkNumeric($cat_id);
		$sql = "SELECT * FROM ".$this->pscCategoriesTable." WHERE id = $cat_id";
		return $this->db->select1($sql);
	}
	
	public function saveCategory($cat_id, $name)
	{
		$this->checkNumeric($cat_id);
		
		if ($cat_id == -1) {
			$sql = "INSERT INTO ".$this->pscCategoriesTable." (name) VALUES ('$name')";
			$this->log($sql);
			return $this->db->dbinsert($sql,$this->pscCategoriesTable);
		} 
		else {
			$sql = "UPDATE ".$this->pscCategoriesTable." SET name='$name' WHERE id = ".$cat_id;
			$this->log($sql);
			return $this->db->dbquery($sql);
		}
	}
	
	public function getAppointments()
	{
		$sql = "SELECT * FROM ".$this->pscAppointmentTable." WHERE status IS NULL OR status != 1";
		$results = $this->db->select($sql);
		
		//$sql = "SELECT * FROM ".$this->pscAppointmentTable;
		//$r = $this->db->select($sql);
	
		return $results;
	}
	
	public function moveOldToArchive($currentDate)
	{
		$this->checkNumeric($currentDate);
		
		$sql = "UPDATE ".$this->pscAppointmentTable." SET status = 1 WHERE enddate < $currentDate AND enddate != 0 AND enddate IS NOT NULL";
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
	
		//$sql = "SELECT * FROM ".$this->pscAppointmentTable." ORDER BY id";
		//$results = $this->db->select($sql);
		return $ok;
	}
	
	public function getArchivedAppointments()
	{
		$sql = "SELECT * FROM ".$this->pscAppointmentTable." ORDER BY begindate ASC";
		return $this->db->select($sql);
	}
	
	public function getAppointment($app_id)
	{
		$this->checkNumeric($app_id);
		
		$sql = "SELECT * FROM ".$this->pscAppointmentTable." WHERE id = ".$app_id;
		return $this->db->select1($sql);
	}
	
	public function categoryIsUsed($cat_id)
	{
		$sql = "SELECT category_id FROM ".$this->pscCategoryAppTable." WHERE category_id = $cat_id";
		$results = $this->db->select($sql);
		
		if (!empty($results)) {
		    return true;
		}
		
		$sql = "SELECT category_id FROM ".$this->pscCategoryAgrTable." WHERE category_id = $cat_id";
		$results = $this->db->select($sql);
		
		if (!empty($results)) {
		    return true;
		}
		return false;
	}
	
	public function getCategoryApp($app_id)
	{
		$this->checkNumeric($app_id);
		$sql = "SELECT * FROM ".$this->pscCategoryAppTable." WHERE appointment_id = ".$app_id;
		return $this->db->select($sql);
	}

	public function saveAppointment($id, $number, $title, $begindate, $enddate, $location, $responsible, $descr_method, $description, $description_index, $categories)
	{
		$this->checkNumeric($id);
		
		if (empty($enddate)) {
		    $enddate = "0";
		}
		
		if ($id == -1) {
			$sql = "INSERT INTO ".$this->pscAppointmentTable." (nummer, title, begindate, enddate, location, responsible, descr_method, description, description_index) ";
			$sql.= "VALUES ('$number','$title',$begindate, $enddate, '$location', '$responsible',$descr_method,'$description', '$description_index')";
			$this->log($sql);
			$id = $this->db->dbinsert($sql,$this->pscAppointmentTable);
		} 
		else {
			$sql = "UPDATE ".$this->pscAppointmentTable." SET ";
			$sql.= "nummer = '$number', title='$title', begindate=$begindate, enddate = $enddate, location='$location', responsible='$responsible', ";
			$sql.= "description = '$description', descr_method = $descr_method, description_index = '$description_index' WHERE id = $id";
			$this->log($sql);
			$ok = $this->db->dbquery($sql);
		}
		// Categories
		$sql = "DELETE FROM ".$this->pscCategoryAppTable." WHERE appointment_id = $id";
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
		
		foreach($categories as $category)
		{
			$sql = "INSERT INTO ".$this->pscCategoryAppTable." (appointment_id, category_id) VALUES ($id, $category)";
			$this->log($sql);
			$ok = $this->db->dbinsert($sql, $this->pscCategoryAppTable);
		}
	}
	
	public function deleteAppointment($app_id)
	{
		$this->checkNumeric($app_id);
		
		$sql = "DELETE FROM ".$this->pscAppointmentTable." WHERE id = $app_id";
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
		
		$sql = "DELETE FROM ".$this->pscCategoryAppTable." WHERE appointment_id = ".$app_id;
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
	}
	
	public function saveCategoryApp($categories, $app_id)
	{
		$this->checkNumeric($app_id);
		
		$sql = "DELETE FROM ".$this->pscCategoryAppTable." WHERE appointment_id = ".$app_id;
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
		
		foreach($categories as $category) {
			$sql = "INSERT INTO ".$this->pscCategoryAppTable." (category_id, appointment_id) VALUES (".$category['id'].", $app_id)";
			$this->log($sql);
			$this->db->dbinsert($sql, $this->pscCategoryAppTable);
		}
	}

	public function deleteCategory($cat_id)
	{
		$this->checkNumeric($cat_id);
		
		$sql = "DELETE FROM ".$this->pscCategoriesTable." WHERE id = $cat_id";
		$this->log($sql);
		$this->db->dbquery($sql);
		
		$sql = "DELETE FROM ".$this->pscCategoryAppTable." WHERE category_id = ".$cat_id;
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
		
		$sql = "DELETE FROM ".$this->pscCategoryTemplateTable." WHERE category_id = $cat_id";
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
	}
	
	public function getTemplates()
	{
		$sql = "SELECT * FROM ".$this->pscTemplatesTable." ORDER BY id ASC";
		return $this->db->select($sql);
	}
	
	public function getTemplate($id)
	{
		$this->checkNumeric($id);
		$sql = "SELECT * FROM ".$this->pscTemplatesTable." WHERE id = $id";
		return $this->db->select1($sql);
	}
	
	public function deleteTemplate($id)
	{
		$this->checkNumeric($id);

		$sql = "DELETE FROM ".$this->pscTemplatesTable." WHERE id = $id";
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
		
		$sql = "DELETE FROM ".$this->pscCategoryTemplateTable." WHERE template_id = $id";
		$this->log($sql);
		return $this->db->dbquery($sql);
	}
	
	public function saveTemplate($id, $title, $entrycount, $sorting, $html, $categories)
	{
		$this->checkNumeric($id);
		if ($id == -1) {
			$sql = "INSERT INTO ".$this->pscTemplatesTable." (title, entrycount, sorting, html) VALUES ('$title', $entrycount, '$sorting', '$html')";
			$this->log($sql);
			$id = $this->db->dbinsert($sql, $this->pscTemplatesTable);
		} 
		else {
			$sql = "UPDATE ".$this->pscTemplatesTable." SET title = '$title', entrycount=$entrycount, sorting='$sorting', html='$html' WHERE id = $id";
			$this->log($sql);
			$ok = $this->db->dbquery($sql);
		}
		
		// Categories
		$sql = "DELETE FROM ".$this->pscCategoryTemplateTable." WHERE template_id = $id";
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
		
		foreach($categories as $category)
		{
			$sql = "INSERT INTO ".$this->pscCategoryTemplateTable." (template_id, category_id) VALUES ($id, $category)";
			$this->log($sql);
			$ok = $this->db->dbquery($sql);
		}
	}
	
	public function getCategoriesForTemplateID($tid)
	{
		$this->checkNumeric($tid);
		if ($tid == -1) {
		    return array();
		}
		$sql = "SELECT category_id FROM ".$this->pscCategoryTemplateTable." WHERE template_id = $tid";
		$results = $this->db->select($sql);
		
		$categories = array();
		foreach($results as $result) {
			$categories[] = $result['category_id'];
		}
		return $categories;
	}
	
	public function getCategoriesForAppointmentID($aid)
	{
		$this->checkNumeric($aid);
		if ($aid == -1) {
		    return array();
		}
		
		$sql = "SELECT category_id FROM ".$this->pscCategoryAppTable." WHERE appointment_id = $aid";
		$results = $this->db->select($sql);
		
		$categories = array();
		foreach($results as $result) {
			$categories[] = $result['category_id'];
		}
		return $categories;
	}
	
	public function getCategoriesForAgreementID($aid)
	{
		$this->checkNumeric($aid);
		if ($aid == -1) {
		    return array();
		}
		
		$sql = "SELECT category_id FROM ".$this->pscCategoryAgrTable." WHERE agr_id = $aid";
		$results = $this->db->select($sql);

		$categories = array();
		foreach($results as $result) {
			$categories[] = $result['category_id'];
		}
		return $categories;
	}

	public function getAppointmentsForTemplateID($tpl_id, $sorting,$entrycount)
	{
		$this->checkNumeric($tpl_id);
		$categories = $this->getCategoriesForTemplateID($tpl_id);
		
		if (count($categories) == 0) {
		    return array();
		}
		
		$sql = "SELECT * FROM ".$this->pscCategoryAppTable." WHERE";
		foreach($categories as $category) {
			$sql .= " category_id = $category OR";
		}
		$sql = substr($sql, 0, -2);
		$results = $this->db->select($sql);
		
		if (count($results) == 0) return array();
		
		$sql = "SELECT * FROM ".$this->pscAppointmentTable." WHERE (status IS NULL OR status != 1) AND (";
		foreach($results as $result) {
			$sql.=" id = ".$result['appointment_id']." OR";
		}
		$sql = substr($sql, 0, -2);
		$sql.=")";
		$sort = explode(":",$sorting);
		$order = $sort[0];
		$dir = $sort[1];
		$sql.=" ORDER BY ".$order." ".$dir;
		return $this->db->select($sql);
	}

	public function getAgreements()
	{
		$sql = "SELECT * FROM ".$this->pscAppAgrTable;
		return $this->db->select($sql);
	}
	
	public function getAgreement($agr_id)
	{
		$this->checkNumeric($agr_id);
		$sql = "SELECT * FROM ".$this->pscAppAgrTable." WHERE id = ".$agr_id;
		return $this->db->select1($sql);
	}
	
	public function saveAgreement($id, $title, $begindate, $enddate, $finaldate, $categories)
	{
		$this->checkNumeric($id);
		
		if (empty($begindate)) {
		    $begindate = "0";
		}
		if (empty($enddate)) {
		    $enddate = "0";
		}
		if (empty($finaldate)) {
		    $finaldate = "0";
		}
		
		if ($id == -1) {
			$sql = "INSERT INTO ".$this->pscAppAgrTable." (title, begin_date, enddate, finaldate) ";
			$sql.= "VALUES ('$title',$begindate, $enddate, $finaldate)";
			$this->log($sql);
			$id = $this->db->dbinsert($sql, $this->pscAppAgrTable);
		} 
		else {
			$sql = "UPDATE ".$this->pscAppAgrTable." SET ";
			$sql.= "title='$title', begin_date=$begindate, enddate = $enddate, finaldate = $finaldate WHERE id = $id";
			$this->log($sql);
			$ok = $this->db->dbquery($sql);
		}
		// Categories
		$sql = "DELETE FROM ".$this->pscCategoryAgrTable." WHERE agr_id = $id";
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
			
		foreach($categories as $category)
		{
			$sql = "INSERT INTO ".$this->pscCategoryAgrTable." (agr_id, category_id) VALUES ($id, $category)";
			$this->log($sql);
			$ok = $this->db->dbinsert($sql, $this->pscCategoryAgrTable);
		}
	}
	
	public function deleteAgreement($agr_id)
	{
		$this->checkNumeric($agr_id);
		
		$sql = "DELETE FROM ".$this->pscAppAgrTable." WHERE id = $agr_id";
		$this->log($sql);
		
		$ok = $this->db->dbquery($sql);
		
		$sql = "DELETE FROM ".$this->pscCategoryAgrTable." WHERE agr_id = ".$agr_id;
		$this->log($sql);
		$ok = $this->db->dbquery($sql);
	}
	
	public function saveDates($agr_id, $emp_id, $dates)
	{
		$this->checkNumeric($agr_id);
		$this->checkNumeric($emp_id);
		
		$result = $this->getDates($agr_id, $emp_id);
		
		if (empty($result)) {
			$sql = "INSERT INTO ".$this->pscAppTable." (app_id,employee_id, dates) VALUES ($agr_id, $emp_id, '$dates')";
			$this->log($sql);
			$id = $this->db->dbinsert($sql,$this->pscAppTable);
		} 
		else {
			$sql = "UPDATE ".$this->pscAppTable." SET dates='$dates' WHERE id = ".$result['id'];
			$this->log($sql);
			$ok = $this->db->dbquery($sql);
		}
	}
	
	public function getDates($agr_id, $emp_id)
	{
		$this->checkNumeric($agr_id);
		$this->checkNumeric($emp_id);
		$sql = "SELECT * FROM ".$this->pscAppTable." WHERE app_id = $agr_id AND employee_id = $emp_id";
		return $this->db->select1($sql);
	}
	
	protected function checkNumeric($number)
	{
		if (!is_numeric($number)) {
		    die ("Internal Error.");
		}
	}
}
