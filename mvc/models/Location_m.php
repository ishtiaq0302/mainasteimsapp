<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Location_m extends MY_Model {

    protected $_table_name = 'location';
    protected $_primary_key = 'locationID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "locationID asc";

    function __construct() {
        parent::__construct();
    }

    function get_location($array=NULL, $signal=FALSE) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get($array, $signal);
        return $query;
    }

    function get_single_location($array) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_single($array);
        return $query;
    }

    function get_order_by_location($array=NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_order_by($array);
        return $query;
    }

    function insert_location($array) {
        $array['adminID']=$this->session->userdata('adminID');
        $id = parent::insert($array);
        return $id;
    }

    function update_location($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    public function delete_location($id){
        parent::delete($id);
    }
}
