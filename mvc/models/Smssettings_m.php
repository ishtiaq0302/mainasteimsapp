<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class smssettings_m extends MY_Model {
	public function insert_smssettings($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$this->db->insert('smssettings',$array);
		return $this->db->insert_id();
	}

	public function update_smssettings($data, $id = NULL) {
		$this->db->where('adminID',$id);
		$this->db->update('smssettings',$data);
	}

	public function get_smssettings() {
		$this->db->select('*');
		$this->db->from('smssettings');
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

	function get_order_by_clickatell() {
		$query = $this->db->get_where('smssetting', array('types' => 'clickatell'));
		return $query->result();
	}

	function update_clickatell($array) {
		$this->db->update_batch('smssetting', $array, 'field_names'); 
	}

	function get_order_by_twilio() {
		$query = $this->db->get_where('smssetting', array('types' => 'twilio'));
		return $query->result();
	}

	function update_twilio($array) {
		$this->db->update_batch('smssetting', $array, 'field_names'); 
	}

	function get_order_by_bulk() {
		$query = $this->db->get_where('smssetting', array('types' => 'bulk'));
		return $query->result();
	}

	function update_bulk($array) {
		$this->db->update_batch('smssetting', $array, 'field_names'); 
	}

    function get_order_by_msg91() {
        $query = $this->db->get_where('smssetting', array('types' => 'msg91'));
        return $query->result();
    }

    function update_msg91($array) {
		$this->db->update_batch('smssetting', $array, 'field_names');
	}


}