<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Teacher extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('teacher_m');
    }

    public function index_post()
    {
        $campusID = $this->post('campusID');
        $adminID = $this->post('adminID') ?: 1;
        if($campusID) {
            $teachers = $this->db->where('adminID', $adminID)->where('campusID', $campusID)->get('teacher')->result_array();
            $this->response(["status" => true, "data" => $teachers], REST_Controller::HTTP_OK);
        } else {
            $this->response(["status" => false, "message" => "Please select a campus"], REST_Controller::HTTP_OK);
        }
    }

    public function view_post()
    {
        $id = $this->post('teacherID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            $teacher = $this->db->where('adminID', $adminID)->where('teacherID', $id)->get('teacher')->row();
            if($teacher) {
                unset($teacher->password);
                $this->response(["status" => true, "data" => $teacher], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Teacher not found"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid teacher ID"], REST_Controller::HTTP_OK);
        }
    }

    public function add_post()
    {
        $data = $this->post();
        if(!empty($data['name']) && !empty($data['username']) && !empty($data['password'])) {
            $data['password'] = hash("sha512", $data['password'] . config_item("encryption_key"));
            $data['usertypeID'] = 2;
            $data['active'] = 1;
            $data['adminID'] = $this->post('adminID') ?: 1;
            $data['create_date'] = date("Y-m-d H:i:s");
            $data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->insert('teacher', $data)) {
                $this->response(["status" => true, "message" => "Teacher added successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Database error"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Required fields missing"], REST_Controller::HTTP_OK);
        }
    }

    public function update_post()
    {
        $id = $this->post('teacherID');
        $data = $this->post();
        $adminID = $this->post('adminID') ?: 1;
        if($id && !empty($data['name'])) {
            $data['modify_date'] = date("Y-m-d H:i:s");
            unset($data['teacherID'], $data['password'], $data['username'], $data['adminID']);

            if($this->db->where('adminID', $adminID)->update('teacher', $data, ['teacherID' => $id])) {
                $this->response(["status" => true, "message" => "Teacher updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid data"], REST_Controller::HTTP_OK);
        }
    }

    public function delete_post()
    {
        $id = $this->post('teacherID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->delete('teacher', ['teacherID' => $id])) {
                $this->response(["status" => true, "message" => "Teacher deleted successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Delete failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid teacher ID"], REST_Controller::HTTP_OK);
        }
    }

    public function status_post()
    {
        $id = $this->post('teacherID');
        $status = $this->post('status');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->update('teacher', ['active' => $status], ['teacherID' => $id])) {
                $this->response(["status" => true, "message" => "Status updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Status update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid teacher ID"], REST_Controller::HTTP_OK);
        }
    }
}
