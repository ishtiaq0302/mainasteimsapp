<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Conference_m extends MY_Model {

	protected $_table_name = 'conferences';
	protected $_primary_key = 'id';
	protected $_primary_filter = 'intval';
	protected $_order_by = "asc";

	function __construct() {
		parent::__construct();
	}

	public function get_attendance($array=NULL, $signal=FALSE) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_attendance($array=NULL) {
        $this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_conference($array) {
        $array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
	}

	public function insert_batch_attendance($array) {
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

	public function update_attendance($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function update_batch_attendance($data, $id = NULL) {
        parent::update_batch($data, $id);
        return TRUE;
    }

	public function delete_attendance($id){
		parent::delete($id);
	}

	public function getByStaff($meeting_id = null,$campusID = 0) {
        $this->db->select('conferences.*,classes.classes,section.section,for_create.name as `create_for_name`,for_create.username as `create_for_surname,create_by.name as `create_by_name`,create_by.username as `create_by_surname,for_create.id as `for_create_employee_id`,for_create_role.usertype as `for_create_role_name`,create_by.id as `create_by_employee_id`')->from('conferences');
        $this->db->join('classes', 'classes.classesID = conferences.class_id');
        $this->db->join('section', 'section.sectionID = conferences.section_id');
      $this->db->join('staff as for_create', 'for_create.id = conferences.staff_id');
      $this->db->join('staff as create_by', 'create_by.id = conferences.created_id');
      // $this->db->join('teacher', 'teacher.teacherID = for_create.teacherID');
    $this->db->join('usertype as `for_create_role`', 'for_create_role.usertypeID = for_create.usertypeID');
      $this->db->join('staff as staff_create_by_roles', 'staff_create_by_roles.id = create_by.id');
      // $this->db->join('usertype as `create_by_role`', 'create_by_role.usertypeID = staff_create_by_roles.teacherID');
        //$this->db->where('conferences.session_id', $this->current_session);
        if ($meeting_id != "") {
            $this->db->where('conferences.meeting_id', $meeting_id);
        }
        if ($campusID != 0) {
            $this->db->where('conferences.campusID', $campusID);
        }

        $this->db->where('conferences.adminID',$this->session->userdata('adminID'));

        $this->db->order_by('DATE(`conferences`.`date`)', 'DESC');
        $this->db->order_by('conferences.date', 'DESC');
        $query = $this->db->get();
        // print_r($this->db->last_query());   
        // exit();
        return $query->result();
    }

    public function getByClassSection($class_id, $section_id) {
        $this->db->select('conferences.*,classes.classes,section.section,for_create.name as `create_for_name`,for_create.username as `create_for_surname,for_create.id as `for_create_employee_id`,for_create_role.usertype as `for_create_role_name`')->from('conferences');
        $this->db->join('classes', 'classes.classesID = conferences.class_id');
        $this->db->join('section', 'section.sectionID = conferences.section_id');
        $this->db->join('staff as for_create', 'for_create.id = conferences.staff_id');
        $this->db->join('staff', 'staff.id = for_create.id');
        $this->db->join('usertype as `for_create_role`', 'for_create_role.usertypeID = staff.usertypeID');
        $this->db->where('conferences.class_id', $class_id);
        $this->db->where('conferences.section_id', $section_id);
        $this->db->where('conferences.adminID',$this->session->userdata('adminID'));
        // $this->db->where('conferences.session_id', $this->current_session);
        $this->db->order_by('DATE(`conferences`.`date`)', 'DESC');
        $this->db->order_by('conferences.date', 'DESC');
        $query = $this->db->get();
        // print_r($this->db->last_query());   
        // exit();
        return $query->result();
    }

    public function updates($id, $data) {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id);
        $query = $this->db->update("conferences", $data);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

    public function gets($id = null) {

        $this->db->select('conferences.*,for_create.name as `create_for_surname,create_by.name as `create_by_name`,create_by.username as `create_by_surname,classes.classes,section.section')->from('conferences');
        $this->db->join('staff as for_create', 'for_create.id = conferences.staff_id', 'left');
        $this->db->join('staff as create_by', 'create_by.id = conferences.created_id');
        $this->db->join('classes', 'classes.classesID = conferences.class_id', 'left');
        $this->db->join('section', 'section.sectionID = conferences.section_id', 'left');
        if ($id != null) {
            $this->db->where('conferences.id', $id);
        } else {
            $this->db->order_by('conferences.id');
        }
        $this->db->where('conferences.adminID',$this->session->userdata('adminID'));
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result();
        }
    }

     public function remove($id) {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id);
        $this->db->delete('conferences');
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

}