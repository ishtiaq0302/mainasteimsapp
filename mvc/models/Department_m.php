<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Department_m extends MY_Model {

	protected $_table_name = 'department';
	protected $_primary_key = 'departmentID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "departmentID asc";

	function __construct() {
		parent::__construct();
	}

	function get_department($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_department($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_department($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	function insert_department($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
	}

	function update_department($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_department($id){
		parent::delete($id);
	}
}

/* End of file department_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/department_m.php */