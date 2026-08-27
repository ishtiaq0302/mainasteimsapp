<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Classes extends REST_Controller
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

    public function index_post()
    {
        $campusID = (int)($this->post('campusID') ?: 0);
        $adminID = $this->_adminID();

        if ($campusID > 0) {
            $classes = $this->db
                ->where('adminID', $adminID)
                ->where('campusID', $campusID)
                ->order_by('classes_numeric', 'ASC')
                ->get('classes')
                ->result_array();

            $this->_response(['status' => true, 'data' => $classes]);
            return;
        }

        $this->_response(['status' => false, 'message' => 'Please select a campus']);
    }

    public function view_post()
    {
        $id = $this->post('classesID');
        if (empty($id)) {
            $id = $this->post('classID');
        }

        $adminID = $this->_adminID();

        if ($id) {
            $class = $this->db
                ->where('adminID', $adminID)
                ->where('classesID', $id)
                ->get('classes')
                ->row_array();

            if ($class) {
                $this->_response(['status' => true, 'data' => $class]);
                return;
            }

            $this->_response(['status' => false, 'message' => 'Class not found']);
            return;
        }

        $this->_response(['status' => false, 'message' => 'Invalid class ID']);
    }

    public function add_post()
    {
        $data = $this->post();
        $adminID = $this->_adminID();

        $className = trim((string)($data['classes'] ?? ''));
        $classNumeric = trim((string)($data['classes_numeric'] ?? ''));
        $campusID = (int)($data['campusID'] ?? 0);
        $teacherID = (int)($data['teacherID'] ?? 0);

        if ($campusID <= 0 || $className === '' || $classNumeric === '' || $teacherID <= 0) {
            $this->_response(['status' => false, 'message' => 'Required fields missing']);
            return;
        }

        $exists = $this->db
            ->where('adminID', $adminID)
            ->where('campusID', $campusID)
            ->group_start()
                ->where('classes', $className)
                ->or_where('classes_numeric', $classNumeric)
            ->group_end()
            ->get('classes')
            ->row();

        if ($exists) {
            $this->_response(['status' => false, 'message' => 'Class already exists']);
            return;
        }

        $insertData = [
            'adminID' => $adminID,
            'campusID' => $campusID,
            'classes' => $className,
            'classes_numeric' => $classNumeric,
            'teacherID' => $teacherID,
            'studentmaxID' => 999999999,
            'note' => trim((string)($data['note'] ?? '')),
            'create_date' => date('Y-m-d H:i:s'),
            'modify_date' => date('Y-m-d H:i:s'),
            'create_userID' => $this->session->userdata('loginuserID') ?: 0,
            'create_username' => $this->session->userdata('username') ?: '',
            'create_usertype' => $this->session->userdata('usertype') ?: '',
        ];

        if ($this->db->insert('classes', $insertData)) {
            $this->_response(['status' => true, 'message' => 'Class added successfully']);
            return;
        }

        $this->_response(['status' => false, 'message' => 'Database error']);
    }

    public function update_post()
    {
        $id = $this->post('classesID');
        if (empty($id)) {
            $id = $this->post('classID');
        }

        $data = $this->post();
        $adminID = $this->_adminID();

        if ($id && !empty(trim((string)($data['classes'] ?? '')))) {
            $campusID = (int)($data['campusID'] ?? 0);
            $teacherID = (int)($data['teacherID'] ?? 0);

            $updateData = [
                'campusID' => $campusID,
                'classes' => trim((string)$data['classes']),
                'classes_numeric' => trim((string)($data['classes_numeric'] ?? '')),
                'teacherID' => $teacherID,
                'note' => trim((string)($data['note'] ?? '')),
                'modify_date' => date('Y-m-d H:i:s'),
            ];

            if ($this->db
                ->where('adminID', $adminID)
                ->where('classesID', $id)
                ->update('classes', $updateData)) {
                $this->_response(['status' => true, 'message' => 'Class updated successfully']);
                return;
            }

            $this->_response(['status' => false, 'message' => 'Update failed']);
            return;
        }

        $this->_response(['status' => false, 'message' => 'Invalid data']);
    }

    public function delete_post()
    {
        $id = $this->post('classesID');
        if (empty($id)) {
            $id = $this->post('classID');
        }

        $adminID = $this->_adminID();

        if ($id) {
            if ($this->db
                ->where('adminID', $adminID)
                ->where('classesID', $id)
                ->delete('classes')) {
                $this->_response(['status' => true, 'message' => 'Class deleted successfully']);
                return;
            }

            $this->_response(['status' => false, 'message' => 'Delete failed']);
            return;
        }

        $this->_response(['status' => false, 'message' => 'Invalid class ID']);
    }

    public function status_post()
    {
        if (!$this->db->field_exists('active', 'classes')) {
            $this->_response(['status' => false, 'message' => 'Status is not supported for classes']);
            return;
        }

        $id = $this->post('classesID');
        if (empty($id)) {
            $id = $this->post('classID');
        }

        $status = $this->post('status');
        $adminID = $this->_adminID();

        if ($id) {
            if ($this->db
                ->where('adminID', $adminID)
                ->where('classesID', $id)
                ->update('classes', ['active' => $status])) {
                $this->_response(['status' => true, 'message' => 'Status updated successfully']);
                return;
            }
        }

        $this->_response(['status' => false, 'message' => 'Invalid class ID']);
    }

    public function classes_view_post(){ $this->view_post(); }
    public function classes_add_post(){ $this->add_post(); }
    public function classes_update_post(){ $this->update_post(); }
    public function classes_delete_post(){ $this->delete_post(); }
    public function class_view_post(){ $this->view_post(); }
    public function class_add_post(){ $this->add_post(); }
    public function class_update_post(){ $this->update_post(); }
    public function class_delete_post(){ $this->delete_post(); }
}
