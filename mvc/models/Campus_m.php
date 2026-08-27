<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Campus_m extends MY_Model {

	protected $_table_name = 'campus';
	protected $_primary_key = 'campusID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "campusID asc";

	function __construct() {
		parent::__construct();
	}

	function get_campus($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_campus($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_campus($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	function insert_campus($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return $this->db->insert_id();
	}

	function add_campus($array) {
		$this->db->insert('campus',$array);
		return $this->db->insert_id();
	}

	function update_campus($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_campus($id){
		parent::delete($id);
	}
}

/* End of file campus_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/campus_m.php */