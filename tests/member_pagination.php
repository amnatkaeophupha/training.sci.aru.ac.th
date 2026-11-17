<?php
// Run: php tests/member_pagination.php. No live database or member changes.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__.'/../system/');
require __DIR__.'/../application/helpers/admin_permissions_helper.php';
class CI_Controller {}
class CI_Model {}
class RedirectResult extends Exception {}
function redirect($url) { throw new RedirectResult($url); }
function site_url($url = '') { return '/training/index.php/'.$url; }
function html_escape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function log_message($level, $message) {}
function check($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function &get_instance() { return $GLOBALS['controller']; }
require __DIR__.'/../application/controllers/Admin/Members.php';
require __DIR__.'/../application/models/Member_model.php';
require __DIR__.'/../system/libraries/Pagination.php';
class InputDouble {
    public $data;
    function get($key, $clean = FALSE) { return isset($this->data[$key]) ? $this->data[$key] : NULL; }
}
class ConfigDouble { function item($key) { return $key === 'url_suffix' ? '' : FALSE; } }
class LangDouble { function line($key) { return FALSE; } }
class LoaderDouble {
    public $data;
    function language($name) {}
    function library($name) {}
    function view($name, $data = array()) { $this->data = $data; }
}
class ModelDouble {
    public $total, $calls = array();
    function count_admin_list($q) { $this->calls[] = array('count', $q); return $this->total; }
    function get_admin_list($q, $limit, $offset) {
        $this->calls[] = array('list', $q, $limit, $offset);
        return $this->total ? range($this->total - $offset, max(1, $this->total - $offset - $limit + 1)) : array();
    }
    function get_admin_stats() { return array('total'=>999, 'active'=>998, 'inactive'=>1); }
}
function controller($total, $get) {
    $c = (new ReflectionClass('Members'))->newInstanceWithoutConstructor();
    $c->input = new InputDouble(); $c->input->data = $get;
    $c->load = new LoaderDouble(); $c->config = new ConfigDouble(); $c->lang = new LangDouble();
    $c->Member_model = new ModelDouble(); $c->Member_model->total = $total;
    $GLOBALS['controller'] = $c;
    $c->pagination = new CI_Pagination();
    return $c;
}
foreach (array(0, 1, 100, 101, 250) as $total) {
    $seen = array();
    $pages = max(1, (int) ceil($total / 100));
    for ($page = 1; $page <= $pages; $page++) {
        $c = controller($total, array('page'=>(string)$page)); $c->index(); $data = $c->load->data;
        check(count($data['members']) === min(100, max(0, $total - ($page - 1) * 100)), 'Wrong page length');
        check($data['range_start'] === ($total ? ($page - 1) * 100 + 1 : 0), 'Wrong start');
        check($data['range_end'] === min($page * 100, $total), 'Wrong end');
        check(($data['pagination_links'] !== '') === ($pages > 1), 'Wrong pagination visibility');
        if ($pages > 1) check(strpos($data['pagination_links'], 'aria-current="page"><span class="page-link">'.$page.'</span>') !== FALSE, 'Active page incorrect');
        $seen = array_merge($seen, $data['members']);
    }
    check(count(array_unique($seen)) === $total && count($seen) === $total, 'Missing/duplicated members');
}
$c = controller(250, array('q'=>'  ชื่อ & test  ', 'page'=>'2')); $c->index(); $data = $c->load->data;
check($data['stats']['total'] === 999 && $data['total_members'] === 250, 'Global stats changed to filtered stats');
check($c->Member_model->calls[1] === array('list', 'ชื่อ & test', 100, 100), 'Search/offset not passed to model');
check(strpos(html_entity_decode($data['pagination_links'], ENT_QUOTES, 'UTF-8'), 'q='.urlencode('ชื่อ & test').'&page=3') !== FALSE, 'Search lost from next link');
$c = controller(250, array()); $c->index();
check($c->load->data['range_start'] === 1, 'Default request did not start on page one');
foreach (array('-1'=>'1', '0'=>'1', 'abc'=>'1', '1.5'=>'1', '999'=>'3', '999999999999999999999999'=>'3', '02'=>'2') as $bad=>$expected) {
    $c = controller(250, array('page'=>(string)$bad, 'q'=>'test'));
    try { $c->index(); throw new RuntimeException('Expected canonical redirect'); }
    catch (RedirectResult $e) { check($e->getMessage() === '/training/index.php/admin/members?q=test&page='.$expected, 'Wrong canonical page'); }
}
$c = controller(250, array('page'=>array('2')));
try { $c->index(); throw new RuntimeException('Array page accepted'); }
catch (RedirectResult $e) { check(strpos($e->getMessage(), 'page=1') !== FALSE, 'Array page not normalized'); }
// Exercise real model methods to ensure count/list share search and use SQL limits.
class QueryDouble {
    public $calls = array();
    function __call($method, $args) { $this->calls[] = array($method, $args); return $this; }
    function count_all_results() { return 250; }
    function result() { return array(); }
}
$model = (new ReflectionClass('Member_model'))->newInstanceWithoutConstructor();
$model->db = new QueryDouble(); $model->get_admin_list(' test ', 100, 200); $list = $model->db->calls;
$model->db = new QueryDouble(); check($model->count_admin_list(' test ') === 250, 'Count failed'); $count = $model->db->calls;
$search = function($calls) { return array_values(array_filter($calls, function($call) { return in_array($call[0], array('group_start','like','or_like','group_end'), TRUE); })); };
check($search($list) === $search($count), 'Count and list search differ');
check(in_array(array('limit', array(100,200)), $list, TRUE), 'SQL limit/offset missing');
check(in_array(array('order_by', array('id','DESC')), $list, TRUE), 'Member ordering changed');
class SessionDouble {
    function flashdata($key) { return NULL; }
    function userdata($key) { return 'staff'; }
}
class Renderer {
    public $load, $session;
    function __construct() { $this->load = new LoaderDouble(); $this->session = new SessionDouble(); }
    function render($data) {
        extract($data); ob_start(); include __DIR__.'/../application/views/admins/members.php'; return ob_get_clean();
    }
}
$data['members'] = array();
$html = (new Renderer())->render($data);
check(strpos($html, 'แสดงรายการ 101–200 จากทั้งหมด 250 รายการ') !== FALSE, 'Range summary not rendered');
check(strpos($html, 'aria-label="หน้ารายชื่อสมาชิก"') !== FALSE, 'Accessible pagination missing');
check(strpos($html, 'method="get" action="/training/index.php/admin/members"') !== FALSE, 'New search does not reset page');
check(strpos($html, 'แสดง 50 รายการล่าสุด') === FALSE, 'Old list limit message remains');
echo "PASS: member pagination sizes, continuity, search links, ranges, invalid pages and SQL limits\n";
