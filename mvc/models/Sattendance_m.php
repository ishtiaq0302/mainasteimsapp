<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sattendance_m extends MY_Model {

	protected $_table_name = 'attendance';
	protected $_primary_key = 'attendanceID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "monthyear asc";

	function __construct() {
		parent::__construct();
	}

	public function get_attendance($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_attendance($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_attendance($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
	}

	public function insert_batch_attendance($array) {
		$total_array=0;
        if(!empty($array[0])){
            $total_array = count($array);
            for($i=0; $i < $total_array ; $i++) { 
                $array[$i]['adminID']=$this->session->userdata('adminID');
            }
        }
        
		$id = parent::insert_batch($array);
		return $id;
	}

	public function update_attendance($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function update_batch_attendance($data, $id = NULL) {
        parent::update_batch($data, $id);
        return TRUE;
    }

	public function delete_attendance($id){
		parent::delete($id);
	}
}