<?php
// Integration test: creates and drops a uniquely named disposable database only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__.'/../system/');
define('ENVIRONMENT', 'testing');
require __DIR__.'/../application/config/database.php';
require __DIR__.'/../application/helpers/certificate_helper.php';
mysqli_report(MYSQLI_REPORT_OFF);
function check($condition,$message) { if (!$condition) throw new RuntimeException($message); }
function log_message($level,$message) {}
class TestResult {
    private $rows;
    function __construct($rows) { $this->rows=$rows; }
    function row() { return isset($this->rows[0])?(object)$this->rows[0]:NULL; }
    function num_rows() { return count($this->rows); }
}
class TestDatabase {
    public $db_debug=TRUE, $connection, $fail_insert=FALSE;
    private $where=array(), $healthy=TRUE;
    function __construct($config,$schema) {
        check((bool)preg_match('/^training_cert_test_[a-f0-9]{12}$/D',$schema),'Unsafe test database name');
        $this->connection=new mysqli($config['hostname'],$config['username'],$config['password'],$schema);
        check(!$this->connection->connect_errno,'Could not connect to disposable test database');
        $this->connection->set_charset('utf8mb4');
    }
    function query($sql,$params=array()) {
        $stmt=$this->connection->prepare($sql);
        if (!$stmt) { $this->healthy=FALSE; return FALSE; }
        if ($params) $stmt->bind_param(str_repeat('s',count($params)),...$params);
        if (!$stmt->execute()) { $this->healthy=FALSE; return FALSE; }
        $result=$stmt->get_result();
        return $result ? new TestResult($result->fetch_all(MYSQLI_ASSOC)) : TRUE;
    }
    function field_exists($field,$table) { return $this->query('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?',array($table,$field))->num_rows()>0; }
    function where($key,$value) { $this->where[$key]=$value; return $this; }
    function update($table,$data) {
        $sets=array(); foreach($data as $key=>$value) $sets[]=$key.'=?';
        $where=array(); foreach($this->where as $key=>$value) $where[]=$key.'=?';
        $params=array_merge(array_values($data),array_values($this->where)); $this->where=array();
        return $this->query('UPDATE '.$table.' SET '.implode(',',$sets).' WHERE '.implode(' AND ',$where),$params);
    }
    function insert($table,$data) {
        if($this->fail_insert) { $this->fail_insert=FALSE; return FALSE; }
        return $this->query('INSERT INTO '.$table.' ('.implode(',',array_keys($data)).') VALUES ('.implode(',',array_fill(0,count($data),'?')).')',array_values($data));
    }
    function trans_begin() { $this->healthy=TRUE; return $this->connection->begin_transaction(); }
    function trans_status() { return $this->healthy; }
    function trans_commit() { return $this->connection->commit(); }
    function trans_rollback() { return $this->connection->rollback(); }
}
class CI_Model { function __construct() { $this->db=$GLOBALS['fixture_db']; } }
require __DIR__.'/../application/models/Certificate_model.php';
class FixtureCertificateModel extends Certificate_model {
    public $clock='2026-11-16T12:00:00+07:00', $eligible=TRUE, $fixture_roster=array();
    protected function issue_time() { return new DateTimeImmutable($this->clock); }
    function get_template($batch) { return $this->db->query('SELECT * FROM training_certificate_templates WHERE batch_id=?',array($batch))->row(); }
    function get_eligible($batch,$participant,$invitation) { return $this->eligible?(object)array('title_name'=>'','first_name'=>'Example','last_name'=>'Recipient'):NULL; }
    function get_roster($batch) { return $this->fixture_roster; }
}
$config=$db['default'];
if (isset($argv[1]) && $argv[1]==='worker') {
    $GLOBALS['fixture_db']=new TestDatabase($config,$argv[2]);
    $model=new FixtureCertificateModel();
    check($model->issue(11,(int)$argv[3],(int)$argv[3],1),'Concurrent test issuance failed');
    echo "OK\n"; exit;
}
$schema='training_cert_test_'.bin2hex(random_bytes(6));
$admin=new mysqli($config['hostname'],$config['username'],$config['password']);
check(!$admin->connect_errno && $admin->query('CREATE DATABASE '.$schema.' CHARACTER SET utf8mb4'),'Could not create disposable test database');
try {
    $GLOBALS['fixture_db']=$fixture=new TestDatabase($config,$schema);
    $model=new FixtureCertificateModel();
    $data=array('background_path'=>'fixture.png','name_x'=>10,'name_y'=>45,'name_width'=>80,'font_size'=>30,'font_color'=>'#172033');
    check($model->save_template(11,$data),'Could not create fixture template');
    check((float)$model->get_template(11)->number_font_size===9.0,'Legacy defaults missing');
    $custom=array_merge($data,certificate_number_defaults(),array('number_x'=>12.5,'number_align'=>'L','number_font_color'=>'#123456'));
    check($model->save_template(11,$custom),'Could not save number settings');
    $saved=$model->get_template(11); check((float)$saved->number_x===12.5 && $saved->number_align==='L' && $saved->number_font_color==='#123456','Number settings not persisted');
    check($model->save_template(12,$data),'Could not create second batch template');
    check((float)$model->get_template(12)->number_x===70.707,'Template settings leaked between batches');
    $old=array('batch_id'=>11,'participant_id'=>1,'invitation_id'=>1,'certificate_no'=>'CERT-000001','active_key'=>'11-1','recipient_name'=>'Legacy','issued_at'=>'2026-01-01 00:00:00');
    check($fixture->insert('training_certificates',$old),'Could not seed legacy certificate');
    check($model->issue(11,2,2,1),'First issuance failed');
    check($fixture->query('SELECT certificate_no FROM training_certificates WHERE participant_id=2')->row()->certificate_no==='SCI-2569-000001','Wrong first number');
    check($model->issue(12,3,3,1),'Cross-batch issuance failed');
    check($fixture->query('SELECT certificate_no FROM training_certificates WHERE participant_id=3')->row()->certificate_no==='SCI-2569-000002','Sequence reset by batch');
    check($model->issue(11,1,1,1) && $model->issue(11,2,2,1),'Repeated issuance failed');
    check((int)$fixture->query('SELECT last_sequence FROM training_certificate_sequences WHERE certificate_year=2569')->row()->last_sequence===2,'Existing certificates consumed numbers');
    $fixture->fail_insert=TRUE;
    check(!$model->issue(11,4,4,1),'Insert failure ignored');
    check((int)$fixture->query('SELECT last_sequence FROM training_certificate_sequences WHERE certificate_year=2569')->row()->last_sequence===2,'Counter did not roll back');
    check($model->issue(11,4,4,1),'Retry failed');
    $fixture->query('UPDATE training_certificates SET status=2,active_key=NULL WHERE participant_id=4');
    check($model->issue(11,4,4,1),'Reissue after cancellation failed');
    check($fixture->query('SELECT certificate_no FROM training_certificates WHERE participant_id=4 AND status=1')->row()->certificate_no==='SCI-2569-000004','Cancelled number reused');
    $model->clock='2026-12-31T16:59:59+00:00'; check($model->issue(11,5,5,1),'Before midnight failed');
    $model->clock='2026-12-31T17:00:00+00:00'; check($model->issue(11,6,6,1),'New year issuance failed');
    check($fixture->query('SELECT certificate_no FROM training_certificates WHERE participant_id=6')->row()->certificate_no==='SCI-2570-000001','Thai new-year reset failed');
    $fixture->query('UPDATE training_certificate_sequences SET last_sequence=999999 WHERE certificate_year=2570');
    check(!$model->issue(11,7,7,1) && $model->last_issue_error!=='','Sequence overflow not reported');
    $seed=$old; $seed['certificate_no']='SCI-2571-000123'; $seed['active_key']='11-8'; $seed['participant_id']=8;
    check($fixture->insert('training_certificates',$seed),'Could not seed imported SCI number');
    $model->clock='2028-01-01T00:00:00+07:00'; check($model->issue(11,9,9,1),'Imported sequence resume failed');
    check($fixture->query('SELECT certificate_no FROM training_certificates WHERE participant_id=9')->row()->certificate_no==='SCI-2571-000124','Did not resume highest existing SCI number');
    $migration=file_get_contents(__DIR__.'/../Design/migrate_certificate_number_settings.sql');
    for($run=0;$run<2;$run++) {
        check($fixture->connection->multi_query($migration),'Migration failed');
        do { $result=$fixture->connection->store_result(); if($result) $result->free(); } while($fixture->connection->more_results() && $fixture->connection->next_result());
        check(!$fixture->connection->errno,'Migration statement failed');
    }
    check($fixture->query('SELECT certificate_no FROM training_certificates WHERE participant_id=1')->row()->certificate_no==='CERT-000001','Legacy number changed');
    check((int)$fixture->query('SELECT last_sequence FROM training_certificate_sequences WHERE certificate_year=2570')->row()->last_sequence===999999,'Migration reset a counter');
    // Separate processes share the disposable schema and contend on the real row lock.
    $workers=array();
    for($i=0;$i<6;$i++) {
        $pipes=array(); $process=proc_open(array(PHP_BINARY,__FILE__,'worker',$schema,(string)(100+$i)),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
        check(is_resource($process),'Could not start concurrency test'); fclose($pipes[0]); $workers[]=array($process,$pipes);
    }
    foreach($workers as $worker) {
        $out=stream_get_contents($worker[1][1]); $err=stream_get_contents($worker[1][2]); fclose($worker[1][1]); fclose($worker[1][2]);
        check(proc_close($worker[0])===0 && trim($out)==='OK','Concurrent worker failed: '.$err.$out);
    }
    $counts=$fixture->query('SELECT COUNT(*) AS total,COUNT(DISTINCT certificate_no) AS unique_numbers FROM training_certificates WHERE participant_id>=100')->row();
    check((int)$counts->total===6 && (int)$counts->unique_numbers===6,'Concurrent duplicate/missing numbers');
    $workers=array();
    $before=(int)$fixture->query('SELECT last_sequence FROM training_certificate_sequences WHERE certificate_year=2569')->row()->last_sequence;
    for($i=0;$i<3;$i++) {
        $pipes=array(); $process=proc_open(array(PHP_BINARY,__FILE__,'worker',$schema,'200'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
        check(is_resource($process),'Could not start repeated participant test'); fclose($pipes[0]); $workers[]=array($process,$pipes);
    }
    foreach($workers as $worker) {
        $out=stream_get_contents($worker[1][1]); $err=stream_get_contents($worker[1][2]); fclose($worker[1][1]); fclose($worker[1][2]);
        check(proc_close($worker[0])===0 && trim($out)==='OK','Repeated participant worker failed: '.$err.$out);
    }
    check((int)$fixture->query('SELECT COUNT(*) AS total FROM training_certificates WHERE participant_id=200')->row()->total===1,'Concurrent repeated issuance duplicated a certificate');
    check((int)$fixture->query('SELECT last_sequence FROM training_certificate_sequences WHERE certificate_year=2569')->row()->last_sequence===$before+1,'Repeated issuance consumed extra numbers');
    $model->clock='2029-01-01T00:00:00+07:00';
    $ready=array('registration_status'=>5,'profile_confirmed_at'=>'2029-01-01','completed_at'=>'2029-01-01','certificate_id'=>NULL,'participant_id'=>20,'invitation_id'=>20);
    $model->fixture_roster=array((object)$ready,(object)array_merge($ready,array('registration_status'=>2,'participant_id'=>21)),(object)array_merge($ready,array('completed_at'=>NULL,'participant_id'=>22)),(object)array_merge($ready,array('certificate_id'=>1,'participant_id'=>23)));
    check($model->issue_batch(11,1)===1,'Bulk issuance did not respect eligibility/existing certificates');
    check($fixture->query('SELECT certificate_no FROM training_certificates WHERE participant_id=20')->row()->certificate_no==='SCI-2572-000001','Bulk issuance did not use annual numbering');
    $fixture->query('INSERT INTO training_certificate_sequences VALUES (2573,999999)');
    $model->clock='2030-01-01T00:00:00+07:00';
    $model->fixture_roster=array((object)array_merge($ready,array('participant_id'=>24,'invitation_id'=>24)));
    check($model->issue_batch(11,1)===0 && $model->last_issue_error!=='','Bulk sequence overflow not reported');
    check($fixture->db_debug===TRUE,'Database error setting not restored');
    check(certificate_number_text('SCI-2569-000001')==='SCI-2569-000001','SCI display prefix incorrect');
    check(strpos(certificate_number_text('CERT-000001'),'CERT-000001')!==FALSE,'Legacy display lost');
    echo "PASS: real MariaDB annual numbering, concurrency, rollback, reissue, Thai timezone, overflow, template persistence and repeatable migration\n";
} finally {
    // The validated name was generated above and refers only to this test's database.
    check((bool)preg_match('/^training_cert_test_[a-f0-9]{12}$/D',$schema),'Unsafe cleanup target');
    $admin->query('DROP DATABASE '.$schema);
}
