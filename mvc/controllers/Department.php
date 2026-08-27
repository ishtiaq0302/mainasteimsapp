<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -----------------------------------------------------
| PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
| -----------------------------------------------------
| AUTHOR:			INILABS TEAM
| -----------------------------------------------------
| EMAIL:			info@inilabs.net
| -----------------------------------------------------
| COPYRIGHT:		RESERVED BY INILABS IT
| -----------------------------------------------------
| WEBSITE:			http://inilabs.net
| -----------------------------------------------------
*/
class Department extends Admin_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model("department_m");
		$this->load->model("hmember_m");
		$this->load->model("student_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('department', $language);	
	}

	public function index() {
		$this->data['departments'] = $this->department_m->get_order_by_department();
		$this->data["subview"] = "department/index";
		$this->load->view('_layout_main', $this->data);
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'name', 
				'label' => $this->lang->line("department_name"), 
				'rules' => 'trim|required|xss_clean|max_length[128]|callback_unique_name'
			) 
		);
		return $rules;
	}

	public function add() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);
		if($_POST) {

			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "department/add";
				$this->load->view('_layout_main', $this->data);			
			} else {
				$array = array(
					"name" => $this->input->post("name")
				);

				$this->department_m->insert_department($array);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("department/index"));
			}
		} else {
			$this->data["subview"] = "department/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$this->data['department'] = $this->department_m->get_department($id);
			if($this->data['department']) {
				if($_POST) {
					$rules = $this->rules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "department/edit";
						$this->load->view('_layout_main', $this->data);			
					} else {
						$array = array(
							"name" => $this->input->post("name")
						);

						$this->department_m->update_department($array, $id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("department/index"));
					}
				} else {
					$this->data["subview"] = "department/edit";
					$this->load->view('_layout_main', $this->data);
				}
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}	
	}

	public function delete() {
			$id = htmlentities(escapeString($this->uri->segment(3)));
			if((int)$id) {
				$hmembers = $this->hmember_m->get_order_by_hmember(array("departmentID" => $id));
				foreach ($hmembers as $hmember) {
					$this->student_m->update_student_classes(array("department" => 0), array("studentID" => $hmember->studentID));
				}
				//$this->hmember_m->delete_hmember_hID($id);
				$this->department_m->delete_department($id);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("department/index"));
			} else {
				redirect(base_url("department/index"));
			}
	}

	function unique_name() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$student = $this->department_m->get_order_by_department(array("name" => $this->input->post("name"), "departmentID !=" => $id));
			if(!empty($student)) {
				$this->form_validation->set_message("unique_name", "%s already exists");
				return FALSE;
			}
			return TRUE;
		} else {
			$student = $this->department_m->get_order_by_department(array("name" => $this->input->post("name")));
			if(!empty($student)) {
				$this->form_validation->set_message("unique_name", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}
	}

	function unique_htype() {
		$htype = $this->input->post('htype');
		if($htype === '0') {
			$this->form_validation->set_message("unique_htype", "The %s field is required");
	     	return FALSE;
		}
		return TRUE;
	}
}

/* End of file department.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/department.php */