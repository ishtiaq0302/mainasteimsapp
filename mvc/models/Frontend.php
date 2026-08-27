<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Frontend extends Frontend_Controller {
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

    protected $_pageName;
    protected $_templateName;
    protected $_homepage;

    function __construct() {
        parent::__construct();
        $this->load->model('pages_m');
        $this->load->model('media_gallery_m');
        $this->load->model('slider_m');
        $this->load->model('country_m');
    }

    public function index() 
    {
        // $type = htmlentities(escapeString($this->uri->segment(3)));
        // $url = htmlentities(escapeString($this->uri->segment(4)));
        // if($type && $url) {
        //     redirect(base_url('frontend/'.$type.'/'.$url));
        // } else {
        //     if(count($this->data['homepage'])) {
        //         if(isset($this->data['homepage']->pagesID)) {
        //             redirect(base_url('frontend/page/'.$this->data['homepage']->url));    
        //         } elseif(isset($this->data['homepage']->postsID)) {
        //             redirect(base_url('frontend/post/'.$this->data['homepage']->url));
        //         } else {
        //             redirect(base_url('frontend/home'));
        //         }
        //     } else {
        //         redirect(base_url('frontend/home'));
        //     }
        // }
        
        
        $this->page();
        
    }

    public function home() 
    {
        // $this->bladeView->render('views/templates/homeempty');
    }

    public function page() 
    {

        $this->data['allcountry'] = $this->country_m->get_country();
        
        
        $this->load->view('frontpage/index');
        
        // $url = htmlentities(escapeString($this->uri->segment(3)));
        // $schoolyearID = $this->data['backend_setting']->school_year;
        // if($this->session->userdata('defaultschoolyearID')) {
        //     $schoolyearID = $this->session->userdata('defaultschoolyearID');
        // }

        // if($url) {
        //     if($url == 'login') {
        //         redirect(base_url('signin/index'));
        //     }

        //     $pages = $this->pages_m->get_pages();
        //     $page = $this->pages_m->get_single_pages(array('url' => $url));
        //     $featured_image = [];

        //     if(count($page)) {
        //         if(!empty($page->featured_image)) {
        //             $featured_image = $this->media_gallery_m->get_single_media_gallery(array('media_galleryID' => $page->featured_image));
        //         }

        //         $sliders = $this->slider_m->get_slider_join_with_media_gallery($page->pagesID);
        //         $this->_pageName = $page->title;
        //         $this->_templateName = $page->template;
        //         if($page->template == 'none') {
        //             $this->bladeView->render('views/templates/none', compact('page', 'featured_image', 'sliders'));
        //         } elseif($page->template == 'blog') {
        //             $posts = $this->posts_m->get_order_by_posts(array('status' => 1));

        //             $featured_image = [];
        //             if(count($posts)) {
        //                 $featured_image = pluck($this->media_gallery_m->get_order_by_media_gallery(array('media_gallery_type' => 1)), 'obj', 'media_galleryID');
        //             }

        //             $allUser = getAllSelectUser();
        //             $this->bladeView->render('views/templates/'.$this->_templateName, compact('page', 'posts', 'allUser', 'featured_image', 'sliders'));                    
        //         } else {
        //             $this->bladeView->render('views/templates/'.$this->_templateName, compact('page', 'featured_image', 'sliders'));
        //         }
        //     } else {
        //         $this->_templateName = 'page404';
        //         $this->bladeView->render('views/templates/'.$this->_templateName);
        //     }
        // } else {
        //     $this->_templateName = 'page404';
        //     $this->bladeView->render('views/templates/'.$this->_templateName);
        // }
    }

    public function post() 
    {
        $url = htmlentities(escapeString($this->uri->segment(3)));
        if($url) {
            if($url == 'login') {
                redirect(base_url('signin/index'));
            }

            $posts = $this->posts_m->get_posts();
            $post = $this->posts_m->get_single_posts(array('url' => $url));
            $featured_image = [];

            if(count($post)) {
                $posts = $this->posts_m->get_order_by_posts(array('status' => 1));
                if(!empty($post->featured_image)) {
                    $featured_image = $this->media_gallery_m->get_single_media_gallery(array('media_galleryID' => $post->featured_image));
                }

                $this->_pageName = $post->title;
                $this->_templateName = 'postnone';
                $allUser = getAllSelectUser();

                $this->bladeView->render('views/templates/'.$this->_templateName, compact('post', 'posts', 'allUser', 'featured_image'));
            } else {
                $this->_templateName = 'page404';
                $this->bladeView->render('views/templates/'.$this->_templateName);
            }
        } else {
            $this->_templateName = 'page404';
            $this->bladeView->render('views/templates/'.$this->_templateName);
        }
    }

    public function event()
    {

        $id = htmlentities(escapeString($this->uri->segment(3)));
        if((int)$id) {
            $eventView = $this->event_m->get_single_event(array('eventID' => $id));
            if(count($eventView)) {
                $this->bladeView->render('views/templates/eventview', compact('eventView'));
            } else {
                $this->_templateName = 'page404';
                $this->bladeView->render('views/templates/'.$this->_templateName);
            }
        } else {
            $this->_templateName = 'page404';
            $this->bladeView->render('views/templates/'.$this->_templateName);
        }
    }

    public function eventGoing()
    {
        $status = FALSE;
        $message = '';
        $id = $this->input->post('id');

        if((int)$id) {
            if($this->session->userdata('loggedin')) {
                $event = $this->event_m->get_single_event(array('eventID' => $id));
                if(count($event)) {
                    $username = $this->session->userdata("username");
                    $usertype = $this->session->userdata("usertype");
                    $photo = $this->session->userdata("photo");
                    $name = $this->session->userdata("name");

                    $this->load->model('eventcounter_m');
                    $have = $this->eventcounter_m->get_order_by_eventcounter(array("eventID" => $id, "username" => $username, "type" => $usertype),TRUE);

                    if(count($have)) {
                        $array = array('status' => 1);
                        $this->eventcounter_m->update($array,$have[0]->eventcounterID);
                        $status = TRUE;
                        $message = 'You are add this event';
                    } else {
                        $array = array('eventID' => $id,
                            'username' => $username,
                            'type' => $usertype,
                            'photo' => $photo,
                            'name' => $name,
                            'status' => 1
                        );
                        $this->eventcounter_m->insert($array);
                        $status = TRUE;
                        $message = 'You are add this event';
                    }
                } else {
                    $message = 'Event id does not found';
                }
            } else {
                $message = 'Please login';
            }
        } else {
            $message = 'ID is not int';
        }

        $json = array(
            "message" => $message, 
            'status' => $status,
        );
        header("Content-Type: application/json", true);
        echo json_encode($json);
        exit;
    }

    public function notice()
    {
        $id = htmlentities(escapeString($this->uri->segment(3)));
        if((int)$id) {
            $noticeView = $this->notice_m->get_single_notice(array('noticeID' => $id));


            if(count($noticeView)) {
                $this->bladeView->render('views/templates/noticeview', compact('noticeView'));
            } else {
                $this->_templateName = 'page404';
                $this->bladeView->render('views/templates/'.$this->_templateName);
            }
        } else {
            $this->_templateName = 'page404';
            $this->bladeView->render('views/templates/'.$this->_templateName);
        }
    }

   
    public function contactMailSend() 
    {
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $subject = $this->input->post('subject');
        $institute = $this->input->post('institute');
        $message = $this->input->post('message');
        if($name && $email && $subject && $message) {           
            $this->load->library('email');
            $this->email->set_mailtype("html");
            if(frontendData::get_backend('email')) {
                $this->email->from($email, frontendData::get_backend('sname'));
                $this->email->to(frontendData::get_backend('email'));
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->send();
                $this->session->set_flashdata('msg','<div class="alert alert-success text-center">Your mail has been sent successfully!</div>');
                redirect('frontend/index');
            } else {
                $this->session->set_flashdata('error', 'Set your email in general setting');
            }
        } else {
            $this->session->set_flashdata('msg','<div class="alert alert-success text-center">Your mail has been sent successfully!</div>');
            redirect('contactform/index');
        }
    }

     public function getaquote() 
    {
        $name = $this->input->post('name');
        $email = $this->input->post('email');
        $institute_type = $this->input->post('institute_type');
        $institute_name = $this->input->post('institute_name');
        $student_no = $this->input->post('student_no');
        $country = $this->input->post('country');
        $subject = 'Wants A Quote';
        if($name && $email) {           
            $this->load->library('email');
            $this->email->set_mailtype("html");
            if(frontendData::get_backend('email')) {
                $this->email->from($email, frontendData::get_backend('sname'));
                $this->email->to(frontendData::get_backend('email'));
            $message = "<h2 style='text-transform: uppercase'>".$name." Wants Quote Detail</h2>
            <p>Name: ".$name."</p>
            <p>Institute Type: ".$institute_type."</p>
            <p>Institue Name: ".$institute_name."</p>
            <p>Number of Student: ".$student_no."</p>
            <p>Country Name: ".$country."</p>";
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->send();
                $this->session->set_flashdata('msg','<div class="alert alert-success text-center">Your mail has been sent successfully!</div>');
                redirect('frontend/index');
            } else {
                $this->session->set_flashdata('error', 'Set your email in general setting');
            }
        } else {
            $this->session->set_flashdata('msg','<div class="alert alert-success text-center">Your mail has been sent successfully!</div>');
            redirect('frontend/index#getquote');
        }
    }


}
    
    
        
        

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
