<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
|	https://codeigniter.com/userguide3/general/routing.html
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
$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['dashboard/participants/(:num)'] = 'dashboard/participants/$1';
$route['dashboard/payment-slip/(:num)'] = 'dashboard/upload_payment_slip/$1';
$route['dashboard/cancel-registration/(:num)'] = 'dashboard/cancel_registration/$1';

$route['auth/reset-password/(:any)'] = 'auth/reset_password/$1';

$route['admin'] = 'Admin/auth/login';
$route['admin/login'] = 'Admin/auth/login';
$route['admin/check-login'] = 'Admin/auth/check_login';
$route['admin/dashboard'] = 'Admin/dashboard/index';
$route['admin/admins'] = 'Admin/admin/index';
$route['admin/admins/store'] = 'Admin/admin/store';
$route['admin/admins/update/(:num)'] = 'Admin/admin/update/$1';
$route['admin/admins/delete/(:num)'] = 'Admin/admin/delete/$1';
$route['admin/categories'] = 'Admin/categories/index';
$route['admin/categories/store'] = 'Admin/categories/store';
$route['admin/categories/update/(:num)'] = 'Admin/categories/update/$1';
$route['admin/categories/delete/(:num)'] = 'Admin/categories/delete/$1';
$route['admin/courses'] = 'Admin/courses/index';
$route['admin/courses/store'] = 'Admin/courses/store';
$route['admin/courses/update/(:num)'] = 'Admin/courses/update/$1';
$route['admin/courses/delete/(:num)'] = 'Admin/courses/delete/$1';
$route['admin/course-details/(:num)'] = 'Admin/course_details/index/$1';
$route['admin/course-details/store/(:num)'] = 'Admin/course_details/store/$1';
$route['admin/course-details/update/(:num)'] = 'Admin/course_details/update/$1';
$route['admin/course-details/delete/(:num)'] = 'Admin/course_details/delete/$1';
$route['admin/course-instructors/(:num)'] = 'Admin/course_instructors/index/$1';
$route['admin/course-instructors/store/(:num)'] = 'Admin/course_instructors/store/$1';
$route['admin/course-instructors/update/(:num)'] = 'Admin/course_instructors/update/$1';
$route['admin/course-instructors/delete/(:num)'] = 'Admin/course_instructors/delete/$1';
$route['admin/batches'] = 'Admin/batches/index';
$route['admin/batches/store'] = 'Admin/batches/store';
$route['admin/batches/update/(:num)'] = 'Admin/batches/update/$1';
$route['admin/batches/delete/(:num)'] = 'Admin/batches/delete/$1';
$route['admin/registrations'] = 'Admin/registrations/index';
$route['admin/registrations/view/(:num)'] = 'Admin/registrations/view/$1';
$route['admin/registrations/update-status/(:num)'] = 'Admin/registrations/update_status/$1';
$route['admin/registrations/update-payment/(:num)/(:num)'] = 'Admin/registrations/update_payment/$1/$2';
$route['admin/instructors'] = 'Admin/instructors/index';
$route['admin/instructors/store'] = 'Admin/instructors/store';
$route['admin/instructors/update/(:num)'] = 'Admin/instructors/update/$1';
$route['admin/instructors/delete/(:num)'] = 'Admin/instructors/delete/$1';
$route['admin/documents'] = 'Admin/documents/index';
$route['admin/documents/store'] = 'Admin/documents/store';
$route['admin/documents/update/(:num)'] = 'Admin/documents/update/$1';
$route['admin/documents/delete/(:num)'] = 'Admin/documents/delete/$1';
$route['admin/reports'] = 'Admin/reports/index';
$route['admin/surveys'] = 'Admin/surveys/index';
$route['admin/surveys/save'] = 'Admin/surveys/save/0';
$route['admin/surveys/save/(:num)'] = 'Admin/surveys/save/$1';
$route['admin/surveys/questions/(:num)'] = 'Admin/surveys/questions/$1';
$route['admin/surveys/questions/(:num)/add'] = 'Admin/surveys/add_question/$1';
$route['admin/surveys/questions/(:num)/delete/(:num)'] = 'Admin/surveys/delete_question/$1/$2';
$route['admin/surveys/questions/(:num)/move/(:num)/(:any)'] = 'Admin/surveys/move_question/$1/$2/$3';
$route['admin/surveys/assign/(:num)'] = 'Admin/surveys/assign/$1';
$route['admin/surveys/assignment/(:num)'] = 'Admin/surveys/assignment/$1';
$route['admin/surveys/assignment/(:num)/close'] = 'Admin/surveys/close/$1';
$route['admin/surveys/assignment/(:num)/regenerate'] = 'Admin/surveys/regenerate/$1';
$route['admin/surveys/report/(:num)'] = 'Admin/surveys/report/$1';
$route['admin/surveys/export/(:num)'] = 'Admin/surveys/export/$1';
$route['evaluation/(:any)/verify/(:num)'] = 'evaluation/verify/$1/$2';
$route['evaluation/(:any)/status'] = 'evaluation/status/$1';
$route['evaluation/(:any)/profile'] = 'evaluation/profile/$1';
$route['evaluation/(:any)/confirm'] = 'evaluation/confirm/$1';
$route['evaluation/(:any)/form'] = 'evaluation/form/$1';
$route['evaluation/(:any)'] = 'evaluation/index/$1';
$route['admin/logout'] = 'Admin/auth/logout';

