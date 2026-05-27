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


$route['admin'] = 'admin/auth/login';
$route['admin/login'] = 'admin/auth/login';
$route['admin/check-login'] = 'admin/auth/check_login';
$route['admin/dashboard'] = 'admin/dashboard/index';
$route['admin/admins'] = 'admin/admin/index';
$route['admin/admins/store'] = 'admin/admin/store';
$route['admin/admins/update/(:num)'] = 'admin/admin/update/$1';
$route['admin/admins/delete/(:num)'] = 'admin/admin/delete/$1';
$route['admin/categories'] = 'admin/categories/index';
$route['admin/categories/store'] = 'admin/categories/store';
$route['admin/categories/update/(:num)'] = 'admin/categories/update/$1';
$route['admin/categories/delete/(:num)'] = 'admin/categories/delete/$1';
$route['admin/courses'] = 'admin/courses/index';
$route['admin/courses/store'] = 'admin/courses/store';
$route['admin/courses/update/(:num)'] = 'admin/courses/update/$1';
$route['admin/courses/delete/(:num)'] = 'admin/courses/delete/$1';
$route['admin/batches'] = 'admin/batches/index';
$route['admin/batches/store'] = 'admin/batches/store';
$route['admin/batches/update/(:num)'] = 'admin/batches/update/$1';
$route['admin/batches/delete/(:num)'] = 'admin/batches/delete/$1';
$route['admin/logout'] = 'admin/auth/logout';
/*

*/
