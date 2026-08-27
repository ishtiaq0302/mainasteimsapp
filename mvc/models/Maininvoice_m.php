<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Maininvoice_m extends MY_Model {

	protected $_table_name = 'maininvoice';
	protected $_primary_key = 'maininvoiceID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "maininvoiceID desc";
	

	public function __construct() {
		parent::__construct();
	}

	public function get_maininvoice_with_studentrelation($schoolyearID = NULL) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maininvoice.maininvoicestudentID AND studentrelation.srclassesID = maininvoice.maininvoiceclassesID AND studentrelation.srschoolyearID = maininvoice.maininvoiceschoolyearID', 'LEFT');
		$this->db->where('maininvoice.maininvoicedeleted_at', 1);
		if($schoolyearID != NULL) {
			$this->db->where('maininvoice.maininvoiceschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}
		$this->db->where('maininvoice.adminID',$this->session->userdata('adminID'));
		$this->db->order_by('maininvoice.maininvoiceID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_maininvoice_with_studentrelations($schoolyearID = NULL,$campusID=0) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maininvoice.maininvoicestudentID AND studentrelation.srclassesID = maininvoice.maininvoiceclassesID AND studentrelation.srschoolyearID = maininvoice.maininvoiceschoolyearID', 'LEFT');
		$this->db->where('maininvoice.maininvoicedeleted_at', 1);
		if($schoolyearID != NULL) {
			$this->db->where('maininvoice.maininvoiceschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}
		$this->db->where('maininvoice.campusID', $campusID);
		$this->db->where('maininvoice.adminID',$this->session->userdata('adminID'));
		$this->db->order_by('maininvoice.maininvoiceID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_maininvoice_with_studentrelation_by_studentID($studentID, $schoolyearID = NULL) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maininvoice.maininvoicestudentID AND studentrelation.srclassesID = maininvoice.maininvoiceclassesID AND studentrelation.srschoolyearID = maininvoice.maininvoiceschoolyearID', 'LEFT');
		$this->db->where('maininvoice.maininvoicestudentID', $studentID);
		$this->db->where('maininvoice.maininvoicedeleted_at', 1);
		$this->db->where('maininvoice.adminID',$this->session->userdata('adminID'));

		if($schoolyearID != NULL) {
			$this->db->where('maininvoice.maininvoiceschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}
		$this->db->order_by('maininvoice.maininvoiceID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_maininvoice_with_studentrelation_by_studentIDs($studentID, $schoolyearID = NULL,$campusID=0) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maininvoice.maininvoicestudentID AND studentrelation.srclassesID = maininvoice.maininvoiceclassesID AND studentrelation.srschoolyearID = maininvoice.maininvoiceschoolyearID', 'LEFT');
		$this->db->where('maininvoice.maininvoicestudentID', $studentID);
		$this->db->where('maininvoice.maininvoicedeleted_at', 1);
		$this->db->where('maininvoice.campusID', $campusID);
		$this->db->where('maininvoice.adminID',$this->session->userdata('adminID'));

		if($schoolyearID != NULL) {
			$this->db->where('maininvoice.maininvoiceschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}
		$this->db->order_by('maininvoice.maininvoiceID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}


	public function get_maininvoice_with_studentrelation_by_multi_studentID($studentIDArrays, $schoolyearID = NULL) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maininvoice.maininvoicestudentID AND studentrelation.srclassesID = maininvoice.maininvoiceclassesID AND studentrelation.srschoolyearID = maininvoice.maininvoiceschoolyearID', 'LEFT');
		$this->db->where('maininvoice.maininvoicedeleted_at', 1);

		if(count($studentIDArrays)) {
			$this->db->where_in('maininvoice.maininvoicestudentID', $studentIDArrays);
		}

		if($schoolyearID != NULL) {
			$this->db->where('maininvoice.maininvoiceschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}
		$this->db->where('maininvoice.adminID',$this->session->userdata('adminID'));

		$this->db->order_by('maininvoice.maininvoiceID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_maininvoice_with_studentrelation_by_multi_studentIDs($studentIDArrays, $schoolyearID = NULL,$campusID=0) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maininvoice.maininvoicestudentID AND studentrelation.srclassesID = maininvoice.maininvoiceclassesID AND studentrelation.srschoolyearID = maininvoice.maininvoiceschoolyearID', 'LEFT');
		$this->db->where('maininvoice.maininvoicedeleted_at', 1);

		if(count($studentIDArrays)) {
			$this->db->where_in('maininvoice.maininvoicestudentID', $studentIDArrays);
		}
		$this->db->where('maininvoice.adminID',$this->session->userdata('adminID'));

		if($schoolyearID != NULL) {
			$this->db->where('maininvoice.maininvoiceschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}
		$this->db->where('maininvoice.campusID', $campusID);

		$this->db->order_by('maininvoice.maininvoiceID', 'desc');
		$query = $this->db->get();
		return $query->result();
	}

	public function get_maininvoice_with_studentrelation_by_maininvoiceID($invoiceID, $schoolyearID = NULL) {
		$this->db->select('*');
		$this->db->from($this->_table_name);
		$this->db->join('studentrelation', 'studentrelation.srstudentID = maininvoice.maininvoicestudentID AND studentrelation.srclassesID = maininvoice.maininvoiceclassesID AND studentrelation.srschoolyearID = maininvoice.maininvoiceschoolyearID', 'LEFT');
		$this->db->where('maininvoice.maininvoiceID', $invoiceID);
		$this->db->where('maininvoice.maininvoicedeleted_at', 1);
		$this->db->where('maininvoice.adminID',$this->session->userdata('adminID'));

		if($schoolyearID != NULL) {
			$this->db->where('maininvoice.maininvoiceschoolyearID', $schoolyearID);
			$this->db->where('studentrelation.srschoolyearID', $schoolyearID);
		}

		$this->db->order_by('maininvoice.maininvoiceID', 'desc');
		$query = $this->db->get();
		return $query->row();
	}

	public function get_maininvoice($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_maininvoice($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	public function get_single_maininvoice($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	public function insert_maininvoice($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return $error;
	}

	public function insert_batch_maininvoice($array) {
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

	public function update_maininvoice($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_maininvoice($id){
		parent::delete($id);
	}
}

/* End of file invoice_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/invoice_m.php */