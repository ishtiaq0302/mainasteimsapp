<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');



class Universalfunction extends Admin_Controller {

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
		$language = $this->session->userdata('lang');
		$this->lang->load('section', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allclass = $this->classes_m->get_order_by_classes(array('campusID' => $campusID));

			echo "<option value='0'>", $this->lang->line("section_select_class"),"</option>";

			foreach ($allclass as $value) {

				echo "<option value=\"$value->classesID\">",$value->classes,"</option>";

			}

		}

	}

	public function examcall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('examschedule', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allexam = $this->exam_m->get_order_by_exam(array('campusID' => $campusID));

			echo "<option value='0'>", $this->lang->line("examschedule_select_exam"),"</option>";

			foreach ($allexam as $value) {

				echo "<option value=\"$value->examID\">",$value->exam,"</option>";

			}

		}

	}

	public function hostelcall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('category', $language);
	
		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allhostel = $this->hostel_m->get_order_by_hostel(array('campusID' => $campusID));

			echo "<option value='0'>", $this->lang->line("category_select_hostel"),"</option>";

			foreach ($allhostel as $value) {

				echo "<option value=\"$value->hostelID\">",$value->name,"</option>";

			}

		}

	}	

	public function assetlocationcall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('asset', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$alllocation = $this->location_m->get_order_by_location(array('campusID' => $campusID));

			echo "<option value='0'>", $this->lang->line("asset_select_location"),"</option>";

			foreach ($alllocation as $value) {

				echo "<option value=\"$value->locationID\">",$value->location,"</option>";

			}

		}

	}	

	public function assetcall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('purchase', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allasset = $this->asset_m->get_order_by_asset(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("purchase_select_asset"),"</option>";

			foreach ($allasset as $value) {

				echo "<option value=\"$value->assetID\">",$value->description,"</option>";

			}

		}

	}

	public function teachercall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('classes', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allteacher = $this->teacher_m->get_order_by_teacher(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("classes_select_teacher"),"</option>";

			foreach ($allteacher as $value) {

				echo "<option value=\"$value->teacherID\">",$value->name,"</option>";

			}

		}

	}	

	public function vendorcall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('purchase', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allvendor = $this->vendor_m->get_order_by_vendor(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("purchase_select_vendor"),"</option>";

			foreach ($allvendor as $value) {

				echo "<option value=\"$value->vendorID\">",$value->name,"</option>";

			}

		}

	}

	public function feetypecall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('invoice', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allfeetype = $this->feetypes_m->get_order_by_feetypes(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("invoice_select_feetype"),"</option>";

			foreach ($allfeetype as $value) {

				echo "<option value=\"$value->feetypesID\">",$value->feetypes,"</option>";

			}

		}

	}

	public function usercall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('purchase', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$alluser = $this->user_m->get_order_by_user(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("purchase_select_purchased_by"),"</option>";

			foreach ($alluser as $value) {

				echo "<option value=\"$value->userID\">",$value->name,"</option>";

			}

		}

	}

	public function productcategorycall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('product', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allproductcategory = $this->productcategory_m->get_order_by_productcategory(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("product_select_category"),"</option>";

			foreach ($allproductcategory as $value) {

				echo "<option value=\"$value->productcategoryID\">",$value->productcategoryname,"</option>";

			}

		}

	}

	public function suppliercall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('productpurchase', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allproductsupplier = $this->productsupplier_m->get_order_by_productsupplier(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("productpurchase_select_supplier"),"</option>";

			foreach ($allproductsupplier as $value) {

				echo "<option value=\"$value->productsupplierID\">",$value->productsuppliercompanyname,"</option>";

			}

		}

	}

	public function warehousecall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('productpurchase', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allproductwarehouse = $this->productwarehouse_m->get_order_by_productwarehouse(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("productpurchase_select_warehouse"),"</option>";

			foreach ($allproductwarehouse as $value) {

				echo "<option value=\"$value->productwarehouseID\">",$value->productwarehousename,"</option>";

			}

		}

	}

	public function bookcall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('issue', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allbook = $this->book_m->get_order_by_book(array('campusID' => $campusID));
			echo "<option value='0'>", $this->lang->line("issue_select_book"),"</option>";

			foreach ($allbook as $value) {

				echo "<option value=\"$value->bookID\">",$value->book,"</option>";

			}

		}

	}	

	public function studentgroupcall() {
		$language = $this->session->userdata('lang');
		$this->lang->load('student', $language);

		$campusID = $this->input->post('id');
		
		if((int)$campusID) {

			$allstudentgroup = $this->studentgroup_m->get_order_by_studentgroup(array('campusID' => $campusID));

			echo "<option value='0'>", $this->lang->line("student_select_studentgroup"),"</option>";

			foreach ($allstudentgroup as $value) {

				echo "<option value=\"$value->studentgroupID\">",$value->group,"</option>";

			}

		}

	}	            





}