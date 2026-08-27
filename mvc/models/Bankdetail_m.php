<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bankdetail_m extends MY_Model {

	protected $_table_name = 'bankdetail';
	protected $_primary_key = 'bankdetailID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "bankdetailID desc";

	function __construct() {
		parent::__construct();
	}

	public function get_bankdetail($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_single_bankdetail($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	public function get_order_by_bankdetail($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_bankdetail($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
		//$error = parent::insert($array);
		//$insert_id = $this->db->insert_id();
		//return  $insert_id;
	}

	public function update_bankdetail($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_bankdetail($id){
		parent::delete($id);
	}
}