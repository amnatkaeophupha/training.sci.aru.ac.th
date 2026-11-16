<?php
// Run: php tests/staff_permissions.php; uses isolated doubles, never a live database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__.'/../system/');
set_error_handler(function($severity,$message,$file,$line) { throw new ErrorException($message,0,$severity,$file,$line); });
class RedirectResult extends Exception {}
function redirect($url) { throw new RedirectResult($url); }
function show_404() { throw new RuntimeException('404'); }
function site_url($url='') { return '/index.php/'.$url; }
function base_url($url='') { return '/'.$url; }
function html_escape($value) { return htmlspecialchars($value,ENT_QUOTES,'UTF-8'); }
function check($ok,$message) { if (!$ok) throw new RuntimeException($message); }
class SessionDouble {
    public $data;
    function __construct($role) { $this->data=array('admin_role'=>$role,'admin_logged_in'=>TRUE); }
    function userdata($key) { return isset($this->data[$key])?$this->data[$key]:NULL; }
    function set_flashdata($key,$value) { $this->data[$key]=$value; }
    function flashdata($key) { return $this->userdata($key); }
}
class InputDouble { public $verb='POST'; function method($upper=FALSE) { return $upper?$this->verb:strtolower($this->verb); } }
class LoaderDouble { function library($name) {} function model($name) {} function view($name) {} }
class ModelDouble {
    public $calls=array(),$linked=FALSE;
    function admin_delete($id) { $this->calls[]='admin_delete'; return !$this->linked; }
    function get_by_id($id) { $this->calls[]='get_by_id'; return (object)array('id'=>$id); }
    function count_batches($id) { $this->calls[]='count_batches'; return $this->linked?1:0; }
    function delete_course($id) { $this->calls[]='delete_course'; return TRUE; }
}
class CI_Controller {
    function __construct() {
        $this->session=$GLOBALS['session']; $this->input=new InputDouble(); $this->load=new LoaderDouble();
        $this->Member_model=new ModelDouble(); $this->Course_model=new ModelDouble();
    }
}
require __DIR__.'/../application/controllers/Admin/Members.php';
require __DIR__.'/../application/controllers/Admin/Courses.php';
require __DIR__.'/../application/controllers/Admin/Admin.php';
require __DIR__.'/../application/config/routes.php';
function destination($fn) { try { $fn(); } catch(RedirectResult $e) { return $e->getMessage(); } throw new RuntimeException('Expected redirect'); }
// Routed and direct controller URLs dispatch to these same guarded methods.
foreach(array('members'=>'Members','courses'=>'Courses') as $path=>$class) {
    check(strtolower($route['admin/'.$path.'/delete/(:num)'])==='admin/'.$path.'/delete/$1','Unexpected route target');
    foreach(array('GET','POST') as $verb) {
        $GLOBALS['session']=new SessionDouble('staff'); $c=new $class(); $c->input->verb=$verb;
        check(destination(function()use($c){$c->delete(7);})==='admin/'.$path,'Staff not redirected');
        check(!$c->Member_model->calls && !$c->Course_model->calls,'Staff reached model');
        check($c->session->flashdata('error')===($path==='members'?'คุณไม่มีสิทธิ์ลบสมาชิก':'คุณไม่มีสิทธิ์ลบหลักสูตรอบรม'),'Missing permission message');
    }
    foreach(array('admin','super_admin','viewer') as $role) {
        foreach(array(FALSE,TRUE) as $linked) {
            $GLOBALS['session']=new SessionDouble($role); $c=new $class();
            $model=$path==='members'?$c->Member_model:$c->Course_model; $model->linked=$linked;
            check(destination(function()use($c){$c->delete(7);})==='admin/'.$path,'Existing role redirect changed');
            check((bool)$c->session->flashdata($linked?'error':'success'),'Existing delete rules changed');
            if($path==='courses' && $linked) check(!in_array('delete_course',$model->calls,TRUE),'Linked course deleted');
        }
    }
}
foreach(array('index','store','update','delete') as $method) {
    $GLOBALS['session']=new SessionDouble('staff');
    check(destination(function()use($method){$c=new Admin();$c->$method(7);})==='admin/dashboard','Staff reached admin action '.$method);
}
$GLOBALS['session']=new SessionDouble('super_admin'); new Admin();
class UriDouble { function uri_string() { return 'admin/members'; } }
class Renderer {
    public $session,$load,$uri;
    function __construct($role) { $this->session=new SessionDouble($role); $this->load=new LoaderDouble(); $this->uri=new UriDouble(); }
    function render($view,$data=array()) { extract($data); ob_start(); include __DIR__.'/../application/views/admins/'.$view.'.php'; return ob_get_clean(); }
}
$member=(object)array('id'=>7,'title_name'=>'','first_name'=>'Test','last_name'=>'Member','email'=>'test@example.test','phone'=>'1','position_name'=>'Staff','organization_name'=>'School','status'=>1,'created_at'=>'2026-01-01');
$course=(object)array('id'=>7,'title'=>'Course','slug'=>'course','status'=>2,'is_featured'=>0,'category_name'=>'Category','training_type'=>'onsite','capacity'=>10,'fee'=>0,'batch_count'=>0);
foreach(array('staff','admin','super_admin','viewer') as $role) {
    $r=new Renderer($role); $allowed=$role!=='staff';
    $html=$r->render('members',array('members'=>array($member),'query'=>'','stats'=>array('total'=>1,'active'=>1,'inactive'=>0)));
    check((strpos($html,'<form class="js-delete-member"')!==FALSE)===$allowed,'Member delete visibility wrong');
    check(strpos($html,'id="add-member"')!==FALSE && strpos($html,'>แก้ไข</button>')!==FALSE,'Member add/edit hidden');
    foreach(array(0,1) as $count) {
        $course->batch_count=$count;
        $html=$r->render('courses',array('courses'=>array($course),'categories'=>array((object)array('id'=>1,'name'=>'Category'))));
        check((strpos($html,'>ลบ</button>')!==FALSE)===$allowed,'Course delete visibility wrong');
        check((strpos($html,'<form class="js-course-delete-form"')!==FALSE)===($allowed && !$count),'Course delete form wrong');
        check(strpos($html,'เพิ่มหลักสูตร')!==FALSE && strpos($html,'>แก้ไข</a>')!==FALSE,'Course add/edit hidden');
    }
    $html=$r->render('layouts/sidebar');
    check((strpos($html,site_url('admin/admins'))!==FALSE)===($role==='super_admin'),'Admin menu visibility wrong');
}
echo "PASS: staff guards, direct actions, existing role/delete rules, and rendered controls\n";
