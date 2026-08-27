<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Campussetting_m extends MY_Model {

	protected $_table_name = 'campussetting';
	protected $_primary_key = 'option';
	protected $_primary_filter = 'intval';
	protected $_order_by = "option asc";

	function __construct() {
		parent::__construct();
	}


	function get_campussetting($id = 1) {
		$compress = array();
		$query = $this->db->where('campusID',$id)->get('campussetting');
		
		foreach ($query->result() as $row) {
		    $compress[$row->fieldoption] = $row->value;
		}
		return (object) $compress;
	}

	function get_campussetting_array() {
		$compress = array();
		$query = $this->db->get('campussetting');
		foreach ($query->result() as $row) {
		    $compress[$row->fieldoption] = $row->value;
		}
		return $compress;
	}

	function get_campussetting_where($data) {
		$this->db->where('fieldoption', $data);
		$query = $this->db->get('campussetting');
		return $query->row();
	}

	function insertorupdate($arrays) {
		foreach ($arrays as $key => $array) {
			$this->db->query("INSERT INTO campussetting (fieldoption, value) VALUES ('".$key."', '".$array."') ON DUPLICATE KEY UPDATE fieldoption='".$key."' , value='".$array."'");
		}
		return TRUE;
	}

	public function delete_campussetting($optionname){
		$this->db->delete('campussetting', array('fieldoption' => $optionname));
		return TRUE;
	}

	public function insert_campussetting($array) {
		$this->db->insert('campussetting', $array);
		return TRUE; 
	}

	public function get_markpercentage() {
		$query = $this->db->query("SELECT * FROM campussetting WHERE fieldoption LIKE 'mark%' AND value=1");
		return $query->result();
	}

	public function update_campussetting($fieldoption, $value) {
		$array = array(
           'value' => $value,
        );

		$this->db->where('fieldoption', $fieldoption);
		$this->db->update($this->_table_name, $array);
		return TRUE;  
	}

	// function get_order_by_campussetting($array=NULL) {
	// 	$query = parent::get_order_by($array);
	// 	return $query;
	// }

	// function insert_campussetting($array) {
	// 	$error = parent::insert($array);
	// 	return TRUE;
	// }

	// function update_campussetting($data, $id = NULL) {
	// 	parent::update($data, $id);
	// 	return $id;
	// }

	
}

/* End of file campussetting_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/campussetting_m.php */
