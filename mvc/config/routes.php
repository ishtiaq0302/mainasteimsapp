<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function __autoload($classname) {
	if(strpos($classname, 'CI_') !== 0) {
		$file = APPPATH . 'libraries/' . $classname . '.php';
		if(file_exists($file) && is_file($file)) {
			@include_once($file);
		}
	}
}

$route['version'] = "app/version";

// Mobile API Routes
$route['api/login'] = 'api/api/login';
$route['api/student_list'] = 'api/api/student_list';
$route['api/metadata'] = 'api/api/metadata';
$route['api/student_add'] = 'api/api/student_add';
$route['api/sidebar_menu'] = 'api/api/sidebar_menu';
$route['api/student_view'] = 'api/api/student_view';
$route['api/dashboard_data'] = 'api/api/dashboard_data';
$route['api/student_delete'] = 'api/api/student_delete';
$route['api/student_status'] = 'api/api/student_status';
$route['api/student_update'] = 'api/api/student_update';
$route['api/settings'] = 'api/setting/index';

// Parent API Routes
$route['api/parents'] = 'api/parents/index';
$route['api/parent_view'] = 'api/parents/view';
$route['api/parent_add'] = 'api/parents/add';
$route['api/parent_update'] = 'api/parents/update';
$route['api/parent_delete'] = 'api/parents/delete';
$route['api/parent_status'] = 'api/parents/status';

// Teacher API Routes
$route['api/teachers'] = 'api/teacher/index';
$route['api/teacher_view'] = 'api/teacher/view';
$route['api/teacher_add'] = 'api/teacher/add';
$route['api/teacher_update'] = 'api/teacher/update';
$route['api/teacher_delete'] = 'api/teacher/delete';
$route['api/teacher_status'] = 'api/teacher/status';

// User API Routes
$route['api/users'] = 'api/user/index';
$route['api/user_view'] = 'api/user/view';
$route['api/user_add'] = 'api/user/add';
$route['api/user_update'] = 'api/user/update';
$route['api/user_delete'] = 'api/user/delete';
$route['api/user_status'] = 'api/user/status';

// Classes API Routes
$route['api/classes']        = 'api/classes/index';
$route['api/class_view']     = 'api/classes/view';
$route['api/classes_add']    = 'api/classes/add';
$route['api/classes_update'] = 'api/classes/update';
$route['api/classes_delete'] = 'api/classes/delete';
$route['api/class_status']   = 'api/classes/status';

// Section API Routes
$route['api/section']        = 'api/section/index';
$route['api/section_view']   = 'api/section/view';
$route['api/section_add']    = 'api/section/add';
$route['api/section_update'] = 'api/section/update';
$route['api/section_delete'] = 'api/section/delete';

// Subject API Routes
$route['api/subject']        = 'api/subject/index';
$route['api/subject_view']   = 'api/subject/view';
$route['api/subject_add']    = 'api/subject/add';
$route['api/subject_update'] = 'api/subject/update';
$route['api/subject_delete'] = 'api/subject/delete';

// Campus API Routes
$route['api/campus']        = 'api/campus/index';
$route['api/campus_view']   = 'api/campus/view';
$route['api/campus_add']    = 'api/campus/add';
$route['api/campus_update'] = 'api/campus/update';
$route['api/campus_delete'] = 'api/campus/delete';

// Lecture API Routes
$route['api/lecture']        = 'api/lecture/index';
$route['api/lecture_view']   = 'api/lecture/view';
$route['api/lecture_add']    = 'api/lecture/add';
$route['api/lecture_update'] = 'api/lecture/update';
$route['api/lecture_delete'] = 'api/lecture/delete';

// Assignment API Routes
$route['api/assignment']        = 'api/assignment/index';
$route['api/assignment_view']   = 'api/assignment/view';
$route['api/assignment_add']    = 'api/assignment/add';
$route['api/assignment_update'] = 'api/assignment/update';
$route['api/assignment_delete'] = 'api/assignment/delete';

// Routine API Routes
$route['api/routine']        = 'api/routine/index';
$route['api/routine_view']   = 'api/routine/view';
$route['api/routine_add']    = 'api/routine/add';
$route['api/routine_update'] = 'api/routine/update';
$route['api/routine_delete'] = 'api/routine/delete';

// Syllabus API Routes
$route['api/syllabus']        = 'api/syllabus/index';
$route['api/syllabus_view']   = 'api/syllabus/view';
$route['api/syllabus_add']    = 'api/syllabus/add';
$route['api/syllabus_update'] = 'api/syllabus/update';
$route['api/syllabus_delete'] = 'api/syllabus/delete';

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/


if ($this->config->item('installed') == 'no') {
    $route["default_controller"] = "install";
} else {
	$route['default_controller'] = "signin";
}
$route['404_override'] = '';
// $route['translate_uri_dashes'] = FALSE;

