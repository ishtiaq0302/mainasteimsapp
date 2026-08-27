<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Markpercentage extends Admin_Controller {
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
		$this->load->model("markpercentage_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('markpercentage', $language);	
	}

	public function index() {
		$campusID=$this->uri->segment(3);
        $this->data['campusID']=$campusID;

		$usertype = $this->session->userdata("usertype");
		$this->data['markpercentage'] = $this->markpercentage_m->get_order_by_markpercentage(array('campusID'=>$campusID));
		$this->data["subview"] = "markpercentage/index";
		$this->load->view('_layout_main', $this->data);
		
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'markpercentagetype', 
				'label' => $this->lang->line("markpercentage_markpercentagetype"), 
				'rules' => 'trim|required|xss_clean|max_length[100]|callback_unique_markpercentage'
			),
			array(
				'field' => 'percentage', 
				'label' => $this->lang->line("markpercentage_percentage"), 
				'rules' => 'trim|required|xss_clean|max_length[3]'
			),
			array(
				'field' => 'campusID',
				'label' => $this->lang->line("select_campus"),
				'rules' => 'trim|required|xss_clean|callback_unique_campusID'
			),
		);
		return $rules;
	}

	public function unique_campusID() {

		if($this->input->post('campusID') == 0) {

			$this->form_validation->set_message("unique_campusID", "The %s field is required");

	     	return FALSE;

		}

		return TRUE;

	}

	public function add() {
		$usertype = $this->session->userdata("usertype");
		if($_POST) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) { 
				$this->data["subview"] = "markpercentage/add";
				$this->load->view('_layout_main', $this->data);			
			} else {
				$array = array(
					"campusID" => $this->input->post("campusID"),
					"markpercentagetype" => $this->input->post("markpercentagetype"),
					"percentage" => $this->input->post("percentage"),
					"create_date" => date("Y-m-d h:i:s"),
					"modify_date" => date("Y-m-d h:i:s"),
					"create_userID" => $this->session->userdata('loginuserID'),
					"create_username" => $this->session->userdata('username'),
					"create_usertype" => $this->session->userdata('usertype')
				);
				$this->markpercentage_m->insert_markpercentage($array);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("markpercentage/index/".$array['campusID']));
			}
		} else {
			$this->data["subview"] = "markpercentage/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		$usertype = $this->session->userdata("usertype");
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$this->data['markpercentage'] = $this->markpercentage_m->get_markpercentage($id);
			$this->data['campusID']=$this->data['markpercentage']->campusID;
			if($this->data['markpercentage']) {
				if($_POST) {
					$rules = $this->rules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "markpercentage/edit";
						$this->load->view('_layout_main', $this->data);			
					} else {
						$array = array(
							"campusID" => $this->input->post("campusID"),
							"markpercentagetype" => $this->input->post("markpercentagetype"),
							"percentage" => $this->input->post("percentage"),
							"modify_date" => date("Y-m-d h:i:s")
						);
						$oldfieloption = 'mark_'. str_replace(' ', '', $this->data['markpercentage']->markpercentagetype);

						/*$olddata = $this->setting_m->get_setting_where($oldfieloption); 
						view($olddata,1);
						if(!empty($olddata)) {
							$this->setting_m->delete_setting($oldfieloption);
							$newdata = array(
								'fieldoption' => 'mark_'. str_replace(' ', '', $this->input->post('markpercentagetype')),
								'value' => $olddata->value
							);
							$this->setting_m->insert_setting($newdata); 
						}*/


						$this->markpercentage_m->update_markpercentage($array, $id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("markpercentage/index"));
					}
				} else {
					$this->data["subview"] = "markpercentage/edit";
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
		$usertype = $this->session->userdata("usertype");
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$this->data['markpercentage'] = $this->markpercentage_m->get_markpercentage($id);
			if($this->data['markpercentage']) {
				if($this->data['markpercentage']->markpercentageID != 1) {
					$oldfieloption = 'mark_'. str_replace(' ', '', $this->data['markpercentage']->markpercentageID);
					//view($olddata,1);

					/*$olddata = $this->setting_m->get_setting_where($oldfieloption);
					if(!empty($olddata)) {
						$this->setting_m->delete_setting($oldfieloption);
					}*/

					$this->markpercentage_m->delete_markpercentage($id);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
					redirect(base_url("markpercentage/index"));
				} else {
					redirect(base_url("markpercentage/index"));
				}
			} else {
				redirect(base_url("markpercentage/index"));
			}
		} else {
			redirect(base_url("markpercentage/index"));
		}	

	}

	public function unique_markpercentage() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$markpercentagetype = $this->markpercentage_m->get_order_by_markpercentage(array("markpercentagetype" => $this->input->post("markpercentagetype"), 'markpercentageID !=' => $id, "campusID" => $this->input->post("campusID")));

			if(!empty($markpercentagetype)) {
				$this->form_validation->set_message("unique_markpercentage", "%s already exists");
				return FALSE;
			}
			return TRUE;
		} else {
			$markpercentagetype = $this->markpercentage_m->get_order_by_markpercentage(array("markpercentagetype" => $this->input->post("markpercentagetype"), "campusID" => $this->input->post("campusID")));

			if(!empty($markpercentagetype)) {
				$this->form_validation->set_message("unique_markpercentage", "%s already exists");
				return FALSE;
			}
			return TRUE;
		}	
	}


}

/* End of file class.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/class.php */