<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Assignment extends REST_Controller
{
    private $_uploadPath = 'uploads/assignment/';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('upload');

        if (!is_dir(FCPATH . $this->_uploadPath)) {
            mkdir(FCPATH . $this->_uploadPath, 0755, true);
        }
    }

    private function _response($data, $code = REST_Controller::HTTP_OK)
    {
        $this->response($data, $code);
    }

    private function _adminID()
    {
        return (int)(isset($_POST['adminID']) ? $_POST['adminID'] : ($this->post('adminID') ?: 1));
    }

    private function _postField($key, $default = '')
    {
        $val = isset($_POST[$key]) ? $_POST[$key] : $this->post($key);
        return $val !== null ? $val : $default;
    }

    private function _uploadFile()
    {
        if (empty($_FILES['file']['name'])) {
            return ['file' => '', 'originalfile' => ''];
        }

        $config = [
            'upload_path'   => FCPATH . $this->_uploadPath,
            'allowed_types' => 'pdf|doc|docx|ppt|pptx|xls|xlsx|txt|zip|rar|jpg|jpeg|png',
            'max_size'      => 10240,
            'encrypt_name'  => true,
        ];
        $this->upload->initialize($config);

        if ($this->upload->do_upload('file')) {
            $d = $this->upload->data();
            return ['file' => $d['file_name'], 'originalfile' => $d['orig_name']];
        }

        return false;
    }

    // ---------------------------------------------------------------
    // LIST  POST /api/assignment
    // ---------------------------------------------------------------
    public function index_post()
    {
        $campusID     = (int)($this->post('campusID')     ?: 0);
        $classesID    = (int)($this->post('classesID')    ?: 0);
        $schoolyearID = (int)($this->post('schoolyearID') ?: 0);
        $adminID      = $this->_adminID();

        if ($campusID <= 0 && $classesID <= 0) {
            $this->_response(['status' => false, 'message' => 'Please provide campusID or classesID']);
            return;
        }

        $this->db->select('a.*, c.classes as class_name, s.subject as subject_name');
        $this->db->from('assignment a');
        $this->db->join('classes c',  'c.classesID  = a.classesID',  'left');
        $this->db->join('subject s',  's.subjectID  = a.subjectID',  'left');
        $this->db->where('a.adminID', $adminID);

        if ($campusID     > 0) $this->db->where('a.campusID',     $campusID);
        if ($classesID    > 0) $this->db->where('a.classesID',    $classesID);
        if ($schoolyearID > 0) $this->db->where('a.schoolyearID', $schoolyearID);

        $this->db->order_by('a.deadlinedate', 'DESC');
        $assignments = $this->db->get()->result_array();

        $baseUrl = base_url($this->_uploadPath);
        foreach ($assignments as &$a) {
            $a['file_url']    = $a['file'] ? $baseUrl . $a['file'] : '';
            $a['sectionIDs']  = json_decode($a['sectionID'] ?? '[]', true) ?: [];
        }

        $this->_response(['status' => true, 'data' => $assignments]);
    }

    // ---------------------------------------------------------------
    // DETAIL  POST /api/assignment_view
    // ---------------------------------------------------------------
    public function view_post()
    {
        $id      = (int)($this->post('assignmentID') ?: 0);
        $adminID = $this->_adminID();

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid assignment ID']);
            return;
        }

        $this->db->select('a.*, c.classes as class_name, s.subject as subject_name');
        $this->db->from('assignment a');
        $this->db->join('classes c',  'c.classesID  = a.classesID',  'left');
        $this->db->join('subject s',  's.subjectID  = a.subjectID',  'left');
        $this->db->where('a.adminID', $adminID);
        $this->db->where('a.assignmentID', $id);
        $assignment = $this->db->get()->row_array();

        if ($assignment) {
            $assignment['file_url']   = $assignment['file'] ? base_url($this->_uploadPath . $assignment['file']) : '';
            $assignment['sectionIDs'] = json_decode($assignment['sectionID'] ?? '[]', true) ?: [];
            $this->_response(['status' => true, 'data' => $assignment]);
        } else {
            $this->_response(['status' => false, 'message' => 'Assignment not found']);
        }
    }

    // ---------------------------------------------------------------
    // ADD  POST /api/assignment_add  (multipart/form-data)
    // sectionID[] — send as repeated form fields or JSON string
    // ---------------------------------------------------------------
    public function add_post()
    {
        $adminID      = $this->_adminID();
        $campusID     = (int)$this->_postField('campusID',     0);
        $classesID    = (int)$this->_postField('classesID',    0);
        $subjectID    = (int)$this->_postField('subjectID',    0);
        $schoolyearID = (int)$this->_postField('schoolyearID', 0);
        $title        = trim((string)$this->_postField('title',        ''));
        $description  = trim((string)$this->_postField('description',  ''));
        $deadlinedate = trim((string)$this->_postField('deadlinedate', ''));

        if ($campusID <= 0 || $classesID <= 0 || $subjectID <= 0 || $title === '' || $description === '' || $deadlinedate === '') {
            $this->_response(['status' => false, 'message' => 'Required fields missing (campusID, classesID, subjectID, title, description, deadlinedate)']);
            return;
        }

        // Section IDs: accept JSON string or repeated form field sectionID[]
        $sectionIDsRaw = isset($_POST['sectionIDs']) ? $_POST['sectionIDs'] : $this->post('sectionIDs');
        if (is_string($sectionIDsRaw)) {
            $sectionIDs = json_decode($sectionIDsRaw, true) ?: [];
        } elseif (is_array($sectionIDsRaw)) {
            $sectionIDs = array_map('intval', $sectionIDsRaw);
        } else {
            $sectionIDs = [];
        }

        // File upload
        $fileData = $this->_uploadFile();
        if ($fileData === false) {
            $this->_response(['status' => false, 'message' => 'File upload failed: ' . $this->upload->display_errors('', '')]);
            return;
        }

        $insertData = [
            'adminID'          => $adminID,
            'campusID'         => $campusID,
            'classesID'        => $classesID,
            'subjectID'        => $subjectID,
            'schoolyearID'     => $schoolyearID,
            'title'            => $title,
            'description'      => $description,
            'deadlinedate'     => date('Y-m-d', strtotime($deadlinedate)),
            'date'             => date('Y-m-d'),
            'file'             => $fileData['file'],
            'originalfile'     => $fileData['originalfile'],
            'sectionID'        => json_encode($sectionIDs),
            'usertypeID'       => 0,
            'userID'           => 0,
            'assignusertypeID' => 0,
            'assignuserID'     => 0,
        ];

        if ($this->db->insert('assignment', $insertData)) {
            $this->_response(['status' => true, 'message' => 'Assignment added successfully', 'assignmentID' => $this->db->insert_id()]);
        } else {
            $this->_response(['status' => false, 'message' => 'Database error']);
        }
    }

    // ---------------------------------------------------------------
    // UPDATE  POST /api/assignment_update  (multipart/form-data)
    // ---------------------------------------------------------------
    public function update_post()
    {
        $adminID      = $this->_adminID();
        $id           = (int)$this->_postField('assignmentID', 0);
        $campusID     = (int)$this->_postField('campusID',     0);
        $classesID    = (int)$this->_postField('classesID',    0);
        $subjectID    = (int)$this->_postField('subjectID',    0);
        $title        = trim((string)$this->_postField('title',        ''));
        $description  = trim((string)$this->_postField('description',  ''));
        $deadlinedate = trim((string)$this->_postField('deadlinedate', ''));

        if ($id <= 0 || $campusID <= 0 || $classesID <= 0 || $subjectID <= 0 || $title === '' || $description === '' || $deadlinedate === '') {
            $this->_response(['status' => false, 'message' => 'Invalid or missing fields']);
            return;
        }

        $sectionIDsRaw = isset($_POST['sectionIDs']) ? $_POST['sectionIDs'] : $this->post('sectionIDs');
        if (is_string($sectionIDsRaw)) {
            $sectionIDs = json_decode($sectionIDsRaw, true) ?: [];
        } elseif (is_array($sectionIDsRaw)) {
            $sectionIDs = array_map('intval', $sectionIDsRaw);
        } else {
            $sectionIDs = [];
        }

        $updateData = [
            'campusID'     => $campusID,
            'classesID'    => $classesID,
            'subjectID'    => $subjectID,
            'title'        => $title,
            'description'  => $description,
            'deadlinedate' => date('Y-m-d', strtotime($deadlinedate)),
            'sectionID'    => json_encode($sectionIDs),
        ];

        // File replacement
        $fileData = $this->_uploadFile();
        if ($fileData === false) {
            $this->_response(['status' => false, 'message' => 'File upload failed: ' . $this->upload->display_errors('', '')]);
            return;
        }
        if ($fileData['file'] !== '') {
            $old = $this->db->select('file')->where('assignmentID', $id)->get('assignment')->row();
            if ($old && $old->file && file_exists(FCPATH . $this->_uploadPath . $old->file)) {
                @unlink(FCPATH . $this->_uploadPath . $old->file);
            }
            $updateData['file']         = $fileData['file'];
            $updateData['originalfile'] = $fileData['originalfile'];
        }

        if ($this->db->where('adminID', $adminID)->where('assignmentID', $id)->update('assignment', $updateData)) {
            $this->_response(['status' => true, 'message' => 'Assignment updated successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Update failed']);
        }
    }

    // ---------------------------------------------------------------
    // DELETE  POST /api/assignment_delete
    // ---------------------------------------------------------------
    public function delete_post()
    {
        $id      = (int)($this->post('assignmentID') ?: 0);
        $adminID = $this->_adminID();

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid assignment ID']);
            return;
        }

        $old = $this->db->select('file')->where('assignmentID', $id)->get('assignment')->row();
        if ($old && $old->file && file_exists(FCPATH . $this->_uploadPath . $old->file)) {
            @unlink(FCPATH . $this->_uploadPath . $old->file);
        }

        if ($this->db->where('adminID', $adminID)->where('assignmentID', $id)->delete('assignment')) {
            $this->_response(['status' => true, 'message' => 'Assignment deleted successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Delete failed']);
        }
    }

    // Alias routes
    public function assignment_view_post()   { $this->view_post();   }
    public function assignment_add_post()    { $this->add_post();    }
    public function assignment_update_post() { $this->update_post(); }
    public function assignment_delete_post() { $this->delete_post(); }
}
