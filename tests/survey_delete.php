<?php
// Run: php tests/survey_delete.php. Only doubles; no real survey is deleted.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/staff_permissions.php';
class CI_Model {}
require __DIR__.'/../application/models/Survey_model.php';
class SurveyRows {
    private $count;
    function __construct($count) { $this->count=$count; }
    function num_rows() { return $this->count; }
}
class SurveyDatabaseDouble {
    public $db_debug=TRUE, $calls=array(), $case='', $selected;
    function table_exists($table) { return TRUE; }
    function trans_begin() { $this->calls[]='begin'; return $this->case!=='begin'; }
    function trans_status() { return $this->case!=='transaction'; }
    function trans_commit() { $this->calls[]='commit'; return $this->case!=='commit'; }
    function trans_rollback() { $this->calls[]='rollback'; return TRUE; }
    function query($sql,$params) {
        check($params===array(7),'Survey query escaped target ID');
        $this->calls[]=$sql;
        if($this->case==='query') return FALSE;
        if(strpos($sql,'FROM training_surveys ')!==FALSE) return new SurveyRows($this->case==='missing'?0:1);
        if(strpos($sql,'FROM training_survey_questions ')!==FALSE) return new SurveyRows($this->case==='questions'?1:0);
        if(strpos($sql,'FROM training_survey_assignments ')!==FALSE) return new SurveyRows($this->case==='assignments'?1:0);
        throw new RuntimeException('Unexpected SQL');
    }
    function where($key,$value) { check($key==='id' && $value===7,'Wrong survey delete scope'); $this->selected=$value; return $this; }
    function delete($table) { check($table==='training_surveys' && $this->selected===7,'Unrelated data deleted'); $this->calls[]='delete'; return $this->case!=='delete'; }
}
function log_message($level,$message) {}
foreach(array(''=>'deleted','missing'=>'missing','questions'=>'has_questions','assignments'=>'in_use','begin'=>'error','query'=>'error','delete'=>'error','transaction'=>'error','commit'=>'error') as $case=>$expected) {
    $model=new Survey_model(); $model->db=new SurveyDatabaseDouble(); $model->db->case=$case;
    check($model->delete_empty_survey(7)===$expected,'Wrong deletion result: '.$case);
    check($model->db->db_debug===TRUE,'Debug configuration not restored');
    if(in_array($case,array('missing','questions','assignments','query','begin'),TRUE)) check(!in_array('delete',$model->db->calls,TRUE),'Nonempty/used/missing survey reached delete');
    if($case!=='begin' && $expected!=='deleted') check(in_array('rollback',$model->db->calls,TRUE),'Failed deletion did not rollback');
    if($expected==='deleted') check(in_array('commit',$model->db->calls,TRUE),'Successful deletion not committed');
}
class SurveyModelResultDouble {
    public $result='deleted', $called=FALSE;
    function delete_empty_survey($id) { $this->called=TRUE; check($id===7,'Wrong controller target'); return $this->result; }
}
class SurveyInputDouble extends InputDouble {
    public $token='valid';
    function post($key,$clean=FALSE) { return $key==='delete_token'?$this->token:NULL; }
}
foreach(array('deleted','missing','has_questions','in_use','error','invalid_token','get','staff') as $case) {
    $GLOBALS['session']=new SessionDouble($case==='staff'?'staff':'admin'); $c=new Surveys();
    $c->Survey_model=new SurveyModelResultDouble(); $c->input=new SurveyInputDouble();
    $c->Survey_model->result=$case;
    if($case==='invalid_token') $c->input->token=array('bad');
    if($case==='get') $c->input->verb='GET';
    try { $c->delete(7); throw new RuntimeException('Expected redirect'); }
    catch(RedirectResult $e) { check($e->getMessage()==='admin/surveys','Wrong survey redirect'); }
    catch(RuntimeException $e) { check($case==='get' && $e->getMessage()==='404','Unexpected action error'); }
    if(in_array($case,array('invalid_token','get','staff'),TRUE)) check(!$c->Survey_model->called,'Rejected request reached deletion');
    if($case==='deleted') check((bool)$c->session->flashdata('success'),'Missing success message');
    elseif($case!=='get') check((bool)$c->session->flashdata('error'),'Missing failure message');
}
echo "PASS: empty survey deletion, usage/permission guards, POST/token checks and transaction failures\n";
