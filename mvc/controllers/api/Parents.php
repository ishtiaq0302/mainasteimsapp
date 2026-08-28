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

        $this->db->where('adminID', $adminID);
        if(!empty($campusID) && $campusID > 0) {
            $this->db->where('campusID', $campusID);
        }
        $parents = $this->db->get('parents')->result_array();
        $this->response(["status" => true, "data" => $parents], REST_Controller::HTTP_OK);
    }

    public function view_post()
    {
        $id = $this->post('parentsID') ?: $this->post('parentID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            $parent = $this->db->where('parentsID', $id)->get('parents')->row_array();
            if($parent) {
                unset($parent['password']);

                $childrens = $this->db->select('sr.*, s.photo, s.email, s.phone, c.classes as class_name, sec.section as section_name')
                    ->from('studentrelation sr')
                    ->join('student s', 's.studentID = sr.srstudentID')
                    ->join('classes c', 'c.classesID = sr.srclassesID', 'left')
                    ->join('section sec', 'sec.sectionID = sr.srsectionID', 'left')
                    ->where('s.parentID', $id)
                    ->get()->result_array();

                $documents = $this->db->get_where('document', ['usertypeID' => 4, 'userID' => $id])->result_array();

                $this->response([
                    "status" => true,
                    "data" => array_merge($parent, [
                        'profile' => $parent,
                        'childrens' => $childrens,
                        'documents' => $documents
                    ])
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Parent not found"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid parent ID"], REST_Controller::HTTP_OK);
        }
    }

    public function add_post()
    {
        $raw = $this->post();
        $name = trim($this->post('name') ?: '');
        $username = trim($this->post('username') ?: '');
        $password = trim($this->post('password') ?: '');

        if(!empty($name) && !empty($username) && !empty($password)) {
            // Check username uniqueness
            $exists = $this->db->get_where('parents', ['username' => $username])->row();
            if ($exists) {
                $this->response(["status" => false, "message" => "Username '$username' is already taken"], REST_Controller::HTTP_OK);
                return;
            }

            $allowed_fields = [
                'name', 'father_name', 'father_profession', 'mother_name', 'mother_profession', 
                'email', 'phone', 'address', 'photo', 'username', 'password', 'usertypeID', 
                'active', 'campusID', 'adminID', 'create_date', 'modify_date',
                'father_phone', 'father_cnic', 'father_office_addresss', 'father_nationality', 
                'mother_phone', 'mother_cnic', 'mother_office_addresss', 'mother_address', 
                'mother_qualification', 'mother_nationality', 'guardian_profession', 
                'guardian_cnic', 'guardian_address', 'guardian_qualification', 
                'guardian_office_addresss', 'guardian_nationality', 'guardian_realation_with_child',
                'create_userID', 'create_username', 'create_usertype'
            ];

            $insert_data = [];
            foreach ($allowed_fields as $field) {
                if (isset($raw[$field])) {
                    $insert_data[$field] = $raw[$field];
                }
            }

            $insert_data['name'] = $name;
            $insert_data['username'] = $username;
            $insert_data['password'] = hash("sha512", $password . config_item("encryption_key"));
            $insert_data['usertypeID'] = 4;
            $insert_data['active'] = 1;
            $insert_data['adminID'] = $this->post('adminID') ?: 1;
            $insert_data['campusID'] = (int)($this->post('campusID') ?: 0);
            $insert_data['create_date'] = date("Y-m-d H:i:s");
            $insert_data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->insert('parents', $insert_data)) {
                $insert_id = $this->db->insert_id();
                $this->response(["status" => true, "message" => "Parent added successfully", "parentsID" => $insert_id], REST_Controller::HTTP_OK);
            } else {
                $dbErr = $this->db->error();
                $this->response(["status" => false, "message" => !empty($dbErr['message']) ? $dbErr['message'] : "Database error"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Required fields missing (Guardian Name, Username, Password)"], REST_Controller::HTTP_OK);
        }
    }

    public function update_post()
    {
        $id = $this->post('parentsID') ?: $this->post('parentID');
        $raw = $this->post();
        if($id && !empty($raw['name'])) {
            $allowed_fields = [
                'name', 'father_name', 'father_profession', 'mother_name', 'mother_profession', 
                'email', 'phone', 'address', 'photo', 'campusID', 'modify_date',
                'father_phone', 'father_cnic', 'father_office_addresss', 'father_nationality', 
                'mother_phone', 'mother_cnic', 'mother_office_addresss', 'mother_address', 
                'mother_qualification', 'mother_nationality', 'guardian_profession', 
                'guardian_cnic', 'guardian_address', 'guardian_qualification', 
                'guardian_office_addresss', 'guardian_nationality', 'guardian_realation_with_child'
            ];

            $update_data = [];
            foreach ($allowed_fields as $field) {
                if (isset($raw[$field])) {
                    $update_data[$field] = $raw[$field];
                }
            }
            $update_data['modify_date'] = date("Y-m-d H:i:s");

            if($this->db->where('parentsID', $id)->update('parents', $update_data)) {
                $this->response(["status" => true, "message" => "Parent updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $dbErr = $this->db->error();
                $this->response(["status" => false, "message" => !empty($dbErr['message']) ? $dbErr['message'] : "Update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid parent ID or missing Name"], REST_Controller::HTTP_OK);
        }
    }

    public function delete_post()
    {
        $id = $this->post('parentsID') ?: $this->post('parentID');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->where('parentsID', $id)->delete('parents')) {
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
        $id = $this->post('parentsID') ?: $this->post('parentID');
        $status = $this->post('status');
        $adminID = $this->post('adminID') ?: 1;
        if($id) {
            if($this->db->where('adminID', $adminID)->where('parentsID', $id)->update('parents', ['active' => $status])) {
                $this->response(["status" => true, "message" => "Status updated successfully"], REST_Controller::HTTP_OK);
            } else {
                $this->response(["status" => false, "message" => "Status update failed"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response(["status" => false, "message" => "Invalid parent ID"], REST_Controller::HTTP_OK);
        }
    }
}

