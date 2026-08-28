<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

/**
 * Campus API
 *
 * Endpoints (all POST):
 *   POST /api/campus          – list all campuses for an admin
 *   POST /api/campus_view     – single campus detail
 *   POST /api/campus_add      – add a new campus
 *   POST /api/campus_update   – update a campus name
 *   POST /api/campus_delete   – delete a campus
 *
 * Derived from Campus.php controller logic:
 *   Fields: campusID, adminID, name
 */
class Campus extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function _response($data, $code = REST_Controller::HTTP_OK)
    {
        $this->response($data, $code);
    }

    private function _adminID()
    {
        return (int)($this->post('adminID') ?: 1);
    }

    // ── LIST  POST /api/campus ────────────────────────────────────────────
    // Returns all campuses belonging to the adminID
    public function index_post()
    {
        $adminID = $this->_adminID();

        $campuses = $this->db
            ->where('adminID', $adminID)
            ->order_by('campusID', 'ASC')
            ->get('campus')
            ->result_array();

        $this->_response(['status' => true, 'data' => $campuses]);
    }

    // ── DETAIL  POST /api/campus_view ─────────────────────────────────────
    // Required: campusID
    public function view_post()
    {
        $id      = (int)($this->post('campusID') ?: 0);
        $adminID = $this->_adminID();

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid campus ID']);
            return;
        }

        $campus = $this->db
            ->where('adminID',  $adminID)
            ->where('campusID', $id)
            ->get('campus')
            ->row_array();

        if ($campus) {
            $this->_response(['status' => true, 'data' => $campus]);
        } else {
            $this->_response(['status' => false, 'message' => 'Campus not found']);
        }
    }

    // ── ADD  POST /api/campus_add ─────────────────────────────────────────
    // Required: name
    public function add_post()
    {
        $data    = $this->post();
        $adminID = $this->_adminID();
        $name    = trim((string)($data['name'] ?? ''));

        if ($name === '') {
            $this->_response(['status' => false, 'message' => 'Campus name is required']);
            return;
        }

        // Duplicate name check within same admin
        $exists = $this->db
            ->where('adminID', $adminID)
            ->where('name',    $name)
            ->get('campus')
            ->row();

        if ($exists) {
            $this->_response(['status' => false, 'message' => 'Campus name already exists']);
            return;
        }

        $insertData = [
            'adminID' => $adminID,
            'name'    => $name,
        ];

        if ($this->db->insert('campus', $insertData)) {
            $this->_response(['status' => true, 'message' => 'Campus added successfully', 'campusID' => $this->db->insert_id()]);
        } else {
            $this->_response(['status' => false, 'message' => 'Database error']);
        }
    }

    // ── UPDATE  POST /api/campus_update ──────────────────────────────────
    // Required: campusID, name
    public function update_post()
    {
        $data    = $this->post();
        $adminID = $this->_adminID();
        $id      = (int)($data['campusID'] ?? 0);
        $name    = trim((string)($data['name'] ?? ''));

        if ($id <= 0 || $name === '') {
            $this->_response(['status' => false, 'message' => 'Campus ID and name are required']);
            return;
        }

        // Duplicate name check (exclude self)
        $exists = $this->db
            ->where('adminID',    $adminID)
            ->where('name',       $name)
            ->where('campusID !=', $id)
            ->get('campus')
            ->row();

        if ($exists) {
            $this->_response(['status' => false, 'message' => 'Campus name already exists']);
            return;
        }

        if ($this->db->where('adminID', $adminID)->where('campusID', $id)->update('campus', ['name' => $name])) {
            $this->_response(['status' => true, 'message' => 'Campus updated successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Update failed']);
        }
    }

    // ── DELETE  POST /api/campus_delete ──────────────────────────────────
    // Required: campusID
    public function delete_post()
    {
        $id      = (int)($this->post('campusID') ?: 0);
        $adminID = $this->_adminID();

        if ($id <= 0) {
            $this->_response(['status' => false, 'message' => 'Invalid campus ID']);
            return;
        }

        if ($this->db->where('adminID', $adminID)->where('campusID', $id)->delete('campus')) {
            $this->_response(['status' => true, 'message' => 'Campus deleted successfully']);
        } else {
            $this->_response(['status' => false, 'message' => 'Delete failed']);
        }
    }

    // ── Alias routes ──────────────────────────────────────────────────────
    public function campus_view_post()   { $this->view_post();   }
    public function campus_add_post()    { $this->add_post();    }
    public function campus_update_post() { $this->update_post(); }
    public function campus_delete_post() { $this->delete_post(); }
}
