<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'Studentparentteacher_m.php';

class Staff_m extends MY_Model {

	protected $_table_name = 'staff';
	protected $_primary_key = 'id';
	protected $_primary_filter = 'intval';
	protected $_order_by = "name asc";

	function __construct() {
		parent::__construct();
	}

	public function get_username($table, $data=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = $this->db->get_where($table, $data);
		return $query->result();
	}

	public function get_teacher_name($value=NULL) {
		$this->db->select('*');
      $this->db->from("staff");
      $this->db->where('usertypeID =', '2');
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = $this->db->get();
		return $query->result();
	}

	public function get_where_in_staff($array, $key=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_where_in($array, $key);
		return $query;
	}

	public function general_get_staff($id=NULL, $single=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($id, $single);
		return $query;
	}

	public function general_get_single_staff($array) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	public function general_get_order_by_staff($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	

	

	public function get_select_staff($select = NULL, $array=[]) {
		if($select == NULL) {
			$select = 'id, name';
		}

		$this->db->select($select);
		$this->db->from($this->_table_name);
		$this->db->where('adminID',$this->session->userdata('adminID'));
		if(count($array)) {
			$this->db->where($array);
		}

		$query = $this->db->get();
		return $query->result();
	}

	public function insert_staff($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
	}

	public function update_staff($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_staff($id){
		parent::delete($id);
	}

	public function hash($string) {
		return parent::hash($string);
	}
}
