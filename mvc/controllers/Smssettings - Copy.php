<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Smssettings extends Admin_Controller {
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
	function __construct () {
		parent::__construct();
		$this->load->model("smssettings_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('smssettings', $language);
		if(config_item('demo')) {
            $this->session->set_flashdata('error', 'In demo SMS setting module is disable!');
            redirect(base_url('dashboard/index'));
        }
	}

	protected function rules_clickatell() {
		$rules = array(
			array(
				'field' => 'clickatell_username', 
				'label' => $this->lang->line("smssettings_username"), 
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			), 
			array(
				'field' => 'clickatell_password', 
				'label' => $this->lang->line("smssettings_password"),
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			),
			array(
				'field' => 'clickatell_api_key', 
				'label' => $this->lang->line("smssettings_api_key"), 
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			),
		);
		return $rules;
	}

	protected function rules_twilio() {
		$rules = array(
			array(
				'field' => 'twilio_accountSID', 
				'label' => $this->lang->line("smssettings_accountSID"), 
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			), 
			array(
				'field' => 'twilio_authtoken', 
				'label' => $this->lang->line("smssettings_authtoken"),
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			),
			array(
				'field' => 'twilio_fromnumber', 
				'label' => $this->lang->line("smssettings_fromnumber"), 
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			),
		);
		return $rules;
	}

	protected function rules_bulk() {
		$rules = array(
			array(
				'field' => 'bulk_username', 
				'label' => $this->lang->line("smssettings_username"), 
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			), 
			array(
				'field' => 'bulk_password', 
				'label' => $this->lang->line("smssettings_password"),
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			),
			array(
				'field' => 'bulk_api_key', 
				'label' => $this->lang->line("smssettings_api_key"), 
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			),
			array(
				'field' => 'bulk_sender', 
				'label' => $this->lang->line("smssettings_sender"), 
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			),
		);
		return $rules;
	}

	protected function rules_msg91() {
		$rules = array(
			array(
				'field' => 'msg91_authKey',
				'label' => $this->lang->line("smssettings_authkey"),
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			),
			array(
				'field' => 'msg91_senderID',
				'label' => $this->lang->line("smssettings_senderID"),
				'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
			)
		);
		return $rules;
	}

	public function unique_field($field) {
        if($this->input->post('type') == 'clickatell') {
            if(!empty($this->input->post('clickatell_username')) || !empty($this->input->post('clickatell_password')) || !empty($this->input->post('clickatell_api_key'))) {

            	if($this->input->post('clickatell_username') == $field) {
            		if($this->input->post('clickatell_username') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	if($this->input->post('clickatell_password') == $field) {
            		if($this->input->post('clickatell_password') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	if($this->input->post('clickatell_api_key') == $field) {
            		if($this->input->post('clickatell_api_key') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	return TRUE;
            } 
            return TRUE;
        } elseif($this->input->post('type') == 'twilio') {
        	if(!empty($this->input->post('twilio_accountSID')) || !empty($this->input->post('twilio_authtoken')) || !empty($this->input->post('twilio_fromnumber'))) {

        		if($this->input->post('twilio_accountSID') == $field) {
            		if($this->input->post('twilio_accountSID') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	if($this->input->post('twilio_authtoken') == $field) {
            		if($this->input->post('twilio_authtoken') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	if($this->input->post('twilio_fromnumber') == $field) {
            		if($this->input->post('twilio_fromnumber') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	}

            	return TRUE; 
            }
            return TRUE;
        } elseif($this->input->post('type') == 'bulk') {
        	if(!empty($this->input->post('bulk_username')) || !empty($this->input->post('bulk_password'))) {

        		if($this->input->post('bulk_username') == $field) {
            		if($this->input->post('bulk_username') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	if($this->input->post('bulk_password') == $field) {
            		if($this->input->post('bulk_password') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	if($this->input->post('bulk_api_key') == $field) {
            		if($this->input->post('bulk_api_key') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	if($this->input->post('bulk_sender') == $field) {
            		if($this->input->post('bulk_sender') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	return TRUE;
            }
            return TRUE;
        } elseif($this->input->post('type') == 'msg91') {
        	if(!empty($this->input->post('msg91_authKey')) || !empty($this->input->post('msg91_senderID'))) {

        		if($this->input->post('msg91_authKey') == $field) {
            		if($this->input->post('msg91_authKey') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	if($this->input->post('msg91_senderID') == $field) {
            		if($this->input->post('msg91_senderID') == '') {
	            		$this->form_validation->set_message("unique_field", "The %s is required.");
	                	return FALSE;
            		}
            		return TRUE;
            	} 

            	return TRUE;
            }
            return TRUE;
        }
    }

	public function index() {

		$get_clickatells = $this->smssettings_m->get_smssettings();
		//$get_clickatells = $this->smssettings_m->get_order_by_clickatell();
		$clickatell_bind = array(
			'clickatell_username' 	=> $get_clickatells['clickatell_username'],
			'clickatell_password' 	=> $get_clickatells['clickatell_password'],
			'clickatell_api_key' 	=> $get_clickatells['clickatell_api_key'],
		);
		/*foreach ($get_clickatells as $key => $get_clickatell) {
			$clickatell_bind[$get_clickatell->field_names] = $get_clickatell->field_values;
		}*/
		$this->data['set_clickatell'] = $clickatell_bind;
		view($this->data['set_clickatell'],1);

		$twilio_bind = array();
		$get_twilios = $this->smssettings_m->get_order_by_twilio();
		foreach ($get_twilios as $key => $get_twilio) {
			$twilio_bind[$get_twilio->field_names] = $get_twilio->field_values;
		}
		$this->data['set_twilio'] = $twilio_bind;

		$bulk_bind = array();
		$get_bulks = $this->smssettings_m->get_order_by_bulk();
		foreach ($get_bulks as $key => $get_bulk) {
			$bulk_bind[$get_bulk->field_names] = $get_bulk->field_values;
		}
		$this->data['set_bulk'] = $bulk_bind;

        $msg91_bind = array();
		$get_msg91s = $this->smssettings_m->get_order_by_msg91();
		foreach ($get_msg91s as $key => $get_msg91) {
			$msg91_bind[$get_msg91->field_names] = $get_msg91->field_values;
		}
		$this->data['set_msg91'] = $msg91_bind;


		if($_POST) {
			$details = $this->smssettings_m->get_smssettings($this->session->userdata('adminID'));
			$type = $this->input->post('type');
			if($type == 'clickatell') {
				$this->data['clickatell'] = 1;
				$this->data['twilio'] = 0;
				$this->data['bulk'] = 0;
				$this->data['msg91'] = 0;

				$rules = $this->rules_clickatell();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data["subview"] = "smssettings/index";
					$this->load->view('_layout_main', $this->data);			
				} else {

					$clickatell_username = $this->input->post('clickatell_username');
					$clickatell_password = $this->input->post('clickatell_password');
					$clickatell_api_key = $this->input->post('clickatell_api_key');



					/*$array = array(
					   array(
					      'field_names' => 'clickatell_username',
					      'field_values' => $username
					   ),
					   array(
					      'field_names' => 'clickatell_password',
					      'field_values' => $password
					   ),
					   array(
					      'field_names' => 'clickatell_api_key',
					      'field_values' => $api_key
					   )
					);*/
					if(!empty($details))
					{
						$array = array(
							"clickatell_username" 	=> $clickatell_username,
							"clickatell_password" 	=> $clickatell_password,
							"clickatell_api_key" 	=> $clickatell_api_key,
						);
						$this->smssettings_m->update_smssettings($array, $this->session->userdata('adminID'));
					}else{
						$array = array(
							"clickatell_username" 	=> $clickatell_username,
							"clickatell_password" 	=> $clickatell_password,
							"clickatell_api_key" 	=> $clickatell_api_key,
						);
						$this->smssettings_m->insert_smssettings($array);		
					}

					//$this->smssettings_m->update_clickatell($array);
					$this->data["subview"] = "smssettings/index";
					$this->load->view('_layout_main', $this->data);
				}
			} elseif($type == 'twilio') {
				$this->data['clickatell'] = 0;
				$this->data['twilio'] = 1;
				$this->data['bulk'] = 0;
                $this->data['msg91'] = 0;

				$rules = $this->rules_twilio();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data["subview"] = "smssettings/index";
					$this->load->view('_layout_main', $this->data);			
				} else {
					$twilio_accountSID = $this->input->post('twilio_accountSID');
					$twilio_authtoken = $this->input->post('twilio_authtoken');
					$twilio_fromnumber = $this->input->post('twilio_fromnumber');

					if(!empty($details))
					{
						$array = array(
							"twilio_accountSID" 	=> $twilio_accountSID,
							"twilio_authtoken" 		=> $twilio_authtoken,
							"twilio_fromnumber" 	=> $twilio_fromnumber,
						);
						$this->smssettings_m->update_smssettings($array, $this->session->userdata('adminID'));
					}else{
						$array = array(
							"twilio_accountSID" 	=> $twilio_accountSID,
							"twilio_authtoken" 		=> $twilio_authtoken,
							"twilio_fromnumber" 	=> $twilio_fromnumber,
						);
						$this->smssettings_m->insert_smssettings($array);		
					}

					/*$array = array(
					   array(
					      'field_names' => 'twilio_accountSID',
					      'field_values' => $accountSID
					   ),
					   array(
					      'field_names' => 'twilio_authtoken',
					      'field_values' => $authtoken
					   ),
					   array(
					      'field_names' => 'twilio_fromnumber',
					      'field_values' => $fromnumber
					   )
					);*/

					//$this->smssettings_m->update_twilio($array);
					$this->data["subview"] = "smssettings/index";
					$this->load->view('_layout_main', $this->data);
				}
			} elseif($type == 'bulk') {
				$this->data['clickatell'] = 0;
				$this->data['twilio'] = 0;
				$this->data['bulk'] = 1;
                $this->data['msg91'] = 0;

				$rules = $this->rules_bulk();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data["subview"] = "smssettings/index";
					$this->load->view('_layout_main', $this->data);			
				} else {
					$bulk_username 	= $this->input->post('bulk_username');
					$bulk_password 	= $this->input->post('bulk_password');
					$bulk_api_key 	= $this->input->post('bulk_api_key');
					$bulk_senderID 	= $this->input->post('bulk_sender');

					if(!empty($details))
					{
						$array = array(
							"bulk_username" 	=> $bulk_username,
							"bulk_password" 	=> $bulk_password,
							"bulk_api_key" 		=> $bulk_api_key,
							"bulk_senderID" 	=> $bulk_senderID,
						);
						$this->smssettings_m->update_smssettings($array, $this->session->userdata('adminID'));
					}else{
						$array = array(
							"bulk_username" 	=> $bulk_username,
							"bulk_password" 	=> $bulk_password,
							"bulk_api_key" 		=> $bulk_api_key,
							"bulk_senderID" 	=> $bulk_senderID,
						);
						$this->smssettings_m->insert_smssettings($array);		
					}

					/*$array = array(
					   array(
					      'field_names' => 'bulk_username',
					      'field_values' => $username
					   ),
					   array(
					      'field_names' => 'bulk_password',
					      'field_values' => $password
					   ),
					   array(
					      'field_names' => 'bulk_api_key',
					      'field_values' => $api_key
					   ),
					   array(
					      'field_names' => 'bulk_sender',
					      'field_values' => $sender
					   )
					);*/

					//$this->smssettings_m->update_bulk($array); 
					$this->data["subview"] = "smssettings/index";
					$this->load->view('_layout_main', $this->data);
				}
			} elseif($type == 'msg91') {
                $this->data['clickatell'] = 0;
                $this->data['twilio'] = 0;
                $this->data['bulk'] = 0;
                $this->data['msg91'] = 1;

                $rules = $this->rules_msg91();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == FALSE) {
                    $this->data["subview"] = "smssettings/index";
                    $this->load->view('_layout_main', $this->data);
                } else {
                    $msg91_authKey = $this->input->post('msg91_authKey');
                    $msg91_senderID = $this->input->post('msg91_senderID');

                    if(!empty($details))
					{
						$array = array(
							"msg91_authKey" 	=> $msg91_authKey,
							"msg91_senderID" 	=> $msg91_senderID,
						);
						$this->smssettings_m->update_smssettings($array, $this->session->userdata('adminID'));
					}else{
						$array = array(
							"msg91_authKey" 	=> $msg91_authKey,
							"msg91_senderID" 	=> $msg91_senderID,
						);
						$this->smssettings_m->insert_smssettings($array);		
					}

                    /*$array = array(
                        array(
                            'field_names' => 'msg91_authKey',
                            'field_values' => $authKey
                        ),
                        array(
                            'field_names' => 'msg91_senderID',
                            'field_values' => $senderID
                        )
                    );*/

                    //$this->smssettings_m->update_msg91($array);
                    $this->data["subview"] = "smssettings/index";
                    $this->load->view('_layout_main', $this->data);
                }
            } 

		} else {
			$this->data['clickatell'] = 1;
			$this->data['twilio'] = 0;
			$this->data['bulk'] = 0;
			$this->data['msg91'] = 0;

			$this->data["subview"] = "smssettings/index";
			$this->load->view('_layout_main', $this->data);
		}
	}
}

/* End of file student.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/student.php */