<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class User extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('user_m');
        $this->load->model('usertype_m');
    }

    public function index_post()
    {
        $campusID = $this->post('campusID');
        $adminID = $this->post('adminID') ?: 1;
        if($campusID) {
            $users = $this->db->select('user.*, usertype.usertype')
                ->from('user')
                ->join('usertype', 'usertype.usertypeID = user.usertypeID', 'LEFT')
                ->where('user.adminID', $adminID)
                ->where('user.campusID', $campusID)
                ->get()->result_array();
            $this->response(["status" => true, "data" => $users], REST_Controller::HTTP_OK);
        } else {
            $this->response(["status" => false, "message" => "Please select a campus"], REST_Controller::HTTP_OK);
        }
    }

    public function view_post()
    {
        $id = $this->post('userID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            $user = $this->db->select('user.*, usertype.usertype')
                ->from('user')
                ->join('usertype', 'usertype.usertypeID = user.usertypeID', 'LEFT')
                ->where('user.adminID', $adminID)
                ->where('user.userID', $id)
                ->get()->row();
            if($user) {
                unset($user->password);
                $this->response(["status" => true, "data" => $user], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "User not found"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid user ID"], REST_Controller::HTTP_OK);
        }
    }

    public function add_post()
    {
        $data = $this->post();
        if(!empty($data['name']) && !empty($data['username']) && !empty($data['password'])) {
            $data['password'] = hash("sha512", $data['password'] . config_item("encryption_key"));
            $data['active'] = 1;
            $data['adminID'] = $this->post('adminID') ?: 1;
            $data['create_date'] = date("Y-m-d H:i:s");
            $data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->insert('user', $data)) {
                $this->response(["status" => true, "message" => "User added successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Database error"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Required fields missing"], REST_Controller::HTTP_OK);
        }
    }

    public function update_post()
    {
        $id = $this->post('userID');
        $data = $this->post();
        $adminID = $this->post('adminID') ?: 1;
        if($id && !empty($data['name'])) {
            $data['modify_date'] = date("Y-m-d H:i:s");
            unset($data['userID'], $data['password'], $data['username'], $data['adminID'], $data['usertype']);

            if($this->db->where('adminID', $adminID)->update('user', $data, ['userID' => $id])) {
                $this->response(["status" => true, "message" => "User updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid data"], REST_Controller::HTTP_OK);
        }
    }

    public function delete_post()
    {
        $id = $this->post('userID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->delete('user', ['userID' => $id])) {
                $this->response(["status" => true, "message" => "User deleted successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Delete failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid user ID"], REST_Controller::HTTP_OK);
        }
    }

    public function status_post()
    {
        $id = $this->post('userID');
        $status = $this->post('status');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->update('user', ['active' => $status], ['userID' => $id])) {
                $this->response(["status" => true, "message" => "Status updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Status update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid user ID"], REST_Controller::HTTP_OK);
        }
    }
}
