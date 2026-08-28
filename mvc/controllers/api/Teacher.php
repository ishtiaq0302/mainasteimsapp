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
        $this->db->where('adminID', $adminID);
        if(!empty($campusID) && $campusID > 0) {
            $this->db->where('campusID', $campusID);
        }
        $teachers = $this->db->get('teacher')->result_array();
        $this->response(["status" => true, "data" => $teachers], REST_Controller::HTTP_OK);
    }

    public function view_post()
    {
        $id = (int)$this->post('teacherID');
        if($id > 0) {
            $teacher = $this->db->where('teacherID', $id)->get('teacher')->row_array();
            if($teacher) {
                unset($teacher['password']);

                // Routines assigned to this teacher
                $this->db->select('r.*, sub.subject, c.classes as class_name, sec.section as section_name');
                $this->db->from('routine r');
                $this->db->join('subject sub', 'sub.subjectID = r.subjectID', 'left');
                $this->db->join('classes c', 'c.classesID = r.classesID', 'left');
                $this->db->join('section sec', 'sec.sectionID = r.sectionID', 'left');
                $this->db->where('r.teacherID', $id);
                $routines = $this->db->get()->result_array();

                // Documents for this teacher
                $documents = $this->db->get_where('document', ['usertypeID' => 2, 'userID' => $id])->result_array();

                $this->response([
                    "status" => true,
                    "data" => array_merge($teacher, [
                        'profile' => $teacher,
                        'routines' => $routines,
                        'documents' => $documents
                    ])
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Teacher not found"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid teacher ID"], REST_Controller::HTTP_OK);
        }
    }

    public function add_post()
    {
        $raw = $this->post();
        $name = trim($this->post('name') ?: '');
        $username = trim($this->post('username') ?: '');
        $password = trim($this->post('password') ?: '');
        $email = trim($this->post('email') ?: '');

        if(!empty($name) && !empty($username) && !empty($password) && !empty($email)) {
            $exists = $this->db->get_where('teacher', ['username' => $username])->row();
            if ($exists) {
                $this->response(["status" => false, "message" => "Username '$username' is already taken"], REST_Controller::HTTP_OK);
                return;
            }

            $allowed_fields = [
                'name', 'designation', 'dob', 'sex', 'religion', 'email', 'phone', 
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
            $insert_data['password'] = hash("sha512", $password . config_item("encryption_key"));
            $insert_data['usertypeID'] = 2;
            $insert_data['active'] = 1;
            $insert_data['adminID'] = $this->post('adminID') ?: 1;
            $insert_data['campusID'] = (int)($this->post('campusID') ?: 0);
            $insert_data['create_date'] = date("Y-m-d H:i:s");
            $insert_data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->insert('teacher', $insert_data)) {
                $insert_id = $this->db->insert_id();
                $this->response(["status" => true, "message" => "Teacher added successfully", "teacherID" => $insert_id], REST_Controller::HTTP_OK);
            } else {
                $dbErr = $this->db->error();
                $this->response(["status" => false, "message" => !empty($dbErr['message']) ? $dbErr['message'] : "Database error"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Required fields missing (Name, Email, Username, Password)"], REST_Controller::HTTP_OK);
        }
    }

    public function update_post()
    {
        $id = (int)$this->post('teacherID');
        $raw = $this->post();
        if($id > 0 && !empty($raw['name'])) {
            $allowed_fields = [
                'name', 'designation', 'dob', 'sex', 'religion', 'email', 'phone', 
                'address', 'jod', 'photo', 'campusID', 'modify_date'
            ];

            $update_data = [];
            foreach ($allowed_fields as $field) {
                if (isset($raw[$field])) {
                    $update_data[$field] = $raw[$field];
                }
            }
            $update_data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->where('teacherID', $id)->update('teacher', $update_data)) {
                $this->response(["status" => true, "message" => "Teacher updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $dbErr = $this->db->error();
                $this->response(["status" => false, "message" => !empty($dbErr['message']) ? $dbErr['message'] : "Update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid teacher ID or missing Name"], REST_Controller::HTTP_OK);
        }
    }

    public function delete_post()
    {
        $id = $this->post('teacherID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->where('teacherID', $id)->delete('teacher')) {
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
            if($this->db->where('adminID', $adminID)->where('teacherID', $id)->update('teacher', ['active' => $status])) {
                $this->response(["status" => true, "message" => "Status updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Status update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid teacher ID"], REST_Controller::HTTP_OK);
        }
    }
}

