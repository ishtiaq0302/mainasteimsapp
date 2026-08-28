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
        $adminID = (int)($this->post('adminID') ?: 1);

        $this->db->select('sr.*, s.photo, s.email, s.phone, s.active');
        $this->db->from('studentrelation sr');
        $this->db->join('student s', 's.studentID = sr.srstudentID');
        $this->db->where('sr.srschoolyearID', $schoolyearID);
        if ($adminID > 0) {
            $this->db->group_start()
                ->where('sr.adminID', $adminID)
                ->or_where('sr.adminID', 0)
                ->group_end();
        }

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
        try {
            $name           = trim((string)$this->post('name'));
            $username       = trim((string)$this->post('username'));
            $password       = trim((string)$this->post('password'));
            $campusID       = (int)$this->post('campusID');
            $classesID      = (int)$this->post('classesID');
            $sectionID      = (int)$this->post('sectionID');
            $parentID       = (int)($this->post('parentID') ?: 0);
            $schoolyearID   = (int)($this->post('schoolyearID') ?: 1);
            $studentgroupID = (int)($this->post('studentgroupID') ?: 0);
            $adminID        = (int)($this->post('adminID') ?: 1);
            $roll           = trim((string)$this->post('roll'));
            $dobRaw         = trim((string)$this->post('dob'));

            if (empty($name) || empty($username) || empty($password)) {
                $this->_response(["status" => false, "message" => "Name, username, and password are required"], REST_Controller::HTTP_OK);
                return;
            }

            if ($campusID <= 0 || $classesID <= 0 || $sectionID <= 0) {
                $this->_response(["status" => false, "message" => "Campus, class, and section are required"], REST_Controller::HTTP_OK);
                return;
            }

            // Check duplicate username across student table
            $userExists = $this->db->where('username', $username)->get('student')->row();
            if ($userExists) {
                $this->_response(["status" => false, "message" => "Username '$username' is already taken"], REST_Controller::HTTP_OK);
                return;
            }

            // Valid date of birth
            $dob = (!empty($dobRaw) && strtotime($dobRaw) !== false) ? date('Y-m-d', strtotime($dobRaw)) : date('Y-m-d');

            // Auto registration number
            $registerNO = 1;
            try {
                $setting = $this->db->get_where('settings', ['campusID' => $campusID])->row();
                if (!$setting) $setting = $this->db->get_where('settings', ['campusID' => 1])->row();
                if ($setting && isset($setting->s_registration_no_auto)) {
                    $registerNO = (int)$setting->s_registration_no_auto + 1;
                }
            } catch (Throwable $e) {
                $registerNO = rand(1000, 9999);
            }

            $encryption_key = config_item('encryption_key');
            $pass_hash      = hash("sha512", $password . $encryption_key);

            $createUserID = (int)($this->post('create_userID') ?? 0);
            if ($createUserID <= 0) {
                $createUserID = $adminID > 0 ? $adminID : 1;
            }

            $this->db->trans_start();

            $student_data = [
                'name'        => $name,
                'dob'         => $dob,
                'sex'         => trim((string)$this->post('sex')),
                'religion'    => trim((string)$this->post('religion')),
                'email'       => trim((string)$this->post('email')),
                'phone'       => trim((string)$this->post('phone')),
                'address'     => trim((string)$this->post('address')),
                'campusID'    => $campusID,
                'adminID'     => $adminID > 0 ? $adminID : 1,
                'classesID'   => $classesID,
                'sectionID'   => $sectionID,
                'roll'        => $roll,
                'registerNO'  => $registerNO,
                'username'    => $username,
                'password'    => $pass_hash,
                'usertypeID'  => 3,
                'parentID'    => $parentID,
                'active'      => 1,
                'create_date' => date('Y-m-d H:i:s'),
                'modify_date' => date('Y-m-d H:i:s'),
                'create_userID'   => $createUserID,
                'create_username' => trim((string)($this->post('create_username') ?? 'admin')),
                'create_usertype' => trim((string)($this->post('create_usertype') ?? 'Admin'))
            ];
            $this->db->insert('student', $student_data);
            $studentID = $this->db->insert_id();

            try {
                $this->db->set('s_registration_no_auto', 's_registration_no_auto + 1', FALSE);
                $this->db->where('campusID', $campusID);
                $this->db->update('settings');
            } catch (Throwable $e) {}

            $rel_data = [
                'srstudentID'      => $studentID,
                'srname'           => $name,
                'srcampusID'       => $campusID,
                'srclassesID'      => $classesID,
                'srsectionID'      => $sectionID,
                'srroll'           => $roll,
                'srregisterNO'     => $registerNO,
                'srschoolyearID'   => $schoolyearID,
                'srstudentgroupID' => $studentgroupID,
                'adminID'          => $adminID > 0 ? $adminID : 1,
            ];
            $this->db->insert('studentrelation', $rel_data);

            $ext_data = [
                'studentID'      => $studentID,
                'studentgroupID' => $studentgroupID,
                'remarks'        => trim((string)$this->post('remarks'))
            ];
            $this->db->insert('studentextend', $ext_data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $dbError = $this->db->error();
                $msg = !empty($dbError['message']) ? $dbError['message'] : "Database error adding student";
                $this->_response(["status" => false, "message" => $msg], REST_Controller::HTTP_OK);
            } else {
                $this->_response(["status" => true, "message" => "Student added successfully", "studentID" => $studentID]);
            }
        } catch (Throwable $e) {
            $this->_response(["status" => false, "message" => "Server error: " . $e->getMessage()], REST_Controller::HTTP_OK);
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
            $this->db->select('s.*, sr.*, se.*, 
                c.classes as class_name, 
                sec.section as section_name, 
                cam.name as campus_name, 
                sg.group as group_name,
                p.name as parent_name, p.father_name, p.father_profession, p.mother_name, p.mother_profession, p.email as parent_email, p.phone as parent_phone, p.address as parent_address, p.username as parent_username');
            $this->db->from('student s');
            $this->db->join('studentrelation sr', 'sr.srstudentID = s.studentID AND sr.srschoolyearID = '.$schoolyearID, 'left');
            $this->db->join('studentextend se', 'se.studentID = s.studentID', 'left');
            $this->db->join('parents p', 'p.parentsID = s.parentID', 'left');
            $this->db->join('classes c', 'c.classesID = sr.srclassesID', 'left');
            $this->db->join('section sec', 'sec.sectionID = sr.srsectionID', 'left');
            $this->db->join('campus cam', 'cam.campusID = sr.srcampusID', 'left');
            $this->db->join('studentgroup sg', 'sg.studentgroupID = sr.srstudentgroupID', 'left');
            $this->db->where('s.studentID', $studentID);
            $student = $this->db->get()->row_array();

            if ($student) {
                unset($student['password']);

                $routines = [];
                if (!empty($student['srclassesID']) && !empty($student['srsectionID'])) {
                    $this->db->select('r.*, sub.subject, t.name as teacher_name');
                    $this->db->from('routine r');
                    $this->db->join('subject sub', 'sub.subjectID = r.subjectID', 'left');
                    $this->db->join('teacher t', 't.teacherID = r.teacherID', 'left');
                    $this->db->where('r.classesID', $student['srclassesID']);
                    $this->db->where('r.sectionID', $student['srsectionID']);
                    $this->db->where('r.schoolyearID', $schoolyearID);
                    $routines = $this->db->get()->result_array();
                }

                $documents = $this->db->get_where('document', ['usertypeID' => 3, 'userID' => $studentID])->result_array();

                $this->_response([
                    "status" => true,
                    "data" => array_merge($student, [
                        'profile' => $student,
                        'routines' => $routines,
                        'documents' => $documents
                    ])
                ]);
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
            $stats['campuses'] = $this->db->count_all_results('campus');
            $stats['routines'] = $this->db->where($campusFilter)->count_all_results('routine');

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
        try {
            $studentID      = (int)$this->post('studentID');
            $name           = trim((string)$this->post('name'));
            $schoolyearID   = (int)($this->post('schoolyearID') ?: 1);
            $campusID       = (int)($this->post('campusID') ?: 0);
            $classesID      = (int)$this->post('classesID');
            $sectionID      = (int)$this->post('sectionID');
            $parentID       = (int)($this->post('parentID') ?: 0);
            $adminID        = (int)($this->post('adminID') ?: 1);
            $studentgroupID = (int)($this->post('studentgroupID') ?: 0);
            $roll           = trim((string)$this->post('roll'));
            $dobRaw         = trim((string)$this->post('dob'));

            if ($studentID <= 0 || empty($name)) {
                $this->_response(["status" => false, "message" => "Valid student ID and name are required"], REST_Controller::HTTP_OK);
                return;
            }

            $dob = (!empty($dobRaw) && strtotime($dobRaw) !== false) ? date('Y-m-d', strtotime($dobRaw)) : date('Y-m-d');

            $createUserID = (int)($this->post('create_userID') ?? 0);
            if ($createUserID <= 0) {
                $createUserID = $adminID > 0 ? $adminID : 1;
            }

            $this->db->trans_start();

            $student_data = [
                'name'        => $name,
                'dob'         => $dob,
                'sex'         => trim((string)$this->post('sex')),
                'religion'    => trim((string)$this->post('religion')),
                'email'       => trim((string)$this->post('email')),
                'phone'       => trim((string)$this->post('phone')),
                'address'     => trim((string)$this->post('address')),
                'classesID'   => $classesID,
                'sectionID'   => $sectionID,
                'roll'        => $roll,
                'parentID'    => $parentID,
                'adminID'     => $adminID > 0 ? $adminID : 1,
                'modify_date' => date('Y-m-d H:i:s'),
                'create_userID'   => $createUserID,
                'create_username' => trim((string)($this->post('create_username') ?? 'admin')),
                'create_usertype' => trim((string)($this->post('create_usertype') ?? 'Admin'))
            ];
            if ($campusID > 0) {
                $student_data['campusID'] = $campusID;
            }

            $this->db->update('student', $student_data, ['studentID' => $studentID]);

            $rel_data = [
                'srname'      => $name,
                'srclassesID' => $classesID,
                'srsectionID' => $sectionID,
                'srroll'      => $roll,
                'adminID'     => $adminID > 0 ? $adminID : 1,
            ];
            if ($campusID > 0) {
                $rel_data['srcampusID'] = $campusID;
            }
            $this->db->update('studentrelation', $rel_data, ['srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID]);

            $ext_data = [
                'studentgroupID' => $studentgroupID,
                'remarks'        => trim((string)$this->post('remarks'))
            ];
            $this->db->update('studentextend', $ext_data, ['studentID' => $studentID]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $dbError = $this->db->error();
                $msg = !empty($dbError['message']) ? $dbError['message'] : "Update failed";
                $this->_response(["status" => false, "message" => $msg], REST_Controller::HTTP_OK);
            } else {
                $this->_response(["status" => true, "message" => "Student updated successfully"]);
            }
        } catch (Throwable $e) {
            $this->_response(["status" => false, "message" => "Server error: " . $e->getMessage()], REST_Controller::HTTP_OK);
        }
    }
}
