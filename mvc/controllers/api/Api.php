<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Api extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('setting_m');
        $this->load->model('student_m');
        $this->load->model('classes_m');
        $this->load->model('section_m');
        $this->load->model('parents_m');
        $this->load->model('studentgroup_m');
        $this->load->model('teacher_m');
        $this->load->model('user_m');
        $this->load->model('systemadmin_m');
        $this->load->model('event_m');
        $this->load->model('holiday_m');
        $this->load->model('book_m');
        $this->load->model('studentrelation_m');
        $this->load->model('studentextend_m');
        $this->load->model('menu_m');
        $this->load->model('permission_m');
        $this->load->model('usertype_m');
    }

    private function _response($data, $code = REST_Controller::HTTP_OK)
    {
        $this->response($data, $code);
    }

    public function login_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');
        $encryption_key = config_item('encryption_key');

        if(!empty($username) && !empty($password)) {
            $pass_hash = hash("sha512", $password . $encryption_key);
            $tables = array('student', 'parents', 'teacher', 'user', 'systemadmin');
            $user_found = false;
            $userdata = null;

            foreach ($tables as $table) {
                $user = $this->db->get_where($table, ["username" => $username, "password" => $pass_hash, 'active' => 1]);
                if($user->num_rows() > 0) {
                    $userdata = (array)$user->row();
                    $user_found = true;
                    $userdata['user_type'] = $table;
                    break;
                }
            }

            if($user_found) {
                $campusID = isset($userdata['campusID']) ? $userdata['campusID'] : 0;
                $adminID = isset($userdata['adminID']) ? $userdata['adminID'] : 1;
                $settings = $this->setting_m->get_setting($campusID > 0 ? $campusID : 1, $adminID);
                $userdata['defaultschoolyearID'] = $settings ? $settings->school_year : 1;
                unset($userdata['password']);

                $this->_response([
                    "status" => true,
                    "message" => "Login successful",
                    "data" => $userdata
                ]);
            } else {
                $this->_response(["status" => false, "message" => "Invalid username or password"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->_response(["status" => false, "message" => "Username and password are required"], REST_Controller::HTTP_OK);
        }
    }

    public function student_list_post()
    {
        $schoolyearID = $this->post('schoolyearID') ?: 1;
        $campusID = $this->post('campusID') ?: 0;
        $classesID = $this->post('classesID') ?: 0;

        $this->db->select('sr.*, s.photo, s.email, s.phone, s.active');
        $this->db->from('studentrelation sr');
        $this->db->join('student s', 's.studentID = sr.srstudentID');
        $this->db->where('sr.srschoolyearID', $schoolyearID);

        if ($campusID > 0) $this->db->where('sr.srcampusID', $campusID);
        if ($classesID > 0) $this->db->where('sr.srclassesID', $classesID);

        $this->db->order_by('sr.srroll', 'ASC');
        $students = $this->db->get()->result_array();

        $this->_response(["status" => true, "data" => $students]);
    }

    public function metadata_post()
    {
        $campusID = $this->post('campusID') ?: 0;
        $classesID = $this->post('classesID') ?: 0;

        $response = array();
        $response['campuses'] = $this->db->select('campusID, name')->get('campus')->result_array();

        if ($campusID > 0) $this->db->where('campusID', $campusID);
        $response['classes'] = $this->db->select('classesID, classes')->get('classes')->result_array();

        if ($classesID > 0) {
            $response['sections'] = $this->db->select('sectionID, section')->where('classesID', $classesID)->get('section')->result_array();
        }

        if ($campusID > 0) $this->db->where('campusID', $campusID);
        $response['parents'] = $this->db->select('parentsID, name, email')->get('parents')->result_array();
        $response['studentgroups'] = $this->db->select('studentgroupID, group')->get('studentgroup')->result_array();

        $this->_response(["status" => true, "data" => $response]);
    }

    public function student_add_post()
    {
        $name = $this->post('name');
        $username = $this->post('username');
        $password = $this->post('password');
        $campusID = (int)$this->post('campusID');
        $encryption_key = config_item('encryption_key');

        if (!empty($name) && !empty($username) && !empty($password)) {
            $this->db->trans_start();

            $setting = $this->db->get_where('settings', ['campusID' => $campusID])->row();
            if (!$setting) $setting = $this->db->get_where('settings', ['campusID' => 1])->row();
            $registerNO = ($setting ? (int)$setting->s_registration_no_auto : 0) + 1;

            $pass_hash = hash("sha512", $password . $encryption_key);
            $student_data = [
                'name' => $name,
                'dob' => $this->post('dob'),
                'sex' => $this->post('sex'),
                'religion' => $this->post('religion'),
                'email' => $this->post('email'),
                'phone' => $this->post('phone'),
                'address' => $this->post('address'),
                'campusID' => $campusID,
                'adminID' => $this->post('adminID') ?: 1,
                'classesID' => $this->post('classesID'),
                'sectionID' => $this->post('sectionID'),
                'roll' => $this->post('roll'),
                'registerNO' => $registerNO,
                'username' => $username,
                'password' => $pass_hash,
                'usertypeID' => 3,
                'parentID' => $this->post('parentID'),
                'active' => 1,
                'create_date' => date('Y-m-d H:i:s'),
                'modify_date' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('student', $student_data);
            $studentID = $this->db->insert_id();

            $this->db->set('s_registration_no_auto', 's_registration_no_auto + 1', FALSE);
            $this->db->where('campusID', $campusID);
            $this->db->update('settings');

            $rel_data = [
                'srstudentID' => $studentID,
                'srname' => $name,
                'srcampusID' => $campusID,
                'srclassesID' => $this->post('classesID'),
                'srsectionID' => $this->post('sectionID'),
                'srroll' => $this->post('roll'),
                'srregisterNO' => $registerNO,
                'srschoolyearID' => $this->post('schoolyearID'),
                'srstudentgroupID' => $this->post('studentgroupID')
            ];
            $this->db->insert('studentrelation', $rel_data);

            $ext_data = [
                'studentID' => $studentID,
                'studentgroupID' => $this->post('studentgroupID'),
                'remarks' => $this->post('remarks')
            ];
            $this->db->insert('studentextend', $ext_data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->_response(["status" => false, "message" => "Database error"], REST_Controller::HTTP_OK);
            } else {
                $this->_response(["status" => true, "message" => "Student added successfully", "studentID" => $studentID]);
            }
        } else {
            $this->_response(["status" => false, "message" => "Required fields missing"], REST_Controller::HTTP_OK);
        }
    }

    public function sidebar_menu_post()
    {
        $usertypeID = (int)$this->post('usertypeID');
        if(!empty($usertypeID)) {
            $all_menus = $this->db->where('status', 1)->order_by('priority', 'DESC')->get('menu')->result_array();
            $filtered_menus = array();

            if($usertypeID == 1) {
                $filtered_menus = $all_menus;
            } else {
                $permissions = $this->db->select('p.name')
                    ->from('permission_relationships pr')
                    ->join('permissions p', 'p.permissionID = pr.permission_id')
                    ->where('pr.usertype_id', $usertypeID)
                    ->where('pr.active', 'yes')
                    ->get()->result_array();
                $perm_names = array_column($permissions, 'name');
                $perm_names[] = 'dashboard';
                if($usertypeID == 3) $perm_names[] = 'take_exam';

                foreach ($all_menus as $menu) {
                    if ($menu['link'] == '#' || in_array($menu['link'], $perm_names)) {
                        $filtered_menus[] = $menu;
                    }
                }
            }

            $menuTree = array();
            $itemsById = array();
            foreach ($filtered_menus as $menu) {
                $menu['child'] = array();
                $itemsById[$menu['menuID']] = $menu;
            }
            foreach ($itemsById as $id => &$item) {
                if ($item['parentID'] == 0) {
                    $menuTree[] = &$item;
                } else if (isset($itemsById[$item['parentID']])) {
                    $itemsById[$item['parentID']]['child'][] = &$item;
                }
            }
            $this->_response(["status" => true, "data" => $menuTree]);
        } else {
            $this->_response(["status" => false, "message" => "Missing usertypeID"], REST_Controller::HTTP_OK);
        }
    }

    public function student_view_post()
    {
        $studentID = (int)$this->post('studentID');
        $schoolyearID = (int)$this->post('schoolyearID') ?: 1;

        if ($studentID > 0) {
            $this->db->select('s.*, sr.*, se.*, p.name as parent_name, p.phone as parent_phone');
            $this->db->from('student s');
            $this->db->join('studentrelation sr', 'sr.srstudentID = s.studentID AND sr.srschoolyearID = '.$schoolyearID, 'left');
            $this->db->join('studentextend se', 'se.studentID = s.studentID', 'left');
            $this->db->join('parents p', 'p.parentsID = s.parentID', 'left');
            $this->db->where('s.studentID', $studentID);
            $student = $this->db->get()->row_array();

            if ($student) {
                unset($student['password']);
                $this->_response(["status" => true, "data" => $student]);
            } else {
                $this->_response(["status" => false, "message" => "Student not found"], REST_Controller::HTTP_OK);
            }
        } else {
            $this->_response(["status" => false, "message" => "Invalid student ID"], REST_Controller::HTTP_OK);
        }
    }

    public function dashboard_data_post()
    {
        $schoolyearID = $this->post('schoolyearID');
        $campusID = $this->post('campusID') ?: 0;

        if(!empty($schoolyearID)) {
            $stats = array();
            $campusFilter = ($campusID > 0) ? ['campusID' => $campusID] : [];
            $srCampusFilter = ($campusID > 0) ? ['srcampusID' => $campusID] : [];

            $stats['students'] = $this->db->where('srschoolyearID', $schoolyearID)->where($srCampusFilter)->count_all_results('studentrelation');
            $stats['classes'] = $this->db->where($campusFilter)->count_all_results('classes');
            $stats['sections'] = $this->db->where($campusFilter)->count_all_results('section');
            $stats['teachers'] = $this->db->where($campusFilter)->count_all_results('teacher');
            $stats['parents'] = $this->db->where($campusFilter)->count_all_results('parents');
            $stats['subjects'] = $this->db->where($campusFilter)->count_all_results('subject');
            $stats['users'] = $this->db->where($campusFilter)->count_all_results('user');
            $stats['books'] = $this->db->where($campusFilter)->count_all_results('book');
            $stats['events'] = $this->db->where('schoolyearID', $schoolyearID)->where($campusFilter)->count_all_results('event');
            $stats['holidays'] = $this->db->where('schoolyearID', $schoolyearID)->where($campusFilter)->count_all_results('holiday');
            $stats['invoices'] = $this->db->where(['maininvoiceschoolyearID' => $schoolyearID, 'maininvoicedeleted_at' => 1])->count_all_results('maininvoice');

            $this->_response(["status" => true, "data" => $stats]);
        } else {
            $this->_response(["status" => false, "message" => "Missing schoolyearID"], REST_Controller::HTTP_OK);
        }
    }

    public function student_delete_post()
    {
        $studentID = (int)$this->post('studentID');
        if ($studentID > 0) {
            $this->db->trans_start();
            $this->db->delete('student', ['studentID' => $studentID]);
            $this->db->delete('studentextend', ['studentID' => $studentID]);
            $this->db->delete('studentrelation', ['srstudentID' => $studentID]);
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->_response(["status" => false, "message" => "Delete failed"], REST_Controller::HTTP_OK);
            } else {
                $this->_response(["status" => true, "message" => "Student deleted successfully"]);
            }
        } else {
            $this->_response(["status" => false, "message" => "Invalid student ID"], REST_Controller::HTTP_OK);
        }
    }

    public function student_status_post()
    {
        $studentID = (int)$this->post('studentID');
        $status = (int)$this->post('status');

        if ($studentID > 0) {
            $this->db->update('student', ['active' => $status], ['studentID' => $studentID]);
            $this->_response(["status" => true, "message" => "Status updated successfully"]);
        } else {
            $this->_response(["status" => false, "message" => "Invalid student ID"], REST_Controller::HTTP_OK);
        }
    }

    public function student_update_post()
    {
        $studentID = (int)$this->post('studentID');
        $name = $this->post('name');
        $schoolyearID = (int)$this->post('schoolyearID');

        if (!empty($studentID) && !empty($name)) {
            $this->db->trans_start();

            $student_data = [
                'name' => $name,
                'dob' => $this->post('dob'),
                'sex' => $this->post('sex'),
                'religion' => $this->post('religion'),
                'email' => $this->post('email'),
                'phone' => $this->post('phone'),
                'address' => $this->post('address'),
                'classesID' => $this->post('classesID'),
                'sectionID' => $this->post('sectionID'),
                'roll' => $this->post('roll'),
                'parentID' => $this->post('parentID'),
                'modify_date' => date('Y-m-d H:i:s')
            ];
            $this->db->update('student', $student_data, ['studentID' => $studentID]);

            $rel_data = [
                'srname' => $name,
                'srclassesID' => $this->post('classesID'),
                'srsectionID' => $this->post('sectionID'),
                'srroll' => $this->post('roll')
            ];
            $this->db->update('studentrelation', $rel_data, ['srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID]);

            $ext_data = [
                'studentgroupID' => $this->post('studentgroupID'),
                'remarks' => $this->post('remarks')
            ];
            $this->db->update('studentextend', $ext_data, ['studentID' => $studentID]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->_response(["status" => false, "message" => "Update failed"], REST_Controller::HTTP_OK);
            } else {
                $this->_response(["status" => true, "message" => "Student updated successfully"]);
            }
        } else {
            $this->_response(["status" => false, "message" => "Required fields missing"], REST_Controller::HTTP_OK);
        }
    }
}
