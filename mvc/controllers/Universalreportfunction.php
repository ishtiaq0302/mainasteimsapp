<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Universalreportfunction extends Admin_Controller {

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
/*
	  $ch = curl_init();
  
    curl_setopt($ch, CURLOPT_URL, 'https://www.completeapi.com/v1/69b5a398831b833b4b154c9e3548115e7ec77104720fba3fb84acbe46abb1892/sms/+264814068582?message=[Hello]');
    curl_setopt($ch, CURLOPT_HEADER, 0);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

   echo  $authToken = curl_exec($ch);
exit;
*/
		$this->load->model("student_m");

		$this->load->model("staff_m");

		$this->load->model("parents_m");

		$this->load->model("campus_m");

		$this->load->model("section_m");

		$this->load->model("classes_m");

		$this->load->model("setting_m");

		$this->load->model('studentrelation_m');


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
	    $this->load->model('asset_m');

	    $this->load->model('systemadmin_m');

	    $this->load->model('conference_m');
	    $this->load->model('vendor_m');
	    $this->load->model('productcategory_m');
	    $this->load->model('productsupplier_m');
	    $this->load->model('productwarehouse_m');
	    $this->load->model('book_m');
	    $this->load->model('classes_m');
	    $this->load->model('hostel_m');
	    $this->load->model('feetypes_m');
	    $this->load->model('teacher_m');
	    $this->load->model('exam_m');
	    $this->load->model('studentgroup_m');





	    $this->load->model('location_m');

		$language = $this->session->userdata('lang');

		$this->lang->load('student', $language);

	}
 
	public function classcall() {

		$campusID = $this->input->post('id');
		if((int)$campusID) {
			$allclass = $this->classes_m->get_order_by_classes(array('campusID' => $campusID));
		}else{
			$allclass = $this->classes_m->get_order_by_classes();
		}

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allclass as $value) {

				echo "<option value=\"$value->classesID\">",$value->classes,"</option>";

			} 
	}

	public function productwarehousecall() {

		$campusID = $this->input->post('id');
		$this->load->model('productwarehouse_m');
		if((int)$campusID) {
			$allproductwarehouse = $this->productwarehouse_m->get_order_by_productwarehouse(array('campusID' => $campusID));
		}else{
			$allproductwarehouse = $this->productwarehouse_m->get_order_by_productwarehouse();
		}

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allproductwarehouse as $value) {

				echo "<option value=\"$value->productwarehouseID\">",$value->productwarehousename,"</option>";

			} 
	}

	public function productsuppliercall() {

		$campusID = $this->input->post('id');
		$this->load->model('productsupplier_m');
		if((int)$campusID) {
			$allproductsupplier = $this->productsupplier_m->get_order_by_productsupplier(array('campusID' => $campusID));
		}else{
			$allproductsupplier = $this->productsupplier_m->get_order_by_productsupplier();
		}

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allproductsupplier as $value) {

				echo "<option value=\"$value->productsupplierID\">",$value->productsuppliercompanyname,"</option>";

			} 
	}

	public function certificattemplatecall() {

		$campusID = $this->input->post('id');
			$this->load->model('certificate_template_m');
		if((int)$campusID) {
			$allcertificatetemp=$this->certificate_template_m->get_order_by_certificate_template(array('campusID' => $campusID));
		}else{
			$allcertificatetemp=$this->certificate_template_m->get_order_by_certificate_template();
		}

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allcertificatetemp as $value) {

				echo "<option value=\"$value->certificate_templateID\">",$value->name,"</option>";

			}


	}

	public function examcall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {
			$allexam = $this->exam_m->get_order_by_exam(array('campusID' => $campusID));
		}else{
			$allexam = $this->exam_m->get_order_by_exam();
		}


			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allexam as $value) {

				echo "<option value=\"$value->examID\">",$value->exam,"</option>";

			}

	}

	public function onlineexamcall() {

		$campusID = $this->input->post('id');
		$this->load->model('online_exam_m');
		if((int)$campusID) {
			$allexam=$this->online_exam_m->get_order_by_online_exam(array('campusID' => $campusID));
		}else{
			$allexam=$this->online_exam_m->get_order_by_online_exam();
		}


			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allexam as $value) {

				echo "<option value=\"$value->onlineExamID\">",$value->name,"</option>";

			}

	}

	public function hostelcall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allhostel = $this->hostel_m->get_order_by_hostel(array('campusID' => $campusID));

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allhostel as $value) {

				echo "<option value=\"$value->hostelID\">",$value->name,"</option>";

			}

		}

	}	

	public function assetlocationcall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$alllocation = $this->location_m->get_order_by_location(array('campusID' => $campusID));

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($alllocation as $value) {

				echo "<option value=\"$value->locationID\">",$value->location,"</option>";

			}

		}

	}	

	public function assetcall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allasset = $this->asset_m->get_order_by_asset(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allasset as $value) {

				echo "<option value=\"$value->assetID\">",$value->description,"</option>";

			}

		}

	}

	public function teachercall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {
			$allteacher = $this->teacher_m->get_order_by_teacher(array('campusID' => $campusID));
		}else{
			$allteacher = $this->teacher_m->get_order_by_teacher();
		}

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allteacher as $value) {

				echo "<option value=\"$value->teacherID\">",$value->name,"</option>";

			}

	}	

	public function vendorcall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allvendor = $this->vendor_m->get_order_by_vendor(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allvendor as $value) {

				echo "<option value=\"$value->vendorID\">",$value->name,"</option>";

			}

		}

	}

	public function feetypecall() {

		$campusID = $this->input->post('id');
		
		$campusID = $this->input->post('id');
		if((int)$campusID) {
			$allfeetype = $this->feetypes_m->get_order_by_feetypes(array('campusID' => $campusID));
		}else{
			$allfeetype = $this->feetypes_m->get_order_by_feetypes();
		}

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allfeetype as $value) {

				echo "<option value=\"$value->feetypesID\">",$value->feetypes,"</option>";

			} 

	}

	public function usercall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$alluser = $this->user_m->get_order_by_user(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($alluser as $value) {

				echo "<option value=\"$value->userID\">",$value->name,"</option>";

			}

		}

	}

	public function productcategorycall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allproductcategory = $this->productcategory_m->get_order_by_productcategory(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allproductcategory as $value) {

				echo "<option value=\"$value->productcategoryID\">",$value->productcategoryname,"</option>";

			}

		}

	}

	public function suppliercall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allproductsupplier = $this->productsupplier_m->get_order_by_productsupplier(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allproductsupplier as $value) {

				echo "<option value=\"$value->productsupplierID\">",$value->productsuppliercompanyname,"</option>";

			}

		}

	}

	public function warehousecall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allproductwarehouse = $this->productwarehouse_m->get_order_by_productwarehouse(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allproductwarehouse as $value) {

				echo "<option value=\"$value->productwarehouseID\">",$value->productwarehousename,"</option>";

			}

		}

	}

	public function bookcall() {

		$campusID = $this->input->post('id');
		if((int)$campusID) {
			$allbook = $this->book_m->get_order_by_book(array('campusID' => $campusID));
		}else{
			$allbook = $this->book_m->get_order_by_book();
		}

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allbook as $value) {

				echo "<option value=\"$value->bookID\">",$value->book,"</option>";

			}
	}	

	public function studentgroupcall() {

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allstudentgroup = $this->studentgroup_m->get_order_by_studentgroup(array('campusID' => $campusID));

			echo "<option value='0'>", $this->lang->line("student_select_section"),"</option>";

			foreach ($allstudentgroup as $value) {

				echo "<option value=\"$value->studentgroupID\">",$value->group,"</option>";

			}

		}

	}	            





}