<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Emailsetting_m extends MY_Model {



	protected $_table_name = 'emailsetting';

	protected $_primary_key = 'fieldoption';

	protected $_primary_filter = 'intval';

	protected $_order_by = "fieldoption asc";



	function __construct() {

		parent::__construct();

	}





	public function get_emailsetting() {

		$emailsettingArray = array();

		$query = $this->db->get($this->_table_name);

		if(!empty($query)) {

			foreach ($query->result() as $row) {

			    $emailsettingArray[$row->fieldoption] = $row->value;

			}

		}

		return (object) $emailsettingArray;

	}



	public function get_emailsettings() {

		$this->db->select('*');

		$this->db->from('emailsettings');

		$this->db->where('adminID',$this->session->userdata('adminID'));

		$query=$this->db->get();

		

		if($query->num_rows()==1)

		{

			return $query->row_array();

		}

		else 

		{

			return NULL;	

		}

	}



	public function insertorupdate($arrays) {

		foreach ($arrays as $key => $array) {

			$this->db->query("INSERT INTO emailsetting (fieldoption, value) VALUES ('".$key."', '".$array."') ON DUPLICATE KEY UPDATE fieldoption='".$key."' , value='".$array."'");

		}

		return TRUE;

	}



	public function insertorupdates($array) {

		$this->db->select('*');

		$this->db->from('emailsettings');

		$this->db->where('adminID',$this->session->userdata('adminID'));

		$query=$this->db->get();

		$result = $query->row_array();

		

		if(!empty($result))

		{

			$this->db->where('adminID',$this->session->userdata('adminID'));

			$this->db->update('emailsettings',$array);			

		}else{

			$array['adminID']=$this->session->userdata('adminID');

			$this->db->insert('emailsettings',$array);	

		}



		/*$array['adminID']=$this->session->userdata('adminID');

		$this->db->insert('emailsettings',$array);



		$this->db->where('adminID',$id);

		$this->db->update('smssettings',$data);

		return $this->db->insert_id();*/

	}



}



/* End of file emailsetting_m.php */

/* Location: .//D/xampp/htdocs/school/mvc/models/emailsetting_m.php */

