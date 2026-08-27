<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Book_m extends MY_Model {

	protected $_table_name = 'book';
	protected $_primary_key = 'bookID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "bookID desc";

	function __construct() {
		parent::__construct();
	}

	function get_book($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_book($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_book($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	function insert_book($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
	}

	function update_book($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_book($id){
		parent::delete($id);
	}

	function allbook($book) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = $this->db->query("SELECT * FROM book WHERE book LIKE '$book%'");
		return $query->result();
	}

	function allauthor($author) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = $this->db->query("SELECT * FROM book WHERE author LIKE '$author%'");
		return $query->result();
	}
}