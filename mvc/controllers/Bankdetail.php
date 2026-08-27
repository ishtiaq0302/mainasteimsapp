<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bankdetail extends Admin_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model("bankdetail_m");
		// $this->load->model("parents_m");
		// $this->load->model("student_m");
		// $this->load->model('studentrelation_m');
		$language = $this->session->userdata('lang');
		$this->lang->load('bankdetail', $language);	
	}

	protected function rules() {
		$rules = array(
			array(
				'field' => 'title', 
				'label' => $this->lang->line("syllabus_title"), 
				'rules' => 'trim|required|xss_clean|max_length[128]'
			), 
			array(
				'field' => 'description', 
				'label' => $this->lang->line("syllabus_description"),
				'rules' => 'trim|required|xss_clean'
			), 
			array(
				'field' => 'classesID', 
				'label' => $this->lang->line("syllabus_classes"),
				'rules' => 'trim|required|numeric|max_length[11]|xss_clean|callback_unique_classes'
			),
			array(
				'field' => 'file', 
				'label' => $this->lang->line("syllabus_file"), 
				'rules' => 'trim|max_length[512]|xss_clean|callback_fileupload'
			)
		);
		return $rules;
	}

	public function fileupload() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$syllabus = array();
		if((int)$id) {
			$syllabus = $this->lecture_m->get_single_lecture(array('lectureID' => $id));	
		}
		
		$new_file = "";
		$original_file_name = '';
		if($_FILES["file"]['name'] !="") {
			$file_name = $_FILES["file"]['name'];
			$original_file_name = $file_name;
			$random = random19();
	    	$makeRandom = hash('sha512', $random.$this->input->post('title') . config_item("encryption_key"));
			$file_name_rename = $makeRandom;
            $explode = explode('.', $file_name);
            if(count($explode) >= 2) {
	            $new_file = $file_name_rename.'.'.end($explode);
				$config['upload_path'] = "./uploads/images";
				$config['allowed_types'] = "mp4|gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv";
				$config['file_name'] = $new_file;
				$config['max_size'] = '100024';
				$config['max_width'] = '3000';
				$config['max_height'] = '3000';
				$this->load->library('upload', $config);
				if(!$this->upload->do_upload("file")) {
					$this->form_validation->set_message("fileupload", $this->upload->display_errors());
	     			return FALSE;
				} else {
					$this->upload_data['file'] =  $this->upload->data();
					$this->upload_data['file']['original_file_name'] = $original_file_name;
					return TRUE;
				}
			} else {
				$this->form_validation->set_message("fileupload", "Invalid file");
	     		return FALSE;
			}
		} else {
			if(!empty($syllabus)) {
				$this->upload_data['file'] = array('file_name' => $syllabus->file);
				$this->upload_data['file']['original_file_name'] = $syllabus->originalfile;
				return TRUE;
			} else {
				if($new_file == '') {
					$this->form_validation->set_message("fileupload", "The %s field is required.");
					return FALSE;
				} else {
					$this->upload_data['file'] = array('file_name' => $new_file);
					$this->upload_data['file']['original_file_name'] = $original_file_name;
					return TRUE;
				}
			}
		}
	}

	public function index() {

		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);
			$schoolyearID = $this->session->userdata("defaultschoolyearID");
			$this->data['bankdetails'] = $this->bankdetail_m->get_order_by_bankdetail();
			// print_r($this->data['bankdetails']);
			// exit();
			
			$this->data["subview"] = "downloadsetting/index";
		     $this->load->view('_layout_main', $this->data);
		
		
	}

	public function add() {
		// echo "<pre>";
		// print_r($this->session->userdata_all());
		// echo "</pre>";
		// exit();
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID') || $this->session->userdata('usertypeID') == 1)) {
			$this->data['headerassets'] = array(
				'css' => array(
					'assets/select2/css/select2.css',
					'assets/select2/css/select2-bootstrap.css'
				),
				'js' => array(
					'assets/select2/select2.js'
				)
			);

			$this->data['classes'] = $this->classes_m->get_classes();
			if($_POST) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) { 
					$this->data["subview"] = "lecture/add";
					$this->load->view('_layout_main', $this->data);			
				} else {
					$array = array(
						"title" => $this->input->post("title"),
						"description" => $this->input->post("description"),
						"date" => date('Y-m-d'),
						"usertypeID" => $this->session->userdata('usertypeID'),
						"userID" => $this->session->userdata('loginuserID'),
						"classesID" => $this->input->post("classesID"),
						"schoolyearID" => $this->session->userdata('defaultschoolyearID'),
					);

					$array['originalfile'] = $this->upload_data['file']['original_file_name'];
					$array['file'] = $this->upload_data['file']['file_name'];

					$this->lecture_m->insert_lecture($array);
					$this->session->set_flashdata('success', $this->lang->line('menu_success'));
					redirect(base_url("lecture/index"));
				}
			} else {
				$this->data["subview"] = "lecture/add";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
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

		$schoolyearID = $this->session->userdata("defaultschoolyearID");
		// print_r($schoolyearID);
		// exit();
		$this->data['bankdetail'] = $this->bankdetail_m->get_bankdetail(array('bankdetailID' => 
			$id,'schoolyearID' => $schoolyearID));
		// echo "<pre>";
		// print_r($this->data['bankdetail']);
		// echo "</pre>";
		// exit();
		$this->data["subview"] = "downloadsetting/edit";
		     $this->load->view('_layout_main', $this->data);
	}

	public function delete() {
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$id = htmlentities(escapeString($this->uri->segment(3)));
		$url = htmlentities(escapeString($this->uri->segment(4)));
		if((int)$id && (int)$url) {
			if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1))  {
				$fetchClasses = pluck($this->classes_m->get_classes(), 'classesID', 'classesID');
				if(isset($fetchClasses[$url])) {
					$syllabus = $this->lecture_m->get_single_lecture(array('lectureID' => $id, 'schoolyearID' => $schoolyearID));
					if(!empty($syllabus)) {
						if(config_item('demo') == FALSE) {
							if(file_exists(FCPATH.'uploads/images/'.$syllabus->file)) {
								unlink(FCPATH.'uploads/images/'.$syllabus->file);
							}
						}
						$this->lecture_m->delete_lecture($id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("lecture/index/$url"));
					} else {
						redirect(base_url("lecture/index"));	
					}
				} else {
					redirect(base_url("lecture/index"));
				}
			} else {
				redirect(base_url("lecture/index"));
			}
		} else {
			redirect(base_url("lecture/index"));
		}
	}

	public function unique_classes() {
		if($this->input->post('classesID') == 0) {
			$this->form_validation->set_message("unique_classes", "The %s field is required");
	     	return FALSE;
		}
		return TRUE;
	}

	public function lecture_list() {
		$classID = $this->input->post('id');
		if((int)$classID) {
			$string = base_url("lecture/index/$classID");
			echo $string;
			
		} else {
			redirect(base_url("lecture/index"));
		}
	}

	public function download() {
		$id = htmlentities(escapeString($this->uri->segment(3)));
		if((int)$id) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$syllabus = $this->lecture_m->get_single_lecture(array('lectureID' => $id, 'schoolyearID' => $schoolyearID));
			// print_r($syllabus);
			// exit();
			if(!empty($syllabus)) {
				$file = realpath('uploads/images/'.$syllabus->file);
				$originalname = $syllabus->originalfile;
				// force_download();
				// print_r($file);
				// exit();
			    if (file_exists($file)) {
			    	header('Content-Description: File Transfer');
				    header('Content-Type: application/octet-stream');
				    header('Content-Disposition: attachment; filename="'.basename($originalname).'"');
				    header('Expires: 0');
				    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				    header('Pragma: public');
				    header('Content-Length: ' . filesize($file));
				    readfile($file);
				    exit;
			    } else {
			    	redirect(base_url('lecture/index'));
			    }
			} else {
				redirect(base_url('lecture/index'));
			}
		} else {
			redirect(base_url('lecture/index'));
		}
	}	
}