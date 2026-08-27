<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Country_m extends MY_Model {

	protected $_table_name = 'country';
	protected $_primary_key = 'countryid';
	protected $_primary_filter = 'intval';
	protected $_order_by = "countryname asc";

	function __construct() {
		parent::__construct();
	}

	public function get_country($id=NULL, $signal=false) {
		$query = parent::get($id, $signal);
		return $query;
	}

	public function get_single_country($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	public function get_order_by_country($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_country($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	public function update_country($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_country($id){
		parent::delete($id);
	}
}