<?php
// Run: php tests/registration_flow.php (no database or Composer required).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__.'/../system/');
require __DIR__.'/../application/helpers/registration_helper.php';
require __DIR__.'/../application/helpers/pricing_helper.php';
class CI_Controller {}
class CI_Model {}
class RedirectResult extends Exception {}
function redirect($url) { throw new RedirectResult($url); }
function log_message($level, $message) {}
function base_url($url = '') { return 'https://example.test/'.$url; }
function html_escape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function check($ok, $message) { if (!$ok) throw new Exception($message); }
class TestInput {
    public $get = array(), $post = array();
    function get($key, $clean = FALSE) { return isset($this->get[$key]) ? $this->get[$key] : NULL; }
    function post($key, $clean = FALSE) { return isset($this->post[$key]) ? $this->post[$key] : NULL; }
    function method() { return $this->post ? 'post' : 'get'; }
}
class TestSession {
    public $data = array();
    function userdata($key) { return isset($this->data[$key]) ? $this->data[$key] : NULL; }
    function set_userdata($key, $value) { $this->data[$key] = $value; }
    function unset_userdata($key) { unset($this->data[$key]); }
    function set_flashdata($key, $value) { $this->data[$key] = $value; }
    function flashdata($key) { return $this->userdata($key); }
}
class TestLoader {
    public $data;
    function model($name) {}
    function view($name, $data = array()) { $this->data = $data; }
}
class TestMember {
    public $member;
    function find_by_email($email) { return $this->member; }
    function find_by_id($id) { return $this->member; }
    function email_exists($email) { return FALSE; }
    function create($data) { return 1; }
}
class TestBatch {
    public $open = TRUE, $saved = TRUE;
    function register_member_with_participant($member, $id) { return $this->saved; }
    function get_selectable_frontend_batch($id) { return $this->open ? (object) array('slug'=>'equipment-and-lab') : NULL; }
}
require __DIR__.'/../application/controllers/Auth.php';
require __DIR__.'/../application/controllers/Dashboard.php';
require __DIR__.'/../application/models/Batch_model.php';
function controller($class) {
    $c = (new ReflectionClass($class))->newInstanceWithoutConstructor();
    $c->input = new TestInput(); $c->session = new TestSession(); $c->load = new TestLoader();
    $c->Member_model = new TestMember(); $c->Batch_model = new TestBatch();
    $c->Member_model->member = array('id'=>1,'title_name'=>'','first_name'=>'Test','last_name'=>'Member','email'=>'test@example.test','status'=>1,'password_hash'=>password_hash('password123', PASSWORD_DEFAULT));
    return $c;
}
function destination($fn) {
    try { $fn(); } catch (RedirectResult $e) { return $e->getMessage(); }
    throw new Exception('Expected redirect');
}
foreach (array(NULL, '', '0', '-1', '3x', '1.2', array('1'), 'https://evil.test', '999999999999999999999999') as $value) {
    check(training_batch_id($value) === 0, 'Invalid batch accepted');
}
check(training_batch_id('12') === 12, 'Valid batch rejected');
$c = controller('Auth'); $c->input->get = array('batch_id'=>'12'); $c->login();
check($c->load->data['batch_id'] === 12, 'GET context lost');
$c->input->post = array('batch_id'=>'12','email'=>'test@example.test','password'=>'wrong'); $c->login();
check($c->load->data['batch_id'] === 12 && $c->load->data['error'] !== '', 'Failed login context lost');
$c->input->post['password'] = 'password123';
check(destination(function() use ($c) { $c->login(); }) === 'home/detail/equipment-and-lab?batch_id=12#register', 'Wrong login return');
check($c->session->userdata('member')['id'] === 1, 'Login session missing');
$c->Batch_model->open = FALSE;
check(destination(function() use ($c) { $c->login(); }) === 'dashboard/courses' && $c->session->flashdata('error'), 'Closed batch not handled');
unset($c->input->post['batch_id']);
check(destination(function() use ($c) { $c->login(); }) === '', 'Ordinary login changed');
$c = controller('Auth'); $c->input->post = array('batch_id'=>'12','email'=>'bad'); $c->register();
check($c->load->data['batch_id'] === 12 && $c->load->data['error'] !== '', 'Failed signup context lost');
$c->input->post = array('batch_id'=>'12','title_name'=>'Mr','first_name'=>'Test','last_name'=>'Member','position_name'=>'Staff','organization_name'=>'School','email'=>'test@example.test','phone'=>'1234567890','password'=>'password123','confirm_password'=>'password123','accept_terms'=>'1');
check(destination(function() use ($c) { $c->register(); }) === 'auth/login?batch_id=12', 'Signup context lost');
$c = controller('Dashboard'); $c->input->get = array('batch_id'=>'12');
check(destination(function() use ($c) { $c->courses(); }) === 'auth/login?batch_id=12', 'Expired session context lost');
foreach (array('success','closed','save_failure','invalid') as $case) {
    $c=controller('Dashboard'); $c->session->data['member']=array('id'=>1); $c->input->get['batch_id']=$case==='invalid'?'12bad':'12';
    if ($case==='closed') $c->Batch_model->open=FALSE;
    if ($case==='save_failure') $c->Batch_model->saved=FALSE;
    check(destination(function() use ($c) { $c->courses(); })==='dashboard/courses', 'Registration destination wrong');
    check($case==='success' ? (bool)$c->session->flashdata('success') : (!$c->session->flashdata('success') && $c->session->flashdata('error')), 'Registration message wrong: '.$case);
}
class DetailRenderer {
    public $input, $session, $load;
    function __construct() { $this->input = new TestInput(); $this->session = new TestSession(); $this->load = new TestLoader(); }
    function render($batches) {
        $course = (object) array('id'=>1,'title'=>'Course','description'=>'','short_description'=>'','training_type'=>'onsite','capacity'=>20,'fee'=>0,'cover_image'=>'','duration_text'=>'','level'=>'','location'=>'');
        ob_start(); include __DIR__.'/../application/views/frontend/detail.php'; return ob_get_clean();
    }
}
$r = new DetailRenderer();
$b1 = (object) array('id'=>12,'batch_no'=>'A','start_date'=>'2026-10-01','end_date'=>'2026-10-02','capacity'=>20,'status'=>1);
$b2 = clone $b1; $b2->id=13; $b2->batch_no='B';
$html=$r->render(array($b1));
check(strpos($html, '<select') === FALSE && strpos($html, 'เลือกรอบอบรม') === FALSE, 'Single batch must not show selector or label');
check(strpos($html, '<p>รอบอบรม: A — 1-2 ต.ค. 2569</p>') !== FALSE, 'Single batch text/date missing');
check(strpos($html, '<input type="hidden" name="batch_id" value="12">') !== FALSE && strpos($html, 'example.test/index.php/auth/login') !== FALSE, 'Guest/single batch submission wrong');
$r->session->data['member']=array('id'=>1);
$html=$r->render(array($b1));
check(strpos($html, '<input type="hidden" name="batch_id" value="12">') !== FALSE && strpos($html, 'example.test/index.php/dashboard/courses') !== FALSE, 'Member/single batch submission wrong');
$escaped=clone $b1; $escaped->batch_no='<b>Round & One</b>';
$html=$r->render(array($escaped));
check(strpos($html, '<p>รอบอบรม: &lt;b&gt;Round &amp; One&lt;/b&gt; — ') !== FALSE, 'Single batch text is not escaped');
$r->session->data['member']=array('id'=>1); $r->input->get['batch_id']='13';
$html=$r->render(array($b1,$b2));
check(strpos($html, '<select id="registration-batch" name="batch_id" required>') !== FALSE && strpos($html, 'value="13" selected') !== FALSE && strpos($html, 'example.test/index.php/dashboard/courses') !== FALSE, 'Member/multiple batch UI wrong');
$r->input->get['batch_id']='99'; $html=$r->render(array($b1,$b2));
check(strpos($html, ' selected') === FALSE, 'Foreign batch selected');
$html=$r->render(array()); check(strpos($html, 'disabled') !== FALSE && strpos($html, 'name="batch_id"') === FALSE, 'Empty batches allow registration');
class TestDB {
    public $db_debug=TRUE, $events=array(), $healthy=TRUE;
    function trans_begin() { $this->events[]='begin'; return TRUE; }
    function trans_rollback() { $this->events[]='rollback'; return TRUE; }
    function trans_status() { return $this->healthy; }
    function trans_commit() { $this->events[]='commit'; return TRUE; }
}
class AtomicBatch extends Batch_model {
    protected function snapshot_registration_fee($id,$batch,$option,$revision) { return TRUE; }
    public $ready=TRUE, $open=TRUE, $id=42, $participant=TRUE, $throws=FALSE;
    function registrations_table_exists() { return $this->ready; }
    function participants_table_exists() { return $this->ready; }
    function get_selectable_frontend_batch($id) { return $this->open; }
    function create_member_registration($member_id,$batch_id) { return $this->id; }
    function ensure_member_registration_participant($id,$member) { if ($this->throws) throw new RuntimeException('Simulated database error'); return $this->participant; }
}
foreach (array('success','participant_failure','registration_failure','closed','missing_tables','sql_failure','exception') as $case) {
    $m=new AtomicBatch(); $m->db=new TestDB();
    if ($case==='participant_failure') $m->participant=FALSE;
    if ($case==='registration_failure') $m->id=FALSE;
    if ($case==='closed') $m->open=FALSE;
    if ($case==='missing_tables') $m->ready=FALSE;
    if ($case==='exception') $m->throws=TRUE;
    if ($case==='sql_failure') $m->db->healthy=FALSE;
    $result=$m->register_member_with_participant(array('id'=>1),12);
    check(($case==='success' ? $result===42 : $result===FALSE), 'Atomic result wrong: '.$case);
    $expected=$case==='missing_tables' ? array() : array('begin',$case==='success'?'commit':'rollback');
    check($m->db->events===$expected && $m->db->db_debug===TRUE, 'Transaction wrong: '.$case);
}
// Render both auth pages: hidden fields and cross-links must preserve the batch.
foreach (array('login','register') as $page) {
    $c=controller('Auth'); $c->input->get['batch_id']='12'; $c->$page();
    $render=function($page,$data) { extract($data); ob_start(); include __DIR__.'/../application/views/frontend/'.$page.'.php'; return ob_get_clean(); };
    $html=$render($page,$c->load->data);
    check(strpos($html, 'name="batch_id" value="12"')!==FALSE, 'Hidden batch missing: '.$page);
    $other=$page==='login'?'register':'login';
    check(strpos($html, 'auth/'.$other.'?batch_id=12')!==FALSE, 'Auth cross-link context lost: '.$page);
}
// Exercise existing deduplication queries, not an overridden create method.
class RegistrationMemoryDB extends TestDB {
    public $rows=array(), $filters=array(), $lastId=0;
    function table_exists($table) { return TRUE; }
    function where($key,$value) { $this->filters[$key]=$value; return $this; }
    function select($fields) { return $this; }
    function limit($limit) { return $this; }
    function matches($table) {
        $rows=isset($this->rows[$table])?$this->rows[$table]:array(); $filters=$this->filters; $this->filters=array();
        return array_values(array_filter($rows,function($row) use ($filters) { foreach($filters as $k=>$v) if($row[$k]!=$v)return FALSE; return TRUE; }));
    }
    function count_all_results($table) { return count($this->matches($table)); }
    function get($table) { $rows=$this->matches($table); return new MemoryResult($rows); }
    function insert($table,$row) { $row['id']=++$this->lastId; $this->rows[$table][]=$row; return TRUE; }
    function insert_id() { return $this->lastId; }
    function field_exists($field,$table) { return TRUE; }
}
class MemoryResult {
    private $rows;
    function __construct($rows) { $this->rows=$rows; }
    function row() { return $this->rows ? (object)$this->rows[0] : NULL; }
}
class DuplicateBatch extends Batch_model {
    protected function snapshot_registration_fee($id,$batch,$option,$revision) { return TRUE; }
    function get_selectable_frontend_batch($id) { return TRUE; }
}
$m=new DuplicateBatch(); $m->db=new RegistrationMemoryDB();
$member=array('id'=>1,'email'=>'member@example.test');
$id=$m->register_member_with_participant($member,12);
check($id && $m->register_member_with_participant($member,12)===$id, 'Repeat registration changed identity');
check(count($m->db->rows['training_registrations'])===1 && count($m->db->rows['training_registration_participants'])===1, 'Repeat registration duplicated records');
echo "PASS: registration context, redirects, detail rendering, and atomic failure handling\n";
