<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Posts_m extends MY_Model {

    protected $_table_name = 'posts';
    protected $_primary_key = 'postsID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "publish_date desc";

    function __construct() {
        parent::__construct();
    }

    function get_posts($array=NULL, $signal=FALSE) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get($array, $signal);
        return $query;
    }

    function get_single_posts($array) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_single($array);
        return $query;
    }

    function get_order_by_posts($array=NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_order_by($array);
        return $query;
    }

    function insert_posts($array) {
        $array['adminID']=$this->session->userdata('adminID');
        $id = parent::insert($array);
        return $id;
    }

    function update_posts($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    public function delete_posts($id){
        parent::delete($id);
    }
}
