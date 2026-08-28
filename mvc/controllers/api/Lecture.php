<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Lecture extends REST_Controller
{
    private $_uploadPath = 'uploads/lecture/';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('upload');

        // Ensure upload directory exists
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
            'max_size'      => 10240, // 10 MB
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
    // LIST  POST /api/lecture
    // ---------------------------------------------------------------
    public function index_post()
    {
        $campusID     = (int)($this->post('campusID')     ?: 0);
        $classesID    = (int)($this->post('classesID')    ?: 0);
        $schoolyearID = (int)($this->post('schoolyearID') ?: 0);
        $adminID      = $this->_adminID();

        $this->db->select('l.*, c.classes as class_name');
        $this->db->from('lecture l');
        $this->db->join('classes c', 'c.classesID = l.classesID', 'left');
        $this->db->where('l.adminID', $adminID);

        if ($campusID     > 0) $this->db->where('l.campusID',     $campusID);
        if ($classesID    > 0) $this->db->where('l.classesID',    $classesID);
        if ($schoolyearID > 0) $this->db->where('l.schoolyearID', $schoolyearID);

        $this->db->order_by('l.date', 'DESC');
        $lectures = $this->db->get()->result_array();

        $baseUrl = base_url($this->_uploadPath);
        foreach ($lectures as &$l) {
            $l['file_url'] = !empty($l['file']) ? $baseUrl . $l['file'] : '';
        }

        $this->_response(['status' => true, 'data' => $lectures]);
    }

    // ---------------------------------------------------------------
    // DETAIL  POST /api/lecture_view
    // ---------------------------------------------------------------
    public function view_post()
    {
        $id      = (int)($this->post('lectureID') ?: 0);
        $adminID = $this->_adminID();

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid lecture ID']);
            return;
        }

        $this->db->select('l.*, c.classes as class_name');
        $this->db->from('lecture l');
        $this->db->join('classes c', 'c.classesID = l.classesID', 'left');
        $this->db->where('l.adminID', $adminID);
        $this->db->where('l.lectureID', $id);
        $lecture = $this->db->get()->row_array();

        if ($lecture) {
            $lecture['file_url'] = !empty($lecture['file']) ? base_url($this->_uploadPath . $lecture['file']) : '';
            $this->_response(['status' => true, 'data' => $lecture]);
        } else {
            $this->_response(['status' => false, 'message' => 'Lecture not found']);
        }
    }

    // ---------------------------------------------------------------
    // ADD  POST /api/lecture_add
    // ---------------------------------------------------------------
    public function add_post()
    {
        $adminID      = $this->_adminID();
        $campusID     = (int)$this->_postField('campusID',     0);
        $classesID    = (int)$this->_postField('classesID',    0);
        $schoolyearID = (int)$this->_postField('schoolyearID', 1);
        $title        = trim((string)$this->_postField('title',       ''));
        $description  = trim((string)$this->_postField('description', ''));

        if ($campusID <= 0 || $classesID <= 0 || $title === '') {
            $this->_response(['status' => false, 'message' => 'Required fields missing (campusID, classesID, title)']);
            return;
        }

        $fileData = $this->_uploadFile();
        if ($fileData === false) {
            $this->_response(['status' => false, 'message' => 'File upload failed: ' . $this->upload->display_errors('', '')]);
            return;
        }

        $insertData = [
            'adminID'      => $adminID,
            'campusID'     => $campusID,
            'classesID'    => $classesID,
            'schoolyearID' => $schoolyearID > 0 ? $schoolyearID : 1,
            'title'        => $title,
            'description'  => $description,
            'date'         => date('Y-m-d'),
            'file'         => $fileData['file'],
            'originalfile' => $fileData['originalfile'],
            'usertypeID'   => 0,
            'userID'       => 0,
        ];

        if ($this->db->insert('lecture', $insertData)) {
            $this->_response(['status' => true, 'message' => 'Lecture added successfully', 'lectureID' => $this->db->insert_id()]);
        } else {
            $this->_response(['status' => false, 'message' => 'Database error']);
        }
    }

    // ---------------------------------------------------------------
    // UPDATE  POST /api/lecture_update
    // ---------------------------------------------------------------
    public function update_post()
    {
        $adminID      = $this->_adminID();
        $id           = (int)$this->_postField('lectureID',    0);
        $campusID     = (int)$this->_postField('campusID',     0);
        $classesID    = (int)$this->_postField('classesID',    0);
        $title        = trim((string)$this->_postField('title',       ''));
        $description  = trim((string)$this->_postField('description', ''));

        if ($id <= 0 || $campusID <= 0 || $classesID <= 0 || $title === '') {
            $this->_response(['status' => false, 'message' => 'Invalid or missing fields']);
            return;
        }

        $updateData = [
            'campusID'    => $campusID,
            'classesID'   => $classesID,
            'title'       => $title,
            'description' => $description,
            'date'        => date('Y-m-d'),
        ];

        $fileData = $this->_uploadFile();
        if ($fileData === false) {
            $this->_response(['status' => false, 'message' => 'File upload failed: ' . $this->upload->display_errors('', '')]);
            return;
        }
        if ($fileData['file'] !== '') {
            $old = $this->db->select('file')->where('lectureID', $id)->get('lecture')->row();
            if ($old && $old->file && file_exists(FCPATH . $this->_uploadPath . $old->file)) {
                @unlink(FCPATH . $this->_uploadPath . $old->file);
            }
            $updateData['file']         = $fileData['file'];
            $updateData['originalfile'] = $fileData['originalfile'];
        }

        if ($this->db->where('adminID', $adminID)->where('lectureID', $id)->update('lecture', $updateData)) {
            $this->_response(['status' => true, 'message' => 'Lecture updated successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Update failed']);
        }
    }

    // ---------------------------------------------------------------
    // DELETE  POST /api/lecture_delete
    // ---------------------------------------------------------------
    public function delete_post()
    {
        $id      = (int)($this->post('lectureID') ?: 0);
        $adminID = $this->_adminID();

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid lecture ID']);
            return;
        }

        $old = $this->db->select('file')->where('lectureID', $id)->get('lecture')->row();
        if ($old && $old->file && file_exists(FCPATH . $this->_uploadPath . $old->file)) {
            @unlink(FCPATH . $this->_uploadPath . $old->file);
        }

        if ($this->db->where('adminID', $adminID)->where('lectureID', $id)->delete('lecture')) {
            $this->_response(['status' => true, 'message' => 'Lecture deleted successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Delete failed']);
        }
    }

    // Alias routes
    public function lecture_view_post()   { $this->view_post();   }
    public function lecture_add_post()    { $this->add_post();    }
    public function lecture_update_post() { $this->update_post(); }
    public function lecture_delete_post() { $this->delete_post(); }
}
