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

        $this->db->select('user.*, usertype.usertype')
            ->from('user')
            ->join('usertype', 'usertype.usertypeID = user.usertypeID', 'LEFT')
            ->where('user.adminID', $adminID);
            
        if(!empty($campusID) && $campusID > 0) {
            $this->db->where('user.campusID', $campusID);
        }

        $users = $this->db->get()->result_array();
        $this->response(["status" => true, "data" => $users], REST_Controller::HTTP_OK);
    }

    public function view_post()
    {
        $id = (int)$this->post('userID');
        if($id > 0) {
            $user = $this->db->select('user.*, usertype.usertype')
                ->from('user')
                ->join('usertype', 'usertype.usertypeID = user.usertypeID', 'LEFT')
                ->where('user.userID', $id)
                ->get()->row_array();
            if($user) {
                unset($user['password']);

                $documents = $this->db->get_where('document', ['usertypeID' => $user['usertypeID'], 'userID' => $id])->result_array();

                $this->response([
                    "status" => true,
                    "data" => array_merge($user, [
                        'profile' => $user,
                        'documents' => $documents
                    ])
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "User not found"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid user ID"], REST_Controller::HTTP_OK);
        }
    }

    public function add_post()
    {
        $raw = $this->post();
        $name = trim($this->post('name') ?: '');
        $username = trim($this->post('username') ?: '');
        $password = trim($this->post('password') ?: '');
        $email = trim($this->post('email') ?: '');
        $usertypeID = (int)($this->post('usertypeID') ?: 0);

        if(!empty($name) && !empty($username) && !empty($password) && !empty($email) && $usertypeID > 0) {
            $exists = $this->db->get_where('user', ['username' => $username])->row();
            if ($exists) {
                $this->response(["status" => false, "message" => "Username '$username' is already taken"], REST_Controller::HTTP_OK);
                return;
            }

            $allowed_fields = [
                'name', 'dob', 'sex', 'religion', 'email', 'phone', 
                'address', 'jod', 'photo', 'username', 'password', 'usertypeID', 
                'active', 'campusID', 'adminID', 'create_date', 'modify_date',
                'create_userID', 'create_username', 'create_usertype'
            ];

            $insert_data = [];
            foreach ($allowed_fields as $field) {
                if (isset($raw[$field])) {
                    $insert_data[$field] = $raw[$field];
                }
            }

            $insert_data['name'] = $name;
            $insert_data['email'] = $email;
            $insert_data['username'] = $username;
            $insert_data['usertypeID'] = $usertypeID;
            $insert_data['password'] = hash("sha512", $password . config_item("encryption_key"));
            $insert_data['active'] = 1;
            $insert_data['adminID'] = $this->post('adminID') ?: 1;
            $insert_data['campusID'] = (int)($this->post('campusID') ?: 0);
            $insert_data['create_date'] = date("Y-m-d H:i:s");
            $insert_data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->insert('user', $insert_data)) {
                $insert_id = $this->db->insert_id();
                $this->response(["status" => true, "message" => "User added successfully", "userID" => $insert_id], REST_Controller::HTTP_OK);
            } else {
                $dbErr = $this->db->error();
                $this->response(["status" => false, "message" => !empty($dbErr['message']) ? $dbErr['message'] : "Database error"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Required fields missing (Name, Email, Role, Username, Password)"], REST_Controller::HTTP_OK);
        }
    }

    public function update_post()
    {
        $id = (int)$this->post('userID');
        $raw = $this->post();
        if($id > 0 && !empty($raw['name'])) {
            $allowed_fields = [
                'name', 'dob', 'sex', 'religion', 'email', 'phone', 
                'address', 'jod', 'photo', 'usertypeID', 'campusID', 'modify_date'
            ];

            $update_data = [];
            foreach ($allowed_fields as $field) {
                if (isset($raw[$field])) {
                    $update_data[$field] = $raw[$field];
                }
            }
            $update_data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->where('userID', $id)->update('user', $update_data)) {
                $this->response(["status" => true, "message" => "User updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $dbErr = $this->db->error();
                $this->response(["status" => false, "message" => !empty($dbErr['message']) ? $dbErr['message'] : "Update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid user ID or missing Name"], REST_Controller::HTTP_OK);
        }
    }

    public function delete_post()
    {
        $id = $this->post('userID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->where('userID', $id)->delete('user')) {
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
            if($this->db->where('adminID', $adminID)->where('userID', $id)->update('user', ['active' => $status])) {
                $this->response(["status" => true, "message" => "Status updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Status update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid user ID"], REST_Controller::HTTP_OK);
        }
    }
}

