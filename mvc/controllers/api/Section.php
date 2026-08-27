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

    private function _adminID()
    {
        return (int)($this->post('adminID') ?: 1);
    }

    // ---------------------------------------------------------------
    // LIST  POST /api/section
    // Required: campusID  (optionally classesID for filtered list)
    // ---------------------------------------------------------------
    public function index_post()
    {
        $campusID  = (int)($this->post('campusID')  ?: 0);
        $classesID = (int)($this->post('classesID') ?: 0);
        $adminID   = $this->_adminID();

        if ($campusID <= 0 && $classesID <= 0) {
            $this->_response(['status' => false, 'message' => 'Please provide campusID or classesID']);
            return;
        }

        $this->db->select('s.*, t.name as teacher_name, c.classes as class_name');
        $this->db->from('section s');
        $this->db->join('teacher t', 't.teacherID = s.teacherID', 'left');
        $this->db->join('classes c', 'c.classesID = s.classesID', 'left');
        $this->db->where('s.adminID', $adminID);

        if ($campusID > 0)  $this->db->where('s.campusID',  $campusID);
        if ($classesID > 0) $this->db->where('s.classesID', $classesID);

        $this->db->order_by('s.section', 'ASC');
        $sections = $this->db->get()->result_array();

        $this->_response(['status' => true, 'data' => $sections]);
    }

    // ---------------------------------------------------------------
    // DETAIL  POST /api/section_view
    // Required: sectionID
    // ---------------------------------------------------------------
    public function view_post()
    {
        $id      = (int)($this->post('sectionID') ?: 0);
        $adminID = $this->_adminID();

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid section ID']);
            return;
        }

        $this->db->select('s.*, t.name as teacher_name, c.classes as class_name');
        $this->db->from('section s');
        $this->db->join('teacher t', 't.teacherID = s.teacherID', 'left');
        $this->db->join('classes c', 'c.classesID = s.classesID', 'left');
        $this->db->where('s.adminID', $adminID);
        $this->db->where('s.sectionID', $id);
        $section = $this->db->get()->row_array();

        if ($section) {
            $this->_response(['status' => true, 'data' => $section]);
        } else {
            $this->_response(['status' => false, 'message' => 'Section not found']);
        }
    }

    // ---------------------------------------------------------------
    // ADD  POST /api/section_add
    // Required: campusID, classesID, teacherID, section, category, capacity
    // ---------------------------------------------------------------
    public function add_post()
    {
        $data      = $this->post();
        $adminID   = $this->_adminID();

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

        // Check duplicate section name within the same class
        $exists = $this->db
            ->where('adminID',   $adminID)
            ->where('classesID', $classesID)
            ->where('section',   $section)
            ->get('section')
            ->row();

        if ($exists) {
            $this->_response(['status' => false, 'message' => 'Section name already exists in this class']);
            return;
        }

        $insertData = [
            'adminID'         => $adminID,
            'campusID'        => $campusID,
            'classesID'       => $classesID,
            'teacherID'       => $teacherID,
            'section'         => $section,
            'category'        => $category,
            'capacity'        => $capacity,
            'note'            => trim((string)($data['note'] ?? '')),
            'create_date'     => date('Y-m-d H:i:s'),
            'modify_date'     => date('Y-m-d H:i:s'),
            'create_userID'   => 0,
            'create_username' => '',
            'create_usertype' => '',
        ];

        if ($this->db->insert('section', $insertData)) {
            $this->_response(['status' => true, 'message' => 'Section added successfully', 'sectionID' => $this->db->insert_id()]);
        } else {
            $this->_response(['status' => false, 'message' => 'Database error']);
        }
    }

    // ---------------------------------------------------------------
    // UPDATE  POST /api/section_update
    // Required: sectionID + same fields as add
    // ---------------------------------------------------------------
    public function update_post()
    {
        $data      = $this->post();
        $adminID   = $this->_adminID();
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

        // Check duplicate section name within same class (exclude self)
        $exists = $this->db
            ->where('adminID',      $adminID)
            ->where('classesID',    $classesID)
            ->where('section',      $section)
            ->where('sectionID !=', $id)
            ->get('section')
            ->row();

        if ($exists) {
            $this->_response(['status' => false, 'message' => 'Section name already exists in this class']);
            return;
        }

        $updateData = [
            'campusID'    => $campusID,
            'classesID'   => $classesID,
            'teacherID'   => $teacherID,
            'section'     => $section,
            'category'    => $category,
            'capacity'    => $capacity,
            'note'        => trim((string)($data['note'] ?? '')),
            'modify_date' => date('Y-m-d H:i:s'),
        ];

        if ($this->db->where('adminID', $adminID)->where('sectionID', $id)->update('section', $updateData)) {
            $this->_response(['status' => true, 'message' => 'Section updated successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Update failed']);
        }
    }

    // ---------------------------------------------------------------
    // DELETE  POST /api/section_delete
    // Required: sectionID
    // ---------------------------------------------------------------
    public function delete_post()
    {
        $id      = (int)($this->post('sectionID') ?: 0);
        $adminID = $this->_adminID();

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid section ID']);
            return;
        }

        if ($this->db->where('adminID', $adminID)->where('sectionID', $id)->delete('section')) {
            $this->_response(['status' => true, 'message' => 'Section deleted successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Delete failed']);
        }
    }

    // ---------------------------------------------------------------
    // Alias routes (matching Classes.php convention)
    // ---------------------------------------------------------------
    public function section_view_post()   { $this->view_post();   }
    public function section_add_post()    { $this->add_post();    }
    public function section_update_post() { $this->update_post(); }
    public function section_delete_post() { $this->delete_post(); }
}
