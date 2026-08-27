<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Omnipay\Omnipay;
class Autoinvoicer extends MY_Controller {
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
	protected $_amountgivenstatus = '';
	protected $_amountgivenstatuserror = [];
	protected $_relation_array = [];
	protected $_user_role_array = [2, 3, 4];
	protected $_extend_array = [
		'studentextendID',
		'studentgroupID',
		'optionalsubjectID',
		'extracurricularactivities',
		'remarks'
	];

	function __construct() {
		parent::__construct();
		$this->load->model("invoice_m");
		$this->load->model("feetypes_m");
		$this->load->model('payment_m');
		$this->load->model("classes_m");
		$this->load->model("student_m");
		$this->load->model("parents_m");
		$this->load->model("section_m");
		$this->load->model('user_m');
		$this->load->model('weaverandfine_m');
		$this->load->model("payment_settings_m");
		$this->load->model("globalpayment_m");
		$this->load->model("maininvoice_m");
		//$this->load->model("studentrelation_m");
		require_once(APPPATH."libraries/Omnipay/vendor/autoload.php");
	}

	public function Index(){
		$students = $this->student_m->get_select_student('*');
		
		foreach($students as $stu){
			
			
			$tution_fee = $stu->monthly_tuttion_fee;
			
			if(empty($stu->monthly_tuttion_fee) || $stu->monthly_tuttion_fee == null ){
				$tution_fee = 0;
			}
			
			$feeTypeArray = array();
			
			$feeTypeArray[] = array(
				'feetypeID' 	=> '17',
				'amount'    	=> '',
				'discount'		=> '',
				'subtotal'		=> '',
				'paidamount'	=> '',
			);

			$feeTypeArray[] = array(
				'feetypeID' 	=> '18',
				'amount'    	=> '',
				'discount'		=> '',
				'subtotal'		=> '',
				'paidamount'	=> '',
			);	
		
			$feeTypeArray[] = array(
				'feetypeID' 	=> '19',
				'amount'    	=> $tution_fee,
				'discount'		=> '',
				'subtotal'		=> $tution_fee,
				'paidamount'	=> '',
			);

			$feeTypeArray[] = array(
				'feetypeID' 	=> '20',
				'amount'    	=> '',
				'discount'		=> '',
				'subtotal'		=> '',
				'paidamount'	=> '',
			);

			$feeTypeArray[] = array(
				'feetypeID' 	=> '23',
				'amount'    	=> '',
				'discount'		=> '',
				'subtotal'		=> '',
				'paidamount'	=> '',
			);

			$feeTypeArray[] = array(
				'feetypeID' 	=> '24',
				'amount'    	=> '',
				'discount'		=> '',
				'subtotal'		=> '',
				'paidamount'	=> '',
			);
			
			$post_data = array(
				'classesID' => $stu->classesID,
				'studentID' => $stu->studentID,
				'date' => date('25-m-Y'),
				'statusID' => '0',
				'paymentmethodID' => '0',
				'feetypeitems' => json_encode($feeTypeArray),
				'totalsubtotal' => $tution_fee,
				'totalpaidamount' => '0',
				'editID' => '0',
				'schoolyearID'	 =>	$stu->schoolyearID,
				'loginuserID'	 =>	'1',
				'usertypeID'	 =>	'1',
				'uname'	 =>	'superadmin',
			);
			
			$_POST = $post_data;
			$this->saveinvoice();
		}
	}

	public function saveinvoice() {
		$maininvoiceID = 0;
        $retArray['status'] = FALSE;
	                
	                
	                	$invoiceMainArray = [];
	                	$globalPaymentArray = [];
	                	$invoiceArray = [];
	                	$paymentArray = [];
	                	$paymentHistoryArray = [];
	                	$studentArray = [];
	                	$globalPaymentIDArray = [];
	                	$feetype = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');

	                	$feetypeitems = json_decode($this->input->post('feetypeitems'));
	                	$schoolyearID = $this->input->post('schoolyearID');

	                	$studentID = $this->input->post('studentID');
	                	$classesID = $this->input->post('classesID');
						
						
	                	if(((int)$studentID || $studentID == 0) && (int)($classesID)) {
	                		if($studentID == 0) {
	                			$getstudents = $this->get_order_by_student(array("srclassesID" => $classesID, 'srschoolyearID' => $schoolyearID));
	                		} else {
	                			$getstudents = $this->get_order_by_student(array("srclassesID" => $classesID, 'srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID));
	                		}

	                		if(!empty($getstudents)) {
	                			$paymentStatus = 0;
			                	if($this->input->post('statusID') !== '0') {
			                		if((float) $this->input->post('totalsubtotal') == (float)0) {
			                			$paymentStatus = 2;
			                		} else {
				                		if((float) $this->input->post('totalpaidamount') > (float)0) {
				                			if((float) $this->input->post('totalsubtotal') == (float) $this->input->post('totalpaidamount')) {
				                				$paymentStatus = 2;
				                			} else {
				                				$paymentStatus = 1;
				                			}
				                		}
			                		}
			                	}

			                	$clearancetype = 'unpaid';
			                	if($paymentStatus == 0) {
			                		$clearancetype = 'unpaid';
			                	} elseif($paymentStatus == 1) {
			                		$clearancetype = 'partial';
			                	} elseif($paymentStatus == 2) {
			                		$clearancetype = 'paid';
			                	}

	                			foreach ($getstudents as $key => $getstudent) {
		                     		$invoiceMainArray[] = array(
		                         		'maininvoiceschoolyearID' => $schoolyearID,
		                         		'maininvoiceclassesID' => $this->input->post('classesID'),
		                         		'maininvoicestudentID' => $getstudent->srstudentID,
		                         		'maininvoicestatus' => (($this->input->post('statusID') !== '0') ? (((float)$this->input->post('totalsubtotal') == (float)0) ? 2 :  (((float)$this->input->post('totalpaidamount') > (float)0) ? ((float) $this->input->post('totalsubtotal') == (float) $this->input->post('totalpaidamount') ? 2 : 1) : 0)) : 0),
		                         		'maininvoiceuserID' => $this->input->post('loginuserID'),
		                         		'maininvoiceusertypeID' => $this->input->post('usertypeID'),
		                         		'maininvoiceuname' => $this->input->post('uname'),
		                         		'maininvoicedate' => date("Y-m-d", strtotime($this->input->post("date"))),
		                         		'maininvoicecreate_date' => date('Y-m-d'),
		                         		'maininvoiceday' => date('d'),
		                         		'maininvoicemonth' => date('m'),
		                         		'maininvoiceyear' => date('Y'),
		                         		'maininvoicedeleted_at' => 1
		                     		);

		                     		$globalPaymentArray[] = array(
		                     			'classesID' => $getstudent->srclassesID,
		                     			'sectionID' => $getstudent->srsectionID,
		                     			'studentID' => $getstudent->srstudentID,
		                     			'clearancetype' => $clearancetype,
		                     			'invoicename' => $getstudent->srregisterNO.'-'.$getstudent->srname,
		                     			'invoicedescription' => '',
		                     			'paymentyear' => date('Y'),
		                     			'schoolyearID' => $schoolyearID,
		                     		);

		                     		$studentArray[] = $getstudent->srstudentID;
		                     	}

		                     	if(!empty($invoiceMainArray)) {
		                     		if(!empty($invoiceMainArray))
		                     		{
		                     			$count = count($invoiceMainArray);
		                     		}else{
		                     			$count = 0;		                     			
		                     		}
				                    $firstID = $this->maininvoice_m->insert_batch_maininvoice($invoiceMainArray);

				                    $lastID = $firstID + ($count-1);

				                    if($lastID >= $firstID) {
				                    	$j = 0;
				                    	for ($i = $firstID; $i <= $lastID ; $i++) {
				                    		if(!empty($feetypeitems)) {
					                    		foreach ($feetypeitems as $feetypeitem) {
													$invoiceArray[] = array(
														'schoolyearID' => $invoiceMainArray[$j]['maininvoiceschoolyearID'],
						                         		'classesID' => $invoiceMainArray[$j]['maininvoiceclassesID'],
						                         		'studentID' => $invoiceMainArray[$j]['maininvoicestudentID'],
						                         		'feetypeID' => isset($feetypeitem->feetypeID) ? $feetypeitem->feetypeID : 0,
						                         		'feetype' => isset($feetype[$feetypeitem->feetypeID]) ? $feetype[$feetypeitem->feetypeID] : '',
		                     							'amount' => isset($feetypeitem->amount) ? $feetypeitem->amount : 0,
		                     							'discount' => (isset($feetypeitem->discount) ? (($feetypeitem->discount == '') ? 0 : $feetypeitem->discount) : 0),
		                     							'paidstatus' => ($this->input->post('statusID') !== '0') ? (((float)$feetypeitem->paidamount > (float)0) ? (((float) $feetypeitem->subtotal == (float) $feetypeitem->paidamount) ? 2 : 1 ) : 0) : 0,
						                         		'userID' => $invoiceMainArray[$j]['maininvoiceuserID'],
						                         		'usertypeID' => $invoiceMainArray[$j]['maininvoiceusertypeID'],
						                         		'uname' => $invoiceMainArray[$j]['maininvoiceuname'],
						                         		'date' => $invoiceMainArray[$j]['maininvoicedate'],
						                         		'create_date' => $invoiceMainArray[$j]['maininvoicecreate_date'],
						                         		'day' => $invoiceMainArray[$j]['maininvoiceday'],
						                         		'month' => $invoiceMainArray[$j]['maininvoicemonth'],
						                         		'year' => $invoiceMainArray[$j]['maininvoiceyear'],
						                         		'deleted_at' => $invoiceMainArray[$j]['maininvoicedeleted_at'],
						                         		'maininvoiceID' => $i
													);

													$paymentHistoryArray[] = array(
														'paymenttype' => ucfirst($this->input->post('paymentmethodID')),
														'paymentamount' =>  $feetypeitem->paidamount
													);
												}
				                    		}
				                    		$j++;
				                    	}
				                    }
		                     	}

		                     	$paymentInserStatus = 0;
		                     	if($this->input->post('statusID') ==! '0') {
		                     		if($this->input->post('totalpaidamount') > 0) {
		                     			if((float) $this->input->post('totalsubtotal') == (float) $this->input->post('totalpaidamount')) {
		                     				$paymentInserStatus = 2;
		                     			} else {
		                     				$paymentInserStatus = 1;
		                     			}
		                     		} else {
		                     			$paymentInserStatus = 0;
		                     		}
		                     	}

		                     	$invoicefirstID = $this->invoice_m->insert_batch_invoice($invoiceArray);

		                     	$invoiceSubtotalStatus = 1;
		                     	if((float) $this->input->post('totalsubtotal') == (float)0) {
		                     		$invoiceSubtotalStatus = 0;
		                     	}

		                     	if($paymentInserStatus && $invoiceSubtotalStatus) {
		                     		if(!empty($invoiceArray)) {
		                     			$invoicecount = count($invoiceArray);
					                    $invoicefirstID = $invoicefirstID;
					                    $invoicelastID = $invoicefirstID + ($invoicecount-1);

					                    $globalcount = count($globalPaymentArray);
					                    $globalfirstID = $this->globalpayment_m->insert_batch_globalpayment($globalPaymentArray);
					                    $globallastID = $globalfirstID + ($globalcount-1);

					                    if(!empty($studentArray)) {
					                    	if(!empty($getstudents))
					                    	{
					                    		$studentcount = count($getstudents);
					                    	}else{
					                    		$studentcount = 0;					                    		
					                    	}
					                    	for($n = 0; $n <= ($studentcount -1); $n++) {
					                    		$globalPaymentIDArray[$studentArray[$n]] = $globalfirstID;
					                    		$globalfirstID++;
					                    	}
					                    }

					                    if($invoicelastID >= $invoicefirstID) {
					                    	$k = 0;
				                    		for ($i = $invoicefirstID; $i <= $invoicelastID ; $i++) {
			                    				$paymentArray[] = array(
													'schoolyearID' 		=> $invoiceArray[$k]['schoolyearID'],
													'invoiceID' 		=> $i,
					                         		'studentID' 		=> $invoiceArray[$k]['studentID'],
					                         		'paymentamount' 	=> isset($paymentHistoryArray[$k]['paymentamount']) ? (($paymentHistoryArray[$k]['paymentamount'] == "") ? NULL : $paymentHistoryArray[$k]['paymentamount'] ) : 0,
													'paymenttype' 		=> ucfirst($this->input->post('paymentmethodID')),
													'paymentdate' 		=> date('Y-m-d'),
													'paymentday' 		=> date('d'),
													'paymentmonth' 		=> date('m'),
													'paymentyear' 		=> date('Y'),
													'userID' 			=> $invoiceArray[$k]['userID'],
													'usertypeID' 		=> $invoiceArray[$k]['usertypeID'],
					                         		'uname' 			=> $invoiceArray[$k]['uname'],
													'transactionID' 	=> 'CASHANDCHEQUE'.random19(),
													'globalpaymentID' 	=> isset($globalPaymentIDArray[$invoiceArray[$k]['studentID']]) ? $globalPaymentIDArray[$invoiceArray[$k]['studentID']] : 0,
				                         		);
				                        		$k++;
				                    		}
					                    }

					                    if(!empty($paymentArray)) {
					                    	$this->payment_m->insert_batch_payment($paymentArray);
					                    }
		                     		}
		                     	}

			                    
	                		} else {
	                			
	                		}
	                	} else {
	                		
	                	}
	        
        
	}

	
	public function get_order_by_student($arrays = [], $studentExtend = FALSE) {
		$this->_relation_array = $this->userRelation();
		if(!count($this->_relation_array) && in_array($this->input->post('usertypeID'), $this->_user_role_array)) {
			return [];
		}

		$arrays = $this->prefixLoad($arrays);
        $this->db->select('*');
        $this->db->from('studentrelation');
        $this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');

        if($studentExtend) {
        	$this->db->join('studentextend', 'studentextend.studentID = studentrelation.srstudentID', 'LEFT');
        }

        if($this->input->post('usertypeID') == 2) {
        	$this->db->where_in('studentrelation.srclassesID', $this->_relation_array);
        } elseif($this->input->post('usertypeID') == 3) {
        	$this->db->where_in('studentrelation.srclassesID', $this->_relation_array);
        } elseif($this->input->post('usertypeID') == 4) {
        	$this->db->where_in('studentrelation.srstudentID', $this->_relation_array);
        } 

        if(!empty($arrays)) {
            $this->db->where($arrays);
        }
        $this->db->where('student.studentID !=', NULL);
        $this->db->order_by('srroll asc');
        $query = $this->db->get();
        return $query->result();
    }

	private function userRelation() {
		$usertypeID = $this->input->post('usertypeID');
		$userID = $this->input->post('loginuserID');
		if($usertypeID == 2) {
			$this->db->from('classes')->where(array('teacherID' => $userID))->order_by('classesID');
			$classQuery = $this->db->get();
			$classResult = $classQuery->result();

			$this->db->from('routine')->where(array('teacherID' => $userID))->order_by('classesID');
			$routineQuery = $this->db->get();
			$routineResult = $routineQuery->result();

			$classMerged = (object) array_merge((array) $classResult , (array) $routineResult);

			$classes = array_unique(pluck($classMerged, 'classesID', 'classesID'));
			ksort($classes);
			return $classes;
		} elseif($usertypeID == 3) {
			$schoolyearID = $this->input->post('schoolyearID');
			$this->db->from('studentrelation');
        	$this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');
       		$this->db->where('studentrelation.srschoolyearID =', $schoolyearID);
       		$this->db->where('student.studentID !=', NULL);
       		$this->db->where('studentrelation.srstudentID', $userID);
       		$this->db->order_by('srroll asc');
			$studentrelationQuery = $this->db->get();
			$studentrelationResult = $studentrelationQuery->result();

			$classesArray = pluck($studentrelationResult, 'srclassesID', 'srclassesID');
			return $classesArray;
		} elseif($usertypeID == 4) {
			$schoolyearID = $this->input->post('schoolyearID');
			$this->db->from('studentrelation');
        	$this->db->join('student', 'student.studentID = studentrelation.srstudentID', 'LEFT');
       		$this->db->where('studentrelation.srschoolyearID =', $schoolyearID);
       		$this->db->where('student.parentID =', $userID);
       		$this->db->where('student.studentID !=', NULL);
       		$this->db->order_by('srroll asc');
			$studentrelationQuery = $this->db->get();
			$studentrelationResult = $studentrelationQuery->result();

			$studentArray = array_unique(pluck($studentrelationResult, 'srstudentID', 'srstudentID'));
			ksort($studentArray);
			return $studentArray;
		}
	}

	private function prefixLoad($array) {
		if(is_array($array)) {
			if(!empty($array)) {
				foreach ($array as $arkey =>  $ar) {
					if(in_array($arkey, $this->_extend_array)) {
						unset($array[$arkey]);
						$array['studentextend.'.$arkey] = $ar;
					} elseif(substr($arkey, 0, 2) == 'sr') {
						unset($array[$arkey]);
						$array['studentrelation.'.$arkey] = $ar;
					} else {
						unset($array[$arkey]);
						$array['student.'.$arkey] = $ar;
					}
				}
			}
		}

		return $array;
	}
}
