<?php

class module extends base_mod
{
	function __construct($module,$command,$isAdmin)
	{
		parent::init();
		$this->moduleName = $module;
		$this->command = $this->lwStringClean(substr($command,0,32));
		$this->isAdmin = $isAdmin;
		$this->moveOldToArchive();
		$this->fatalError = false;
	}
	
	protected function testForActiveUser()
	{
		if ($this->moduleName == "output") return;
		$user_id  = $this->auth->getUserdata("id");
		$userdata = $this->dh->getUserData($user_id);
		if ($userdata['status'] == "inactive") {
			$this->fatalError = "<h1>Access Denied</h1><p>You have no access to the project scheduler";
		}
	}
	
	public function execute()
	{
		if (empty($this->command)) {
			$this->command = $this->getDefaultCommand();
		}

		$am = "am_".$this->command;

		if(!$this->isAdmin) {
			$this->testForActiveUser();
		}

		if ($this->fatalError !== false) {
			$this->output = $this->fatalError;
			return;
		}

		if (method_exists($this,$am)) {
			$this->commandName =$this->command;
			$this->output = $this->$am();
		} 
		else {
			$this->output = $this->getCommandNotFoundError();
		}
	}
	
	protected function getCommandNotFoundError()
	{
		return "Command not found.";
	}
	
	protected function autotemplate()
	{
		$file = $this->moduleName."_".$this->commandName.".tpl.html";
		$path = $this->conf['scheduler']['templates'].$file;
		$template = $this->loadFile($path);
		$tpl = new lw_te($template);
		return $tpl;
	}
	
	protected function getTemplate($name)
	{
		$file = $name.".tpl.html";
		$path = $this->conf['scheduler']['templates'].$file;
		$template = $this->loadFile($path);
		$tpl = new lw_te($template);
		return $tpl;
	}
	
	protected function getTableBgColor($i)
	{
		if ($i % 2 == 0) {
			return "#F3F3F3";
		} 
		else {
			return "#E9EAF3";
		}
	}
	
	protected function getRowClass($i)
	{
		if ($i % 2 == 0) {
			return "psc_table_even_row";
		} 
		else {
			return "psc_table_odd_row";
		}
	}
	
	protected function getErrorMessage($message)
	{
		return "<h1>Error</h1><p>An error occured.</p>";
	}
	
	protected function getCountryOptions($selected_country) 
	{
		$countries = array('  ' =>    'Please select a country', 
		            '--' => 'None', 
		            'AF' => 'Afganistan', 
		            'AL' => 'Albania', 
		            'DZ' => 'Algeria', 
		            'AS' => 'American Samoa', 
		            'AD' => 'Andorra',  
		            'AO' => 'Angola', 
		            'AI' => 'Anguilla', 
		            'AQ' => 'Antarctica', 
		            'AG' => 'Antigua and Barbuda',  
		            'AR' => 'Argentina',  
		            'AM' => 'Armenia',  
		            'AW' => 'Aruba',  
		            'AU' => 'Australia',  
		            'AT' => 'Austria',  
		            'AZ' => 'Azerbaijan', 
		            'BS' => 'Bahamas',  
		            'BH' => 'Bahrain',  
		            'BD' => 'Bangladesh', 
		            'BB' => 'Barbados', 
		            'BY' => 'Belarus',  
		            'BE' => 'Belgium',  
		            'BZ' => 'Belize', 
		            'BJ' => 'Benin',  
		            'BM' => 'Bermuda',  
		            'BT' => 'Bhutan', 
		            'BO' => 'Bolivia',  
		            'BA' => 'Bosnia and Herzegowina', 
		            'BW' => 'Botswana', 
		            'BV' => 'Bouvet Island',  
		            'BR' => 'Brazil', 
		            'IO' => 'British Indian Ocean Territory', 
		            'BN' => 'Brunei Darussalam',  
		            'BG' => 'Bulgaria', 
		            'BF' => 'Burkina Faso', 
		            'BI' => 'Burundi',  
		            'KH' => 'Cambodia', 
		            'CM' => 'Cameroon', 
		            'CA' => 'Canada', 
		            'CV' => 'Cape Verde', 
		            'KY' => 'Cayman Islands', 
		            'CF' => 'Central African Republic', 
		            'TD' => 'Chad', 
		            'CL' => 'Chile',  
		            'CN' => 'China', 
		            'CX' => 'Christmas Island',     
		            'CC' => 'Cocos (Keeling) Islands',  
		            'CO' => 'Colombia', 
		            'KM' => 'Comoros',  
		            'CG' => 'Congo',  
		            'CD' => 'Congo, the Democratic Republic of the',  
		            'CK' => 'Cook Islands', 
		            'CR' => 'Costa Rica', 
		            'CI' => 'Cote d\'Ivoire',  
		            'HR' => 'Croatia (Hrvatska)', 
		            'CU' => 'Cuba', 
		            'CY' => 'Cyprus', 
		            'CZ' => 'Czech Republic', 
		            'DK' => 'Denmark',  
		            'DJ' => 'Djibouti', 
		            'DM' => 'Dominica', 
		            'DO' => 'Dominican Republic', 
		            'TP' => 'East Timor', 
		            'EC' => 'Ecuador',  
		            'EG' => 'Egypt',  
		            'SV' => 'El Salvador',  
		            'GQ' => 'Equatorial Guinea',  
		            'ER' => 'Eritrea',  
		            'EE' => 'Estonia',  
		            'ET' => 'Ethiopia', 
		            'FK' => 'Falkland Islands (Malvinas)',  
		            'FO' => 'Faroe Islands',  
		            'FJ' => 'Fiji', 
		            'FI' => 'Finland', 
		            'FR' => 'France', 
		            'FX' => 'France, Metropolitan', 
		            'GF' => 'French Guiana',  
		            'PF' => 'French Polynesia', 
		            'TF' => 'French Southern Territories',  
		            'GA' => 'Gabon',  
		            'GM' => 'Gambia', 
		            'GE' => 'Georgia',  
		            'DE' => 'Germany',  
		            'GH' => 'Ghana',  
		            'GI' => 'Gibraltar',  
		            'GR' => 'Greece', 
		            'GL' => 'Greenland',  
		            'GD' => 'Grenada',  
		            'GP' => 'Guadeloupe', 
		            'GU' => 'Guam', 
		            'GT' => 'Guatemala',  
		            'GN' => 'Guinea', 
		            'GW' => 'Guinea-Bissau',  
		            'GY' => 'Guyana', 
		            'HT' => 'Haiti',  
		            'HM' => 'Heard and Mc Donald Islands',  
		            'VA' => 'Holy See (Vatican City State)',  
		            'HN' => 'Honduras', 
		            'HK' => 'Hong Kong',  
		            'HU' => 'Hungary',  
		            'IS' => 'Iceland',  
		            'IN' => 'India',  
		            'ID' => 'Indonesia',  
		            'IR' => 'Iran (Islamic Republic of)', 
		            'IQ' => 'Iraq', 
		            'IE' => 'Ireland',  
		            'IL' => 'Israel', 
		            'IT' => 'Italy',  
		            'JM' => 'Jamaica',  
		            'JP' => 'Japan', 
		            'JO' => 'Jordan', 
		            'KZ' => 'Kazakhstan', 
		            'KE' => 'Kenya',  
		            'KI' => 'Kiribati', 
		            'KP' => 'Korea, Democratic People\'s Republic of', 
		            'KR' => 'Korea, Republic of', 
		            'KW' => 'Kuwait', 
		            'KG' => 'Kyrgyzstan', 
		            'LA' => 'Lao People\'s Democratic Republic', 
		            'LV' => 'Latvia', 
		            'LB' => 'Lebanon', 
		            'LS' => 'Lesotho',  
		            'LR' => 'Liberia',  
		            'LY' => 'Libyan Arab Jamahiriya', 
		            'LI' => 'Liechtenstein',  
		            'LT' => 'Lithuania', 
		            'LU' => 'Luxembourg', 
		            'MO' => 'Macau',  
		            'MK' => 'Macedonia, The Former Yugoslav Republic of', 
		            'MG' => 'Madagascar', 
		            'MW' => 'Malawi', 
		            'MY' => 'Malaysia', 
		            'MV' => 'Maldives', 
		            'ML' => 'Mali', 
		            'MT' => 'Malta', 
		            'MH' => 'Marshall Islands', 
		            'MQ' => 'Martinique', 
		            'MR' => 'Mauritania', 
		            'MU' => 'Mauritius', 
		            'YT' => 'Mayotte',  
		            'MX' => 'Mexico', 
		            'FM' => 'Micronesia, Federated States of', 
		            'MD' => 'Moldova, Republic of', 
		            'MC' => 'Monaco', 
		            'MN' => 'Mongolia', 
		            'MS' => 'Montserrat', 
		            'MA' => 'Morocco', 
		            'MZ' => 'Mozambique', 
		            'MM' => 'Myanmar', 
		            'NA' => 'Namibia', 
		            'NR' => 'Nauru',  
		            'NP' => 'Nepal',  
		            'NL' => 'Netherlands', 
		            'AN' => 'Netherlands Antilles', 
		            'NC' => 'New Caledonia', 
		            'NZ' => 'New Zealand',  
		            'NI' => 'Nicaragua',  
		            'NE' => 'Niger',  
		            'NG' => 'Nigeria',  
		            'NU' => 'Niue', 
		            'NF' => 'Norfolk Island', 
		            'MP' => 'Northern Mariana Islands', 
		            'NO' => 'Norway', 
		            'OM' => 'Oman', 
		            'PK' => 'Pakistan', 
		            'PW' => 'Palau', 
		            'PA' => 'Panama', 
		            'PG' => 'Papua New Guinea', 
		            'PY' => 'Paraguay', 
		            'PE' => 'Peru', 
		            'PH' => 'Philippines', 
		            'PN' => 'Pitcairn', 
		            'PL' => 'Poland', 
		            'PT' => 'Portugal', 
		            'PR' => 'Puerto Rico', 
		            'QA' => 'Qatar', 
		            'RE' => 'Reunion', 
		            'RO' => 'Romania', 
		            'RU' => 'Russian Federation', 
		            'RW' => 'Rwanda', 
		            'KN' => 'Saint Kitts and Nevis',  
		            'LC' => 'Saint LUCIA',  
		            'VC' => 'Saint Vincent and the Grenadines', 
		            'WS' => 'Samoa',  
		            'SM' => 'San Marino', 
		            'ST' => 'Sao Tome and Principe', 
		            'SA' => 'Saudi Arabia', 
		            'SN' => 'Senegal', 
		            'SC' => 'Seychelles', 
		            'SL' => 'Sierra Leone', 
		            'SG' => 'Singapore',  
		            'SK' => 'Slovakia (Slovak Republic)', 
		            'SI' => 'Slovenia', 
		            'SB' => 'Solomon Islands', 
		            'SO' => 'Somalia',  
		            'ZA' => 'South Africa', 
		            'GS' => 'South Georgia and the South Sandwich Islands', 
		            'ES' => 'Spain', 
		            'LK' => 'Sri Lanka', 
		            'SH' => 'St. Helena', 
		            'PM' => 'St. Pierre and Miquelon',  
		            'SD' => 'Sudan',  
		            'SR' => 'Suriname', 
		            'SJ' => 'Svalbard and Jan Mayen Islands', 
		            'SZ' => 'Swaziland',  
		            'SE' => 'Sweden', 
		            'CH' => 'Switzerland',  
		            'SY' => 'Syrian Arab Republic', 
		            'TW' => 'Taiwan, Province of China', 
		            'TJ' => 'Tajikistan', 
		            'TZ' => 'Tanzania, United Republic of', 
		            'TH' => 'Thailand', 
		            'TG' => 'Togo', 
		            'TK' => 'Tokelau', 
		            'TO' => 'Tonga',  
		            'TT' => 'Trinidad and Tobago',  
		            'TN' => 'Tunisia',  
		            'TR' => 'Turkey', 
		            'TM' => 'Turkmenistan', 
		            'TC' => 'Turks and Caicos Islands', 
		            'TV' => 'Tuvalu', 
		            'UG' => 'Uganda', 
		            'UA' => 'Ukraine', 
		            'AE' => 'United Arab Emirates', 
		            'GB' => 'United Kingdom', 
		            'US' => 'United States', 
		            'UM' => 'United States Minor Outlying Islands', 
		            'UY' => 'Uruguay',  
		            'UZ' => 'Uzbekistan', 
		            'VU' => 'Vanuatu',  
		            'VE' => 'Venezuela', 
		            'VN' => 'Viet Nam', 
		            'VG' => 'Virgin Islands (British)', 
		            'VI' => 'Virgin Islands (U.S.)',  
		            'WF' => 'Wallis and Futuna Islands',  
		            'EH' => 'Western Sahara', 
		            'YE' => 'Yemen',  
		            'YU' => 'Yugoslavia', 
		            'ZM' => 'Zambia', 
		            'ZW' => 'Zimbabwe');
		
		$str = "";
		foreach($countries as $key=>$value) {
			if ($key == $selected_country) {
				$str.="<option value='$key' selected='selected'>$value</option>";
			} 
			else {
				$str.="<option value='$key'>$value</option>";
			}
		}
		return $str;
	}
	
	protected function buildNewDate($date)
	{
		if ($date == 0) return "";
		$year  = substr($date, 0, 4);
		$month = substr($date, 4, 2);
		$day   = substr($date, 6, 2);
		
		if (empty($year)) return "";
		
		return $day.".".$month.".".$year;
	}
	
	protected function filterDate($value)
	{
		if (empty($value)) return "0";
		
		if ($value=="loeschen") return -1;
		
		$date = $value;
		$day   = substr($date, 0, 2);
		$month = substr($date, 3, 2);
		$year  = substr($date, 6, 4);
		
		$value = $year.$month.$day;
		
		if (strlen($value) != 8) {
			die("Datum nicht korrekt: $value");
		}
		if (!is_numeric($value)) die("Datum ist keine Zahl.");
		
		return $value;
	}
	
	protected function getReadableTimePeriod($date1, $date2) 
	{
		if (empty($date1)) {
		    return "n/a";
		}
		if (empty($date2)) {
			return $this->getReadableDate($date1);
		} 
		else {
			if ($date1 == $date2) {
				return $this->getReadableDate($date1);
			}
			return $this->getReadableDate($date1)." - ".$this->getReadableDate($date2);
		}
	}
	
	public function moveOldToArchive()
	{
		$currentDate = date("Ymd");
		$this->dh->moveOldToArchive($currentDate);
	}
}
