<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once 'Studentparentteacher_m.php';

class Teacher_m extends MY_Model {

	protected $_table_name = 'teacher';
	protected $_primary_key = 'teacherID';
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

	public function get_where_in_teacher($array, $key=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_where_in($array, $key);
		return $query;
	}

	public function general_get_teacher($id=NULL, $single=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($id, $single);
		return $query;
	}

	public function general_get_single_teacher($array) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	public function general_get_order_by_teacher($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_teacher($id=NULL, $single=FALSE) {
		$usertypeID = $this->session->userdata('usertypeID');
		if($usertypeID == 3 || $usertypeID == 4) {
			$studentparentteacher = new Studentparentteacher_m;
			return $studentparentteacher->get_studentparent_teacher($id, $single);
		} else {
			$this->db->where('adminID',$this->session->userdata('adminID'));
			$query = parent::get($id, $single);
			return $query;
		}
	}

	public function get_single_teacher($array) {
		$usertypeID = $this->session->userdata('usertypeID');
		if($usertypeID == 3 || $usertypeID == 4) {
			$studentparentteacher = new Studentparentteacher_m;
			return $studentparentteacher->get_single_studentparent_teacher($array);
		} else {
			$this->db->where('adminID',$this->session->userdata('adminID'));
			$query = parent::get_single($array);
			return $query;
		}
	}

	public function get_order_by_teacher($array=NULL) {
		$usertypeID = $this->session->userdata('usertypeID');
		if($usertypeID == 3 || $usertypeID == 4) {
			$studentparentteacher = new Studentparentteacher_m;
			return $studentparentteacher->get_order_by_studentparent_teacher($array);
		} else {
			$this->db->where('adminID',$this->session->userdata('adminID'));
			$query = parent::get_order_by($array);
			return $query;
		}
	}

	public function get_select_teacher($select = NULL, $array=[]) {

		if($select == NULL) {
			$select = 'teacherID, name, photo';
		}

		$this->db->select($select);
		$this->db->from($this->_table_name);
		$this->db->where('adminID',$this->session->userdata('adminID'));
		
		if(!empty($array)) {
			$this->db->where($array);
		}

		$query = $this->db->get();
		return $query->result();
	}

	public function insert_teacher($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		$insert_id = $this->db->insert_id();
		// print_r($insert_id);
		// exit();
		return  $insert_id;
		
	}

	public function update_teacher($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_teacher($id){
		parent::delete($id);
	}

	public function hash($string) {
		return parent::hash($string);
	}
}
