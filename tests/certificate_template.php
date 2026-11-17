<?php
// Run: php tests/certificate_template.php. Validation/rendering only; no database.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__.'/../system/');
require __DIR__.'/../application/helpers/certificate_helper.php';
class CI_Controller {}
class RedirectResult extends Exception {}
function redirect($url) { throw new RedirectResult($url); }
function show_404() { throw new RuntimeException('404'); }
function site_url($url='') { return '/training/index.php/'.$url; }
function base_url($url='') { return '/training/'.$url; }
function html_escape($value) { return htmlspecialchars($value,ENT_QUOTES,'UTF-8'); }
function check($condition,$message) { if (!$condition) throw new RuntimeException($message); }
require __DIR__.'/../application/controllers/Admin/Certificates.php';
class InputDouble {
    public $values=array();
    function post($key) { return array_key_exists($key,$this->values)?$this->values[$key]:NULL; }
    function get($key,$clean=FALSE) { return NULL; }
    function method($upper=FALSE) { return 'POST'; }
}
class SessionDouble {
    public $flash=array();
    function set_flashdata($key,$value) { $this->flash[$key]=$value; }
    function flashdata($key) { return NULL; }
}
class TemplateDouble {
    public $saved, $existing;
    function get_batch($id) { return (object)array('id'=>$id); }
    function get_template($id) { return $this->existing; }
    function save_template($id,$data) { $this->saved=array($id,$data); return TRUE; }
}
function controller() {
    $c=(new ReflectionClass('Certificates'))->newInstanceWithoutConstructor();
    $c->input=new InputDouble(); $c->session=new SessionDouble(); $c->Certificate_model=new TemplateDouble();
    $c->Certificate_model->existing=(object)array_merge(certificate_number_defaults(),array('background_path'=>'fixture.png'));
    return $c;
}
foreach(array(
    array('number_x',-1),array('number_y',101),array('number_width',0),array('number_font_size',5),array('number_font_size',73),
    array('number_x','not numeric'),array('number_x',array('1')),array('number_font_color','#12345'),array('number_align','Z'),
    array('number_width',100),array('number_y',99)
) as $invalid) {
    $c=controller(); $c->input->values[$invalid[0]]=$invalid[1];
    $_FILES=array('background'=>array('name'=>'must-not-be-uploaded.png'));
    try { $c->template(11); } catch (RedirectResult $e) {}
    check($c->Certificate_model->saved===NULL && isset($c->session->flash['error']),'Invalid settings reached upload/save');
}
$_FILES=array();
foreach(array('L','C','R') as $alignment) {
    $c=controller(); $c->input->values=array_merge(certificate_number_defaults(),array('number_x'=>15,'number_y'=>20,'number_width'=>40,'number_font_size'=>24,'number_font_color'=>'#123abc','number_align'=>$alignment));
    try { $c->template(11); } catch (RedirectResult $e) {}
    check($c->Certificate_model->saved[0]===11 && $c->Certificate_model->saved[1]['number_align']===$alignment,'Valid settings did not save');
}
$c=controller(); $c->Certificate_model->existing->number_x=12;
try { $c->template(11); } catch (RedirectResult $e) {}
check($c->Certificate_model->saved[1]['number_x']===12.0,'Omitted fields overwrote existing settings');
class LoaderDouble { function view($name) {} }
class Renderer {
    public $load,$session;
    function __construct() { $this->load=new LoaderDouble(); $this->session=new SessionDouble(); }
    function render($template) {
        $batch=(object)array('id'=>11,'course_title'=>'Example course','batch_no'=>'1');
        $batches=array(); $roster=array();
        ob_start(); include __DIR__.'/../application/views/admins/certificates.php'; return ob_get_clean();
    }
}
foreach(array(NULL,(object)array('background_path'=>'fixture.png','name_x'=>10,'name_y'=>45,'name_width'=>80,'font_size'=>30,'font_color'=>'#172033')) as $template) {
    $html=(new Renderer())->render($template);
    foreach(array_keys(certificate_number_defaults()) as $key) check(strpos($html,'name="'.$key.'"')!==FALSE,'Missing number control');
    check(strpos($html,'id="name-box"')!==FALSE && strpos($html,'id="number-box"')!==FALSE,'Preview boxes missing');
    check(strpos($html,'SCI-')!==FALSE && strpos($html,'certificate-template.js')!==FALSE,'Preview initialization missing');
}
echo "PASS: certificate number validation before upload, alignments, saved defaults and legacy/new editor rendering\n";
