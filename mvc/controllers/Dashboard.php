<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Dashboard extends Admin_Controller 
{
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
	protected $_versionCheckingUrl = 'http://demo.inilabs.net/autoupdate/update/index';
 
	function __construct() 
	{
		parent::__construct();
		$this->load->model('systemadmin_m');
		$this->load->model("dashboard_m");
		$this->load->model("automation_shudulu_m");
		$this->load->model("automation_rec_m");
		$this->load->model("setting_m");
		$this->load->model("notice_m");
		$this->load->model("user_m");
		$this->load->model("student_m");
		$this->load->model("classes_m");
		$this->load->model("teacher_m");
		$this->load->model("parents_m");
		$this->load->model("sattendance_m");
		$this->load->model("tattendance_m");
		$this->load->model("subjectattendance_m");
		$this->load->model("eattendance_m");
		$this->load->model("subject_m");
		$this->load->model("feetypes_m");
		$this->load->model("invoice_m");
		$this->load->model("expense_m");
		$this->load->model("payment_m");
		$this->load->model("lmember_m");
		$this->load->model("book_m");
		$this->load->model("issue_m");
		$this->load->model("student_info_m");
		$this->load->model('hmember_m');
		$this->load->model('tmember_m');
		$this->load->model('event_m');
		$this->load->model('holiday_m');
		$this->load->model('visitorinfo_m');
		$this->load->model('income_m');
		$this->load->model('make_payment_m');
		$this->load->model('maininvoice_m');
		$this->load->model('studentrelation_m');
		$this->load->model('conference_m'); 
		$language = $this->session->userdata('lang');
		$this->lang->load('dashboard', $language);

		$this->automation();
	}

	private function automation() 
	{
		/* Automation Start */
		if($this->data['siteinfos']->auto_invoice_generate == 1) {

			$array = [];
			$autoRecArray = [];
			$cnt = 0;
			$date = date('Y-m-d');
			$day = date('d');
			$month = date('m');
			$year = date('Y');
			$setting = $this->setting_m->get_setting(1);
			if($day >= $setting->automation) {
				$libraryFeetype = $this->feetypes_m->get_single_feetypes(array('feetypes' => $this->lang->line('dashboard_libraryfee')));
				if(!count($libraryFeetype)) {
					$this->feetypes_m->insert_feetypes(array('feetypes' => $this->lang->line('dashboard_libraryfee'), 'note' => "Don't delete it!"));
				}
				$libraryFeetype = $this->feetypes_m->get_single_feetypes(array('feetypes' => $this->lang->line('dashboard_libraryfee')));

				$transportFeetype = $this->feetypes_m->get_single_feetypes(array('feetypes' => $this->lang->line('dashboard_transportfee')));
				if(!count($transportFeetype)) {
					$this->feetypes_m->insert_feetypes(array('feetypes' => $this->lang->line('dashboard_transportfee'), 'note' => "Don't delete it!"));
				}
				$transportFeetype = $this->feetypes_m->get_single_feetypes(array('feetypes' => $this->lang->line('dashboard_transportfee')));

				$hostelFeetype = $this->feetypes_m->get_single_feetypes(array('feetypes' => $this->lang->line('dashboard_hostelfee')));
				if(!count($hostelFeetype)) {
					$this->feetypes_m->insert_feetypes(array('feetypes' => $this->lang->line('dashboard_hostelfee'), 'note' => "Don't delete it!"));
				}
				$hostelFeetype = $this->feetypes_m->get_single_feetypes(array('feetypes' => $this->lang->line('dashboard_hostelfee')));

				$automation_shudulus = $this->automation_shudulu_m->get_automation_shudulu();

				if(!empty($automation_shudulus)) {
					foreach ($automation_shudulus as $automation_shudulu) {
						if($automation_shudulu->month == $month && $automation_shudulu->year == $year) {
							$cnt = 1;
						}
					}

					if($cnt === 0) {
						$alltotalamount = 0;
						$alltotalamounttransport = 0;
						$alltotalamounthostel = 0;

						$automationStudents = $this->student_m->general_get_order_by_student(array('schoolyearID' => $this->data['siteinfos']->school_year, 'classesID !=' => $this->data['siteinfos']->ex_class));
						$automationLMember = pluck($this->lmember_m->get_lmember(), 'lbalance', 'studentID');
						$automationTMember = pluck($this->tmember_m->get_tmember(), 'tbalance', 'studentID');
						$automationHMember = pluck($this->hmember_m->get_hmember(), 'hbalance', 'studentID');
						$allRecord = $this->getAllRec($this->automation_rec_m->get_automation_rec());
						$superAdmin = $this->systemadmin_m->get_systemadmin(1);

						if(!empty($automationStudents)) {
							foreach ($automationStudents as $aTstudentkey => $aTstudent) {
								if(!empty($automationLMember)) {
									if(isset($automationLMember[$aTstudent->studentID])) {
										if($automationLMember[$aTstudent->studentID] > 0) {
											if(!isset($allRecord[5427279][$aTstudent->studentID][$month][$year])) {

												$mainInvoiceArray[] = array(
													'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
					                         		'maininvoiceclassesID' => $aTstudent->classesID,
					                         		'maininvoicestudentID' =>  $aTstudent->studentID,
					                         		'maininvoicestatus' => 0,
					                         		'maininvoiceuserID' => 1,
					                         		'maininvoiceusertypeID' => 1,
					                         		'maininvoiceuname' => NULL,
					                         		'maininvoicedate' => date("Y-m-d"),
					                         		'maininvoicecreate_date' => date('Y-m-d'),
					                         		'maininvoiceday' => date('d'),
					                         		'maininvoicemonth' => date('m'),
					                         		'maininvoiceyear' => date('Y'),
					                         		'maininvoicedeleted_at' => 1
												);

												$array[] = array(
													'schoolyearID' => $this->data['siteinfos']->school_year,
													'classesID' => $aTstudent->classesID,
													'studentID' => $aTstudent->studentID,
													'feetypeID' => count($libraryFeetype) ? $libraryFeetype->feetypesID : 0,
													'feetype' => count($libraryFeetype) ? $libraryFeetype->feetypes : NULL,
													'amount' => (int)$automationLMember[$aTstudent->studentID],
													'discount' => 0,
													'paidstatus' => 0,
													'userID' => 1,
													'usertypeID' => 1,
													'uname' => $superAdmin->name,
													'date' => date("Y-m-d"),
													'create_date' => date('Y-m-d'),
													'day' => date('d'),
													'month' => date('m'),
													'year' => date('Y'),
													'deleted_at' => 1
												);

												$autoRecArray[] = array(
													'studentID' => $aTstudent->studentID,
													'date' => $date,
													'day' => $day,
													'month' => $month,
													'year' => $year,
													'nofmodule' => 5427279
												);

											}
										}

									}
								}

								if(!empty($automationTMember)) {
									if(isset($automationTMember[$aTstudent->studentID])) {
										if($automationTMember[$aTstudent->studentID] > 0) {
											if(!isset($allRecord[872677678][$aTstudent->studentID][$month][$year])) {

												$mainInvoiceArray[] = array(
													'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
					                         		'maininvoiceclassesID' => $aTstudent->classesID,
					                         		'maininvoicestudentID' =>  $aTstudent->studentID,
					                         		'maininvoicestatus' => 0,
					                         		'maininvoiceuserID' => 1,
					                         		'maininvoiceusertypeID' => 1,
					                         		'maininvoiceuname' => NULL,
					                         		'maininvoicedate' => date("Y-m-d"),
					                         		'maininvoicecreate_date' => date('Y-m-d'),
					                         		'maininvoiceday' => date('d'),
					                         		'maininvoicemonth' => date('m'),
					                         		'maininvoiceyear' => date('Y'),
					                         		'maininvoicedeleted_at' => 1
												);

												$array[] = array(
													'schoolyearID' => $this->data['siteinfos']->school_year,
													'classesID' => $aTstudent->classesID,
													'studentID' => $aTstudent->studentID,
													'feetypeID' => count($transportFeetype) ? $transportFeetype->feetypesID : 0,
													'feetype' => count($transportFeetype) ? $transportFeetype->feetypes : 0,
													'amount' => (int)$automationTMember[$aTstudent->studentID],
													'discount' => 0,
													'paidstatus' => 0,
													'userID' => 1,
													'usertypeID' => 1,
													'uname' => $superAdmin->name,
													'date' => date("Y-m-d"),
													'create_date' => date('Y-m-d'),
													'day' => date('d'),
													'month' => date('m'),
													'year' => date('Y'),
													'deleted_at' => 1
												);

												$autoRecArray[] = array(
													'studentID' => $aTstudent->studentID,
													'date' => $date,
													'day' => $day,
													'month' => $month,
													'year' => $year,
													'nofmodule' => 872677678
												);
											}

										}
									}
								}


								if(!empty($automationHMember)) {
									if(isset($automationHMember[$aTstudent->studentID])) {
										if($automationHMember[$aTstudent->studentID] > 0) {
											if(!isset($allRecord[467835][$aTstudent->studentID][$month][$year])) {

												$mainInvoiceArray[] = array(
													'maininvoiceschoolyearID' => $this->data['siteinfos']->school_year,
					                         		'maininvoiceclassesID' => $aTstudent->classesID,
					                         		'maininvoicestudentID' =>  $aTstudent->studentID,
					                         		'maininvoicestatus' => 0,
					                         		'maininvoiceuserID' => 1,
					                         		'maininvoiceusertypeID' => 1,
					                         		'maininvoiceuname' => NULL,
					                         		'maininvoicedate' => date("Y-m-d"),
					                         		'maininvoicecreate_date' => date('Y-m-d'),
					                         		'maininvoiceday' => date('d'),
					                         		'maininvoicemonth' => date('m'),
					                         		'maininvoiceyear' => date('Y'),
					                         		'maininvoicedeleted_at' => 1
												);

												$array[] = array(
													'schoolyearID' => $this->data['siteinfos']->school_year,
													'classesID' => $aTstudent->classesID,
													'studentID' => $aTstudent->studentID,
													'feetypeID' => count($hostelFeetype) ? $hostelFeetype->feetypesID : NULL,
													'feetype' => count($hostelFeetype) ? $hostelFeetype->feetypes : NULL,
													'feetype' => $this->lang->line('dashboard_hostelfee'),
													'amount' => (int)$automationHMember[$aTstudent->studentID],
													'discount' => 0,
													'paidstatus' => 0,
													'userID' => 1,
													'usertypeID' => 1,
													'uname' => $superAdmin->name,
													'date' => date("Y-m-d"),
													'create_date' => date('Y-m-d'),
													'day' => date('d'),
													'month' => date('m'),
													'year' => date('Y'),
													'deleted_at' => 1
												);

												$autoRecArray[] = array(
													'studentID' => $aTstudent->studentID,
													'date' => $date,
													'day' => $day,
													'month' => $month,
													'year' => $year,
													'nofmodule' => 467835
												);
											}
										}
									}
								}
							}
						}

	                    if(!empty($mainInvoiceArray)) {
							$count = count($mainInvoiceArray);
		                    $firstID = $this->maininvoice_m->insert_batch_maininvoice($mainInvoiceArray);

		                    $lastID = $firstID + ($count-1);

		                    if($lastID >= $firstID) {
		                    	$j = 0;
		                    	for ($i = $firstID; $i <= $lastID ; $i++) {
		                    		$array[$j]['maininvoiceID'] = $i;
		                    		$j++;
		                    	}
		                    }

							if(!empty($array)) {
								$this->invoice_m->insert_batch_invoice($array);
							}

							if(!empty($autoRecArray)) {
								$this->automation_rec_m->insert_batch_automation_rec($autoRecArray);
							}

							$this->automation_shudulu_m->insert_automation_shudulu(array(
								'date' => $date,
								'day' => $day,
								'month' => $month,
								'year' => $year
							));
	                    }
					}
				} else {
					$this->automation_shudulu_m->insert_automation_shudulu(array(
						'date' => $date,
						'day' => $day,
						'month' => $month,
						'year' => $year
					));
				}
			}
		}
		/* Automation Close */
	}

	public function index($campusID=0) 
	{
		
		if($this->session->userdata('campus_id')!=0)
		{
			if($campusID==0){
				redirect(base_url('dashboard/index/'.$this->session->userdata('campus_id')));
			}
		}

		$this->data['headerassets'] = array(
			'css' => array(
				'assets/fullcalendar/lib/cupertino/jquery-ui.min.css',
				'assets/fullcalendar/fullcalendar.css',
			),
			'js' => array(
				'assets/highcharts/highcharts.js',
				'assets/highcharts/highcharts-more.js',
				'assets/highcharts/data.js',
				'assets/highcharts/drilldown.js',
				'assets/highcharts/exporting.js',
				'assets/fullcalendar/lib/jquery-ui.min.js',
				'assets/fullcalendar/lib/moment.min.js',
				'assets/fullcalendar/fullcalendar.min.js',
			)
		);
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$loginuserID = $this->session->userdata('loginuserID');
		if($campusID>0){
			$this->data["siteinfos"] = $this->site_m->get_site_($campusID);
			$students =$this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID,'srcampusID' => $campusID));

			$classes	= pluck($this->classes_m->get_order_by_classes(array('campusID'=>$campusID)), 'obj', 'classesID');
			$teachers	= pluck($this->teacher_m->get_order_by_teacher(array('campusID'=>$campusID)), 'obj', 'teacherID');
			$parents	= $this->parents_m->get_order_by_parents(array('campusID'=>$campusID));
			$user	    = $this->user_m->get_order_by_user(array('campusID'=>$campusID));
			$visitors 	= $this->visitorinfo_m->get_order_by_visitorinfo(array('schoolyearID' => $schoolyearID,'campusID' => $campusID));
			$lmembers	= $this->lmember_m->get_lmember();
			$books		= $this->book_m->get_order_by_book(array('campusID'=>$campusID));
			$feetypes	= $this->feetypes_m->get_order_by_feetypes(array('campusID'=>$campusID));
			$events		= $this->event_m->get_order_by_event(array('schoolyearID' => $schoolyearID,'campusID' => $campusID));
			$holidays	= $this->holiday_m->get_order_by_holiday(array('schoolyearID' => $schoolyearID,'campusID' => $campusID));
			$subjects	= $this->subject_m->get_order_by_subject(array('campusID'=>$campusID));
		}else{
			$this->data["siteinfos"] = $this->site_m->get_site_();
			$students = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID));
			$classes	= pluck($this->classes_m->get_classes(), 'obj', 'classesID');
			$teachers	= pluck($this->teacher_m->get_teacher(), 'obj', 'teacherID');
			$parents	= $this->parents_m->get_parents();
			$user	    = $this->user_m->get_user();
			$visitors 	= $this->visitorinfo_m->get_order_by_visitorinfo(array('schoolyearID' => $schoolyearID));
			$lmembers	= $this->lmember_m->get_lmember();
			$books		= $this->book_m->get_book();
			$feetypes	= $this->feetypes_m->get_feetypes();
			$events		= $this->event_m->get_order_by_event(array('schoolyearID' => $schoolyearID));
			$holidays	= $this->holiday_m->get_order_by_holiday(array('schoolyearID' => $schoolyearID));
			$subjects	= $this->subject_m->get_order_by_subject();
		}

		$allmenu 	= pluck($this->menu_m->get_order_by_menu(), 'icon', 'link');
		$allmenulang = pluck($this->menu_m->get_order_by_menu(), 'menuName', 'link');
		//view($this->data['siteinfos'],1);
		if((config_item('demo') === FALSE) && ($this->data['siteinfos']->auto_update_notification == 1) && ($this->session->userdata('usertypeID') == 1) && ($this->session->userdata('adminLogin') == 1)) {
			if($this->session->userdata('updatestatus') === null) {
				$this->data['versionChecking'] = $this->checkUpdate();
			} else {
				$this->data['versionChecking'] = 'none';
			}
		} else {
			$this->data['versionChecking'] = 'none';
		}

		if($this->session->userdata('usertypeID') == 3) {
			$getLoginStudent = $this->studentrelation_m->get_single_student(array('srstudentID' => $loginuserID, 'srschoolyearID' => $schoolyearID));
			if(!empty($getLoginStudent)) {
				//$subjects	= $this->subject_m->get_order_by_subject(array('classesID' => $getLoginStudent->srclassesID));
				$invoices	= $this->maininvoice_m->get_order_by_maininvoice(array('maininvoicestudentID' => $getLoginStudent->srstudentID, 'maininvoiceschoolyearID' => $schoolyearID, 'maininvoicedeleted_at' => 1));
				$lmember = $this->lmember_m->get_single_lmember(array('studentID' => $getLoginStudent->srstudentID));
				if(!empty($lmember)) {
					$issues = $this->issue_m->get_order_by_issue(array("lID" => $lmember->lID, 'return_date' => NULL));
				} else {
					$issues = [];
				}
			} else {
				$invoices = [];
				$subjects = [];
				$issues = [];
			}
		} else {
			$invoices	= $this->maininvoice_m->get_order_by_maininvoice(array('maininvoiceschoolyearID' => $schoolyearID, 'maininvoicedeleted_at'=> 1));
			//$subjects	= $this->subject_m->get_subject();
			//$user	    = $this->user_m->get_user();
			

			$issues		= $this->issue_m->get_order_by_issue(array('return_date' => NULL));
		}

		$deshboardTopWidgetUserTypeOrder = $this->session->userdata('master_permission_set');

		$this->data['dashboardWidget']['students'] 		= count($students);
		$this->data['dashboardWidget']['classes']  		= count($classes);
		$this->data['dashboardWidget']['teachers'] 		= count($teachers);
		$this->data['dashboardWidget']['parents'] 		= count($parents);
		$this->data['dashboardWidget']['subjects'] 		= count($subjects);
		$this->data['dashboardWidget']['user'] 		    = count($user);
		$this->data['dashboardWidget']['visitors'] 		= count($visitors);
		$this->data['dashboardWidget']['books'] 		= count($books);
		$this->data['dashboardWidget']['feetypes'] 		= count($feetypes);
		$this->data['dashboardWidget']['lmembers'] 		= count($lmembers);
		$this->data['dashboardWidget']['events'] 		= count($events);
		$this->data['dashboardWidget']['issues'] 		= count($issues);
		$this->data['dashboardWidget']['holidays'] 		= count($holidays);
		$this->data['dashboardWidget']['invoices'] 		= count($invoices);
		$this->data['dashboardWidget']['allmenu'] 		= $allmenu;
		$this->data['dashboardWidget']['allmenulang'] 	= $allmenulang;
		
		$attendanceSystem = $this->data['siteinfos']->attendance;
		// print_r($attendanceSystem);
		// exit();
		$this->data['attendanceSystem'] = $attendanceSystem;

		// print_r($attendanceSystem);
		// exit();

		if($attendanceSystem != 'subject') {
		// 	 print_r($attendanceSystem);
	 // exit();
			$attendances = $this->sattendance_m->get_order_by_attendance(array('schoolyearID' => $schoolyearID, 'monthyear' => date('m-Y')));
			// echo "<pre>";
			// print_r($attendances);
			// echo "</pre>";
			// exit();

			$classWiseAttendance = [];
			foreach ($attendances as $attendance ) {

				for($i=1;$i<=31;$i++) {

					if($i > date('d')) break;

					$date = 'a'.$i;

					 // print_r($attendance->classesID);
					 // exit();

					if(!isset($classWiseAttendance[$attendance->classesID][$i]['P'])) {
						$classWiseAttendance[$attendance->classesID][$i]['P'] = 0;
					}

					if(!isset($classWiseAttendance[$attendance->classesID][$i]['A'])) {
						$classWiseAttendance[$attendance->classesID][$i]['A'] = 0;
					}

					if($attendance->$date == 'P' || $attendance->$date == 'L' ||  $attendance->$date == 'LE') {
						$classWiseAttendance[$attendance->classesID][$i]['P']++;
					} else {
						$classWiseAttendance[$attendance->classesID][$i]['A']++;
					}

				}

			}

			$todaysAttendance = [];
			foreach ($classWiseAttendance as $key => $value) {
				$todaysAttendance[$key] = $value[(int)date('d')];
			}
			// echo "<pre>";
			// print_r($todaysAttendance[$key]['P']);
			// echo "</pre>";
			// exit();

			$this->data['classes'] = $classes;
			$this->data['classWiseAttendance'] = $classWiseAttendance;
			$this->data['todaysAttendance'] = $todaysAttendance;
		} else {
			$attendances = $this->subjectattendance_m->get_order_by_sub_attendance(array('schoolyearID' => $schoolyearID, 'monthyear' => date('m-Y')));

			$subjectWiseAttendance = [];
			foreach ($attendances as $attendance ) {
				for($i=1;$i<=31;$i++) {
					if($i > date('d')) break;

					$date = 'a'.$i;

					if(!isset($subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['P'])) {
						$subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['P'] = 0;
					}

					if(!isset($subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['A'])) {
						$subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['A'] = 0;
					}

					if($attendance->$date == 'P' || $attendance->$date == 'L' || $attendance->$date == 'LE') {
						$subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['P']++;
					} else {
						$subjectWiseAttendance[$attendance->classesID][$attendance->subjectID][$i]['A']++;
					}
				}
			}

			$todaysSubjectWiseAttendance = array();
			foreach ($subjectWiseAttendance as $class => $subject) {
				foreach ($subject as $key => $value) {
					if(!isset($todaysSubjectWiseAttendance[$class])) {
						$todaysSubjectWiseAttendance[$class]['P'] = 0;
						$todaysSubjectWiseAttendance[$class]['A'] = 0;
					}
					$todaysSubjectWiseAttendance[$class]['P'] += $value[(int)date('d')]['P'];
					$todaysSubjectWiseAttendance[$class]['A'] += $value[(int)date('d')]['A'];
				}
			}
			// print_r($todaysSubjectWiseAttendance);
			// exit();

			$this->data['classes'] = $classes;
			$this->data['subjectWiseAttendance'] = $subjectWiseAttendance;
			$this->data['todaysSubjectWiseAttendance'] = $todaysSubjectWiseAttendance;
		}

		$months = array(
		    1 => 'January',
		    'February',
		    'March',
		    'April',
		    'May',
		    'June',
		    'July ',
		    'August',
		    'September',
		    'October',
		    'November',
		    'December',
		);
		$monthArray = [];
		$schoolyear = $this->schoolyear_m->get_obj_schoolyear($schoolyearID);
 		if(!empty($schoolyear)) {
			$monthStart = abs($schoolyear->startingmonth);
			if($schoolyear->startingyear == $schoolyear->endingyear) {
				$monthLimit = (($schoolyear->endingmonth - $schoolyear->startingmonth) + 1);
			} else {
				$monthLimit = ($schoolyear->startingmonth + $schoolyear->endingmonth + 1);
			}

			$n = $monthStart;
			for($k = 1; $k <= $monthLimit; $k++) {
				$monthArray[$n] = $months[$n];
				$n++;
				if($n > 12) {
					$n = 1;
				}
			}
			$months = $monthArray;
			// print_r($months);
			// exit();
		}

 	if($campusID>0){
		$incomes  = $this->income_m->get_order_by_income(array('schoolyearID' => $schoolyearID,'campusID' => $campusID));
	}else{
		$incomes  = $this->income_m->get_order_by_income(array('schoolyearID' => $schoolyearID));
	}
		// echo "<pre>";
		// 	print_r($incomes);
		// 	echo "</pre>";
		// 	exit();
		// print_r($incomes);
		// exit();
		$payments  = $this->payment_m->get_order_by_payment(array('schoolyearID' => $schoolyearID, 'paymentamount' => NULL));
if($campusID>0){
		$expenses = $this->expense_m->get_order_by_expense(array('schoolyearID' => $schoolyearID,'campusID' => $campusID));
	}else{
		$expenses = $this->expense_m->get_order_by_expense(array('schoolyearID' => $schoolyearID));
	}
		$makepayments = $this->make_payment_m->get_order_by_make_payment(array('schoolyearID' => $schoolyearID));
		// $teachers = $this->sattendance_m->get_order_by_attendance(array('schoolyearID' => $schoolyearID, 'monthyear' => date('m-Y')));
		// $currentDate = strtotime(date('Y-m-d H:i:s'));
		// $previousSevenDate = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days')));

		// foreach ($teachers as $teacher) {
		// 	$date = date('j M',$teacher->a.);
		// 	print_r($date);
		// 	if(!isset($showTeacherAttendance[$date])) {
		// 		$showTeacherAttendance[$date] = 0;
		// 	}
		// 	$showTeacherAttendance[$date]++;
		// }
		// echo "<pre>";
		// print_r($teachers);
		// echo "</pre>";
		// exit();
			// print_r($incomes);
			// exit();

		$incomeMonthAndDay = [];
		$incomeMonthTotal  = [];
		if(!empty($incomes)) {
			foreach ($incomes as $incomeKey => $income) {
				if(!isset($incomeMonthAndDay[(int)$income->incomemonth][$income->incomeday])) {
					$incomeMonthAndDay[(int)$income->incomemonth][(string)$income->incomeday] = 0;
				}

				$incomeMonthAndDay[(int)$income->incomemonth][(string)$income->incomeday] += $income->amount;

				if(!isset($incomeMonthTotal[(int)$income->incomemonth])) {
					$incomeMonthTotal[(int)$income->incomemonth] = 0;
				}
				$incomeMonthTotal[(int)$income->incomemonth] += $income->amount;
			}
		}

		if(!empty($payments)) {
			foreach ($payments as $paymentKey => $payment) {
				if(!isset($incomeMonthAndDay[(int)$payment->paymentmonth][$payment->paymentday])) {
					$incomeMonthAndDay[(int)$payment->paymentmonth][(string)$payment->paymentday] = 0;
				}

				$incomeMonthAndDay[(int)$payment->paymentmonth][(string)$payment->paymentday] += $payment->paymentamount;

				if(!isset($incomeMonthTotal[(int)$payment->paymentmonth])) {
					$incomeMonthTotal[(int)$payment->paymentmonth] = 0;
				}
				$incomeMonthTotal[(int)$payment->paymentmonth] += $payment->paymentamount;
			}
		}

		$expenseMonthAndDay = [];
		$expenseMonthTotal  = [];
		if(!empty($expenses)) {
			foreach ($expenses as $expenseKey => $expense) {
				if(!isset($expenseMonthAndDay[(int)$expense->expensemonth][$expense->expenseday])) {
					$expenseMonthAndDay[(int)$expense->expensemonth][(string)$expense->expenseday] = 0;
				}

				$expenseMonthAndDay[(int)$expense->expensemonth][(string)$expense->expenseday] += $expense->amount;

				if(!isset($expenseMonthTotal[(int)$expense->expensemonth])) {
					$expenseMonthTotal[(int)$expense->expensemonth] = 0;
				}
				$expenseMonthTotal[(int)$expense->expensemonth] += $expense->amount;
			}
		}

		if(!empty($makepayments)) {
			foreach ($makepayments as $makepaymentKey => $makepayment) {
				$makepaymentDay = date('d',  strtotime($makepayment->create_date));
				$makepaymentMonth = date('m',  strtotime($makepayment->create_date));
				if(!isset($expenseMonthAndDay[(int)$makepaymentMonth][$makepaymentDay])) {
					$expenseMonthAndDay[(int)$makepaymentMonth][(string)$makepaymentDay] = 0;
				}

				$expenseMonthAndDay[(int)$makepaymentMonth][(string)$makepaymentDay] += $makepayment->payment_amount;

				if(!isset($expenseMonthTotal[(int)$makepaymentMonth])) {
					$expenseMonthTotal[(int)$makepaymentMonth] = 0;
				}
				$expenseMonthTotal[(int)$makepaymentMonth] += $makepayment->payment_amount;
			}
		}

		$this->data['months'] = $months;
		$this->data['incomeMonthAndDay'] = $incomeMonthAndDay;
		$this->data['incomeMonthTotal'] = $incomeMonthTotal;
		$this->data['expenseMonthAndDay'] = $expenseMonthAndDay;
		$this->data['expenseMonthTotal'] = $expenseMonthTotal;

		

			

			// $todaysAttendance1 = [];
			// foreach ($teacherAttendance as $key => $value) {
			// 	$todaysAttendance1[$key] = $value[(int)date('d')];

			// $this->data['todays1Attendance'] = $todays1Attendance;

		$currentDate = strtotime(date('Y-m-d H:i:s'));

		$previousSevenDate = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days')));

		$visitors = $this->loginlog_m->get_order_by_loginlog(array('login <= ' => $currentDate, 'login >= ' => $previousSevenDate));

		$showChartVisitor = [];
		foreach ($visitors as $visitor) {
			$date = date('j M',$visitor->login);
			 

			if(!isset($showChartVisitor[$date])) {
				$showChartVisitor[$date] = 0;
			}
			$showChartVisitor[$date]++;
		}
		// print_r($showChartVisitor[$date]++);
		// exit();


		$this->data['showChartVisitor'] = $showChartVisitor;
		$months = array(
		    1 => 'January',
		    'February',
		    'March',
		    'April',
		    'May',
		    'June',
		    'July ',
		    'August',
		    'September',
		    'October',
		    'November',
		    'December',
		);
		$monthArray = [];
		$schoolyear = $this->schoolyear_m->get_obj_schoolyear($schoolyearID);

 		if(!empty($schoolyear)) {
 			 
			$monthStart = abs($schoolyear->startingmonth);
			if($schoolyear->startingyear == $schoolyear->endingyear) {
				$monthLimit = (($schoolyear->endingmonth - $schoolyear->startingmonth) + 1);
			} else {

				$monthLimit = ($schoolyear->startingmonth + $schoolyear->endingmonth + 1);
			}

			$n = $monthStart;
			for($k = 1; $k <= $monthLimit; $k++) {
				$monthArray[$n] = $months[$n];
				$n++;
				if($n > 12) {
					$n = 1;
				}
			}
			$months = $monthArray;
		}

		$totalteachers = $this->teacher_m->general_get_order_by_teacher();
		 
		$teacherattendances = $this->tattendance_m->get_order_by_tattendance(array('schoolyearID' => $schoolyearID, 'monthyear' => date('m-Y')));
			 

		$teacherWiseAttendance = [];
			foreach ($teacherattendances as $attendance ) {
					$orderdate = explode('-', $attendance->monthyear);
					
					// After Error I have Added this Date;
					$date = date('j M');

					$day = 'a'.(int)$date[0];
					$monthyear = $date[1].'-'.$date[2];


				for($i=1;$i<=31;$i++) {

					if($i > date('d')) break;

					$date = 'a'.$i;

					if(!isset($teacherWiseAttendance[$attendance->monthyear][$i]['P'])) {
						$teacherWiseAttendance[$attendance->monthyear][$i]['P'] = 0;
					}

					if(!isset($teacherWiseAttendance[$attendance->monthyear][$i]['A'])) {
						$teacherWiseAttendance[$attendance->monthyear][$i]['A'] = 0;
					}

					if(!isset($teacherWiseAttendance[$attendance->monthyear][$i]['L'])) {
						$teacherWiseAttendance[$attendance->monthyear][$i]['L'] = 0;
					}

					if($attendance->$date == 'P') {
						$teacherWiseAttendance[$attendance->monthyear][$i]['P']++;
					}
					 else if($attendance->$date == 'A') {
						$teacherWiseAttendance[$attendance->monthyear][$i]['A']++;
					}
					else if($attendance->$date == 'L' || $attendance->$date == 'LE') {
						$teacherWiseAttendance[$attendance->monthyear][$i]['L']++;
					}
					

				}

			}
			 

			$todaysAttendances = [];
			foreach ($teacherWiseAttendance as $key => $value) { 
				$todaysAttendances[$key] = $value[(int)date('d')]; 
			} 

			if(isset($date))
			{
				$date = $date;
			}else{
				$date = '';
			}
			$this->data['date'] = $date;

			$this->data['totalteachers'] = $totalteachers;
			$this->data['teacherWiseAttendance'] = $teacherWiseAttendance;
			$this->data['todaysAttendances'] = $todaysAttendances;


			$student_current_class = $this->session->all_userdata();
    	$list = $this->conference_m->getByClassSection($student_current_class['classesID'], $student_current_class['sectionID']);

    	$this->data['conferences_student'] = $list;
		// echo "<pre>";
		// 	print_r($teachers);
		// 	echo "</pre>";
		// 	exit();
		// $id = str_replace("attendance", "", $key);
		// 					$updateArray[] = array(
		// 						'tattendanceID' => $id,
		// 						'a'.abs($day) => $singleAttendance
		// 					); 
		// $showTeacherAttendance = [];
		// $day = $this->input->post('day');
		// foreach ($teachers as $teacher) {
		// 	$orderdate = explode('-', $teacher->monthyear);
		// 	$month = $orderdate[0];
		// 	$year = $orderdate[1];
			// print_r($year);
			// exit();
			// $date1 = date('m-Y');
			//$data= array('a'.abs() => $teacher);
			// print_r($data);
			// exit();
			// $date = date($date1,$teacher->monthyear);
			// print_r($date);
			// exit();

		// 	if(!isset($showTeacherAttendance[$date])) {
		// 		$showTeacherAttendance[$date] = 0;
		// 	}
		// 	$showTeacherAttendance[$date]++;
		// }
		// $this->data['months'] = $months;
		// $this->data['showTeacherAttendance'] = $showTeacherAttendance;
	
		$userTypeID = $this->session->userdata('usertypeID');
		$loginUserID = $this->session->userdata('loginuserID');
		$this->data['usertype'] = $this->session->userdata('usertype');

		if($userTypeID == 1) {
			$this->data['user'] = $this->systemadmin_m->get_single_systemadmin(array('systemadminID' => $loginUserID));
		} elseif($userTypeID == 2) {
			$this->data['user'] = $this->teacher_m->get_single_teacher(array('teacherID' => $loginUserID));
		}  elseif($userTypeID == 3) {
			$this->data['user'] = $this->studentrelation_m->general_get_single_student(array('studentID' => $loginUserID));
		} elseif($userTypeID == 4) {
			$this->data['user'] = $this->parents_m->get_single_parents(array('parentsID' => $loginUserID));
		} else {
			$this->data['user'] = $this->user_m->get_single_user(array('userID' => $loginUserID));
		}
		if($campusID>0){
			$this->data['notices'] = $this->notice_m->get_order_by_notice(array('schoolyearID' => $schoolyearID,'campusID' => $campusID));
		}else{
			$this->data['notices'] = $this->notice_m->get_order_by_notice(array('schoolyearID' => $schoolyearID));
		}
		$this->data['holidays'] = $holidays;
		$this->data['events'] = $events;

		$this->data["subview"] = "dashboard/index";
		$this->load->view('_layout_main', $this->data);
	}

	public function getDayWiseAttendance()
	{
		$showChartData = [];
		if($this->input->post('dayWiseAttendance')) {
			$dayWiseAttendance = json_decode($this->input->post('dayWiseAttendance'), true);
			$type = $this->input->post('type');
			foreach ($dayWiseAttendance as $key => $value) {
				$showChartData[$key] = $value[$type];

			}
			// print_r($showChartData[$key]);
			// 	exit();

		}
		// print_r($showChartData[$key]);
		// exit();
		echo json_encode($showChartData);
	}

	public function dayWiseExpenseOrIncome()
	{
		$type = $this->input->post('type');
		$monthID = $this->input->post('monthID');
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$showChartData = [];
		if($type && $monthID) {
			$year = date('Y');

			$yearArray = [];
			$schoolyear = $this->schoolyear_m->get_obj_schoolyear($schoolyearID);
	 		if(!empty($schoolyear)) {
				$monthStart = abs($schoolyear->startingmonth);
				if($schoolyear->startingyear == $schoolyear->endingyear) {
					$monthLimit = (($schoolyear->endingmonth - $schoolyear->startingmonth) + 1);
				} else {
					$monthLimit = ($schoolyear->startingmonth + $schoolyear->endingmonth + 1);
				}

				$n = $monthStart;
				$endYearStatus = FALSE;
				for($k = 1; $k <= $monthLimit; $k++) {
					if($endYearStatus == FALSE) {
						$yearArray[$n] = $schoolyear->startingyear;
					}

					if($endYearStatus) {
						$yearArray[$n] = $schoolyear->endingyear;
					}

					$n++;
					if($n > 12) {
						$n = 1;
						$endYearStatus = TRUE;
					}
				}
				$year = (isset($yearArray[abs($monthID)]) ? $yearArray[abs($monthID)] : date('Y'));
			}

			$days = date('t', mktime(0, 0, 0, $monthID, 1, $year));
			$dayWiseData = json_decode($this->input->post('dayWiseData'), true);
			for ($i=1; $i <= $days; $i++) {
				if(!isset($dayWiseData[lzero($i)])) {
					$showChartData[$i] = 0;
				} else {
					$showChartData[$i] = isset($dayWiseData[lzero($i)]) ? $dayWiseData[lzero($i)] : 0;
				}
			}
		} else {
			for ($i=1; $i <= 31; $i++) {
				$showChartData[$i] = 0;
			}
		}

	    echo json_encode($showChartData);
	}

	public function getSubjectWiseAttendance() 
	{
		$subjectWiseAttendance = json_decode($this->input->post('subjectWiseAttendance'), true);
		$classID = $this->input->post('classID');
		$data['subjects'] = pluck($this->subject_m->get_order_by_subject(array('classesID' => $classID)), 'obj', 'subjectID');
		$present = [];
		$absent = [];
		foreach ($subjectWiseAttendance as $subjectID => $days) {
			foreach ($days as $key => $attendance) {
				if(!isset($present[$subjectID])) {
					$present[$subjectID] = 0;
				}

				if(!isset($absent[$subjectID])) {
					$absent[$subjectID] = 0;
				}

				$present[$subjectID] += $attendance['P'];
				$absent[$subjectID] += $attendance['A'];
			}
		}

		$data['present'] = $present;
		$data['absent'] = $absent;
		$data['subjectWiseAttendance'] = $subjectWiseAttendance;
		echo json_encode($data);
	}

	private function getAllRec($arrays) 
	{
		$returnArray = [];
		if(!empty($arrays)) {
			foreach ($arrays as $key => $array) {
				$returnArray[$array->nofmodule][$array->studentID][$array->month][$array->year] = 'Yes';
			}
		}
		return $returnArray;
	}

	private function checkUpdate()
	{
		$version = 'none';
		if($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1) {
			if(!empty($postDatas = @$this->postData())) {
				$versionChecking = $this->versionChecking($postDatas);
				/*if($versionChecking->status) {
					$version = $versionChecking->version;
				}*/
			}
		}

		return $version;
	}

	private function postData()
	{
		$postDatas = [];
		$this->load->model('update_m');
		$updates = $this->update_m->get_max_update();

		if(!empty($updates)) {
			$postDatas = array(
				'username' => count($this->data['siteinfos']) ? $this->data['siteinfos']->purchase_username : '', 
				'purchasekey' => count($this->data['siteinfos']) ? $this->data['siteinfos']->purchase_code : '',
				'domainname' => base_url(),
				'email' => count($this->data['siteinfos']) ? $this->data['siteinfos']->email : '',
				'currentversion' => $updates->version,
				'projectname' => 'school',
			);
		}
		return $postDatas; 
	}

	private function versionChecking($postDatas) 
	{
		$result = array(
			'status' => false,
			'message' => 'Error',
			'version' => 'none'
		);

		$postDataStrings = json_encode($postDatas);       
		$ch = curl_init($this->_versionCheckingUrl);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");       
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStrings);                       
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                           
		curl_setopt($ch, CURLOPT_HTTPHEADER, 
			array(
			    'Content-Type: application/json',
			    'Content-Length: ' . strlen($postDataStrings)
			)
		);
		
		$result = curl_exec($ch);
		curl_close($ch);
		if(!empty($result)) {
			$result = json_decode($result, true);
		}
		return (object) $result;
	}

	public function update()
	{
		if($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1){
			$this->session->set_userdata('updatestatus', true);
			redirect(base_url('update/autoupdate'));
		}
	}

	public function remind()
	{
		if($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1){
			$this->session->set_userdata('updatestatus', false);
			redirect(base_url('dashboard/index'));
		}
	}

	public function reset_data()
	{
		$this->db->truncate('activities');
		$this->db->truncate('activitiescategory');
		$this->db->truncate('activitiescomment');
		$this->db->truncate('activitiesmedia');
		$this->db->truncate('activitiesstudent');
		$this->db->truncate('asset');
		$this->db->truncate('asset_assignment');
		$this->db->truncate('asset_category');
		$this->db->truncate('assignment');
		$this->db->truncate('assignmentanswer');
		$this->db->truncate('attendance');
		$this->db->truncate('automation_rec');
		$this->db->truncate('automation_shudulu');
		$this->db->truncate('bankdetail');
		$this->db->truncate('book');
		$this->db->truncate('campus');
		$this->db->truncate('category');
		$this->db->truncate('certificate_template');
		$this->db->truncate('childcare');
		$this->db->truncate('classes');
		$this->db->truncate('complain');
		$this->db->truncate('conferences');
		$this->db->truncate('conversation_message_info');
		$this->db->truncate('conversation_msg');
		$this->db->truncate('conversation_user');
		$this->db->truncate('document');
		$this->db->truncate('eattendance');
		$this->db->truncate('ebooks');
		$this->db->truncate('emailsetting');
		$this->db->truncate('event');
		$this->db->truncate('eventcounter');
		$this->db->truncate('exam');
		$this->db->truncate('examschedule');
		$this->db->truncate('expense');
		$this->db->truncate('feetypes');
		$this->db->truncate('globalpayment');
		$this->db->truncate('grade');
		$this->db->truncate('hmember');
		$this->db->truncate('holiday');
		$this->db->truncate('hostel');
		$this->db->truncate('hourly_template');
		//$this->db->truncate('idmanager');
		$this->db->truncate('income');
		//$this->db->truncate('ini_config');
		//$this->db->truncate('instruction');
		$this->db->truncate('invoice');
		$this->db->truncate('issue');
		$this->db->truncate('leaveapplications');
		$this->db->truncate('leaveassign');
		$this->db->truncate('leavecategory');
		$this->db->truncate('lecture');
		$this->db->truncate('lmember');
		$this->db->truncate('location');
		$this->db->truncate('loginlog');
		$this->db->truncate('mailandsms');
		$this->db->truncate('mailandsmstemplate');
		//$this->db->truncate('mailandsmstemplatetag');
		$this->db->truncate('maininvoice');
		$this->db->truncate('make_payment');
		$this->db->truncate('manage_salary');
		$this->db->truncate('mark');
		$this->db->truncate('markpercentage');
		$this->db->truncate('markrelation');
		$this->db->truncate('media');
		$this->db->truncate('media_category');
		$this->db->truncate('media_gallery');
		$this->db->truncate('media_share');
		$this->db->truncate('notice');
		$this->db->truncate('onlineadmission');
		$this->db->truncate('online_exam');
		$this->db->truncate('online_exam_question');
		$this->db->truncate('online_exam_user_answer');
		$this->db->truncate('online_exam_user_answer_option');
		$this->db->truncate('online_exam_user_status');
		$this->db->truncate('parents');
		$this->db->truncate('payment');
		$this->db->truncate('product');
		$this->db->truncate('productcategory');
		$this->db->truncate('productcategorytype');
		$this->db->truncate('productpurchase');
		$this->db->truncate('productpurchaseitem');
		$this->db->truncate('productpurchasepaid');
		$this->db->truncate('productsale');
		$this->db->truncate('productsaleitem');
		$this->db->truncate('productsalepaid');
		$this->db->truncate('productsupplier');
		$this->db->truncate('productwarehouse');
		$this->db->truncate('promotionlog');
		$this->db->truncate('purchase');
		$this->db->truncate('question_answer');
		$this->db->truncate('question_bank');
		$this->db->truncate('question_group');
		$this->db->truncate('question_level');
		$this->db->truncate('question_option');
		//$this->db->truncate('question_type');
		$this->db->truncate('routine');
		$this->db->truncate('salary_option');
		$this->db->truncate('salary_template');
		$this->db->truncate('schoolyear');
		$this->db->truncate('school_sessions');
		$this->db->truncate('section');
		$this->db->truncate('slider');
		$this->db->truncate('sociallink');
		$this->db->truncate('staff');
		$this->db->truncate('student');
		$this->db->truncate('studentextend');
		$this->db->truncate('studentrelation');
		$this->db->truncate('subject');
		$this->db->truncate('subjectteacher');
		$this->db->truncate('sub_attendance');
		$this->db->truncate('syllabus');
		$this->db->truncate('systemadmin');
		$this->db->truncate('settings');
		$this->db->truncate('tattendance');
		$this->db->truncate('teacher');
		$this->db->truncate('tmember');
		$this->db->truncate('transport');
		$this->db->truncate('uattendance');
		$this->db->truncate('user');
		$this->db->truncate('vendor');
		$this->db->truncate('visitorinfo');
		$this->db->truncate('weaverandfine');
		$this->db->truncate('permission_relationships');
		//$this->db->truncate('posts');
		//$this->db->truncate('posts_categories');
		//$this->db->truncate('posts_category');

		/*
		INSERT INTO `campus` (`campusID`, `name`, `adminID`) VALUES ('1', 'Campus One', '1');
		
		INSERT INTO `systemadmin` (`systemadminID`, `campusID`, `accountCampusID`, `name`, `dob`, `sex`, `religion`, `email`, `phone`, `address`, `jod`, `photo`, `username`, `password`, `usertypeID`, `create_date`, `modify_date`, `create_userID`, `create_username`, `create_usertype`, `active`, `meeting_id`) VALUES
(1, 0, 1, 'Owner', '1990-01-01', 'Male', 'Muslim', 'info@asteims.com', NULL, 'Suite# 5, 3rd Floor, Omer Plaza, 34/35 Commercial Area,, Chaklala Scheme-III, Rawalpindi', '2019-07-04', NULL, 'admin', 'e019b7053c0a364a8d51018e9ba0f6e9d921f2303eb9ff5d077c03ae2cd8c5da6ba20c26acf57f6c2969293957f2189625f204bca4fa8531c073cacbabf571c0', 1, '2019-07-04 10:44:21', '2019-07-04 10:44:21', 0, 'azam', 'Admin', 1, 0);

		INSERT INTO `certificate_template` (`certificate_templateID`, `campusID`, `usertypeID`, `name`, `theme`, `top_heading_title`, `top_heading_left`, `top_heading_right`, `top_heading_middle`, `main_middle_text`, `template`, `footer_left_text`, `footer_right_text`, `footer_middle_text`, `background_image`) VALUES ('1', '1', '3', 'Certificate of Appreciation', '1', 'ASTEIMS', NULL, NULL, NULL, 'AST', 'Temp1', NULL, NULL, NULL, '20396a86485b7702f8e30a3b2712790e54d8c1ca501252869f0117d0ceff2bbedc16065d21cd0d3f6c7ef0eaa0d88e6d56794ad5760497e771a069de15857c16.jpg');

		INSERT INTO `schoolyear` (`schoolyearID`, `schooltype`, `schoolyear`, `schoolyeartitle`, `startingdate`, `endingdate`, `semestercode`, `create_date`, `modify_date`, `create_userID`, `create_username`, `create_usertype`, `adminID`) VALUES ('1', 'classbase', '2023', 'AY 2023-24', '2023-01-15', '2024-01-15', NULL, '2023-01-08 10:47:19', '2023-01-08 10:47:19', '1', 'admin', 'Admin','1');
		
		INSERT INTO `settings` (`setting_id`, `campusID`, `adminID`, `absent_auto_sms`, `address`, `attendance`, `attendance_notification`, `attendance_notification_template`, `attendance_smsgateway`, `automation`, `auto_invoice_generate`, `auto_update_notification`, `backend_theme`, `captcha_status`, `currency_code`, `currency_symbol`, `email`, `ex_class`, `footer`, `frontendorbackend`, `frontend_theme`, `google_analytics`, `language`, `language_status`, `note`, `phone`, `photo`, `profile_edit`, `purchase_code`, `purchase_username`, `recaptcha_secret_key`, `recaptcha_site_key`, `school_type`, `school_year`, `sname`, `student_ID_format`, `s_registration_no_auto`, `time_zone`, `updateversion`, `weekends`, `zoom_api_key`, `zoom_api_secret`, `mark`) VALUES
(1, 1, 1, 1, 'Punjab Pakistan', 'day', 'none', 0, '0', 5, 0, 0, 'whiteblue', 1, 'PKR', 'Rs.', 'info@asteims.com', 0, 'Copyright &copy; Azam Systems & Technologies (Pvt.)Ltd | 2020', 'YES', 'default', '', 'english', 0, 1, '+92 51 5766144', 'f87e94cc5ec3b5f23eb6cadf1e0a6e609289e3bb915ffa3d950427d8641e180aa21558beabd09447ec748781b7ca297c3dfd245e882add2e554bd9e3b950b475.png', 1, 'e427758a-e3c9-41ca-b31c-ff68ad3309e4', 'jawadkhan511', '6LcyDAkdAAAAAOH7eX68f5cImKGbtgMvtOCSDKqc', '6LcyDAkdAAAAAJsN1RWwXnGyNcRNpEzVL7xRuQfe', 'classbase', 1, 'ASTEIMS', 1, 0, 'Asia/Karachi', 4.2, 6, 'Bws78mVkQd2j4rkK8jbvHg', 'dFcpLMiFxo5sbH2efSQUuwPSJj9CmtIqMeII', 'a:0:{}');

		UPDATE `setting` SET `value` = '0' WHERE `setting`.`fieldoption` = 's_registration_no_auto';
		*/

		/*
		$this->db->truncate('fmenu');
		$this->db->truncate('fmenu_relation');
		$this->db->truncate('frontend_setting');
		$this->db->truncate('frontend_template');
		$this->db->truncate('menu');
		$this->db->truncate('migrations');
		$this->db->truncate('online_exam_type');
		$this->db->truncate('pages');
		$this->db->truncate('permissions');
		$this->db->truncate('reset');
		$this->db->truncate('setting');
		$this->db->truncate('smssettings');
		$this->db->truncate('studentgroup');
		$this->db->truncate('themes');
		$this->db->truncate('update');
		$this->db->truncate('usertype');
		*/
		$this->load->model('permission_m');
		$this->permission_m->reset_insertion();
		$this->permission_m->permission_relation_insertion(1);
		redirect(base_url('dashboard/index'));
	}
}