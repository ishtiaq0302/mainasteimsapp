<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Productsaleitem_m extends MY_Model {

	protected $_table_name = 'productsaleitem';
	protected $_primary_key = 'productsaleitemID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "productsaleitemID asc";

	function __construct() {
		parent::__construct();
	}

	public function get_productsaleitem($array=NULL, $signal=FALSE) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_single_productsaleitem($array) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_single($array);
		return $query;
	}

	public function get_order_by_productsaleitem($array=NULL) {
		$this->db->where('adminID',$this->session->userdata('adminID'));
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_productsaleitem($array) {
		$array['adminID']=$this->session->userdata('adminID');
		$error = parent::insert($array);
		return TRUE;
	}

	public function insert_batch_productsaleitem($array) {
		$total_array=0;
        if(!empty($array[0])){
            $total_array = count($array);
            for($i=0; $i < $total_array ; $i++) { 
                $array[$i]['adminID']=$this->session->userdata('adminID');
            }
        }
        
		$id = parent::insert_batch($array);
		return $id;
	}

	public function update_productsaleitem($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_productsaleitem($id){
		parent::delete($id);
	}

	public function delete_productsaleitem_by_productsaleID($id) {
		$this->db->delete($this->_table_name, array('productsaleID' => $id)); 
		return TRUE;
	}

	public function get_productsaleitem_sum($array) {
		if(isset($array['productsaleID']) && isset($array['schoolyearID'])) {
			$string = "SELECT SUM(productsaleunitprice), SUM(productsalequantity), SUM(productsaleunitprice*productsalequantity) AS result FROM ".$this->_table_name." WHERE productsaleID = '".$array['productsaleID']."' && schoolyearID = '".$array['schoolyearID']."'";
		} else {
			$string = "SELECT SUM(productsaleunitprice), SUM(productsalequantity), SUM(productsaleunitprice*productsalequantity) AS result FROM ".$this->_table_name;
		}

		$query = $this->db->query($string);
		return $query->row();
	}

	public function get_productsaleitem_quantity($productsaleID = 0) {
		if($productsaleID == 0) {
			$string = 'SELECT SUM(productsaleitem.productsalequantity) AS quantity, productsaleitem.productID AS productID FROM productsaleitem LEFT JOIN productsale on productsale.productsaleID = productsaleitem.productsaleID WHERE productsalerefund = 0 GROUP BY productsaleitem.productID';
		} else {
			$string = 'SELECT SUM(productsaleitem.productsalequantity) AS quantity, productsaleitem.productID AS productID FROM productsaleitem LEFT JOIN productsale on productsale.productsaleID = productsaleitem.productsaleID WHERE productsalerefund = 0 && productsaleitem.productsaleID = "'.$productsaleID.'" GROUP BY productsaleitem.productID';
		}

		$query = $this->db->query($string);
		return $query->result();
	}


	// 

}