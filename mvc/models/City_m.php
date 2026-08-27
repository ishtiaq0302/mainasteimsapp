<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class City_m extends MY_Model {

	protected $_table_name = 'city';
	protected $_primary_key = 'cityid';
	protected $_primary_filter = 'intval';
	protected $_order_by = "cityname asc";

	function __construct() {
		parent::__construct();
	}

	public function get_city($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_single_city($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	public function get_order_by_city($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_city($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function general_get_order_by_city($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function general_get_city($id=NULL, $single=FALSE) {
		$query = parent::get($id, $single);
		return $query;
	}

	public function update_city($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_city($id){
		parent::delete($id);
	}
}