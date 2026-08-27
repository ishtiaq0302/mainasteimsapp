<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Onlineadmission_m extends MY_Model {


    protected $_table_name = 'onlineadmission';
    protected $_primary_key = 'onlineadmissionID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "onlineadmissionID desc";

	function __construct() {
		parent::__construct();
	}

    public function get_onlineadmission($array=NULL, $signal=FALSE) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get($array, $signal);
        return $query;
    }

    public function get_order_by_onlineadmission($array=NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_order_by($array);
        return $query;
    } 

    public function get_where_in_onlineadmission($array, $key = NULL, $whereArray = NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_where_in($array, $key, $whereArray);
        return $query;
    }

    public function get_single_onlineadmission($array=NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_single($array);
        return $query;
    }

	public function insert_onlineadmission($array) {
        $array['adminID']=$this->session->userdata('adminID');
		$id = parent::insert($array);
		return $id;
	}
    
    public function update_onlineadmission($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    public function delete_onlineadmission($id){
        parent::delete($id);
    }
}