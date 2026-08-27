<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Parents extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('parents_m');
        $this->load->model('student_m');
    }

    public function index_post()
    {
        $campusID = $this->post('campusID');
        $adminID = $this->post('adminID') ?: 1;
        if($campusID) {
            $parents = $this->db->where('adminID', $adminID)->where('campusID', $campusID)->get('parents')->result_array();
            $this->response(["status" => true, "data" => $parents], REST_Controller::HTTP_OK);
        } else {
            $this->response(["status" => false, "message" => "Please select a campus"], REST_Controller::HTTP_OK);
        }
    }

    public function view_post()
    {
        $id = $this->post('parentsID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            $parent = $this->db->where('adminID', $adminID)->where('parentsID', $id)->get('parents')->row();
            if($parent) {
                unset($parent->password);
                $this->response(["status" => true, "data" => $parent], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Parent not found"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid parent ID"], REST_Controller::HTTP_OK);
        }
    }

    public function add_post()
    {
        $data = $this->post();
        if(!empty($data['name']) && !empty($data['username']) && !empty($data['password'])) {
            $data['password'] = hash("sha512", $data['password'] . config_item("encryption_key"));
            $data['usertypeID'] = 4;
            $data['active'] = 1;
            $data['adminID'] = $this->post('adminID') ?: 1;
            $data['create_date'] = date("Y-m-d H:i:s");
            $data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->insert('parents', $data)) {
                $this->response(["status" => true, "message" => "Parent added successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Database error"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Required fields missing"], REST_Controller::HTTP_OK);
        }
    }

    public function update_post()
    {
        $id = $this->post('parentsID');
        $data = $this->post();
        $adminID = $this->post('adminID') ?: 1;
        if($id && !empty($data['name'])) {
            $data['modify_date'] = date("Y-m-d H:i:s");
            unset($data['parentsID'], $data['password'], $data['username'], $data['adminID']);

            if($this->db->where('adminID', $adminID)->update('parents', $data, ['parentsID' => $id])) {
                $this->response(["status" => true, "message" => "Parent updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid data"], REST_Controller::HTTP_OK);
        }
    }

    public function delete_post()
    {
        $id = $this->post('parentsID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->delete('parents', ['parentsID' => $id])) {
                $this->response(["status" => true, "message" => "Parent deleted successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Delete failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid parent ID"], REST_Controller::HTTP_OK);
        }
    }

    public function status_post()
    {
        $id = $this->post('parentsID');
        $status = $this->post('status');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->update('parents', ['active' => $status], ['parentsID' => $id])) {
                $this->response(["status" => true, "message" => "Status updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Status update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid parent ID"], REST_Controller::HTTP_OK);
        }
    }
}
