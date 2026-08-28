<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

/**
 * Routine API
 *
 * Endpoints (all POST):
 *   POST /api/routine          – list routines (filter by campusID, classesID, sectionID, schoolyearID)
 *   POST /api/routine_view     – single routine detail
 *   POST /api/routine_add      – add a new routine entry
 *   POST /api/routine_update   – update an existing routine entry
 *   POST /api/routine_delete   – delete a routine entry
 */
class Routine extends REST_Controller
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

    // ── LIST  POST /api/routine ───────────────────────────────────────────
    public function index_post()
    {
        $data         = $this->_getPostData();
        $adminID      = $this->_adminID($data);
        $campusID     = (int)($data['campusID']     ?? 0);
        $classesID    = (int)($data['classesID']    ?? 0);
        $sectionID    = (int)($data['sectionID']    ?? 0);
        $schoolyearID = (int)($data['schoolyearID'] ?? 0);

        $this->db->select('r.*, t.name as teacher_name, c.classes as class_name, s.section as section_name, sub.subject as subject_name, sy.schoolyear as school_year');
        $this->db->from('routine r');
        $this->db->join('teacher t',     't.teacherID   = r.teacherID',   'left');
        $this->db->join('classes c',     'c.classesID   = r.classesID',   'left');
        $this->db->join('section s',     's.sectionID   = r.sectionID',   'left');
        $this->db->join('subject sub',   'sub.subjectID = r.subjectID',   'left');
        $this->db->join('schoolyear sy', 'sy.schoolyearID = r.schoolyearID', 'left');
        $this->db->where('r.adminID', $adminID);

        if ($campusID > 0) {
            $this->db->group_start()
                     ->where('r.campusID', $campusID)
                     ->or_where('r.campusID', 0)
                     ->or_where('r.campusID IS NULL', null, false)
                     ->group_end();
        }
        if ($classesID > 0)    $this->db->where('r.classesID', $classesID);
        if ($sectionID > 0)    $this->db->where('r.sectionID', $sectionID);
        if ($schoolyearID > 0) $this->db->where('r.schoolyearID', $schoolyearID);

        $this->db->order_by('r.classesID', 'ASC');
        $this->db->order_by('r.day',       'ASC');
        $this->db->order_by('r.start_time','ASC');

        $routines = $this->db->get()->result_array();

        $this->_response(['status' => true, 'data' => $routines]);
    }

    // ── DETAIL  POST /api/routine_view ───────────────────────────────────
    public function view_post()
    {
        $data    = $this->_getPostData();
        $id      = (int)($data['routineID'] ?? 0);
        $adminID = $this->_adminID($data);

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid routine ID']);
            return;
        }

        $this->db->select('r.*, t.name as teacher_name, c.classes as class_name, s.section as section_name, sub.subject as subject_name, sy.schoolyear as school_year');
        $this->db->from('routine r');
        $this->db->join('teacher t',     't.teacherID   = r.teacherID',   'left');
        $this->db->join('classes c',     'c.classesID   = r.classesID',   'left');
        $this->db->join('section s',     's.sectionID   = r.sectionID',   'left');
        $this->db->join('subject sub',   'sub.subjectID = r.subjectID',   'left');
        $this->db->join('schoolyear sy', 'sy.schoolyearID = r.schoolyearID', 'left');
        $this->db->where('r.adminID',   $adminID);
        $this->db->where('r.routineID', $id);

        $routine = $this->db->get()->row_array();

        if ($routine) {
            $this->_response(['status' => true, 'data' => $routine]);
        } else {
            $this->_response(['status' => false, 'message' => 'Routine not found']);
        }
    }

    // ── ADD  POST /api/routine_add ────────────────────────────────────────
    public function add_post()
    {
        $data         = $this->_getPostData();
        $adminID      = $this->_adminID($data);

        $campusID     = (int)($data['campusID']     ?? 0);
        $classesID    = (int)($data['classesID']    ?? 0);
        $sectionID    = (int)($data['sectionID']    ?? 0);
        $subjectID    = (int)($data['subjectID']    ?? 0);
        $teacherID    = (int)($data['teacherID']    ?? 0);
        $schoolyearID = (int)($data['schoolyearID'] ?? 0);
        $day          = trim((string)($data['day']        ?? ''));
        $start_time   = trim((string)($data['start_time'] ?? ''));
        $end_time     = trim((string)($data['end_time']   ?? ''));
        $room         = trim((string)($data['room']       ?? ''));

        if ($schoolyearID <= 0) {
            $schoolyearID = $this->_getDefaultSchoolyearID($adminID);
        }

        if ($classesID <= 0 || $sectionID <= 0 || $subjectID <= 0 ||
            $teacherID <= 0 || $day === '' ||
            $start_time === '' || $end_time === '' || $room === '') {
            $this->_response(['status' => false, 'message' => 'Required fields missing (classesID, sectionID, subjectID, teacherID, day, start_time, end_time, room)']);
            return;
        }

        $insertData = [
            'adminID'      => $adminID,
            'campusID'     => $campusID,
            'classesID'    => $classesID,
            'sectionID'    => $sectionID,
            'subjectID'    => $subjectID,
            'teacherID'    => $teacherID,
            'schoolyearID' => $schoolyearID,
            'day'          => $day,
            'start_time'   => $start_time,
            'end_time'     => $end_time,
            'room'         => $room,
        ];

        if ($this->db->insert('routine', $insertData)) {
            $this->_response(['status' => true, 'message' => 'Routine added successfully', 'routineID' => $this->db->insert_id()]);
        } else {
            $this->_response(['status' => false, 'message' => 'Database error']);
        }
    }

    // ── UPDATE  POST /api/routine_update ─────────────────────────────────
    public function update_post()
    {
        $data         = $this->_getPostData();
        $adminID      = $this->_adminID($data);
        $id           = (int)($data['routineID']    ?? 0);

        $campusID     = (int)($data['campusID']     ?? 0);
        $classesID    = (int)($data['classesID']    ?? 0);
        $sectionID    = (int)($data['sectionID']    ?? 0);
        $subjectID    = (int)($data['subjectID']    ?? 0);
        $teacherID    = (int)($data['teacherID']    ?? 0);
        $schoolyearID = (int)($data['schoolyearID'] ?? 0);
        $day          = trim((string)($data['day']        ?? ''));
        $start_time   = trim((string)($data['start_time'] ?? ''));
        $end_time     = trim((string)($data['end_time']   ?? ''));
        $room         = trim((string)($data['room']       ?? ''));

        if ($schoolyearID <= 0) {
            $schoolyearID = $this->_getDefaultSchoolyearID($adminID);
        }

        if ($id <= 0 || $classesID <= 0 || $sectionID <= 0 ||
            $subjectID <= 0 || $teacherID <= 0 ||
            $day === '' || $start_time === '' || $end_time === '' || $room === '') {
            $this->_response(['status' => false, 'message' => 'Invalid or missing fields']);
            return;
        }

        $updateData = [
            'campusID'     => $campusID,
            'classesID'    => $classesID,
            'sectionID'    => $sectionID,
            'subjectID'    => $subjectID,
            'teacherID'    => $teacherID,
            'schoolyearID' => $schoolyearID,
            'day'          => $day,
            'start_time'   => $start_time,
            'end_time'     => $end_time,
            'room'         => $room,
        ];

        if ($this->db->where('adminID', $adminID)->where('routineID', $id)->update('routine', $updateData)) {
            $this->_response(['status' => true, 'message' => 'Routine updated successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Update failed']);
        }
    }

    // ── DELETE  POST /api/routine_delete ─────────────────────────────────
    public function delete_post()
    {
        $data    = $this->_getPostData();
        $id      = (int)($data['routineID'] ?? 0);
        $adminID = $this->_adminID($data);

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid routine ID']);
            return;
        }

        if ($this->db->where('adminID', $adminID)->where('routineID', $id)->delete('routine')) {
            $this->_response(['status' => true, 'message' => 'Routine deleted successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Delete failed']);
        }
    }

    // ── Alias routes ──────────────────────────────────────────────────────
    public function routine_view_post()   { $this->view_post();   }
    public function routine_add_post()    { $this->add_post();    }
    public function routine_update_post() { $this->update_post(); }
    public function routine_delete_post() { $this->delete_post(); }
}

