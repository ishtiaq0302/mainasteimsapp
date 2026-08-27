<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Site_m extends MY_Model {



	protected $_table_name = 'setting';

	protected $_primary_key = 'option';

	protected $_primary_filter = 'intval';

	protected $_order_by = "option asc";



	function __construct() {

		parent::__construct();

	}



	function get_site($id = NULL) {

		$compress = array();

		$query = $this->db->get('setting');

		foreach ($query->result() as $row) {

		    $compress[$row->fieldoption] = $row->value;

		}

		return (object) $compress;

	}



	function get_site_($campusID=0) {

		if($campusID==0){		

			if($this->session->userdata('accountCampusID') != 0){

				$query = $this->db->where('adminID',$this->session->userdata('adminID'))->where('campusID',$this->session->userdata('accountCampusID'))->get('settings');

			}else{

				$query = $this->db->where('adminID',$this->session->userdata('adminID'))->where('campusID',$this->session->userdata('campus_id'))->get('settings');

			}



		}else{



				$query = $this->db->where('adminID',$this->session->userdata('adminID'))->where('campusID',$campusID)->get('settings');

		}





		if($this->session->userdata('adminID')=='' && $this->session->userdata('campus_id')=='')

		{

			$query = $this->db->where('adminID',1)->where('campusID',1)->get('settings');

		}



		return $query->row();

	}



	function get_sites($id = NULL,$campusID=1) {

		$compress = array();

		$query = $this->db->where('campusID',$campusID)->get('setting');

		foreach ($query->result() as $row) {

		    $compress[$row->fieldoption] = $row->value;

		}

		return (object) $compress;

	}



 

}



/* End of file site_m.php */

/* Location: .//D/xampp/htdocs/school/mvc/models/site_m.php */