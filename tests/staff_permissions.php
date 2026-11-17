<?php
// Run: php tests/staff_permissions.php; uses isolated doubles, never a live database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__.'/../system/');
require __DIR__.'/../application/helpers/admin_permissions_helper.php';
require __DIR__.'/../application/helpers/pricing_helper.php';
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
    function __construct($role) { $this->data=array('admin_role'=>$role,'admin_logged_in'=>TRUE,'registration_delete_token'=>'valid','survey_delete_token'=>'valid'); }
    function userdata($key) { return isset($this->data[$key])?$this->data[$key]:NULL; }
    function set_flashdata($key,$value) { $this->data[$key]=$value; }
    function flashdata($key) { return $this->userdata($key); }
}
class InputDouble {
    public $verb='POST';
    function method($upper=FALSE) { return $upper?$this->verb:strtolower($this->verb); }
    function post($key,$clean=FALSE) { return $key==='delete_token'?'valid':NULL; }
    function get($key,$clean=FALSE) { return NULL; }
}
class LoaderDouble { function library($name) {} function model($name) {} function view($name) {} }
class ModelDouble {
    public $calls=array(),$linked=FALSE;
    function admin_delete($id) { $this->calls[]='admin_delete'; return !$this->linked; }
    function get_by_id($id) { $this->calls[]='get_by_id'; return (object)array('id'=>$id,'course_id'=>9,'file_path'=>'','status'=>1,'payment_status'=>0); }
    function count_batches($id) { $this->calls[]='count_batches'; return $this->linked?1:0; }
    function delete_course($id) { $this->calls[]='delete_course'; return TRUE; }
    function delete_registration($id) { $this->calls[]='delete_registration'; return array(); }
    function delete_empty_survey($id) { $this->calls[]='delete_empty_survey'; return 'deleted'; }
    function __call($method,$args) { $this->calls[]=$method; return strpos($method,'count_')===0?($this->linked?1:0):TRUE; }
}
class CI_Controller {
    function __construct() {
        $this->session=$GLOBALS['session']; $this->input=new InputDouble(); $this->load=new LoaderDouble();
        foreach(array('Member','Course','Batch','Category','Course_detail','Course_instructor','Document','Instructor','Registration','Survey','Certificate','Admin') as $name) $this->{$name.'_model'}=new ModelDouble();
    }
}
require __DIR__.'/../application/controllers/Admin/Members.php';
require __DIR__.'/../application/controllers/Admin/Courses.php';
require __DIR__.'/../application/controllers/Admin/Admin.php';
foreach(array('Batches','Categories','Course_details','Course_instructors','Documents','Instructors','Registrations','Surveys') as $name) require __DIR__.'/../application/controllers/Admin/'.$name.'.php';
require __DIR__.'/../application/controllers/Admin/Certificates.php';
require __DIR__.'/../application/config/routes.php';
function destination($fn) { try { $fn(); } catch(RedirectResult $e) { return $e->getMessage(); } throw new RuntimeException('Expected redirect'); }
// Routed and direct controller URLs dispatch to these same guarded methods.
foreach(array('members'=>'Members','courses'=>'Courses') as $path=>$class) {
    check(strtolower($route['admin/'.$path.'/delete/(:num)'])==='admin/'.$path.'/delete/$1','Unexpected route target');
    foreach(array('GET','POST') as $verb) {
        $GLOBALS['session']=new SessionDouble('staff'); $c=new $class(); $c->input->verb=$verb;
        check(destination(function()use($c){$c->delete(7);})==='admin/'.$path,'Staff not redirected');
        check(!$c->Member_model->calls && !$c->Course_model->calls,'Staff reached model');
        check($c->session->flashdata('error')==='คุณไม่มีสิทธิ์ลบข้อมูล','Missing permission message');
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
// Every explicit deletion endpoint, including direct controller URLs, is guarded.
$endpoints=array(
    array('Batches','delete',array(7),'admin/batches/delete/(:num)','admin/batches','Batch'),
    array('Categories','delete',array(7),'admin/categories/delete/(:num)','admin/categories','Category'),
    array('Course_details','delete',array(7),'admin/course-details/delete/(:num)','admin/courses','Course_detail'),
    array('Course_instructors','delete',array(7),'admin/course-instructors/delete/(:num)','admin/courses','Course_instructor'),
    array('Documents','delete',array(7),'admin/documents/delete/(:num)','admin/documents','Document'),
    array('Instructors','delete',array(7),'admin/instructors/delete/(:num)','admin/instructors','Instructor'),
    array('Registrations','delete',array(7),'admin/registrations/delete/(:num)','admin/registrations','Registration'),
    array('Surveys','delete_question',array(9,7),'admin/surveys/questions/(:num)/delete/(:num)','admin/surveys/questions/9','Survey'),
    array('Surveys','delete',array(7),'admin/surveys/delete/(:num)','admin/surveys','Survey')
);
foreach($endpoints as $endpoint) {
    list($class,$method,$args,$route_key,$return_url,$model_name)=$endpoint;
    check(strpos(strtolower($route[$route_key]),'admin/'.strtolower($class).'/'.$method.'/')===0,'Route bypasses guarded action');
    foreach(array('GET','POST') as $verb) {
        $GLOBALS['session']=new SessionDouble('staff'); $c=new $class(); $c->input->verb=$verb;
        check(destination(function()use($c,$method,$args){call_user_func_array(array($c,$method),$args);})===$return_url,'Wrong staff redirect: '.$class);
        check($c->session->flashdata('error')==='คุณไม่มีสิทธิ์ลบข้อมูล','Wrong permission message');
        foreach(get_object_vars($c) as $name=>$value) if(substr($name,-6)==='_model') check(!$value->calls,'Staff reached model/file workflow: '.$class);
    }
    foreach(array('admin','super_admin','viewer') as $role) {
        $GLOBALS['session']=new SessionDouble($role); $c=new $class();
        destination(function()use($c,$method,$args){call_user_func_array(array($c,$method),$args);});
        check((bool)$c->{$model_name.'_model'}->calls,'Existing role blocked: '.$role.'/'.$class);
        if($class!=='Surveys') check((bool)$c->session->flashdata('success'),'Allowed delete did not succeed: '.$class);
    }
}
$anonymous=new SessionDouble('admin'); $anonymous->data['admin_logged_in']=FALSE;
check(!admin_can_delete($anonymous),'Anonymous deletion allowed');
check(destination(function()use($anonymous){admin_require_delete_permission($anonymous,'admin/courses');})==='admin','Anonymous guard did not redirect to login');
foreach(array('admin','staff','viewer') as $role) {
    $GLOBALS['session']=new SessionDouble($role);
    check(destination(function(){new Admin();})==='admin/dashboard','Non-super-admin reached account management');
}
class UriDouble { function uri_string() { return 'admin/members'; } }
class ConfigDouble { function item($key) { return FALSE; } }
class Renderer {
    public $session,$load,$uri,$config,$input;
    function __construct($role) { $this->session=new SessionDouble($role); $this->load=new LoaderDouble(); $this->uri=new UriDouble(); $this->config=new ConfigDouble(); $this->input=new InputDouble(); }
    function render($view,$data=array()) { extract($data); ob_start(); include __DIR__.'/../application/views/admins/'.$view.'.php'; return ob_get_clean(); }
}
$member=(object)array('id'=>7,'title_name'=>'','first_name'=>'Test','last_name'=>'Member','email'=>'test@example.test','phone'=>'1','position_name'=>'Staff','organization_name'=>'School','status'=>1,'created_at'=>'2026-01-01');
$course=(object)array('id'=>7,'title'=>'Course','slug'=>'course','status'=>2,'is_featured'=>0,'category_name'=>'Category','training_type'=>'onsite','capacity'=>10,'fee'=>0,'batch_count'=>0);
$row=(object)array(
    'id'=>7,'course_id'=>9,'title'=>'Fixture','slug'=>'fixture','name'=>'Fixture','description'=>'Description','sort_order'=>1,
    'is_active'=>1,'is_public'=>1,'section_type'=>'learning','content'=>'Content','instructor_name'=>'Instructor',
    'department'=>'Department','email'=>'fixture@example.test','phone'=>'1','photo'=>'','position'=>'Position','role'=>'วิทยากรหลัก','bio'=>'Bio',
    'course_title'=>'Course','course_slug'=>'course','file_path'=>'uploads/documents/fixture.pdf','file_type'=>'pdf','file_size'=>10,
    'batch_no'=>'A','capacity'=>10,'category_name'=>'Category','status'=>1,'participant_count'=>1,
    'certificate_issued_count'=>0,'certificate_template_count'=>0,'evaluation_id'=>0,'evaluation_status'=>0,
    'evaluation_open_at'=>'','evaluation_close_at'=>'','registration_start'=>'','registration_end'=>'',
    'start_date'=>'','end_date'=>'','start_time'=>'','end_time'=>''
);
$view_cases=array(
    array('categories',array('categories'=>array($row)),'admin/categories/delete/7'),
    array('batches',array('batches'=>array($row)),'admin/batches/delete/7'),
    array('course_details',array('course'=>$course,'details'=>array($row)),'admin/course-details/delete/7'),
    array('course_instructors',array('course'=>$course,'course_instructors'=>array($row)),'admin/course-instructors/delete/7'),
    array('instructors',array('instructors'=>array($row)),'admin/instructors/delete/7'),
    array('documents',array('documents'=>array($row)),'admin/documents/delete/7'),
    array('registrations',array('delete_token'=>'valid','registrations'=>array((object)array_merge((array)$member,array('registration_code'=>'REG7','course_title'=>'Course','batch_no'=>'A','fee'=>0,'participant_count'=>1,'paid_amount'=>0,'payment_status'=>0)))),'admin/registrations/delete/7')
);
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
    foreach($view_cases as $case) {
        $html=$r->render($case[0],$case[1]);
        check((strpos($html,'action="'.site_url($case[2]).'"')!==FALSE)===$allowed,'Delete control wrong: '.$role.'/'.$case[0]);
        check(strpos($html,$case[0]==='registrations'?'รายละเอียด':'แก้ไข')!==FALSE,'Other controls disappeared: '.$case[0]);
    }
    $html=$r->render('survey_questions',array('survey'=>$course,'locked'=>FALSE,'questions'=>array((object)array('id'=>7,'question_text'=>'Question','question_type'=>'rating','is_required'=>1,'options'=>array()))));
    check((strpos($html,site_url('admin/surveys/questions/7/delete/7'))!==FALSE)===$allowed,'Question delete control wrong');
    check(strpos($html,'>เพิ่มคำถาม</button>')!==FALSE && strpos($html,'/move/7/up')!==FALSE,'Question add/reorder hidden');
    foreach(array(0,1) as $questions) {
        foreach(array(0,1) as $assignments) {
            $survey=(object)array('id'=>7,'title'=>'Survey','description'=>'Description','question_count'=>$questions,'assignment_count'=>$assignments);
            $html=$r->render('surveys',array('tables_ready'=>TRUE,'surveys'=>array($survey),'assignments'=>array(),'delete_token'=>'valid'));
            check((strpos($html,'action="'.site_url('admin/surveys/delete/7').'"')!==FALSE)===($allowed && $questions===0 && $assignments===0),'Survey delete button condition wrong');
            check(strpos($html,'>แก้ไข</a>')!==FALSE && strpos($html,'>คำถาม</a>')!==FALSE,'Survey edit/questions hidden');
        }
    }
    foreach(range(1,5) as $status) {
        foreach(range(0,4) as $payment_status) {
            $registration=(object)array_merge((array)$member,array('registration_code'=>'REG7','course_title'=>'Course','batch_no'=>'A','fee'=>0,'participant_count'=>1,'paid_amount'=>0,'status'=>$status,'payment_status'=>$payment_status));
            $html=$r->render('registrations',array('delete_token'=>'valid','registrations'=>array($registration)));
            $locked=$payment_status===3 && in_array($status,array(2,5),TRUE);
            check((strpos($html,'action="'.site_url('admin/registrations/delete/7').'"')!==FALSE)===($allowed && !$locked),'Registration delete button condition wrong');
            check(strpos($html,'>รายละเอียด</a>')!==FALSE,'Locked registration details hidden');
        }
    }
    $html=$r->render('layouts/sidebar');
    check((strpos($html,site_url('admin/admins'))!==FALSE)===($role==='super_admin'),'Admin menu visibility wrong');
}
$GLOBALS['session']=new SessionDouble('staff'); $c=new Certificates();
destination(function()use($c){$c->cancel(7);});
check(in_array('cancel',$c->Certificate_model->calls,TRUE),'Certificate cancellation incorrectly blocked');
echo "PASS: staff guards, direct actions, existing role/delete rules, and rendered controls\n";
