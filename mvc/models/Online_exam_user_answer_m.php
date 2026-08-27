<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Online_exam_user_answer_m extends MY_Model {

    protected $_table_name = 'online_exam_user_answer';
    protected $_primary_key = 'onlineExamUserAnswerID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "onlineExamUserAnswerID asc";

    function __construct() {
        parent::__construct();
    }

    function get_online_exam_user_answer($array=NULL, $signal=FALSE) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get($array, $signal);
        return $query;
    }

    function get_single_online_exam_user_answer($array) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_single($array);
        return $query;
    }

    function get_order_by_online_exam_user_answer($array=NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
        $query = parent::get_order_by($array);
        return $query;
    }

    function insert_online_exam_user_answer($array) {
        $array['adminID']=$this->session->userdata('adminID');
        $id = parent::insert($array);
        return $id;
    }

    function update_online_exam_user_answer($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    public function delete_online_exam_user_answer($id){
        parent::delete($id);
    }
}
