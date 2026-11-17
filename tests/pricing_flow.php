<?php
// Controller and rendered selection form checks; no database writes.
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
define('BASEPATH',__DIR__.'/../system/');
require __DIR__.'/../application/helpers/registration_helper.php';
require __DIR__.'/../application/helpers/pricing_helper.php';
class CI_Controller {}
class RedirectResult extends Exception {}
function redirect($url) { throw new RedirectResult($url); }
function show_404() { throw new RuntimeException('404'); }
function site_url($url='') { return '/training/index.php/'.$url; }
function html_escape($value) { return htmlspecialchars($value,ENT_QUOTES,'UTF-8'); }
function check($ok,$message) { if (!$ok) throw new RuntimeException($message); }
require __DIR__.'/../application/controllers/Dashboard.php';
class InputDouble {
    public $verb='GET', $query=array('batch_id'=>'11'), $data=array();
    function get($key,$clean=FALSE) { return isset($this->query[$key])?$this->query[$key]:NULL; }
    function post($key,$clean=FALSE) { return isset($this->data[$key])?$this->data[$key]:NULL; }
    function method($upper=FALSE) { return $upper?$this->verb:strtolower($this->verb); }
}
class SessionDouble {
    public $data=array('member'=>array('id'=>2),'course_fee_token'=>'valid');
    function userdata($key) { return isset($this->data[$key])?$this->data[$key]:NULL; }
    function set_userdata($key,$value) { $this->data[$key]=$value; }
    function unset_userdata($key) { unset($this->data[$key]); }
    function set_flashdata($key,$value) { $this->data[$key]=$value; }
}
class LoaderDouble {
    public $name,$data;
    function model($name) {}
    function view($name,$data=array()) { $this->name=$name; $this->data=$data; }
}
class MemberDouble { function find_by_id($id) { return array('id'=>2); } }
class BatchDouble {
    public $calls=array(), $success=TRUE;
    function get_selectable_frontend_batch($id) { return (object)array('fee_mode'=>'options'); }
    function member_is_registered($member,$batch) { return FALSE; }
    function register_member_with_participant($member,$batch,$option=NULL,$revision=NULL) { $this->calls[]=array($member,$batch,$option,$revision); return $this->success?42:FALSE; }
}
class PricingDouble {
    public $revision=2,$change=TRUE,$calls=array();
    function configuration_for_batch($id) { return (object)array('id'=>7,'title'=>'Course','batch_no'=>'1','slug'=>'course','fee_mode'=>'options','pricing_revision'=>$this->revision,'fee_options'=>array(array('id'=>3,'label'=>'Child <7>','amount'=>'200.00'),array('id'=>4,'label'=>'Adult','amount'=>'300.00'))); }
    function change_selection($registration,$member,$option) { $this->calls[]=array($registration,$member,$option); return $this->change; }
}
function controller() {
    $c=(new ReflectionClass('Dashboard'))->newInstanceWithoutConstructor(); $c->input=new InputDouble(); $c->session=new SessionDouble(); $c->load=new LoaderDouble();
    $c->Member_model=new MemberDouble(); $c->Batch_model=new BatchDouble(); $c->Pricing_model=new PricingDouble(); return $c;
}
$c=controller(); $c->courses();
check($c->load->name==='frontend/registration_fee' && !$c->Batch_model->calls,'GET created a conditional registration');
foreach(array('success','token','option','array','revision','failure') as $case) {
    $c=controller(); $c->input->verb='POST'; $c->input->data=array('fee_token'=>'valid','fee_option_id'=>'3','pricing_revision'=>'2');
    if($case==='token') $c->input->data['fee_token']='invalid';
    if($case==='option') $c->input->data['fee_option_id']='999';
    if($case==='array') $c->input->data['fee_option_id']=array('3');
    if($case==='revision') $c->Pricing_model->revision=3;
    if($case==='failure') $c->Batch_model->success=FALSE;
    try { $c->courses(); } catch(RedirectResult $e) { check($case==='success' && $e->getMessage()==='dashboard/courses','Unexpected redirect'); }
    if(in_array($case,array('token','option','array','revision'),TRUE)) check(!$c->Batch_model->calls,'Invalid selection reached registration');
    if($case!=='success') check($c->load->data['error']!=='','No visible selection error');
    else check($c->Batch_model->calls[0][2]==='3' && $c->Batch_model->calls[0][3]==='2','Server did not pass selected option/revision');
}
foreach(array('success','token','locked','get') as $case) {
    $c=controller(); $c->input->verb=$case==='get'?'GET':'POST'; $c->input->data=array('fee_token'=>'valid','fee_option_id'=>'4');
    if($case==='token') $c->input->data['fee_token']=array('invalid');
    if($case==='locked') $c->Pricing_model->change=FALSE;
    try { $c->change_fee(42); } catch(RedirectResult $e) { check($e->getMessage()==='dashboard/courses','Wrong change redirect'); }
    catch(RuntimeException $e) { check($case==='get' && $e->getMessage()==='404','Unexpected change failure'); }
    if(in_array($case,array('token','get'),TRUE)) check(!$c->Pricing_model->calls,'Invalid request reached price update');
    if($case==='success') check($c->Pricing_model->calls===array(array(42,2,'4')),'Ownership/selection not passed');
}
class ConfigDouble { function item($key) { return FALSE; } }
class Renderer {
    public $load,$config;
    function __construct() { $this->load=new LoaderDouble(); $this->config=new ConfigDouble(); }
    function render() {
        $course=(new PricingDouble())->configuration_for_batch(11); $batch_id=11; $error=''; $fee_token='valid';
        ob_start(); include __DIR__.'/../application/views/frontend/registration_fee.php'; return ob_get_clean();
    }
}
$html=(new Renderer())->render();
check(substr_count($html,'type="radio"')===2 && strpos($html,'Child &lt;7&gt;')!==FALSE,'Selection form missing options/escaping');
check(strpos($html,'name="pricing_revision" value="2"')!==FALSE && strpos($html,'name="fee_token" value="valid"')!==FALSE,'Selection protection fields missing');
echo "PASS: explicit group selection, stale quotes, POST/token guards, ownership and rendered pricing choices\n";
