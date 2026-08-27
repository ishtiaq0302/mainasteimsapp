<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Createaccount extends Admin_Controller {
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
	function __construct() {
		parent::__construct();
		$this->load->model("createaccount_m");
		$this->load->model("systemadmin_m");
		$this->load->model("campus_m");
		$this->load->model("setting_m");
		$data = array(
			"lang" => $this->data["siteinfos"]->language,
		);
		$this->session->set_userdata($data);
		$language = $this->session->userdata('lang');
		$this->lang->load('createaccount', $language);
		if(!isset($this->data["siteinfos"]->captcha_status)) {
			$this->data["siteinfos"]->captcha_status = 1;
		}
	}

	protected function rules() {
		$rules = array(
			 	array(
					'field' => 'email',
					'label' => $this->lang->line("systemadmin_email"),
					'rules' => 'trim|required|max_length[40]|valid_email|xss_clean|callback_unique_email'
				),
				array(
					'field' => 'password',
					'label' => "Password",
					'rules' => 'trim|required|max_length[40]|xss_clean'
				)
			);

		if($this->data["siteinfos"]->captcha_status == 0) {
			$rules[] = array(
				'field' => 'g-recaptcha-response',
				'label' => "captcha",
				'rules' => 'trim|required'
			);
		}

		return $rules;
	}

	public function unique_email() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$systemadmin_info = $this->systemadmin_m->get_single_systemadmin(array('systemadminID' => $id));
			$tables = array('systemadmin' => 'systemadmin');
			$array = array();
			$i = 0;
			foreach ($tables as $table) {
				$user = $this->systemadmin_m->get_username($table, array("email" => $this->input->post('email'), 'username !=' => $systemadmin_info->username ));
				if(!empty($user)) {
					$this->form_validation->set_message("unique_email", "%s already exists");
					$array['permition'][$i] = 'no';
				} else {
					$array['permition'][$i] = 'yes';
				}
				$i++;
			}
			if(in_array('no', $array['permition'])) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			$tables = array('systemadmin' => 'systemadmin');
			$array = array();
			$i = 0;
			foreach ($tables as $table) {
				$user = $this->systemadmin_m->get_username($table, array("email" => $this->input->post('email')));
				if(!empty($user)) {
					$this->form_validation->set_message("unique_email", "%s already exists");
					$array['permition'][$i] = 'no';
				} else {
					$array['permition'][$i] = 'yes';
				}
				$i++;
			}

			if(in_array('no', $array['permition'])) {
				return FALSE;
			} else {
				return TRUE;
			}
		}
	}

	protected function rules_cpassword() {
		$rules = array(
				array(
					'field' => 'old_password',
					'label' => $this->lang->line('old_password'),
					'rules' => 'trim|required|max_length[40]|min_length[4]|xss_clean|callback_old_password_unique'
				),
				array(
					'field' => 'new_password',
					'label' => $this->lang->line('new_password'),
					'rules' => 'trim|required|max_length[40]|min_length[4]|xss_clean'
				),
				array(
					'field' => 're_password',
					'label' => $this->lang->line('re_password'),
					'rules' => 'trim|required|max_length[40]|min_length[4]|matches[new_password]|xss_clean'
				)
			);
		return $rules;
	}

	public function index() {
		if($this->data['siteinfos']->captcha_status == 0) {
			$this->load->library('recaptcha');
			$this->data['recaptcha'] = array(
	            'widget' => $this->recaptcha->getWidget(),
	            'script' => $this->recaptcha->getScriptTag(),
	        );
		}
		//$this->createaccount_m->loggedin() == FALSE || redirect(base_url('dashboard/index'));
		$this->data['form_validation'] = 'No';
		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data['form_validation'] = validation_errors();
				$this->data["subview"] = "createaccount/index";
				$this->load->view('_layout_signin', $this->data);
			} else {				 
				$array = array();
				$array2 = array();
				
				
				/*Add Campus*/
				$get_char = $this->input->post("email");
				if(!empty($get_char)){
					$get_char = ucwords(strtok($get_char,'@'));
				}
				$array2["name"]= $get_char.' Campus';
				$campusID = $this->campus_m->add_campus($array2);

				/*Add Systemadmin*/
				$array["accountCampusID"] = $campusID;
				$array["campusID"] = 0;
				$array["name"] = '';
				$array["dob"] = date("Y-m-d");
				$array["sex"] = 'Male';
				$array["religion"] = '';
				$array["email"] = $this->input->post("email");
				$array["phone"] = '';
				$array["address"] = '';
				$array["jod"] = date("Y-m-d");
				$array["username"] = $this->input->post("email");
				$array['password'] = $this->systemadmin_m->hash($this->input->post("password"));
				$array["usertypeID"] = 1;
				$array["create_date"] = date("Y-m-d h:i:s");
				$array["modify_date"] = date("Y-m-d h:i:s");
				$array["create_userID"] = 0;
				$array["create_username"] = 'admin';
				$array["create_usertype"] = 'Admin';
				$array["adminID"] = 0;
				$array["active"] = 1;
				//$array['photo'] = $this->upload_data['file']['file_name'];

				//$this->usercreatemail($this->input->post('email'), $this->input->post('username'), $this->input->post('password'));

				$admindata = $this->systemadmin_m->insert_systemadmin($array);

				/* Update Campus */
				$update_campus = array(
					"adminID" => $admindata
				);
				$this->campus_m->update_campus($update_campus, $campusID);
				

 

				$year = date('Y');
				$month = date('m');
				$day = date('d');
				$yearplus = $year+1;
				$insert_schoolyear = array(
					"schooltype" => 'classbase',
					"schoolyear" => $year,
					"schoolyeartitle" => 'AY '.$year.'-'.$yearplus,
					"startingdate" => $year.'-'.$month.'-'.$day,
					"endingdate" => $yearplus.'-'.$month.'-'.$day,
					"create_date" => date("Y-m-d h:i:s"),
					"modify_date" => date("Y-m-d h:i:s"),
					"create_userID" => 1,
					"create_username" => 'admin',
					"create_usertype" => 'Admin',
					"adminID" => $admindata
				);
				$school_year_id=$this->schoolyear_m->insert_schoolyears($insert_schoolyear);

				$this->setting_m->insertsettings($admindata,$campusID,$school_year_id);
				$this->permission_m->permission_relation_insertion($admindata);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url('signin/index'));
					 
				/*	$this->session->set_flashdata("errors", $checkArray['message']);
					$this->data['form_validation'] = $checkArray['message'];
					$this->data["subview"] = "createaccount/index";
					$this->load->view('_layout_signin', $this->data);*/
			}

		} else {

			$this->data["subview"] = "createaccount/index";
			$this->load->view('_layout_signin', $this->data);
			$this->session->sess_destroy();
		}
	}

	public function cpassword() {
		$this->load->library("session");
		if($_POST) {
			$rules = $this->rules_cpassword();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "createaccount/cpassword";
				$this->load->view('_layout_main', $this->data);
			} else {
				redirect(base_url('createaccount/cpassword'));
			}
		} else {
			$this->data["subview"] = "createaccount/cpassword";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function old_password_unique() {
		if($this->createaccount_m->change_password() == TRUE) {
			return TRUE;
		} else {
			$this->form_validation->set_message("old_password_unique", "%s does not match");
			return FALSE;
		}
	}

	public function signout() {
		$this->createaccount_m->signout();
		if($this->data["siteinfos"]->frontendorbackend === 'YES') {
			redirect(base_url('frontend/index'));
		} else {
			redirect(base_url("createaccount/index"));
		}
	}
}



