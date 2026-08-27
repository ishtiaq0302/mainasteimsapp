<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Setting extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('setting_m');
    }

    public function index_post()
    {
        $campusID = $this->post('campusID') ?: 1;
        $adminID = $this->post('adminID') ?: 1;

        // Since Setting_m uses session, we manually query for the API
        $query = $this->db->where('adminID', $adminID)->where('campusID', $campusID)->get('settings');
        $settings = $query->row();

        if($settings) {
            // Include logo URL
            if ($settings->photo) {
                $settings->logo_url = base_url('uploads/images/' . $settings->photo);
            }
            $this->response(["status" => true, "data" => $settings], REST_Controller::HTTP_OK);
        } else {
            $this->response(["status" => false, "message" => "Settings not found"], REST_Controller::HTTP_OK);
        }
    }

    public function update_post()
    {
        // Implement update logic if needed, following Setting.php logic
        // For now, providing a get-focused API as requested for Flutter dynamic content
        $this->response(["status" => false, "message" => "Update not implemented via API yet"], REST_Controller::HTTP_NOT_IMPLEMENTED);
    }
}
