<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Studentgroup_m extends MY_Model {

    protected $_table_name = 'studentgroup';
    protected $_primary_key = 'studentgroupID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "studentgroupID asc";

    function __construct() {
        parent::__construct();
    }

    public function get_studentgroup($array=NULL, $signal=FALSE) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get($array, $signal);
        return $query;
    }

    public function get_single_studentgroup($array) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_single($array);
        return $query;
    }

    public function get_order_by_studentgroup($array=NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_order_by($array);
        return $query;
    }

    public function insert_studentgroup($array) {
        $array['adminID']=$this->session->userdata('adminID');
        $error = parent::insert($array);
        return TRUE;
    }

    public function update_studentgroup($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    public function delete_studentgroup($id){
        parent::delete($id);
    }
}
