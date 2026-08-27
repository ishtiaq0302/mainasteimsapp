<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Productcategorytype_m extends MY_Model {

    protected $_table_name = 'productcategorytype';
    protected $_primary_key = 'productcategorytypeID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "productcategorytypeID asc";

    function __construct() {
        parent::__construct();
    }

    function get_productcategorytype($array=NULL, $signal=FALSE) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get($array, $signal);
        return $query;
    }

    function get_single_productcategorytype($array) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_single($array);
        return $query;
    }

    function get_order_by_productcategorytype($array=NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_order_by($array);
        return $query;
    }

    function insert_productcategorytype($array) {
        $array['adminID']=$this->session->userdata('adminID');
        $id = parent::insert($array);
        return $id;
    }

    function update_productcategorytype($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    public function delete_productcategorytype($id){
        parent::delete($id);
    }
}
