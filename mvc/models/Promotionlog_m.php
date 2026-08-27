<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Promotionlog_m extends MY_Model {

	protected $_table_name = 'promotionlog';
	protected $_primary_key = 'promotionLogID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "promotionLogID asc";

	function __construct() {
		parent::__construct();
	}

	function get_promotionlog($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_promotionlog($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_order_by_promotionlog_with_subject($classes) {
		$this->db->select('*');
		$this->db->from('subject');
		$this->db->join('promotionlog', 'subject.subjectID = promotionlog.subjectID', 'LEFT');
		$this->db->where('subject.classesID', $classes);
		$this->db->where('subject.adminID',$this->session->userdata('adminID'));
		$query = $this->db->get();
		return $query->result();
	}

	function insert_promotionlog($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
	}

	function update_promotionlog($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_promotionlog($id){
		parent::delete($id);
	}
}

/* End of file promotionlog_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/promotionlog_m.php */
