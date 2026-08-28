<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Subject extends REST_Controller
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

    // ---------------------------------------------------------------
    // LIST  POST /api/subject
    // ---------------------------------------------------------------
    public function index_post()
    {
        $data      = $this->_getPostData();
        $campusID  = (int)($data['campusID']  ?? 0);
        $classesID = (int)($data['classesID'] ?? 0);
        $adminID   = $this->_adminID($data);

        // subject table has NO teacherID column.
        // Teacher is linked via the subjectteacher junction table.
        // teacher_name is a denormalized text column on subject.
        $this->db->select('s.*, c.classes as class_name');
        $this->db->from('subject s');
        $this->db->join('classes c', 'c.classesID = s.classesID', 'left');

        if ($adminID > 0) {
            $this->db->group_start()
                     ->where('s.adminID', $adminID)
                     ->or_where('s.adminID', 0)
                     ->or_where('s.adminID IS NULL', null, false)
                     ->group_end();
        }

        if ($campusID > 0) {
            $this->db->group_start()
                     ->where('s.campusID', $campusID)
                     ->or_where('s.campusID', 0)
                     ->or_where('s.campusID IS NULL', null, false)
                     ->group_end();
        }
        if ($classesID > 0) {
            $this->db->where('s.classesID', $classesID);
        }

        $this->db->order_by('s.subject', 'ASC');
        $subjects = $this->db->get()->result_array();

        // Resolve teacher names from subjectteacher junction table
        if ($this->db->table_exists('subjectteacher')) {
            foreach ($subjects as &$sub) {
                if (empty($sub['teacher_name'])) {
                    $st = $this->db->select('t.name')
                        ->from('subjectteacher st')
                        ->join('teacher t', 't.teacherID = st.teacherID', 'left')
                        ->where('st.subjectID', $sub['subjectID'])
                        ->get()->row_array();
                    if ($st && !empty($st['name'])) {
                        $sub['teacher_name'] = $st['name'];
                    }
                }
            }
        }

        $this->_response(['status' => true, 'data' => $subjects]);
    }

    // ---------------------------------------------------------------
    // DETAIL  POST /api/subject_view
    // ---------------------------------------------------------------
    public function view_post()
    {
        $data    = $this->_getPostData();
        $id      = (int)($data['subjectID'] ?? 0);
        $adminID = $this->_adminID($data);

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid subject ID']);
            return;
        }

        $this->db->select('s.*, c.classes as class_name');
        $this->db->from('subject s');
        $this->db->join('classes c', 'c.classesID = s.classesID', 'left');
        if ($adminID > 0) {
            $this->db->group_start()
                     ->where('s.adminID', $adminID)
                     ->or_where('s.adminID', 0)
                     ->or_where('s.adminID IS NULL', null, false)
                     ->group_end();
        }
        $this->db->where('s.subjectID', $id);
        $subject = $this->db->get()->row_array();

        if ($subject) {
            // Resolve teacher from junction
            if (empty($subject['teacher_name']) && $this->db->table_exists('subjectteacher')) {
                $st = $this->db->select('t.name, st.teacherID')
                    ->from('subjectteacher st')
                    ->join('teacher t', 't.teacherID = st.teacherID', 'left')
                    ->where('st.subjectID', $id)
                    ->get()->row_array();
                if ($st && !empty($st['name'])) {
                    $subject['teacher_name'] = $st['name'];
                    $subject['teacherID']    = $st['teacherID'];
                }
            }
            $this->_response(['status' => true, 'data' => $subject]);
        } else {
            $this->_response(['status' => false, 'message' => 'Subject not found']);
        }
    }

    // ---------------------------------------------------------------
    // ADD  POST /api/subject_add
    // ---------------------------------------------------------------
    public function add_post()
    {
        $data         = $this->_getPostData();
        $adminID      = $this->_adminID($data);

        $campusID     = (int)($data['campusID']   ?? 0);
        $classesID    = (int)($data['classesID']  ?? 0);
        $teacherID    = (int)($data['teacherID']  ?? 0);
        $subject      = trim((string)($data['subject']      ?? ''));
        $subject_code = trim((string)($data['subject_code'] ?? ''));
        $passmark     = (int)($data['passmark']   ?? 0);
        $finalmark    = (int)($data['finalmark']  ?? 0);

        // type column is int: 1=Compulsory, 0=Optional
        $rawType = trim((string)($data['type'] ?? '1'));
        if (strtolower($rawType) === 'compulsory' || $rawType === '1') {
            $type = 1;
        } elseif (strtolower($rawType) === 'optional' || $rawType === '0') {
            $type = 0;
        } else {
            $type = (int)$rawType;
        }

        if ($campusID <= 0 || $classesID <= 0 || $subject === '' || $subject_code === '') {
            $this->_response(['status' => false, 'message' => 'Required fields missing (campus, class, subject name, or code)']);
            return;
        }

        // Duplicate name check
        $exists = $this->db
            ->where('adminID',   $adminID)
            ->where('classesID', $classesID)
            ->where('subject',   $subject)
            ->get('subject')->row();

        if ($exists) {
            $this->_response(['status' => false, 'message' => 'Subject name already exists in this class']);
            return;
        }

        // Resolve teacher_name for the denormalized column
        $teacher_name = '';
        if ($teacherID > 0) {
            $teacher = $this->db->select('name')->where('teacherID', $teacherID)->get('teacher')->row();
            if ($teacher) $teacher_name = $teacher->name;
        }

        $insertData = [
            'adminID'         => $adminID,
            'campusID'        => $campusID,
            'classesID'       => $classesID,
            'subject'         => $subject,
            'subject_code'    => $subject_code,
            'type'            => $type,
            'passmark'        => $passmark,
            'finalmark'       => $finalmark,
            'subject_author'  => trim((string)($data['subject_author'] ?? '')),
            'teacher_name'    => $teacher_name,
            'create_date'     => date('Y-m-d H:i:s'),
            'modify_date'     => date('Y-m-d H:i:s'),
            'create_userID'   => (int)($data['create_userID'] ?? 0),
            'create_username' => trim((string)($data['create_username'] ?? 'admin')),
            'create_usertype' => trim((string)($data['create_usertype'] ?? 'Admin')),
        ];

        if ($this->db->insert('subject', $insertData)) {
            $subjectID = $this->db->insert_id();
            // Insert into junction table
            if ($teacherID > 0 && $this->db->table_exists('subjectteacher')) {
                $this->db->insert('subjectteacher', [
                    'adminID'   => $adminID,
                    'campusID'  => $campusID,
                    'subjectID' => $subjectID,
                    'teacherID' => $teacherID,
                    'classesID' => $classesID,
                ]);
            }
            $this->_response(['status' => true, 'message' => 'Subject added successfully', 'subjectID' => $subjectID]);
        } else {
            $err = $this->db->error();
            $this->_response(['status' => false, 'message' => 'Database error: ' . ($err['message'] ?? 'Unknown')]);
        }
    }

    // ---------------------------------------------------------------
    // UPDATE  POST /api/subject_update
    // ---------------------------------------------------------------
    public function update_post()
    {
        $data         = $this->_getPostData();
        $adminID      = $this->_adminID($data);
        $id           = (int)($data['subjectID']    ?? 0);
        $campusID     = (int)($data['campusID']     ?? 0);
        $classesID    = (int)($data['classesID']    ?? 0);
        $teacherID    = (int)($data['teacherID']    ?? 0);
        $subject      = trim((string)($data['subject']      ?? ''));
        $subject_code = trim((string)($data['subject_code'] ?? ''));

        // type column is int: 1=Compulsory, 0=Optional
        $rawType = trim((string)($data['type'] ?? '1'));
        if (strtolower($rawType) === 'compulsory' || $rawType === '1') {
            $type = 1;
        } elseif (strtolower($rawType) === 'optional' || $rawType === '0') {
            $type = 0;
        } else {
            $type = (int)$rawType;
        }

        if ($id <= 0 || $campusID <= 0 || $classesID <= 0 || $subject === '' || $subject_code === '') {
            $this->_response(['status' => false, 'message' => 'Invalid or missing fields']);
            return;
        }

        // Resolve teacher_name
        $teacher_name = '';
        if ($teacherID > 0) {
            $teacher = $this->db->select('name')->where('teacherID', $teacherID)->get('teacher')->row();
            if ($teacher) $teacher_name = $teacher->name;
        }

        $updateData = [
            'campusID'       => $campusID,
            'classesID'      => $classesID,
            'subject'        => $subject,
            'subject_code'   => $subject_code,
            'type'           => $type,
            'passmark'       => (int)($data['passmark']  ?? 0),
            'finalmark'      => (int)($data['finalmark'] ?? 0),
            'subject_author' => trim((string)($data['subject_author'] ?? '')),
            'teacher_name'   => $teacher_name,
            'modify_date'    => date('Y-m-d H:i:s'),
        ];

        if ($this->db->where('adminID', $adminID)->where('subjectID', $id)->update('subject', $updateData)) {
            // Update junction table
            if ($this->db->table_exists('subjectteacher')) {
                $this->db->where('subjectID', $id)->delete('subjectteacher');
                if ($teacherID > 0) {
                    $this->db->insert('subjectteacher', [
                        'adminID'   => $adminID,
                        'campusID'  => $campusID,
                        'subjectID' => $id,
                        'teacherID' => $teacherID,
                        'classesID' => $classesID,
                    ]);
                }
            }
            $this->_response(['status' => true, 'message' => 'Subject updated successfully']);
        } else {
            $err = $this->db->error();
            $this->_response(['status' => false, 'message' => 'Update failed: ' . ($err['message'] ?? 'Unknown')]);
        }
    }

    // ---------------------------------------------------------------
    // DELETE  POST /api/subject_delete
    // ---------------------------------------------------------------
    public function delete_post()
    {
        $data    = $this->_getPostData();
        $id      = (int)($data['subjectID'] ?? 0);
        $adminID = $this->_adminID($data);

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid subject ID']);
            return;
        }

        if ($this->db->where('adminID', $adminID)->where('subjectID', $id)->delete('subject')) {
            if ($this->db->table_exists('subjectteacher')) {
                $this->db->where('subjectID', $id)->delete('subjectteacher');
            }
            $this->_response(['status' => true, 'message' => 'Subject deleted successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Delete failed']);
        }
    }

    // Alias routes
    public function subject_view_post()   { $this->view_post();   }
    public function subject_add_post()    { $this->add_post();    }
    public function subject_update_post() { $this->update_post(); }
    public function subject_delete_post() { $this->delete_post(); }
}
