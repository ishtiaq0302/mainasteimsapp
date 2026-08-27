<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setting_m extends MY_Model {

	protected $_table_name = 'settings';
	protected $_primary_key = 'option';
	protected $_primary_filter = 'intval';
	protected $_order_by = "option asc";

	function __construct() {
		parent::__construct();
	}

	function get_setting($campusID=0,$adminID=0) {
		if($adminID==0){
			if (isset($this->session)) {
				$adminID = $this->session->userdata('adminID');
			}
		}

		if($campusID==0)
		{
			if (isset($this->session)) {
				if($this->session->userdata('campus_id')==0){
					$campusID=$this->session->userdata('accountCampusID');
				}else{
					$campusID=$this->session->userdata('campus_id');
				}
			}
		}

		$this->db->where('campusID',$campusID);
		if ($adminID > 0) {
			$this->db->where('adminID',$adminID);
		}
		$query = $this->db->get('settings');

		return $query->row();
	}

	function get_setting_where($data) {
		$this->db->where('fieldoption', $data);
		$query = $this->db->get('setting');
		return $query->row();
	}

	/*function get_setting($id = 1,$campusID=1) {
		$compress = array();
		$query = $this->db->where('campusID',$campusID)->get('setting');
		foreach ($query->result() as $row) {
		    $compress[$row->fieldoption] = $row->value;
		    $compress[$row->fieldoption] = $row->value;
		}
		return (object) $compress;
	}*/

	/*function get_unique_setting($id = 1) {
		$compress = array();
		$query = $this->db->get('setting');
		//view($query->result());
		foreach ($query->result() as $row) {
		    $compress[$row->fieldoption] = $row->value;
		    $compress[$row->fieldoption] = $row->value;
		}
		//view($compress,1);
		return (object) $compress;
	}*/

	/*function get_setting_array() {
		$compress = array();
		$query = $this->db->get('setting');
		foreach ($query->result() as $row) {
		    $compress[$row->fieldoption] = $row->value;
		}
		return $compress;
	}*/

	/*function get_setting_where($data) {
		$this->db->where('fieldoption', $data);
		$query = $this->db->get('setting');
		return $query->row();
	}*/

	/*function insertorupdate($arrays,$campusID=0) {
		if($campusID==0)
		{
			$campusID = $this->session->userdata('campus_id');
		}
		
		$adminID = $this->session->userdata('adminID');
		foreach ($arrays as $key => $array) {
			$this->db->query("INSERT INTO setting (fieldoption, value, campusID) VALUES ('".$key."', '".$array."', '".$campusID."') ON DUPLICATE KEY UPDATE fieldoption='".$key."' , value='".$array."' , campusID='".$campusID."' ");
		}
		return TRUE;
	}*/

	function updatesettings($arrays,$campusID=0,$marks=0) {
		$mark_array=array();

		if(!empty($marks) || $marks!=0)
		{
			$mark = count($marks);
		}else{
			$mark = 0;
		}
		
		for($i=0; $i<$mark; $i++)
		{
			//markpercentageID
			$mark_array['mark_'.$marks[$i]->markpercentageID]=$arrays['mark_'.$marks[$i]->markpercentageID];
		}

		$markArray=serialize($mark_array);
	//	view($arrays,1);
		if($campusID==0)
		{
			$campusID = $this->session->userdata('campus_id');
		}
		
		$adminID = $this->session->userdata('adminID');

		$insert_setting=array
		(
			'sname'								=>	$arrays['sname'],
			'phone'								=>	$arrays['phone'],
			'email'								=>	$arrays['email'],
			'automation'						=>	$arrays['automation'],
			'auto_invoice_generate'				=>	$arrays['auto_invoice_generate'],
			'note'								=>	$arrays['note'],
			'google_analytics'					=>	$arrays['google_analytics'],
			'currency_code'						=>	$arrays['currency_code'],
			'currency_symbol'					=>	$arrays['currency_symbol'],
			'footer'							=>	$arrays['footer'],
			'address'							=>	$arrays['address'],
			'frontendorbackend'					=>	$arrays['frontendorbackend'],
			'language'							=>	$arrays['language'],
			'attendance'						=>	$arrays['attendance'],
			'school_year'						=>	$arrays['school_year'],
			'photo'								=>	$arrays['photo'],
			'captcha_status'					=>	$arrays['captcha_status'],
			'language_status'					=>	$arrays['language_status'],
			'attendance_notification'			=>	$arrays['attendance_notification'],
			'attendance_smsgateway'				=>	$arrays['attendance_smsgateway'],
			'attendance_notification_template'	=>	$arrays['attendance_notification_template'],
			'ex_class'							=>	$arrays['ex_class'],
			'profile_edit'						=>	$arrays['profile_edit'],
			'time_zone'							=>	$arrays['time_zone'],
			'auto_update_notification'			=>	$arrays['auto_update_notification'],
			'weekends'							=>	$arrays['weekends'],
			'zoom_api_key'						=>	$arrays['zoom_api_key'],
			'zoom_api_secret'					=>	$arrays['zoom_api_secret'],
			'mark'								=>	$markArray,
		);

		if(!empty($arrays['recaptcha_site_key'])){
			$insert_setting['recaptcha_site_key'] =	$arrays['recaptcha_site_key'];
		}
		if(!empty($arrays['recaptcha_secret_key'])){
			$insert_setting['recaptcha_secret_key'] =	$arrays['recaptcha_secret_key'];
		}
		//view($insert_setting,1);
		$this->db->where('campusID',$campusID);
		$this->db->where('adminID',$adminID);
		$this->db->update('settings',$insert_setting);
		return TRUE;
	}

	function insertsettings($adminID,$campusID,$school_year_id,$marks=0) {
		$mark_array=array();
		for($i=1; $i<=$marks; $i++)
		{
			$mark_array['mark_'.$i]=$arrays['mark_'.$i];
		}

		$markArray=serialize($mark_array);
 
		$insert_setting=array
		(
			'adminID'							=>	$adminID,
			'campusID'							=>	$campusID,
			'sname'								=>	'ASTEIMS',
			'phone'								=>	'+92 51 5766144',
			'email'								=>	'info@asteims.com',
			'automation'						=>	5,
			'auto_invoice_generate'				=>	0,
			'note'								=>	1,
			'google_analytics'					=>	'',
			'currency_code'						=>	'PKR',
			'currency_symbol'					=>	'Rs.',
			'footer'							=>	'Copyright &copy; Azam Systems & Technologies (Pvt.)Ltd | 2020',
			'address'							=>	'Punjab Pakistan',
			'frontendorbackend'					=>	'YES',
			'language'							=>	'english',
			'attendance'						=>	'day',
			'school_year'						=>	$school_year_id,
			'photo'								=>	'6f4e208226d5251320956c1f4b35fcd5e5b67210b52597b73215fe7047868e45065e8e7be746116e425c6f8eb8fe5b2b90193a925787fa7ae3412ff6ea617edc.png',
			'captcha_status'					=>	1,
			'language_status'					=>	0,
			'attendance_notification'			=>	'none',
			'attendance_smsgateway'				=>	0,
			'attendance_notification_template'	=>	0,
			'ex_class'							=>	0,
			'profile_edit'						=>	1,
			'time_zone'							=>	'Asia/Karachi',
			'auto_update_notification'			=>	0,
			'weekends'							=>	6,
			'mark'								=>	$markArray,
			'recaptcha_site_key'				=>	'6LcyDAkdAAAAAJsN1RWwXnGyNcRNpEzVL7xRuQfe',
			'recaptcha_secret_key'				=>	'6LcyDAkdAAAAAOH7eX68f5cImKGbtgMvtOCSDKqc',
			'backend_theme'						=>	'default',
			'student_ID_format'					=>	1,
			'frontend_theme'					=>	'default',
			'absent_auto_sms'					=>	1,
			'school_type'						=>	'classbase',
		); 
		$this->db->insert('settings',$insert_setting);

		return TRUE;
	}

	public function delete_setting($optionname){
		$this->db->delete('setting', array('fieldoption' => $optionname));
		return TRUE;
	}

	public function insert_setting($array) {
		$this->db->insert('setting', $array);
		return TRUE; 
	}

	public function get_markpercentage($campusID=0) {
		//$query = $this->db->query("SELECT * FROM setting WHERE fieldoption LIKE 'mark%' AND value=1");
		$adminID= $this->session->userdata('adminID');
		$query = $this->db->query("SELECT * FROM settings WHERE adminID=$adminID AND campusID=$campusID");
		return $query->row();
	}

	public function update_setting($fieldoption, $value) {
		$array = array(
           'value' => $value,
        );

		$this->db->where('fieldoption', $fieldoption);
		$this->db->update($this->_table_name, $array);
		return TRUE;  
	}

	public function update_settings($fieldoption, $value) {
		$array = array(
           'backend_theme' => $value,
        );

		$this->db->where('campusID', $fieldoption);
		$this->db->where('adminID', $this->session->userdata('adminID'));
		$this->db->update('settings', $array);
		return TRUE;  
	}

	// function get_order_by_setting($array=NULL) {
	// 	$query = parent::get_order_by($array);
	// 	return $query;
	// }

	// function insert_setting($array) {
	// 	$error = parent::insert($array);
	// 	return TRUE;
	// }

	// function update_setting($data, $id = NULL) {
	// 	parent::update($data, $id);
	// 	return $id;
	// }

	
}

/* End of file setting_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/setting_m.php */
