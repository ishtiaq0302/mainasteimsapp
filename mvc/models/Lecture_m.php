<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lecture_m extends MY_Model {

	protected $_table_name = 'lecture';
	protected $_primary_key = 'lectureID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "date asc";

	function __construct() {
		parent::__construct();
	}

	public function get_lecture($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_single_lecture($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	public function get_order_by_lecture($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_lecture($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
	}

	public function update_lecture($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_lecture($id){
		parent::delete($id);
	}
}