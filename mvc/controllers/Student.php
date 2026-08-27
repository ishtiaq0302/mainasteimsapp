<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Student extends Admin_Controller {

	private $zoom_api_key = '';
    private $zoom_api_secret = '';
	private $redirect_uri = '';
	private $ulr2 = '';

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
		$setting_result = $this->setting_m->get_setting();
        $this->zoom_api_key = $setting_result->zoom_api_key;
        $this->zoom_api_secret = $setting_result->zoom_api_secret;
        $this->redirect_uri = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']).'student/callback';
        $this->ulr2 = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

		$this->load->model("student_m");

		$this->load->model("staff_m");

		$this->load->model("parents_m");

		$this->load->model("campus_m");

		$this->load->model("section_m");

		$this->load->model("classes_m");

		$this->load->model("setting_m");

		$this->load->model('studentrelation_m');

		$this->load->model('studentgroup_m');

		$this->load->model('studentextend_m');

		$this->load->model('subject_m');

		$this->load->model('routine_m');

		$this->load->model('teacher_m');

		$this->load->model('subjectattendance_m');

		$this->load->model('sattendance_m');

		$this->load->model('invoice_m');

		$this->load->model('payment_m');

		$this->load->model('weaverandfine_m');

		$this->load->model('feetypes_m');

		$this->load->model('exam_m');

		$this->load->model('grade_m');

		$this->load->model('markpercentage_m');

		$this->load->model('markrelation_m');

		$this->load->model('mark_m');

		$this->load->model('document_m');

		$this->load->model('leaveapplication_m');

	    $this->load->model('usertype_m');

	    $this->load->model('user_m');

	    $this->load->model('systemadmin_m');

	    $this->load->model('conference_m');

		$language = $this->session->userdata('lang');

		$this->lang->load('student', $language);

	}



	public function send_mail_rules() {

		$rules = array(

			array(

				'field' => 'to',

				'label' => $this->lang->line("student_to"),

				'rules' => 'trim|required|max_length[60]|valid_email|xss_clean'

			),

			array(

				'field' => 'subject',

				'label' => $this->lang->line("student_subject"),

				'rules' => 'trim|required|xss_clean'

			),

			array(

				'field' => 'message',

				'label' => $this->lang->line("student_message"),

				'rules' => 'trim|xss_clean'

			),

			array(

				'field' => 'studentID',

				'label' => $this->lang->line("student_studentID"),

				'rules' => 'trim|required|max_length[10]|xss_clean|callback_unique_data'

			),

			array(

				'field' => 'classesID',

				'label' => $this->lang->line("student_classesID"),

				'rules' => 'trim|required|max_length[10]|xss_clean|callback_unique_data'

			)

		);

		return $rules;

	}



	public function unique_data($data) {
		if($data != '') {

			if($data == '0') {

				$this->form_validation->set_message('unique_data', 'The %s field is required.');

				return FALSE;

			}

			return TRUE;

		}

		return TRUE;

	}



	private function getView($id, $url, $campusID) {

		$schoolyearID = $this->session->userdata('defaultschoolyearID');

		$fetchClasses = pluck($this->classes_m->get_classes(), 'classesID', 'classesID');

		if(isset($fetchClasses[$url])) {

			if((int)$id && (int)$url) {

				$studentInfo = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srclassesID' => $url, 'srschoolyearID' => $schoolyearID, 'srcampusID' => $campusID), TRUE);


				$this->pluckInfo();

				$this->basicInfo($studentInfo);

				$this->parentInfo($studentInfo);

				$this->routineInfo($studentInfo);

				$this->attendanceInfo($studentInfo);

				$this->markInfo($studentInfo);

				$this->invoiceInfo($studentInfo);

				$this->paymentInfo($studentInfo);

				$this->documentInfo($studentInfo);



				if(!empty($studentInfo)) {
					$this->data['leaveapplications'] = $this->leave_applications_date_list_by_user_and_schoolyear($id,$schoolyearID,$studentInfo->usertypeID);
					$this->data['set'] = $url;
					$this->data['campusID'] = $campusID;

					$this->data["subview"] = "student/getView";

					$this->load->view('_layout_main', $this->data);

				} else {

					$this->data["subview"] = "error";

					$this->load->view('_layout_main', $this->data);

				}

			}

		} else {

			$this->data["subview"] = "error";

			$this->load->view('_layout_main', $this->data);

		}

	}



	private function allPaymentByInvoice($payments) {

		$retPaymentArr = [];

		if($payments) {

			foreach ($payments as $payment) {

				if(isset($retPaymentArr[$payment->invoiceID])) {

					$retPaymentArr[$payment->invoiceID] += $payment->paymentamount;

				} else {

					$retPaymentArr[$payment->invoiceID] = $payment->paymentamount;					

				}

			}

		}

		return $retPaymentArr;

	}



	private function allWeaverAndFineByInvoice($weaverandfines) {

		$retWeaverAndFineArr = [];

		if($weaverandfines) {

			foreach ($weaverandfines as $weaverandfine) {

				if(isset($retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'])) {

					$retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'] += $weaverandfine->weaver;

				} else {

					$retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'] = $weaverandfine->weaver;					

				}



				if(isset($retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'])) {

					$retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'] += $weaverandfine->fine;

				} else {

					$retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'] = $weaverandfine->fine;					

				}

			}

		}

		return $retWeaverAndFineArr;

	}



	private function get_setting_mark_percentage($campusID=0) {

		$markpercentagesDatabases = $this->markpercentage_m->get_markpercentage();
		$markpercentagesSet = $this->setting_m->get_markpercentage($campusID);
		$markpercentages = [];
		$array = [];
		$mark = unserialize($markpercentagesSet->mark);
		$markpercentagesSettings=$mark;

		if(!empty($markpercentagesSettings)) {
			foreach ($markpercentagesSettings as $key => $markpercentagesSetting) {
				$expfieldname = explode('_', $key);
				$array[] = (int)$expfieldname[1];
			}
		}

		if(!empty($markpercentagesDatabases)) {
			foreach ($markpercentagesDatabases as $key => $markpercentagesDatabase) {
				if(in_array($markpercentagesDatabase->markpercentageID, $array)) {
					$markpercentages[] = $markpercentagesDatabase;
				}
			}
		}

		return $markpercentages;

		/*
		OLD WORKING
		$markpercentagesDatabases = $this->markpercentage_m->get_markpercentage();
		$markpercentagesSettings = $this->setting_m->get_markpercentage();
		view($markpercentagesSettings,1);

		$markpercentages = array();

		$array = array();

		if(!empty($markpercentagesSettings)) {

			foreach ($markpercentagesSettings as $key => $markpercentagesSetting) {

				$expfieldname = explode('_', $markpercentagesSetting->fieldoption);

				$array[] = (int)$expfieldname[1];

			}

		}


		if(!empty($markpercentagesDatabases)) {

			foreach ($markpercentagesDatabases as $key => $markpercentagesDatabase) {

				if(in_array($markpercentagesDatabase->markpercentageID, $array)) {

					$markpercentages[] = $markpercentagesDatabase;

				}

			}

		}

		return $markpercentages;*/


	}



	private function getMark($studentID, $classesID) {

		if((int)$studentID && (int)$classesID) {

			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$student = $this->studentrelation_m->get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID));

			if(!empty($student))
			{
				$campID=$student->srcampusID;
			}else{
				$campID=1;
			}

			$classes = $this->classes_m->get_single_classes(array('classesID' => $classesID));

			if(!empty($student) && !empty($classes)) {

				$queryArray = [

					'classesID' => $student->srclassesID,

					'sectionID' => $student->srsectionID,

					'studentID' => $student->srstudentID, 

					'schoolyearID' => $schoolyearID, 

				];



				$exams = $this->exam_m->get_exam();

				$grades = $this->grade_m->get_grade();

				$marks = $this->mark_m->student_all_mark_array($queryArray);
				//view($marks,1);
				$markpercentages = $this->get_setting_mark_percentage($campID);



				$mandatorySubjects = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1));

				$optionalSubjects = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 0));



				$retMark = [];

				if(!empty($marks)) {

					foreach ($marks as $mark) {

						$retMark[$mark->examID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;

					}

				}



				$allStudentMarks = $this->mark_m->student_all_mark_array(array('classesID' => $classesID, 'schoolyearID' => $schoolyearID));



				$highestMarks = array();

				foreach ($allStudentMarks as $key => $value) {

					if(!isset($highestMarks[$value->examID][$value->subjectID][$value->markpercentageID])) {

						$highestMarks[$value->examID][$value->subjectID][$value->markpercentageID] = -1;

					}

					$highestMarks[$value->examID][$value->subjectID][$value->markpercentageID] = max($value->mark, $highestMarks[$value->examID][$value->subjectID][$value->markpercentageID]);

				}



				$this->data['exams'] = $exams;

				$this->data['grades'] = $grades;

				$this->data['markpercentages'] = $markpercentages;

				$this->data['mandatorysubjects'] = $mandatorySubjects;

				$this->data['optionalsubjects'] = pluck($optionalSubjects,'subject','subjectID');

				$this->data['marks'] = $retMark;

				$this->data['hightmarks'] = $highestMarks;

			} else {

				$this->data['exams'] = [];

				$this->data['grades'] = [];

				$this->data['markpercentages'] = [];

				$this->data['mandatorysubjects'] = [];

				$this->data['optionalsubjects'] = [];

				$this->data['marks'] = [];

				$this->data['hightmarks'] = [];

			}

		} else {

			$this->data['exams'] = [];

			$this->data['grades'] = [];

			$this->data['markpercentages'] = [];

			$this->data['mandatorysubjects'] = [];

			$this->data['optionalsubjects'] = [];

			$this->data['marks'] = [];

			$this->data['hightmarks'] = [];

		}

	}



	private function pluckInfo() {

		$this->data['subjects'] = pluck($this->subject_m->general_get_subject(), 'subject', 'subjectID');

		$this->data['teachers'] = pluck($this->teacher_m->get_teacher(), 'name', 'teacherID');
		
		$this->data['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');

	}



	private function basicInfo($studentInfo) {
		if(!empty($studentInfo)) {
			$this->data['profile'] = $studentInfo;

			$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => 3));

			$this->data['class'] = $this->classes_m->get_single_classes(array('classesID' => $studentInfo->srclassesID));
			$this->data['campus'] = $this->campus_m->get_single_campus(array('campusID' => $studentInfo->srcampusID));

			$this->data['section'] = $this->section_m->general_get_single_section(array('sectionID' => $studentInfo->srsectionID));

			$this->data['group'] = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $studentInfo->srstudentgroupID));

			$this->data['optionalsubject'] = $this->subject_m->general_get_single_subject(array('subjectID' => $studentInfo->sroptionalsubjectID));

		} else {

			$this->data['profile'] = [];

		}

	}



	private function parentInfo($studentInfo) {

		if(!empty($studentInfo)) {

			$this->data['parents'] = $this->parents_m->get_single_parents(array('parentsID' => $studentInfo->parentID));

		} else {

			$this->data['parents'] = [];

		}

	}



	private function routineInfo($studentInfo) {

		$dayArrays = array('SUNDAY','MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY');

		$retWeekend = [];

		if($this->data['siteinfos']->weekends != '') {

			$settingWeekends = explode(',', $this->data['siteinfos']->weekends);

			if(!empty($settingWeekends)) {

				foreach ($settingWeekends as $settingWeekend) {

					$retWeekend[] = $dayArrays[$settingWeekend];

				}

			}

		}

		$this->data['routineweekends'] = $retWeekend;

		if(!empty($studentInfo)) {

			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$this->data['routines'] = pluck_multi_array($this->routine_m->get_order_by_routine(array('classesID'=>$studentInfo->srclassesID, 'sectionID'=>$studentInfo->srsectionID, 'schoolyearID'=> $schoolyearID)), 'obj', 'day');

		} else {

			$this->data['routines'] = [];

		}

	}



	private function attendanceInfo($studentInfo) {

		$this->data['holidays'] =  $this->getHolidaysSession();

		$this->data['getWeekendDays'] =  $this->getWeekendDaysSession();

		if(!empty($studentInfo)) {

			$this->data['setting'] = $this->setting_m->get_setting();

			if($this->data['setting']->attendance == "subject") {

				$this->data["attendancesubjects"] = $this->subject_m->general_get_order_by_subject(array("classesID" => $studentInfo->srclassesID));

			}



			if($this->data['setting']->attendance == "subject") {

				$attendances = $this->subjectattendance_m->get_order_by_sub_attendance(array("studentID" => $studentInfo->srstudentID, "classesID" => $studentInfo->srclassesID));

				$this->data['attendances_subjectwisess'] = pluck_multi_array_key($attendances, 'obj', 'subjectID', 'monthyear');

			} else {

				$attendances = $this->sattendance_m->get_order_by_attendance(array("studentID" => $studentInfo->srstudentID, "classesID" => $studentInfo->srclassesID));

				$this->data['attendancesArray'] = pluck($attendances,'obj','monthyear');

			}

		} else {

			$this->data['setting'] = [];

			$this->data['attendancesubjects'] = [];

			$this->data['attendances_subjectwisess'] = [];

			$this->data['attendancesArray'] = [];

		}

	}



	private function markInfo($studentInfo) {
		if(!empty($studentInfo)) {

			$this->getMark($studentInfo->srstudentID, $studentInfo->srclassesID);

		} else {

			$this->data['set'] 				= [];

			$this->data["exams"] 			= [];

			$this->data["grades"] 			= [];

			$this->data['markpercentages']	= [];

			$this->data['validExam'] 		= [];

			$this->data['separatedMarks'] 	= [];

			$this->data["highestMarks"] 	= [];

			$this->data["section"] 			= [];

		}

	}



	private function invoiceInfo($studentInfo) {

		$schoolyearID = $this->session->userdata('defaultschoolyearID');

		if(!empty($studentInfo)) {

			$this->data['invoices'] = $this->invoice_m->get_order_by_invoice(array('schoolyearID' => $schoolyearID, 'studentID' => $studentInfo->srstudentID, 'classesID' => $studentInfo->srclassesID,'deleted_at' => 1));



			$payments = $this->payment_m->get_order_by_payment(array('schoolyearID' => $schoolyearID, 'studentID' => $studentInfo->srstudentID));

			$weaverandfines = $this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID, 'studentID' => $studentInfo->srstudentID));



			$this->data['allpaymentbyinvoice'] = $this->allPaymentByInvoice($payments);

			$this->data['allweaverandpaymentbyinvoice'] = $this->allWeaverAndFineByInvoice($weaverandfines);

		} else {

			$this->data['invoices'] = [];

			$this->data['allpaymentbyinvoice'] = [];

			$this->data['allweaverandpaymentbyinvoice'] = [];

		}

	}



	private function paymentInfo($studentInfo) {

		$schoolyearID = $this->session->userdata('defaultschoolyearID');

		if(!empty($studentInfo)) {

			$this->data['payments'] = $this->payment_m->get_payment_with_studentrelation_by_studentID_and_schoolyearID($studentInfo->srstudentID, $schoolyearID);

		} else {

			$this->data['payments'] = [];

		}

	}



	protected function rules_documentupload() {

		$rules = array(

			array(

				'field' => 'title',

				'label' => $this->lang->line("student_name"),

				'rules' => 'trim|required|xss_clean|max_length[128]'

			),

			array(

				'field' => 'file',

				'label' => $this->lang->line("student_file"),

				'rules' => 'trim|xss_clean|max_length[200]|callback_unique_document_upload'

			)

		);



		return $rules;

	}



	public function unique_document_upload() {

		$new_file = '';

		if($_FILES["file"]['name'] !="") {

			$file_name = $_FILES["file"]['name'];

			$random = random19();

	    	$makeRandom = hash('sha512', $random.(strtotime(date('Y-m-d H:i:s'))). config_item("encryption_key"));

			$file_name_rename = $makeRandom;

            $explode = explode('.', $file_name);

            if(count($explode) >= 2) {

	            $new_file = $file_name_rename.'.'.end($explode);

				$config['upload_path'] = "./uploads/documents";

				$config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv";

				$config['file_name'] = $new_file;

				$config['max_size'] = '5120';

				$config['max_width'] = '10000';

				$config['max_height'] = '10000';

				$this->load->library('upload', $config);

				if(!$this->upload->do_upload("file")) {

					$this->form_validation->set_message("unique_document_upload", $this->upload->display_errors());

	     			return FALSE;

				} else {

					$this->upload_data['file'] =  $this->upload->data();

					return TRUE;

				}

			} else {

				$this->form_validation->set_message("unique_document_upload", "Invalid file");

	     		return FALSE;

			}

		} else {

			$this->form_validation->set_message("unique_document_upload", "The file is required.");

			return FALSE;

		}

	}



	public function documentUpload() {

		$retArray['status'] = FALSE;

		$retArray['render'] = '';



		if(permissionChecker('student_add')) {

			if($_POST) {

				$rules = $this->rules_documentupload();

				$this->form_validation->set_rules($rules);

				if ($this->form_validation->run() == FALSE) {

					$retArray['errors'] = $this->form_validation->error_array();

					$retArray['status'] = FALSE;

				    echo json_encode($retArray);

				    exit;

				} else {

					$title = $this->input->post('title');

					$file = $this->upload_data['file']['file_name'];

					$userID = $this->input->post('studentID');



					$array = array(

						'title' => $title,

						'file' => $file,

						'userID' => $userID,

						'usertypeID' => 3,

						"create_date" => date("Y-m-d H:i:s"),

						"create_userID" => $this->session->userdata('loginuserID'),

						"create_usertypeID" => $this->session->userdata('usertypeID')

					);



					$this->document_m->insert_document($array);

					$this->session->set_flashdata('success', $this->lang->line('menu_success'));



					$retArray['status'] = TRUE;

					$retArray['render'] = 'Success';

				    echo json_encode($retArray);

				    exit;

				}

			} else {

				$retArray['status'] = FALSE;

				$retArray['render'] = 'Error';

			    echo json_encode($retArray);

			    exit;

			}

		} else {

			$retArray['status'] = FALSE;

			$retArray['render'] = 'Permission Denay.';

		    echo json_encode($retArray);

		    exit;

		}

	}



	private function documentInfo($studentInfo) {

		if(!empty($studentInfo)) {

			$this->data['documents'] = $this->document_m->get_order_by_document(array('usertypeID' => 3, 'userID' => $studentInfo->srstudentID));

		} else {

			$this->data['documents'] = [];

		}

	}



	public function download_document() {

		$id 		= htmlentities(escapeString($this->uri->segment(3)));

		$studentID 	= htmlentities(escapeString($this->uri->segment(4)));

		$classesID 	= htmlentities(escapeString($this->uri->segment(5)));

		if((int)$id && (int)$studentID && (int)$classesID) {

			if((permissionChecker('student_add') && permissionChecker('student_delete')) || ($this->session->userdata('usertypeID') == 3 && $this->session->userdata('loginuserID') == $studentID)) {

				$document = $this->document_m->get_single_document(array('documentID' => $id));

				if(!empty($document)) {

					$file = realpath('uploads/documents/'.$document->file);

				    if (file_exists($file)) {

				    	$expFileName = explode('.', $file);

						$originalname = ($document->title).'.'.end($expFileName);

				    	header('Content-Description: File Transfer');

					    header('Content-Type: application/octet-stream');

					    header('Content-Disposition: attachment; filename="'.basename($originalname).'"');

					    header('Expires: 0');

					    header('Cache-Control: must-revalidate');

					    header('Pragma: public');

					    header('Content-Length: ' . filesize($file));

					    readfile($file);

					    exit;

				    } else {

				    	redirect(base_url('student/view/'.$studentID.'/'.$classesID));

				    }

				} else {

					redirect(base_url('student/view/'.$studentID.'/'.$classesID));

				}

			} else {

				redirect(base_url('student/view/'.$studentID.'/'.$classesID));

			}

		} else {

			redirect(base_url('student/index'));

		}

	}



	public function delete_document() {

		$id 		= htmlentities(escapeString($this->uri->segment(3)));

		$studentID 	= htmlentities(escapeString($this->uri->segment(4)));

		$classesID 	= htmlentities(escapeString($this->uri->segment(5)));

		if((int)$id && (int)$studentID && (int)$classesID) {

			if(permissionChecker('student_add') && permissionChecker('student_delete')) {

				$document = $this->document_m->get_single_document(array('documentID' => $id));

				if(!empty($document)) {

					if(config_item('demo') == FALSE) {

						if(file_exists(FCPATH.'uploads/document/'.$document->file)) {

							unlink(FCPATH.'uploads/document/'.$document->file);

						}

					}



					$this->document_m->delete_document($id);

					$this->session->set_flashdata('success', $this->lang->line('menu_success'));

					redirect(base_url('student/view/'.$studentID.'/'.$classesID));

				} else {

					redirect(base_url('student/view/'.$studentID.'/'.$classesID));

				}

			} else {

				redirect(base_url('student/view/'.$studentID.'/'.$classesID));

			}

		} else {

			redirect(base_url('student/index'));

		}

	}



	protected function rules() {

		$rules = array(

			array(

				'field' => 'name',

				'label' => $this->lang->line("student_name"),

				'rules' => 'trim|required|xss_clean|max_length[60]'

			),

			array(

				'field' => 'dob',

				'label' => $this->lang->line("student_dob"),

				'rules' => 'trim|max_length[10]|callback_date_valid|xss_clean'

			),

			array(

				'field' => 'sex',

				'label' => $this->lang->line("student_sex"),

				'rules' => 'trim|required|max_length[10]|xss_clean'

			),

			array(

				'field' => 'bloodgroup',

				'label' => $this->lang->line("student_bloodgroup"),

				'rules' => 'trim|max_length[5]|xss_clean'

			),

			array(

				'field' => 'religion',

				'label' => $this->lang->line("student_religion"),

				'rules' => 'trim|max_length[25]|xss_clean'

			),

			array(

				'field' => 'email',

				'label' => $this->lang->line("student_email"),

				'rules' => 'trim|max_length[40]|valid_email|xss_clean|callback_unique_email'

			),

			array(

				'field' => 'phone',

				'label' => $this->lang->line("student_phone"),

				'rules' => 'trim|max_length[25]|min_length[5]|xss_clean'

			),

			array(

				'field' => 'address',

				'label' => $this->lang->line("student_address"),

				'rules' => 'trim|max_length[200]|xss_clean'

			),

			array(

				'field' => 'state',

				'label' => $this->lang->line("student_state"),

				'rules' => 'trim|max_length[128]|xss_clean'

			),

			array(

				'field' => 'country',

				'label' => $this->lang->line("student_country"),

				'rules' => 'trim|max_length[128]|xss_clean'

			),

			array(

				'field' => 'classesID',

				'label' => $this->lang->line("student_classes"),

				'rules' => 'trim|required|numeric|max_length[11]|xss_clean|callback_unique_classesID'

			),

			array(

				'field' => 'sectionID',

				'label' => $this->lang->line("student_section"),

				'rules' => 'trim|required|numeric|max_length[11]|xss_clean|callback_unique_sectionID|callback_unique_capacity'

			),

			array(

				'field' => 'campusID',

				'label' => $this->lang->line("student_campus"),

				'rules' => 'trim|required|xss_clean|callback_unique_campusID'

			),

			array(

				'field' => 'registerNO',

				'label' => $this->lang->line("student_registerNO"),

				'rules' => 'trim|required|max_length[40]|callback_unique_registerNO|xss_clean'

			),

			array(

				'field' => 'roll',

				'label' => $this->lang->line("student_roll"),

				'rules' => 'trim|max_length[11]|numeric|xss_clean'

			),

			array(

				'field' => 'guargianID',

				'label' => $this->lang->line("student_guargian"),

				'rules' => 'trim|required|max_length[11]|xss_clean|numeric'

			),

			array(

				'field' => 'photo',

				'label' => $this->lang->line("student_photo"),

				'rules' => 'trim|max_length[200]|xss_clean|callback_photoupload'

			),



            array(

                'field' => 'studentGroupID',

                'label' => $this->lang->line("student_studentgroup"),

                'rules' => 'trim|max_length[11]|xss_clean|numeric'

            ),



            array(

                'field' => 'optionalSubjectID',

                'label' => $this->lang->line("student_optionalsubject"),

                'rules' => 'trim|max_length[11]|xss_clean|numeric'

            ),



            array(

                'field' => 'extraCurricularActivities',

                'label' => $this->lang->line("student_extracurricularactivities"),

                'rules' => 'trim|max_length[128]|xss_clean'

            ),



            array(

                'field' => 'remarks',

                'label' => $this->lang->line("student_remarks"),

                'rules' => 'trim|max_length[300]|xss_clean'

            ),



			array(

				'field' => 'username',

				'label' => $this->lang->line("student_username"),

				'rules' => 'trim|required|min_length[4]|max_length[40]|xss_clean|callback_lol_username'

			),

			array(

				'field' => 'password',

				'label' => $this->lang->line("student_password"),

				'rules' => 'trim|required|min_length[4]|max_length[40]|xss_clean'

			),

			

			array(

                'field' => 'monthly_tuttion_fee',

                'label' => $this->lang->line("monthly_tuttion_fee"),

                'rules' => 'trim|max_length[10]|xss_clean'

            ),

			array(

                'field' => 'student_pob',

                'label' => $this->lang->line("student_pob"),

                'rules' => 'trim|max_length[150]|xss_clean'

            ),

			array(

                'field' => 'emergency_contact_no',

                'label' => $this->lang->line("emergency_contact_no"),

                'rules' => 'trim|max_length[150]|xss_clean'

            ),

			array(

                'field' => 'emergency_contact_relation',

                'label' => $this->lang->line("emergency_contact_relation"),

                'rules' => 'trim|max_length[150]|xss_clean'

            ),

			array(

                'field' => 'admission_result',

                'label' => $this->lang->line("admission_result"),

                'rules' => 'trim|max_length[20]|xss_clean'

            ),

			array(

                'field' => 'admission_status',

                'label' => $this->lang->line("admission_status"),

                'rules' => 'trim|max_length[20]|xss_clean'

            ),

			array(

                'field' => 'registration_fee',

                'label' => $this->lang->line("registration_fee"),

                'rules' => 'trim|max_length[10]|xss_clean'

            ),

			array(

                'field' => 'admission_fee',

                'label' => $this->lang->line("admission_fee"),

                'rules' => 'trim|max_length[10]|xss_clean'

            )

		);
		return $rules;

	}



	public function photoupload() {

		$id = htmlentities(escapeString($this->uri->segment(3)));

		$student = array();

		if((int)$id) {

			$student = $this->student_m->general_get_single_student(array('studentID' => $id));

		}



		$new_file = "default.png";

		if($_FILES["photo"]['name'] !="") {

			$file_name = $_FILES["photo"]['name'];

			$random = random19();

	    	$makeRandom = hash('sha512', $random.$this->input->post('username') . config_item("encryption_key"));

			$file_name_rename = $makeRandom;

            $explode = explode('.', $file_name);

            if(count($explode) >= 2) {

	            $new_file = $file_name_rename.'.'.end($explode);

				$config['upload_path'] = "./uploads/images";

				$config['allowed_types'] = "gif|jpg|png";

				$config['file_name'] = $new_file;

				$config['max_size'] = '1024';

				$config['max_width'] = '3000';

				$config['max_height'] = '3000';

				$this->load->library('upload', $config);

				if(!$this->upload->do_upload("photo")) {

					$this->form_validation->set_message("photoupload", $this->upload->display_errors());

	     			return FALSE;

				} else {

					$this->upload_data['file'] =  $this->upload->data();

					return TRUE;

				}

			} else {

				$this->form_validation->set_message("photoupload", "Invalid file");

	     		return FALSE;

			}

		} else {

			if(!empty($student)) {

				$this->upload_data['file'] = array('file_name' => $student->photo);

				return TRUE;

			} else {

				$this->upload_data['file'] = array('file_name' => $new_file);

				return TRUE;

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

	    
		$myProfile = false; 

		$schoolyearID = $this->session->userdata('defaultschoolyearID');
 
		
		if($this->session->userdata('usertypeID') == 3) {

			$id = $this->data['myclass'];

			if(!permissionChecker('student_view')) {

				$myProfile = true;

			}

		} else {

			$id = htmlentities(escapeString($this->uri->segment(4)));
			$campusID = htmlentities(escapeString($this->uri->segment(3)));

		}



		if($this->session->userdata('usertypeID') == 3 && $myProfile) {
			$url = $id;

			$id = $this->session->userdata('loginuserID');

			$this->getView($id, $url);

		} else {


			$this->data['set'] = $id;
			$this->data['setcampusID'] = $campusID;

			$this->data['classes'] = $this->classes_m->get_classes();
			$this->data['campuse'] = $this->campus_m->get_campus();
			
		 

			if((int)$id) {

				//$this->data['students'] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $id, 'srschoolyearID' => $schoolyearID));

				$this->data['students'] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $id,'srcampusID' => $campusID, 'srschoolyearID' => $schoolyearID));

				if(!empty($this->data['students'])) {
					$sections = $this->section_m->general_get_order_by_section(array("classesID" => $id));

					$this->data['sections'] = $sections;

					foreach ($sections as $key => $section) {

						$this->data['allsection'][$section->sectionID] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $id,'srcampusID' => $campusID, "srsectionID" => $section->sectionID, 'srschoolyearID' => $schoolyearID));

					}

				} else {

					$this->data['students'] = [];

				}

			} 

				else if($id=="all"){
				$this->data['students'] = $this->studentrelation_m->get_order_by_student(array('srcampusID' => $campusID, 'srschoolyearID' => $schoolyearID));
			}

			else {

				$this->data['set_campusID'] = [];
				$this->data['students'] = [];

			}



			$this->data["subview"] = "student/index";

			$this->load->view('_layout_main', $this->data);

		}

	}



	public function add($campusID=0) {

		if($campusID==0){
			if(campuses()==1)
	    	{
				$getCampus = $this->getCampusesDetails();
	        	redirect(base_url("student/add/".$getCampus[0]['campusID']));
	    	}else{
	    		if($this->session->userdata('campus_id')!=0){
	    			redirect(base_url("student/add/".$this->session->userdata('campus_id')));
	    		}
	    	}
		}


		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {

			$this->data['headerassets'] = array(

				'css' => array(

					'assets/datepicker/datepicker.css',

					'assets/select2/css/select2.css',

					'assets/select2/css/select2-bootstrap.css'

				),

				'js' => array(

					'assets/datepicker/datepicker.js',

					'assets/select2/select2.js'

				)

			);
			if($campusID==0)
			{
				$campusID = $this->session->userdata('campus_id');
			}
			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$this->data['classes'] = $this->classes_m->general_get_order_by_classes(array("campusID" =>$campusID));
			//$this->data['classes'] = $this->classes_m->get_classes();

			$this->data['campuse'] = $this->campus_m->get_campus();
			$this->data['campusID']= $campusID;

			$this->data['sections'] = $this->section_m->general_get_section();

			//$this->data['parents'] = $this->parents_m->get_parents();
			$this->data['parents'] = $this->parents_m->get_order_by_parents(array("campusID" =>$campusID));

			//$this->data['studentgroups'] = $this->studentgroup_m->get_studentgroup();
			$this->data['studentgroups'] = $this->studentgroup_m->get_order_by_studentgroup();
  
			$classesID = $this->input->post("classesID"); 

			if($classesID > 0) {
				$this->data['sections'] = $this->section_m->general_get_order_by_section(array("classesID" =>$classesID));

	            $this->data['optionalSubjects'] = $this->subject_m->general_get_order_by_subject(array("classesID" =>$classesID, 'type' => 0));

			} else {

				$this->data['sections'] = [];

				$this->data['optionalSubjects'] = [];

			}



			$this->data['sectionID'] = $this->input->post("sectionID");			 

	        $this->data['optionalSubjectID'] = 0;

			

			$registerNO = $this->student_m->get_auto_register_no($campusID);

            if (($registerNO) > 0) {

                $this->data['registerNO'] = (int)$registerNO + 1;

            } else {

                $this->data['registerNO'] = 1;

            }

			if($_POST) {

				$rules = $this->rules();
				$this->form_validation->set_rules($rules);

				if ($this->form_validation->run() == FALSE) {

					$this->data["subview"] = "student/add";

					$this->load->view('_layout_main', $this->data);

				} else {

					$education_detail = array();
					$campusID = $this->input->post("campusID"); 
					$name_of_school = $this->input->post("name_of_school");

					$start_year = $this->input->post("start_year");

					$end_year = $this->input->post("end_year");

					$up_to_class = $this->input->post("up_to_class");

					$reason_for_leaving = $this->input->post("reason_for_leaving");

					/*prepare education to save in db*/

					if(!empty($name_of_school)){

						foreach($name_of_school as $key=>$sc){

							if(!empty($name_of_school[$key])){

								$education_detail[] = array(

									'name_of_school' => $name_of_school[$key],

									'start_year' => $start_year[$key],

									'end_year' => $end_year[$key],

									'up_to_class' => $up_to_class[$key],

									'reason_for_leaving' => $reason_for_leaving[$key],

								);

							}

						}

					}

					

				

					$sectionID = $this->input->post("sectionID");

					if($sectionID == 0) {

						$this->data['sectionID'] = 0;

					} else {

						$this->data['sections'] = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));

						$this->data['sectionID'] = $this->input->post("sectionID");

					}



					if($this->input->post('optionalSubjectID')) {

	                    $this->data['optionalSubjectID'] = $this->input->post('optionalSubjectID');

	                } else {

	                    $this->data['optionalSubjectID'] = 0;

	                }



					$array["name"] = $this->input->post("name");

					$array["sex"] = $this->input->post("sex");

					$array["religion"] = $this->input->post("religion");
					$array["ethnicity"] = $this->input->post("ethnicity");
					$array["email"] = $this->input->post("email");

					$array["phone"] = $this->input->post("phone");

					$array["campusID"] = $this->input->post("campusID");

					$array["address"] = $this->input->post("address");

					$array["classesID"] = $this->input->post("classesID");

					$array["sectionID"] = $this->input->post("sectionID");

					if(empty($this->input->post("roll"))){

						$array["roll"] = NULL;

					}

					else{

						$array["roll"] = $this->input->post("roll");

					}

					

					$array["bloodgroup"] = $this->input->post("bloodgroup");

					$array["state"] = $this->input->post("state");

					$array["country"] = $this->input->post("country");

					

					$registerNO = $this->student_m->get_auto_register_no($campusID);

					if (($registerNO) > 0) {

						$registerNO = (int)$registerNO + 1;

					} else {

						$registerNO = 1;

					}

					$array["registerNO"] = $registerNO;

					$array["username"] = $this->input->post("username");

					$array['password'] = $this->student_m->hash($this->input->post("password"));

					$array['usertypeID'] = 3;

					$array['parentID'] = $this->input->post('guargianID');

					$array['library'] = 0;

					$array['hostel'] = 0;

					$array['transport'] = 0;

					$array['createschoolyearID'] = $schoolyearID;

					$array['schoolyearID'] = $schoolyearID;

					$array["create_date"] = date("Y-m-d H:i:s");

					$array["modify_date"] = date("Y-m-d H:i:s");

					$array["create_userID"] = $this->session->userdata('loginuserID');

					$array["create_username"] = $this->session->userdata('username');

					$array["create_usertype"] = $this->session->userdata('usertype');

					$array["active"] = 1;



					if($this->input->post('dob')) {

						$array["dob"] = date("Y-m-d", strtotime($this->input->post("dob")));

					}

					$array['photo'] = $this->upload_data['file']['file_name'];

					$this->usercreatemail($this->input->post('email'), $this->input->post('username'), $this->input->post('password'));



					$array["monthly_tuttion_fee"] = $this->input->post("monthly_tuttion_fee");

					$array["student_pob"] = $this->input->post("student_pob");

					$array["emergency_contact_no"] = $this->input->post("emergency_contact_no");

					$array["emergency_contact_relation"] = $this->input->post("emergency_contact_relation");

					$array["admission_result"] = $this->input->post("admission_result");

					$array["admission_status"] = $this->input->post("admission_status");

					

					$array["registration_fee"] = $this->input->post("registration_fee");

					$array["admission_fee"] = $this->input->post("admission_fee");

					$array["education_detail"] = json_encode($education_detail);

					 

					$this->student_m->insert_student($array);

					 $studentID = $this->db->insert_id();
					
					
					$section = $this->section_m->general_get_section($this->input->post("sectionID"));

					$classes = $this->classes_m ->get_classes($this->input->post("classesID"));

					$this->student_m->update_auto_register_no($array);



					if(!empty($classes)) {

						$setClasses = $classes->classes;

					} else {

						$setClasses = NULL;

					}

					/*if(count($campuse)) {

						$setcampuse = $campuse->name;

					} else {

						$setcampuse = NULL;

					}*/


					if(!empty($section)) {

						$setSection = $section->section;

					} else {

						$setSection = NULL;

					}



					$arrayStudentRelation = array(

						'srstudentID' => $studentID,

						'srname' => $this->input->post("name"),
						'srcampusID' => $campusID,

						'srclassesID' => $this->input->post("classesID"),

						'srclasses' => $setClasses,

						'srregisterNO' => $this->input->post("registerNO"),

						'srsectionID' => $this->input->post("sectionID"),

						'srsection' => $setSection,

						'srstudentgroupID' => $this->input->post('studentGroupID'),

						'sroptionalsubjectID' => $this->input->post('optionalSubjectID'),

						'srschoolyearID' => $schoolyearID,

					);

					if(empty($this->input->post("roll"))){

						$arrayStudentRelation["srroll"] = NULL;

					}

					else{

						$arrayStudentRelation["srroll"] = $this->input->post("roll");

					}



	                $studentExtendArray = array(

	                    'studentID' => $studentID,

	                    'studentgroupID' => $this->input->post('studentGroupID'),

	                    'optionalsubjectID' => $this->input->post('optionalSubjectID'),

	                    'extracurricularactivities' => $this->input->post('extraCurricularActivities'),

	                    'remarks' => $this->input->post('remarks')

	                );



	                $this->studentextend_m->insert_studentextend($studentExtendArray);

					$this->studentrelation_m->insert_studentrelation($arrayStudentRelation);



					$this->session->set_flashdata('success', $this->lang->line('menu_success'));

					redirect(base_url("student/index/$campusID/$classesID"));

				}

			} else {

			

		

				$this->data["subview"] = "student/add";

				$this->load->view('_layout_main', $this->data);

			}

		} else {

		

			$this->data["subview"] = "error";

			$this->load->view('_layout_main', $this->data);

		}

	}



	public function edit() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {

			$this->data['headerassets'] = array(

				'css' => array(

					'assets/datepicker/datepicker.css',

					'assets/select2/css/select2.css',

					'assets/select2/css/select2-bootstrap.css'

				),

				'js' => array(

					'assets/datepicker/datepicker.js',

					'assets/select2/select2.js'

				)

			);

			$usertype = $this->session->userdata("usertype");

			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$studentID = htmlentities(escapeString($this->uri->segment(3)));
			$url = htmlentities(escapeString($this->uri->segment(4)));
			$campusID = htmlentities(escapeString($this->uri->segment(5)));

			$this->data['studentID']= $studentID;
			$this->data['urlID']= $url;
			$this->data['campusID']= $campusID;

			if((int)$studentID && (int)$url || $url=='all') {

				//$this->data['classes'] = $this->classes_m->get_classes();
				$this->data['classes'] = $this->classes_m->general_get_order_by_classes(array("campusID" =>$campusID));
				$this->data['campuse'] = $this->campus_m->get_campus();

				$this->data['student'] = $this->studentrelation_m->get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID), TRUE);



				//$this->data['parents'] = $this->parents_m->get_parents();
				$this->data['parents'] = $this->parents_m->get_order_by_parents(array("campusID" =>$campusID));

				$this->data['studentgroups'] = $this->studentgroup_m->get_order_by_studentgroup();
	            //$this->data['studentgroups'] = $this->studentgroup_m->get_studentgroup();



				if(!empty($this->data['student'])) {

					$classesID = $this->data['student']->srclassesID;

					$this->data['sections'] = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));

	                $this->data['optionalSubjects'] = $this->subject_m->general_get_order_by_subject(array("classesID" =>$classesID, 'type' => 0));

	                if($this->input->post('optionalSubjectID')) {

	                    $this->data['optionalSubjectID'] = $this->input->post('optionalSubjectID');

	                } else {

	                    $this->data['optionalSubjectID'] = 0;

	                }

				}



				$this->data['set'] = $url;

				if(!empty($this->data['student'])) {

					if($_POST) {
						$rules = $this->rules();
						unset($rules[22]);

						$this->form_validation->set_rules($rules);

						if ($this->form_validation->run() == FALSE) {
							$this->data["subview"] = "student/edit";

							$this->load->view('_layout_main', $this->data);

						} else {
							$education_detail = array();
							$campusID = $campusID;
							$name_of_school = $this->input->post("name_of_school");

							$start_year = $this->input->post("start_year");

							$end_year = $this->input->post("end_year");

							$up_to_class = $this->input->post("up_to_class");

							$reason_for_leaving = $this->input->post("reason_for_leaving");

							/*prepare education to save in db*/

							if(!empty($name_of_school)){

								foreach($name_of_school as $key=>$sc){

									if(!empty($name_of_school[$key])){

										$education_detail[] = array(

											'name_of_school' => $name_of_school[$key],

											'start_year' => $start_year[$key],

											'end_year' => $end_year[$key],

											'up_to_class' => $up_to_class[$key],

											'reason_for_leaving' => $reason_for_leaving[$key],

										);

									}

								}

							}

							$array = array();

							$array["name"] = $this->input->post("name");

							$array["sex"] = $this->input->post("sex");

							$array["religion"] = $this->input->post("religion");
							$array["ethnicity"] = $this->input->post("ethnicity");
							$array["email"] = $this->input->post("email");
							$array["campusID"] = $campusID;
							$array["phone"] = $this->input->post("phone");

							$array["address"] = $this->input->post("address");

							$array["classesID"] = $this->input->post("classesID");

							$array["sectionID"] = $this->input->post("sectionID");

							$array["roll"] = $this->input->post("roll");

							$array["bloodgroup"] = $this->input->post("bloodgroup");

							$array["state"] = $this->input->post("state");

							$array["country"] = $this->input->post("country");

							//$array["registerNO"] = $this->input->post("registerNO");

							$array["parentID"] = $this->input->post("guargianID");

							$array["username"] = $this->input->post("username");

							$array["modify_date"] = date("Y-m-d H:i:s");

							$array['photo'] = $this->upload_data['file']['file_name'];



							if($this->input->post('dob')) {

								$array["dob"] 	= date("Y-m-d", strtotime($this->input->post("dob")));

							} else {

								$array["dob"] = NULL;

							}

							

							$array["monthly_tuttion_fee"] = $this->input->post("monthly_tuttion_fee");

							$array["student_pob"] = $this->input->post("student_pob");

							$array["emergency_contact_no"] = $this->input->post("emergency_contact_no");

							$array["emergency_contact_relation"] = $this->input->post("emergency_contact_relation");

							$array["admission_result"] = $this->input->post("admission_result");

							$array["admission_status"] = $this->input->post("admission_status");

							

							$array["registration_fee"] = $this->input->post("registration_fee");

							$array["admission_fee"] = $this->input->post("admission_fee");

							$array["education_detail"] = json_encode($education_detail);





							$studentReletion = $this->studentrelation_m->general_get_order_by_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID));

							$section = $this->section_m->general_get_section($this->input->post("sectionID"));

							$classes = $this->classes_m ->get_classes($this->input->post("classesID"));



							if(!empty($classes)) {

								$setClasses = $classes->classes;

							} else {

								$setClasses = NULL;

							}


							if(!empty($campuse)) {

								$setcampuse = $campuse->name;

							} else {

								$setcampuse = NULL;

							}

							if(!empty($section)) {

								$setSection = $section->section;

							} else {

								$setSection = NULL;

							}



							if(!count($studentReletion)) {

								$arrayStudentRelation = array(

									'srstudentID' => $studentID,

									'srcampusID' => $campusID,

									'srname' => $this->input->post("name"),

									'srclassesID' => $this->input->post("classesID"),

									'srclasses' => $setClasses,

									'srroll' => $this->input->post("roll"),

									'srregisterNO' => $this->input->post("registerNO"),

									'srsectionID' => $this->input->post("sectionID"),

									'srsection' => $setSection,

									'srstudentgroupID' => $this->input->post("studentGroupID"),

									'sroptionalsubjectID' => $this->input->post("optionalSubjectID"),

									'srschoolyearID' => $schoolyearID

								);

								$this->studentrelation_m->insert_studentrelation($arrayStudentRelation);

							} else {

								$arrayStudentRelation = array(

									'srname' => $this->input->post("name"),

									'srclassesID' => $this->input->post("classesID"),

									'srclasses' => $setClasses,

									'srroll' => $this->input->post("roll"),

									'srregisterNO' => $this->input->post("registerNO"),

									'srsectionID' => $this->input->post("sectionID"),

									'srsection' => $setSection,

									'srstudentgroupID' => $this->input->post("studentGroupID"),

									'srcampusID' => $campusID,

									'sroptionalsubjectID' => $this->input->post("optionalSubjectID"),

								);

								$this->studentrelation_m->update_studentrelation_with_multicondition($arrayStudentRelation, array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID));

							}



	                        $studentExtendArray = array(

	                            'studentgroupID' => $this->input->post('studentGroupID'),

	                            'optionalsubjectID' => $this->input->post('optionalSubjectID'),

	                            'extracurricularactivities' => $this->input->post('extraCurricularActivities'),

	                            'remarks' => $this->input->post('remarks')

	                        );



	                        $this->studentextend_m->update_studentextend_by_studentID($studentExtendArray, $studentID);

							$this->student_m->update_student($array, $studentID);

							$this->session->set_flashdata('success', $this->lang->line('menu_success'));

							redirect(base_url("student/index/$url"));

						}

					} else {

						$this->data["subview"] = "student/edit";

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

		} else {

			$this->data["subview"] = "error";

			$this->load->view('_layout_main', $this->data);

		}

	}



	public function view() {

		$this->data['headerassets'] = array(

			'css' => array(

				'assets/custom-scrollbar/jquery.mCustomScrollbar.css'

			),

			'js' => array(

				'assets/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js'

			)

		);



		$schoolyearID = $this->session->userdata('defaultschoolyearID');

		$id = htmlentities(escapeString($this->uri->segment(3)));

		$url = htmlentities(escapeString($this->uri->segment(4)));
		$campusID = htmlentities(escapeString($this->uri->segment(5)));

		$this->getView($id, $url, $campusID);

	}



	public function print_preview() {
		if(permissionChecker('student_view') || (($this->session->userdata('usertypeID') == 3) && permissionChecker('student') && ($this->session->userdata('loginuserID') == htmlentities(escapeString($this->uri->segment(3)))))) {

			$usertypeID = $this->session->userdata('usertypeID');

			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$this->data['studentgroups'] = pluck($this->studentgroup_m->get_studentgroup(), 'group', 'studentgroupID');

			$this->data['optionalSubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('type' => 0)), 'subject', 'subjectID');

			$id = htmlentities(escapeString($this->uri->segment(3)));

			$url = htmlentities(escapeString($this->uri->segment(4)));

			if ((int)$id && (int)$url) {

				$this->data["student"] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srclassesID' => $url, 'srschoolyearID' => $schoolyearID), TRUE);
				$this->data["siteinfos"] = $this->site_m->get_site_($this->data["student"]->srcampusID);
				if(!empty($this->data["student"])) {

					$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data['student']->usertypeID));

					$this->data["class"] = $this->classes_m->general_get_classes($this->data['student']->srclassesID);

					$this->data["section"] = $this->section_m->general_get_section($this->data['student']->srsectionID);

					$this->reportPDF('studentmodule.css',$this->data, 'student/print_preview');

				} else {

					$this->data["subview"] = "error";

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



	public function send_mail() {

		$retArray['status'] = FALSE;

		$retArray['message'] = '';

		if(permissionChecker('student_view') || (($this->session->userdata('usertypeID') == 3) && permissionChecker('student') && ($this->session->userdata('loginuserID') == $this->input->post('studentID')))) {

			if($_POST) {

				$rules = $this->send_mail_rules();

				$this->form_validation->set_rules($rules);

				if ($this->form_validation->run() == FALSE) {

					$retArray = $this->form_validation->error_array();

					$retArray['status'] = FALSE;

				    echo json_encode($retArray);

				    exit;

				} else {

					$this->data['studentgroups'] = pluck($this->studentgroup_m->get_studentgroup(), 'group', 'studentgroupID');

					$this->data['optionalSubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('type' => 0)), 'subject', 'subjectID');

					$id = $this->input->post('studentID');

					$url = $this->input->post('classesID');

					if ((int)$id && (int)$url) {

						$schoolyearID = $this->session->userdata('defaultschoolyearID');

						$this->data["student"] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srclassesID' => $url, 'srschoolyearID' => $schoolyearID), TRUE);

						if(!empty($this->data["student"])) {

							$this->data['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data['student']->usertypeID));

							$this->data["class"] = $this->classes_m->general_get_single_classes(array('classesID' => $this->data['student']->srclassesID));

							$this->data["section"] = $this->section_m->general_get_single_section(array('sectionID' => $this->data['student']->srsectionID));

							$email = $this->input->post('to');

							$subject = $this->input->post('subject');

							$message = $this->input->post('message');

							$this->reportSendToMail('studentmodule.css', $this->data, 'student/print_preview', $email, $subject, $message);

							$retArray['message'] = "Message";

							$retArray['status'] = TRUE;

							echo json_encode($retArray);

						    exit;

						} else {

							$retArray['message'] = $this->lang->line('student_data_not_found');

							echo json_encode($retArray);

							exit;

						}

					} else {

						$retArray['message'] = $this->lang->line('student_data_not_found');

						echo json_encode($retArray);

						exit;

					}

				}

			} else {

				$retArray['message'] = $this->lang->line('student_permissionmethod');

				echo json_encode($retArray);

				exit;

			}

		} else {

			$retArray['message'] = $this->lang->line('student_permission');

			echo json_encode($retArray);

			exit;

		}

	}



	public function delete() {

		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) {

			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$id = htmlentities(escapeString($this->uri->segment(3)));

			$url = htmlentities(escapeString($this->uri->segment(4)));

			if((int)$id && (int)$url) {

				$this->data['student'] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srschoolyearID' => $schoolyearID));

				if(!empty($this->data['student'])) {

					if(config_item('demo') == FALSE) {

						if($this->data['student']->photo != 'default.png' && $this->data['student']->photo != 'defualt.png') {

							if(file_exists(FCPATH.'uploads/images/'.$this->data['student']->photo)) {

								unlink(FCPATH.'uploads/images/'.$this->data['student']->photo);

							}

						}

					}

					$this->student_m->delete_student($id);

					$this->studentextend_m->delete_studentextend_by_studentID($id);

					$this->session->set_flashdata('success', $this->lang->line('menu_success'));

					redirect(base_url("student/index/$url"));

				} else {

					redirect(base_url("student/index"));

				}

			} else {

				redirect(base_url("student/index/$url"));

			}

		} else {

			$this->data["subview"] = "error";

			$this->load->view('_layout_main', $this->data);

		}

	}



	public function unique_roll() {

		$id = htmlentities(escapeString($this->uri->segment(3)));

		$schoolyearID = $this->session->userdata('defaultschoolyearID');

		if((int)$id) {

			$student = $this->studentrelation_m->general_get_order_by_student(array("srroll" => $this->input->post("roll"), "srstudentID !=" => $id, "srclassesID" => $this->input->post('classesID'), 'srschoolyearID' => $schoolyearID));

			if(!empty($student)) {

				$this->form_validation->set_message("unique_roll", "The %s is already exists.");

				return FALSE;

			}

			return TRUE;

		} else {

			$student = $this->studentrelation_m->general_get_order_by_student(array("srroll" => $this->input->post("roll"), "srclassesID" => $this->input->post('classesID'), 'srschoolyearID' => $schoolyearID));



			if(!empty($student)) {

				$this->form_validation->set_message("unique_roll", "The %s is already exists.");

				return FALSE;

			}

			return TRUE;

		}

	}



	public function lol_username() {

		$id = htmlentities(escapeString($this->uri->segment(3)));

		if((int)$id) {

			$student = $this->student_m->general_get_single_student(array('studentID' => $id));
			if(!empty($student))
			{
				$std_name = $student->username;
			}else{
				$std_name = '';				
			}

			$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');

			$array = array();

			$i = 0;

			foreach ($tables as $table) {
				$user = $this->student_m->get_username($table, array("username" => $this->input->post('username'), "username !=" => $std_name));

				if(!empty($user)) {
					$this->form_validation->set_message("lol_username", "%s already exists");
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

			$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');

			$array = array();

			$i = 0;

			foreach ($tables as $table) {

				$user = $this->student_m->get_username($table, array("username" => $this->input->post('username')));

				if(!empty($user)) {

					$this->form_validation->set_message("lol_username", "%s already exists");

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



	public function date_valid($date) {

		if($date) {

			if(strlen($date) <10) {

				$this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");

		     	return FALSE;

			} else {

		   		$arr = explode("-", $date);

		        $dd = $arr[0];

		        $mm = $arr[1];

		        $yyyy = $arr[2];

		      	if(checkdate($mm, $dd, $yyyy)) {

		      		return TRUE;

		      	} else {

		      		$this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");

		     		return FALSE;

		      	}

		    }

		}

		return TRUE;

	}



	public function unique_classesID() {

		if($this->input->post('classesID') == 0) {

			$this->form_validation->set_message("unique_classesID", "The %s field is required");

	     	return FALSE;

		}

		return TRUE;

	}

	public function unique_campusID() {

		if($this->input->post('campusID') == 0) {

			$this->form_validation->set_message("unique_campusID", "The %s field is required");

	     	return FALSE;

		}

		return TRUE;

	}



	public function unique_sectionID() {

		if($this->input->post('sectionID') == 0) {

			$this->form_validation->set_message("unique_sectionID", "The %s field is required");

	     	return FALSE;

		}

		return TRUE;

	}



	public function student_list() {

		$classID = $this->input->post('id');
		$campusID = $this->input->post('campusID');

		if((int)$classID) {

			$string = base_url("student/index/$campusID/$classID");

			echo $string;

		}
		else if($classID=='all')
		{
			$string = base_url("student/index/$campusID/$classID");

			echo $string;
		} else {

			redirect(base_url("student/index"));

		}

	}



	public function unique_email() {

		if($this->input->post('email')) {

			$id = htmlentities(escapeString($this->uri->segment(3)));

			if((int)$id) {

				$student_info = $this->student_m->general_get_single_student(array('studentID' => $id));

				$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');

				$array = array();

				$i = 0;

				if(isset($student_info->username))
				{
					$std_info = $student_info->username;
				}else{
					$std_info = '';					
				}

				foreach ($tables as $table) {

					$user = $this->student_m->get_username($table, array("email" => $this->input->post('email'), 'username !=' => $std_info ));

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

				$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');

				$array = array();

				$i = 0;

				foreach ($tables as $table) {

					$user = $this->student_m->get_username($table, array("email" => $this->input->post('email')));

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

		return TRUE;

	}



	public function sectioncall() {

		$classesID = $this->input->post('id');
		
		if((int)$classesID) {

			$allsection = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allsection as $value) {

				echo "<option value=\"$value->sectionID\">",$value->section,"</option>";

			}

		}

	}

	public function get_meeting_id() {

		$meeting_id = $this->input->post('id');

		if((int)$meeting_id) {

			$allsection = $this->staff_m->general_get_order_by_staff(array('id' => $meeting_id));
foreach ($allsection as $value) {

				echo $value->meeting_id;

			}
		

		}

	}

	public function sectioncall1() {

		$classesID = $this->input->post('id');

		if((int)$classesID) {

			$allsection = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allsection as $value) {

				echo "<option value=\"$value->sectionID\">",$value->section,"</option>";

			}

		}

	}
	public function sectioncall2() {

		$classesID = $this->input->post('id');

		if((int)$classesID) {

			$allsection = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allsection as $value) {

				echo "<option value=\"$value->sectionID\">",$value->section,"</option>";

			}

		}

	}
	
	public function get_zoom_api_key() {

		$zoom_api_key = $this->input->post('id');

		if((int)$zoom_api_key) {

			$allsection = $this->staff_m->general_get_order_by_staff(array('id' => $zoom_api_key));
				foreach ($allsection as $value) {

				echo $value->zoom_api_key;

			}
		

		}

	}

	public function get_zoom_api_secret() {

		$zoom_api_secret = $this->input->post('id');

		if((int)$zoom_api_secret) {

		 $allsection = $this->staff_m->general_get_order_by_staff(array('id' => $zoom_api_secret));
				foreach ($allsection as $value) {

				echo $value->zoom_api_secret;

			}
		

		}

	}

	public function systemusers() {

		$usertypeID = $this->input->post('id');

		if((int)$usertypeID) {

			$allsystemusers = $this->systemadmin_m->get_order_by_systemadmin(array('usertypeID' => $usertypeID));

			echo "<option value='0'>", $this->lang->line("select_user"),"</option>";

			foreach ($allsystemusers as $value) {

				echo "<option value=\"$value->systemadminID\">",$value->username,"</option>";

			}

		}

	}



    public function optionalsubjectcall() {

        $classesID = $this->input->post('id');

        if((int)$classesID) {

            $allOptionalSubjects = $this->subject_m->general_get_order_by_subject(array("classesID" =>$classesID, 'type' => 0));

            echo "<option value='0'>", $this->lang->line("student_select_optionalsubject"),"</option>";

            foreach ($allOptionalSubjects as $value) {

                echo "<option value=\"$value->subjectID\">",$value->subject,"</option>";

            }

        }

    }



	public function unique_capacity() {

		$id = htmlentities(escapeString($this->uri->segment(3)));

		if((int)$id) {

			if($this->input->post('sectionID')) {

				$sectionID = $this->input->post('sectionID');

				$classesID = $this->input->post('classesID');

				$schoolyearID = $this->data['siteinfos']->school_year;

				$section = $this->section_m->general_get_section($this->input->post('sectionID'));

				$student = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srsectionID' => $sectionID, 'srschoolyearID' => $schoolyearID, 'srstudentID !=' => $id));

				if(count($student) >= $section->capacity) {

					$this->form_validation->set_message("unique_capacity", "The %s capacity is full.");

		     		return FALSE;

				}

				return TRUE;

			} else {

				$this->form_validation->set_message("unique_capacity", "The %s field is required.");

		     	return FALSE;

			}

		} else {

			if($this->input->post('sectionID')) {

				$sectionID = $this->input->post('sectionID');

				$classesID = $this->input->post('classesID');

				$schoolyearID = $this->data['siteinfos']->school_year;

				$section = $this->section_m->general_get_section($this->input->post('sectionID'));

				$student = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srsectionID' => $sectionID, 'srschoolyearID' => $schoolyearID));

				if(count($student) >= $section->capacity) {

					$this->form_validation->set_message("unique_capacity", "The %s capacity is full.");

		     		return FALSE;

				}

				return TRUE;

			} else {

				$this->form_validation->set_message("unique_capacity", "The %s field is required.");

		     	return FALSE;

			}

		}

	}



 	public function unique_registerNO() {

		$id = htmlentities(escapeString($this->uri->segment(3)));

		if((int)$id) {

			$student = $this->studentrelation_m->general_get_single_student(array("srregisterNO" => $this->input->post("registerNO"), "srstudentID !=" => $id, "srcampusID" => $this->input->post("campusID")));
			if(!empty($student)) {

				$this->form_validation->set_message("unique_registerNO", "The %s is already exists.");

				return FALSE;

			}

			return TRUE;

		} else {

			$student = $this->studentrelation_m->general_get_single_student(array("srregisterNO" => $this->input->post("registerNO")));

			if(!empty($student)) {

				$this->form_validation->set_message("unique_registerNO", "The %s is already exists.");

				return FALSE;

			}

			return TRUE;

		}

	}



	public function active() {

		if(permissionChecker('student_edit')) {

			$id = $this->input->post('id');

			$status = $this->input->post('status');

			if($id != '' && $status != '') {

				if((int)$id) {

					$schoolyearID = $this->session->userdata('defaultschoolyearID');

					$student = $this->studentrelation_m->get_single_studentrelation(array('srstudentID' => $id, 'srschoolyearID' => $schoolyearID));

					if(!empty($student)) {

						if($status == 'chacked') {

							$this->student_m->update_student(array('active' => 1), $id);

							echo 'Success';

						} elseif($status == 'unchacked') {

							$this->student_m->update_student(array('active' => 0), $id);

							echo 'Success';

						} else {

							echo "Error";

						}

					} else {

						echo 'Error';

					}

				} else {

					echo "Error";

				}

			} else {

				echo "Error";

			}

		} else {

			echo "Error";

		}

	}



	private function leave_applications_date_list_by_user_and_schoolyear($userID, $schoolyearID, $usertypeID) {

		$leaveapplications = $this->leaveapplication_m->get_order_by_leaveapplication(array('create_userID'=>$userID,'create_usertypeID'=>$usertypeID,'schoolyearID'=>$schoolyearID,'status'=>1));

		

		$retArray = [];

		if(!empty($leaveapplications)) {

			$oneday    = 60*60*24;

			foreach($leaveapplications as $leaveapplication) {

			    for($i=strtotime($leaveapplication->from_date); $i<= strtotime($leaveapplication->to_date); $i= $i+$oneday) {

			        $retArray[] = date('d-m-Y', $i);

			    }

			}

		}

		return $retArray;

	}

	public function getSchoolDateFormat($date_only = true, $time = false)
    {

      

        $time_format = $setting_result[0]['time_format'];

        $hi_format = ' h:i A';
        $Hi_format = ' H:i';

        $admin = $this->session->userdata('admin');
        if ($admin) {
            if ($date_only && !$time) {

                return $admin['date_format'];
            } elseif ($time_format == "24-hour") {

                return $admin['date_format'] . $Hi_format;
            } elseif ($time_format == "12-hour") {

                return $admin['date_format'] . $hi_format;
            }
        } else if ($this->session->userdata('student')) {

            $student = $this->session->userdata('student');
            if ($date_only && !$time) {

                return $student['date_format'];
            } elseif ($time_format == "24-hour") {

                return $student['date_format'] . $Hi_format;
            } elseif ($time_format == "12-hour") {

                return $student['date_format'] . $hi_format;
            }
        }
    }

    public function timetable($campusID=0) 
    {

    // 	echo "<pre>";
    //  print_r($this->session->all_userdata());
    //  echo "</pre>";
    // exit();
    	   $this->data['headerassets'] = array(
			'css' => array(

					 'assets/datetimepicker/datetimepicker.css',

					'assets/select2/css/select2.css',

					'assets/select2/css/select2-bootstrap.css'
					),
					  'js' => array(

					'assets/datetimepicker/moment.js',

					'assets/datetimepicker/datetimepicker.js',

					 'assets/select2/select2.js'
					)
					);

    	//$data = array();
    	$role = $this->session->userdata('usertypeID');
    	if($this->session->userdata('campus_id')==0)
    	{
    		$campusID = $campusID;	
    	}else{
    		$campusID = $this->session->userdata('campus_id');	    		
    	}

    	$this->data['campusID']=$campusID;

        $class = $this->classes_m->get_join_classes();
        // print_r($class);
        // exit();
        $this->data['classlist'] = $class;
        $this->data['role'] = $role;
        $staff_id = $this->session->userdata('loginuserID');

        // print_r($staff_id);
        // exit();
        $this->data['logged_staff_id'] = $staff_id;
        if ($role == 2) {

            

            $this->data['conferences'] = $this->conference_m->getByStaff($this->session->userdata('meeting_id'),$campusID);
            // echo "<pre>";
            // print_r($this->data['conferences']);
            // echo "</pre>";
            // exit();
            // $userdata = $this->customlib->getUserData();
            // $role_id = $userdata["role_id"];
            // $condition = "";
            // foreach ($days as $day_key => $day_value) {
            //     $timetableid = "";
            //     $concate = "no";
            //     if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            //         $myclasssubjects = $this->subjecttimetable_model->getByStaffClassTeacherandDay($this->customlib->getStaffID(), $day_key);

            //         if (!empty($myclasssubjects[0]->timetable_id)) {

            //             $timetableid = $myclasssubjects[0]->timetable_id;
            //             $concate = "yes";
            //         }
            //     }

            //     $mysubjects = $this->subjecttimetable_model->getByTeacherSubjectandDay($this->customlib->getStaffID(), $day_key);
            //     if (!empty($mysubjects[0]->timetable_id)) {
            //         if ($concate == 'yes') {
            //             $timetableid = $timetableid . "," . $mysubjects[0]->timetable_id;
            //         } else {
            //             $timetableid = $mysubjects[0]->timetable_id;
            //         }
            //     }

            //     if ($timetableid == '') {

            //         $condition = " and subject_timetable.id in(0) ";
            //     } else {

            //         $condition = " and subject_timetable.id in(" . $timetableid . ") ";
            //     }
            //     $data['timetable'][$day_value] = $this->subjecttimetable_model->getSyllabussubject($staff_id, $day_key, $condition);
           // }
        } else {

            $this->data['conferences'] = $this->conference_m->getByStaff("",$campusID);
            //echo $this->db->last_query();die;
            //  echo "<pre>";
            // print_r($data['conferences'])
            // echo "</pre>";
            // exit();
        }

        
        // echo "<pre>";
        // print_r($this->data['conferences']);
        // echo "</pre>";
        // exit();
         $roles = $this->usertype_m->get_usertype();
         // print_r($roles);
         // exit();
           if ($role == 2) {

          $this->data["subview"] = "student/timetable_teacher";
        } else {
            $roles = $this->usertype_m->get_usertype();
            
             $this->data['roles'] = $roles;
		// $schoolyearID = $this->session->userdata("defaultschoolyearID");
		// $this->data['events'] = $this->event_m->get_order_by_event(array('schoolyearID' => $schoolyearID));
		$this->data["subview"] = "student/timetable";
        }
        
		$this->load->view('_layout_main', $this->data);
	}

	public function addonlineclass() {
     
        $this->data['headerassets'] = array(
			'css' => array(

					 'assets/datetimepicker/datetimepicker.css',

					'assets/select2/css/select2.css',

					'assets/select2/css/select2-bootstrap.css'
					),
					  'js' => array(

					'assets/datetimepicker/moment.js',

					'assets/datetimepicker/datetimepicker.js',

					 'assets/select2/select2.js'
					)
					);
		$this->data['classes'] = $this->classes_m->get_classes();
	
		$this->data['usertypes'] = $this->usertype_m->general_get_usertype();
		// $meeting_id = $this->staff_m->get_teacher_name();
	    

		$this->data['users'] = $this->staff_m->get_teacher_name();
		// $meeting_id = $this->session->userdata('usertypeID');

		$access_token = $this->session->userdata('zoom_access_token');
		if(empty($access_token)){
			$this->meetings();
		}

		if($_POST) {
		$role = $this->session->userdata('usertypeID');
            $api_type = 'global';
            $staff = $this->staff_m->general_get_staff();
            $zoom_api_key = $this->input->post('zoom_api_key');
            $zoom_api_secret = $this->input->post('zoom_api_secret');
            // echo "<pre>";
            // print_r($zoom_api_secret);
            // echo "</pre>";
            // exit();
            // if ($role != 2) {
            // $params = array(
            //         'zoom_api_key' => "",
            //         'zoom_api_secret' => "",
            //     );
            // } else {

				if ( $zoom_api_key != "" && $zoom_api_secret != "") {
                    $api_type = "global";
                }
                $params = array(
                    'zoom_api_key' => $zoom_api_key,
                    'zoom_api_secret' => $zoom_api_secret,
                );
               
                // print_r($params);
                // exit();
            // }
            // print_r($params);
            // exit();
            $this->load->library('zoom_api', $params);
            $explode = explode('-', $this->input->post("date"));
 				// exit();

	            $insert_array = array(
					'campusID'=> $this->input->post('campusID'),
					'staff_id'=> $this->input->post('id'),
					'meeting_id'=> $this->input->post('meeting_id'),
					'title' => $this->input->post('title'),
					'date' => date('Y-m-d H:i:s', strtotime($explode[0])),
	                'class_id' => $this->input->post('class_id'),
	                'section_id' => $this->input->post('section_id'),
	                'duration' => $this->input->post('duration'),
	                'password' => $this->input->post('password'),
	                'created_id' => $this->session->userdata('loginuserID'),
	                'api_type' => $api_type,
	                'host_video' => $this->input->post('host_video'),
	                'client_video' => $this->input->post('client_video'),
	                'description' => $this->input->post('description'),
					);

				// print_r($insert_array);
				// exit();
				/*$response = $this->zoom_api->createAMeeting($insert_array);*/
				/*$access_token = $this->session->userdata('zoom_access_token');
 				if(empty($access_token)){
 					$this->meetings();
 				}*/

				$response = $this->create_meeting($insert_array);
				//view($response,1);

				 if ($response) {
				 if (isset($response['id'])) {
				 /*if (isset($response->code)) {
				 if ($response->code == 200) {*/
                    $insert_array['return_response'] = json_encode($response);
					$this->conference_m->insert_conference($insert_array);

                   $data = $this->db->insert_id();
                  	$sender_details = array('class_id' => $insert_array['class_id'], 'section_id' => $insert_array['section_id'], 'title' => $insert_array['title'], 'date' => $insert_array['date'], 'duration' => $insert_array['duration']);
                    // $this->mailsmsconf->mailsms('online_classes', $sender_details);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("student/timetable/"));
                    // $response = array('status' => 1, 'message' => $this->lang->line('success_message'));
                } else {
                    $response = array('status' => 0, 'error' => array($response->message));
                }
            } else {
                $response = array('status' => 0, 'error' => array('Something went wrong.'));
            }  
			} 
			else {
			$this->data["subview"] = "student/add_onlineclass";
			$this->load->view('_layout_main', $this->data);
		}
	}


	public function add_classbyteacher() {
    // echo "<pre>";
    //  print_r($this->session->all_userdata());
    //  echo "</pre>";
    // exit();
        $this->data['headerassets'] = array(
			'css' => array(

					 'assets/datetimepicker/datetimepicker.css',

					'assets/select2/css/select2.css',

					'assets/select2/css/select2-bootstrap.css'
					),
					  'js' => array(

					'assets/datetimepicker/moment.js',

					'assets/datetimepicker/datetimepicker.js',

					 'assets/select2/select2.js'
					)
					);
		$this->data['classes'] = $this->classes_m->get_classes_teacher();
		
		$this->data['usertypes'] = $this->usertype_m->general_get_usertype();
		
		$this->data['users'] = $this->staff_m->general_get_staff();
		
		$zoom_api_key = $this->session->userdata('zoom_api_key');
        $zoom_api_secret = $this->session->userdata('zoom_api_secret');
		// echo "<pre>";
		// print_r($this->data['users']);
		// echo "</pre>";
		// exit();
		$access_token = $this->session->userdata('zoom_access_token');
		if(empty($access_token)){
			$this->meetings();
		}
		if($_POST) {
		$role = $this->session->userdata('usertypeID');
		 $api_type = 'global';
				 $params = array(
                'zoom_api_key' => $zoom_api_key,
                'zoom_api_secret' => $zoom_api_secret,
            );
            $this->load->library('zoom_api', $params);
            $explode = explode('-', $this->input->post("date"));
            $insert_array = array(
				'campusID'=> $this->session->userdata('campus_id'),
				'staff_id'=> $this->session->userdata('loginuserID'),
				'meeting_id'=> $this->session->userdata('meeting_id'),
				 'title' => $this->input->post('title'),
				'date' => date('Y-m-d H:i:s', strtotime($explode[0])),
                'class_id' => $this->input->post('class_id'),
                'section_id' => $this->input->post('section_id'),
                'duration' => $this->input->post('duration'),
                'password' => $this->input->post('password'),
                'created_id' => $this->session->userdata('loginuserID'),
                'api_type' => $api_type,
                'host_video' => $this->input->post('host_video'),
                'client_video' => $this->input->post('client_video'),
                'description' => $this->input->post('description'),
				);

            	$response = $this->create_meeting($insert_array);
				 //$response = $this->zoom_api->createAMeeting($insert_array);
				
				 if ($response) {
				 if (isset($response['id'])) {
                    $insert_array['return_response'] = json_encode($response);
					$this->conference_m->insert_conference($insert_array);
                   $data = $this->db->insert_id();
                  	$sender_details = array('class_id' => $this->input->post('class_id'), 'section_id' => $this->input->post('section_id'), 'title' => $this->input->post('title'), 'date' => $this->input->post('date'), 'duration' => $this->input->post('duration'));
                    // $this->mailsmsconf->mailsms('online_classes', $sender_details);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("student/timetable/"));
                    // $response = array('status' => 1, 'message' => $this->lang->line('success_message'));
                } else {
                    $response = array('status' => 0, 'error' => array($response->message));
                }
            } else {
                $response = array('status' => 0, 'error' => array('Something went wrong.'));
            }  
			} 
			else {
			$this->data["subview"] = "student/add_classbyteacher";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function chgstatus() {
        $response = array();
		$insert_array = array(
                'status' => $this->input->post('chg_status'),
            );

            $insert_id = $this->conference_m->updates($this->input->post('conference_id'), $insert_array);
            $response = array('status' => 1, 'message' => $this->lang->line('update_message'));

        return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(200)
                        ->set_output(json_encode($response));
    }

    public function timetable_student() 
    {
    // 	echo "<pre>";
    //  print_r($this->session->all_userdata());
    //  echo "</pre>";
    // exit();
    	   $this->data['headerassets'] = array(
			'css' => array(

					 'assets/datetimepicker/datetimepicker.css',

					'assets/select2/css/select2.css',

					'assets/select2/css/select2-bootstrap.css'
					),
					  'js' => array(

					'assets/datetimepicker/moment.js',

					'assets/datetimepicker/datetimepicker.js',

					 'assets/select2/select2.js'
					)
					);

    	//$data = array();
    	$student_current_class = $this->session->all_userdata();
    	//view($student_current_class,1);
    	  $list = $this->conference_m->getByClassSection($student_current_class['classesID'], $student_current_class['sectionID']);

    	$this->data['conferences_student'] = $list;
		$this->data["subview"] = "student/student_timetable";
		$this->load->view('_layout_main', $this->data);
	}

	public function delete_meeting($id, $zoom_id) {



        /********** Laterly Added **********/
        $data['title'] = 'Delete Conference';
        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
        $this->conference_m->remove($id);

        /*$result = $this->conference_m->get($id);
        if (empty($result)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-error text-left">Something went wrong.</div>');
            redirect($_SERVER['HTTP_REFERER'], 'refresh');
        }

        if ($result->api_type == 'global') {

            $params = array(
                'zoom_api_key' => "",
                'zoom_api_secret' => "",
            );
        } else {
            $staff = $this->staff_m->get($this->session->userdata('loginuserID'));
            if ($staff['zoom_api_key'] == "" && $staff['zoom_api_secret'] == "") {
                $this->session->set_flashdata('msg', '<div class="alert alert-error text-left">You have created by your own account, API Credential not exists.</div>');
                redirect($_SERVER['HTTP_REFERER'], 'refresh');
            }
            $params = array(
                'zoom_api_key' => $staff['zoom_api_key'],
                'zoom_api_secret' => $staff['zoom_api_secret'],
            );
        }
        $this->load->library('zoom_api', $params);

        $response = $this->zoom_api->deleteMeeting($zoom_id);

        if (!empty($response)) {
          $this->session->set_flashdata('success', $this->lang->line('menu_success'));
        } else {
            $data['title'] = 'Delete Conference';
            // $this->session->set_flashdata('msg', '<div class="alert alert-error text-left">' . $this->lang->line('delete_message') . '</div>');
            $this->session->set_flashdata('success', $this->lang->line('menu_success'));
            $this->conference_m->remove($id);
        }*/

        redirect('student/timetable/', 'refresh');
    }


    public function login() {
        $authorization_url = "https://zoom.us/oauth/authorize?response_type=code&client_id={$this->zoom_api_key}&redirect_uri={$this->redirect_uri}";
        redirect($authorization_url);
    }

    public function callback() {
        if ($this->input->get('code')) {
            $code = $this->input->get('code');
            $token_url = "https://zoom.us/oauth/token";
            $post_fields = array(
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirect_uri
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $token_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Basic ' . base64_encode($this->zoom_api_key . ':' . $this->zoom_api_secret)
            ));

            $response = curl_exec($ch);
            curl_close($ch);

            $response_data = json_decode($response, true);
            if (isset($response_data['access_token'])) {
                $this->session->set_userdata('zoom_access_token', $response_data['access_token']);
                // Redirect to a secured area or home
                redirect($this->ulr2.'student/meetings');
                //$this->meetings();
            } else {
                // Handle error
                echo 'Error retrieving access token';
            }
        } else {
            // Handle error
            echo 'Authorization code not found';
        }
    }

    public function meetings() {
        $access_token = $this->session->userdata('zoom_access_token');
        if (!$access_token) {
            redirect($this->ulr2.'student/login');
        }
        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.zoom.us/v2/users/me/meetings");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $response_data = json_decode($response, true);

        $this->session->set_flashdata('success', 'Zoom Access has been given. Now you can add Live Classes.');
		redirect(base_url("student/addonlineclass/"));

    }

    public function create_meeting($data=0) {

        $access_token = $this->session->userdata('zoom_access_token');
        if (!$access_token) {
            redirect($this->ulr2.'student/login');
        	//$this->login();
        }

        $post_time = $data['date'];
        $start_time = gmdate("Y-m-d\TH:i:s", strtotime($post_time));
        $createAMeetingArray = array();
        if (!empty($data['alternative_host_ids'])) {
            if (count($data['alternative_host_ids']) > 1) {
                $alternative_host_ids = implode(",", $data['alternative_host_ids']);
            } else {
                $alternative_host_ids = $data['alternative_host_ids'][0];
            }
        }
        $createAMeetingArray['topic'] = $data['title'];
        $createAMeetingArray['agenda'] = !empty($data['agenda']) ? $data['agenda'] : "";
        $createAMeetingArray['type'] = !empty($data['type']) ? $data['type'] : 2; //Scheduled
        $createAMeetingArray['start_time'] = $start_time;
        //$createAMeetingArray['timezone'] = $data['timezone'];
        $createAMeetingArray['timezone'] = $data['date'];
        $createAMeetingArray['password'] = !empty($data['password']) ? $data['password'] : "";
        $createAMeetingArray['duration'] = !empty($data['duration']) ? $data['duration'] : 60;
        $createAMeetingArray['settings'] = array(
            'join_before_host' => !empty($data['join_before_host']) ? true : false,
            'host_video' => !empty($data['host_video']) ? true : false,
            'participant_video' => !empty($data['client_video']) ? true : false,
            'mute_upon_entry' => !empty($data['option_mute_participants']) ? true : false,
            'enforce_login' => !empty($data['option_enforce_login']) ? true : false,
            'auto_recording' => !empty($data['option_auto_recording']) ? $data['option_auto_recording'] : "none",
            'alternative_hosts' => isset($alternative_host_ids) ? $alternative_host_ids : "",
        );

        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.zoom.us/v2/users/me/meetings");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($createAMeetingArray));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);
        $response_datas = json_decode($response, true);
        if($response_datas['code']==124){
        	redirect($this->ulr2.'student/login');
        	//$this->login();
        }

        return $response_data = json_decode($response, true);
    }






}