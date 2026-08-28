<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Section extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _response($data, $code = REST_Controller::HTTP_OK)
    {
        $this->response($data, $code);
    }

    private function _getPostData()
    {
        $data = $this->post();
        if (empty($data)) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: [];
        }
        return $data;
    }

    private function _adminID($data = [])
    {
        return (int)($data['adminID'] ?? $this->post('adminID') ?? 1);
    }

    public function index_post()
    {
        $data      = $this->_getPostData();
        $campusID  = (int)($data['campusID']  ?? $this->post('campusID')  ?: 0);
        $classesID = (int)($data['classesID'] ?? $this->post('classesID') ?: 0);
        $adminID   = $this->_adminID($data);

        if ($campusID <= 0 && $classesID <= 0) {
            $this->_response(['status' => false, 'message' => 'Please provide campusID or classesID']);
            return;
        }

        $this->db->select('s.*, t.name as teacher_name, c.classes as class_name');
        $this->db->from('section s');
        $this->db->join('teacher t', 't.teacherID = s.teacherID', 'left');
        $this->db->join('classes c', 'c.classesID = s.classesID', 'left');
        if ($adminID > 0) {
            $this->db->group_start()
                ->where('s.adminID', $adminID)
                ->or_where('s.adminID', 0)
                ->group_end();
        }

        if ($campusID > 0)  $this->db->where('s.campusID',  $campusID);
        if ($classesID > 0) $this->db->where('s.classesID', $classesID);

        $this->db->order_by('s.section', 'ASC');
        $sections = $this->db->get()->result_array();

        $this->_response(['status' => true, 'data' => $sections]);
    }

    public function view_post()
    {
        $data    = $this->_getPostData();
        $id      = (int)($data['sectionID'] ?? $this->post('sectionID') ?: 0);
        $adminID = $this->_adminID($data);

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid section ID']);
            return;
        }

        $this->db->select('s.*, t.name as teacher_name, c.classes as class_name');
        $this->db->from('section s');
        $this->db->join('teacher t', 't.teacherID = s.teacherID', 'left');
        $this->db->join('classes c', 'c.classesID = s.classesID', 'left');
        if ($adminID > 0) {
            $this->db->group_start()
                ->where('s.adminID', $adminID)
                ->or_where('s.adminID', 0)
                ->group_end();
        }
        $this->db->where('s.sectionID', $id);
        $section = $this->db->get()->row_array();

        if ($section) {
            $this->_response(['status' => true, 'data' => $section]);
        } else {
            $this->_response(['status' => false, 'message' => 'Section not found']);
        }
    }

    public function add_post()
    {
        $data      = $this->_getPostData();
        $adminID   = $this->_adminID($data);

        $campusID  = (int)($data['campusID']  ?? 0);
        $classesID = (int)($data['classesID'] ?? 0);
        $teacherID = (int)($data['teacherID'] ?? 0);
        $section   = trim((string)($data['section']  ?? ''));
        $category  = trim((string)($data['category'] ?? ''));
        $capacity  = (int)($data['capacity'] ?? 0);

        if ($campusID <= 0 || $classesID <= 0 || $teacherID <= 0 || $section === '' || $category === '' || $capacity <= 0) {
            $this->_response(['status' => false, 'message' => 'Required fields missing (campusID, classesID, teacherID, section, category, capacity)']);
            return;
        }

        $exists = $this->db
            ->group_start()
                ->where('adminID', $adminID)
                ->or_where('adminID', 0)
            ->group_end()
            ->where('classesID', $classesID)
            ->where('section',   $section)
            ->get('section')
            ->row();

        if ($exists) {
            $this->_response(['status' => false, 'message' => 'Section name already exists in this class']);
            return;
        }

        $createUserID = (int)($data['create_userID'] ?? 0);
        if ($createUserID <= 0) {
            $createUserID = $adminID > 0 ? $adminID : 1;
        }

        $insertData = [
            'adminID'         => $adminID > 0 ? $adminID : 1,
            'campusID'        => $campusID,
            'classesID'       => $classesID,
            'teacherID'       => $teacherID,
            'section'         => $section,
            'category'        => $category,
            'capacity'        => $capacity,
            'note'            => trim((string)($data['note'] ?? '')),
            'create_date'     => date('Y-m-d H:i:s'),
            'modify_date'     => date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('create_userID', 'section')) {
            $insertData['create_userID']   = $createUserID;
            $insertData['create_username'] = trim((string)($data['create_username'] ?? 'admin'));
            $insertData['create_usertype'] = trim((string)($data['create_usertype'] ?? 'Admin'));
        }

        if ($this->db->insert('section', $insertData)) {
            $this->_response(['status' => true, 'message' => 'Section added successfully', 'sectionID' => $this->db->insert_id()]);
        } else {
            $this->_response(['status' => false, 'message' => 'Database error']);
        }
    }

    public function update_post()
    {
        $data      = $this->_getPostData();
        $adminID   = $this->_adminID($data);
        $id        = (int)($data['sectionID'] ?? 0);

        $campusID  = (int)($data['campusID']  ?? 0);
        $classesID = (int)($data['classesID'] ?? 0);
        $teacherID = (int)($data['teacherID'] ?? 0);
        $section   = trim((string)($data['section']  ?? ''));
        $category  = trim((string)($data['category'] ?? ''));
        $capacity  = (int)($data['capacity'] ?? 0);

        if ($id <= 0 || $campusID <= 0 || $classesID <= 0 || $teacherID <= 0 || $section === '' || $category === '' || $capacity <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid or missing fields']);
            return;
        }

        $exists = $this->db
            ->group_start()
                ->where('adminID', $adminID)
                ->or_where('adminID', 0)
            ->group_end()
            ->where('classesID',    $classesID)
            ->where('section',      $section)
            ->where('sectionID !=', $id)
            ->get('section')
            ->row();

        if ($exists) {
            $this->_response(['status' => false, 'message' => 'Section name already exists in this class']);
            return;
        }

        $createUserID = (int)($data['create_userID'] ?? 0);
        if ($createUserID <= 0) {
            $createUserID = $adminID > 0 ? $adminID : 1;
        }

        $updateData = [
            'adminID'     => $adminID > 0 ? $adminID : 1,
            'campusID'    => $campusID,
            'classesID'   => $classesID,
            'teacherID'   => $teacherID,
            'section'     => $section,
            'category'    => $category,
            'capacity'    => $capacity,
            'note'        => trim((string)($data['note'] ?? '')),
            'modify_date' => date('Y-m-d H:i:s'),
        ];

        if ($this->db->field_exists('create_userID', 'section')) {
            $updateData['create_userID']   = $createUserID;
            $updateData['create_username'] = trim((string)($data['create_username'] ?? 'admin'));
            $updateData['create_usertype'] = trim((string)($data['create_usertype'] ?? 'Admin'));
        }

        if ($adminID > 0) {
            $this->db->group_start()
                ->where('adminID', $adminID)
                ->or_where('adminID', 0)
                ->group_end();
        }
        if ($this->db->where('sectionID', $id)->update('section', $updateData)) {
            $this->_response(['status' => true, 'message' => 'Section updated successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Update failed']);
        }
    }

    public function delete_post()
    {
        $data    = $this->_getPostData();
        $id      = (int)($data['sectionID'] ?? $this->post('sectionID') ?: 0);
        $adminID = $this->_adminID($data);

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid section ID']);
            return;
        }

        if ($adminID > 0) {
            $this->db->group_start()
                ->where('adminID', $adminID)
                ->or_where('adminID', 0)
                ->group_end();
        }
        if ($this->db->where('sectionID', $id)->delete('section')) {
            $this->_response(['status' => true, 'message' => 'Section deleted successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Delete failed']);
        }
    }

    public function section_view_post()   { $this->view_post();   }
    public function section_add_post()    { $this->add_post();    }
    public function section_update_post() { $this->update_post(); }
    public function section_delete_post() { $this->delete_post(); }
}
