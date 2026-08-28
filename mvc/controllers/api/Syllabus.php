<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

/**
 * Syllabus API
 *
 * Endpoints (all POST):
 *   POST /api/syllabus          – list syllabuses (filter by campusID, classesID, schoolyearID)
 *   POST /api/syllabus_view     – single syllabus detail
 *   POST /api/syllabus_add      – add a new syllabus (with base64 file upload support)
 *   POST /api/syllabus_update   – update an existing syllabus
 *   POST /api/syllabus_delete   – delete a syllabus (also removes the uploaded file)
 */
class Syllabus extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper('action');
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
        return (int)($data['adminID'] ?? $this->post('adminID') ?: 1);
    }

    private function _getDefaultSchoolyearID($adminID = 1)
    {
        $site = $this->db->get('siteinfos')->row();
        if ($site && !empty($site->school_year)) {
            return (int)$site->school_year;
        }
        $sy = $this->db->order_by('schoolyearID', 'DESC')->get('schoolyear')->row();
        return $sy ? (int)$sy->schoolyearID : 1;
    }

    /**
     * Save a base64-encoded file to uploads/images/
     */
    private function _saveFile($base64Data, $originalFileName)
    {
        if (empty($base64Data) || empty($originalFileName)) {
            return false;
        }

        $parts   = explode('.', $originalFileName);
        $ext     = strtolower(end($parts));
        $allowed = ['gif','jpg','jpeg','png','pdf','doc','docx','xml','xls','xlsx','txt','ppt','csv'];

        if (!in_array($ext, $allowed)) {
            return false;
        }

        if (strpos($base64Data, ',') !== false) {
            $base64Data = explode(',', $base64Data)[1];
        }

        $decoded = base64_decode($base64Data, true);
        if ($decoded === false) {
            return false;
        }

        $newName = hash('sha256', uniqid('', true) . $originalFileName) . '.' . $ext;
        $path    = FCPATH . 'uploads/images/' . $newName;

        if (file_put_contents($path, $decoded) === false) {
            return false;
        }

        return ['file_name' => $newName, 'original_file_name' => $originalFileName];
    }

    // ── LIST  POST /api/syllabus ──────────────────────────────────────────
    public function index_post()
    {
        $data         = $this->_getPostData();
        $adminID      = $this->_adminID($data);
        $campusID     = (int)($data['campusID']     ?? 0);
        $classesID    = (int)($data['classesID']    ?? 0);
        $schoolyearID = (int)($data['schoolyearID'] ?? 0);

        $this->db->select('sy.*, c.classes as class_name, cp.name as campus_name');
        $this->db->from('syllabus sy');
        $this->db->join('classes c',   'c.classesID  = sy.classesID',  'left');
        $this->db->join('campus  cp',  'cp.campusID  = sy.campusID',   'left');
        $this->db->where('sy.adminID', $adminID);

        if ($campusID > 0) {
            $this->db->group_start()
                     ->where('sy.campusID', $campusID)
                     ->or_where('sy.campusID', 0)
                     ->or_where('sy.campusID IS NULL', null, false)
                     ->group_end();
        }
        if ($classesID    > 0) $this->db->where('sy.classesID',    $classesID);
        if ($schoolyearID > 0) $this->db->where('sy.schoolyearID', $schoolyearID);

        $this->db->order_by('sy.date', 'DESC');
        $syllabuses = $this->db->get()->result_array();

        $base = base_url('uploads/images/');
        foreach ($syllabuses as &$row) {
            $row['file_url'] = !empty($row['file']) ? $base . $row['file'] : null;
            $row['uploader'] = function_exists('getNameByUsertypeIDAndUserID')
                ? getNameByUsertypeIDAndUserID($row['usertypeID'], $row['userID'])
                : 'Admin';
        }
        unset($row);

        $this->_response(['status' => true, 'data' => $syllabuses]);
    }

    // ── DETAIL  POST /api/syllabus_view ──────────────────────────────────
    public function view_post()
    {
        $data    = $this->_getPostData();
        $id      = (int)($data['syllabusID'] ?? 0);
        $adminID = $this->_adminID($data);

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid syllabus ID']);
            return;
        }

        $this->db->select('sy.*, c.classes as class_name, cp.name as campus_name');
        $this->db->from('syllabus sy');
        $this->db->join('classes c',   'c.classesID  = sy.classesID',  'left');
        $this->db->join('campus  cp',  'cp.campusID  = sy.campusID',   'left');
        $this->db->where('sy.adminID',   $adminID);
        $this->db->where('sy.syllabusID', $id);
        $syllabus = $this->db->get()->row_array();

        if ($syllabus) {
            $syllabus['file_url'] = !empty($syllabus['file'])
                ? base_url('uploads/images/' . $syllabus['file'])
                : null;
            $syllabus['uploader'] = function_exists('getNameByUsertypeIDAndUserID')
                ? getNameByUsertypeIDAndUserID($syllabus['usertypeID'], $syllabus['userID'])
                : 'Admin';
            $this->_response(['status' => true, 'data' => $syllabus]);
        } else {
            $this->_response(['status' => false, 'message' => 'Syllabus not found']);
        }
    }

    // ── ADD  POST /api/syllabus_add ───────────────────────────────────────
    public function add_post()
    {
        $data         = $this->_getPostData();
        $adminID      = $this->_adminID($data);

        $campusID     = (int)($data['campusID']     ?? 0);
        $classesID    = (int)($data['classesID']    ?? 0);
        $schoolyearID = (int)($data['schoolyearID'] ?? 0);
        $title        = trim((string)($data['title']       ?? ''));
        $description  = trim((string)($data['description'] ?? ''));

        if ($schoolyearID <= 0) {
            $schoolyearID = $this->_getDefaultSchoolyearID($adminID);
        }

        if ($classesID <= 0 || $title === '' || $description === '') {
            $this->_response(['status' => false, 'message' => 'Required fields missing (classesID, title, description)']);
            return;
        }

        // Handle file
        $fileBase64  = trim((string)($data['file_base64'] ?? ''));
        $fileName    = trim((string)($data['file_name']   ?? ''));
        $savedFile   = null;

        if (!empty($fileBase64) && !empty($fileName)) {
            $savedFile = $this->_saveFile($fileBase64, $fileName);
            if ($savedFile === false) {
                $this->_response(['status' => false, 'message' => 'File upload failed — check file type and size']);
                return;
            }
        } else {
            $this->_response(['status' => false, 'message' => 'A syllabus file is required']);
            return;
        }

        $insertData = [
            'adminID'      => $adminID,
            'campusID'     => $campusID,
            'classesID'    => $classesID,
            'schoolyearID' => $schoolyearID,
            'title'        => $title,
            'description'  => $description,
            'date'         => date('Y-m-d'),
            'file'         => $savedFile['file_name'],
            'originalfile' => $savedFile['original_file_name'],
            'usertypeID'   => 1,
            'userID'       => 0,
        ];

        if ($this->db->insert('syllabus', $insertData)) {
            $this->_response(['status' => true, 'message' => 'Syllabus added successfully', 'syllabusID' => $this->db->insert_id()]);
        } else {
            $this->_response(['status' => false, 'message' => 'Database error']);
        }
    }

    // ── UPDATE  POST /api/syllabus_update ─────────────────────────────────
    public function update_post()
    {
        $data         = $this->_getPostData();
        $adminID      = $this->_adminID($data);
        $id           = (int)($data['syllabusID']   ?? 0);
        $campusID     = (int)($data['campusID']     ?? 0);
        $classesID    = (int)($data['classesID']    ?? 0);
        $schoolyearID = (int)($data['schoolyearID'] ?? 0);
        $title        = trim((string)($data['title']       ?? ''));
        $description  = trim((string)($data['description'] ?? ''));

        if ($id <= 0 || $classesID <= 0 || $title === '' || $description === '') {
            $this->_response(['status' => false, 'message' => 'Invalid or missing fields']);
            return;
        }

        $existing = $this->db->where('adminID', $adminID)->where('syllabusID', $id)->get('syllabus')->row_array();
        if (!$existing) {
            $this->_response(['status' => false, 'message' => 'Syllabus not found']);
            return;
        }

        $updateData = [
            'campusID'    => $campusID,
            'classesID'   => $classesID,
            'title'       => $title,
            'description' => $description,
            'date'        => date('Y-m-d'),
        ];

        if ($schoolyearID > 0) {
            $updateData['schoolyearID'] = $schoolyearID;
        }

        $fileBase64 = trim((string)($data['file_base64'] ?? ''));
        $fileName   = trim((string)($data['file_name']   ?? ''));

        if (!empty($fileBase64) && !empty($fileName)) {
            $savedFile = $this->_saveFile($fileBase64, $fileName);
            if ($savedFile === false) {
                $this->_response(['status' => false, 'message' => 'File upload failed — check file type']);
                return;
            }
            $oldPath = FCPATH . 'uploads/images/' . $existing['file'];
            if (!empty($existing['file']) && file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $updateData['file']         = $savedFile['file_name'];
            $updateData['originalfile'] = $savedFile['original_file_name'];
        }

        if ($this->db->where('adminID', $adminID)->where('syllabusID', $id)->update('syllabus', $updateData)) {
            $this->_response(['status' => true, 'message' => 'Syllabus updated successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Update failed']);
        }
    }

    // ── DELETE  POST /api/syllabus_delete ─────────────────────────────────
    public function delete_post()
    {
        $data    = $this->_getPostData();
        $id      = (int)($data['syllabusID'] ?? 0);
        $adminID = $this->_adminID($data);

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid syllabus ID']);
            return;
        }

        $syllabus = $this->db->where('adminID', $adminID)->where('syllabusID', $id)->get('syllabus')->row_array();
        if (!$syllabus) {
            $this->_response(['status' => false, 'message' => 'Syllabus not found']);
            return;
        }

        if (!empty($syllabus['file'])) {
            $filePath = FCPATH . 'uploads/images/' . $syllabus['file'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        if ($this->db->where('adminID', $adminID)->where('syllabusID', $id)->delete('syllabus')) {
            $this->_response(['status' => true, 'message' => 'Syllabus deleted successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Delete failed']);
        }
    }

    // ── Schoolyears list  POST /api/schoolyears ───────────────────────────
    public function schoolyears_post()
    {
        $data    = $this->_getPostData();
        $adminID = $this->_adminID($data);

        $years = $this->db
            ->select('schoolyearID, schoolyear as year, schoolyear, schoolyeartitle')
            ->where('adminID', $adminID)
            ->order_by('schoolyearID', 'DESC')
            ->get('schoolyear')
            ->result_array();

        if (empty($years)) {
            $years = $this->db
                ->select('schoolyearID, schoolyear as year, schoolyear, schoolyeartitle')
                ->order_by('schoolyearID', 'DESC')
                ->get('schoolyear')
                ->result_array();
        }

        $this->_response(['status' => true, 'data' => $years]);
    }

    // ── Alias routes ──────────────────────────────────────────────────────
    public function syllabus_view_post()   { $this->view_post();        }
    public function syllabus_add_post()    { $this->add_post();         }
    public function syllabus_update_post() { $this->update_post();      }
    public function syllabus_delete_post() { $this->delete_post();      }
    public function syllabus_years_post()  { $this->schoolyears_post(); }
}

